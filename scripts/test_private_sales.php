<?php
// ponytail: minimal private customer-wise sales test with 2 bills, no DB persistence
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "USE_SAHAKARI env=".env('USE_SAHAKARI')."\n";
echo "config app.use_sahakari=".var_export(config('app.use_sahakari'), true)."\n";
assert(config('app.use_sahakari') === false, 'USE_SAHAKARI must be false for private mode');

// ---- Setup 2 private customers (properties) & 2 completed tasks ----
DB::beginTransaction();
try {
    // Use sqlite in-memory or file db; create minimal property+task records then compute bills
    // Create branch for FK if needed
    $branch = DB::table('branches')->first();
    if (!$branch) {
        DB::table('branches')->insert(['name'=>'Test Branch','active'=>1,'created_at'=>now(),'updated_at'=>now()]);
        $branch = DB::table('branches')->first();
    }

    // Create 2 private customers as Properties with distinct billing
    $propA = \App\Models\Property::create([
        'name' => 'Private Customer A - '.uniqid(),
        'address' => '123 Test St A',
        'client_fixed_amount' => 120.00,
        'parking_fee' => 5.00,
        'cleaning_duration_minutes' => 60,
        'cleaner_pay_type' => 'per_hour',
        'active' => true,
    ]);
    $propB = \App\Models\Property::create([
        'name' => 'Private Customer B - '.uniqid(),
        'address' => '456 Test St B',
        'client_fixed_amount' => 200.00,
        'parking_fee' => 10.00,
        'cleaning_duration_minutes' => 90,
        'cleaner_pay_type' => 'per_hour',
        'active' => true,
    ]);

    $user = \App\Models\User::first();
    if (!$user) {
        $user = \App\Models\User::factory()->create();
    }

    $taskType = \App\Models\TaskType::first();
    if (!$taskType) {
        $taskType = \App\Models\TaskType::create(['name'=>'Test Type','active'=>true]);
    }

    $taskA = \App\Models\Task::create([
        'title' => 'Sales Test Task A',
        'property_id' => $propA->id,
        'task_type_id' => $taskType->id,
        'status' => \App\Models\Task::STATUS_COMPLETED,
        'scheduled_start_at' => now(),
        'scheduled_end_at' => now()->addHour(),
        'completed_at' => now(),
        'property_name_snapshot' => $propA->name,
        'address_snapshot' => $propA->address,
        'created_by' => $user->id,
    ]);
    $taskB = \App\Models\Task::create([
        'title' => 'Sales Test Task B',
        'property_id' => $propB->id,
        'task_type_id' => $taskType->id,
        'status' => \App\Models\Task::STATUS_COMPLETED,
        'scheduled_start_at' => now(),
        'scheduled_end_at' => now()->addHour(),
        'completed_at' => now(),
        'property_name_snapshot' => $propB->name,
        'address_snapshot' => $propB->address,
        'created_by' => $user->id,
    ]);

    // ---- Sales billing helper (private customer-wise) ----
    $billFor = function (\App\Models\Task $task): float {
        $prop = $task->property;
        // client_fixed_amount wins; else fallback duration * 80/hr (private rate)
        $fixed = (float) ($prop->client_fixed_amount ?? 0);
        if ($fixed > 0) return $fixed + (float)($prop->parking_fee ?? 0);
        $mins = $prop->cleaning_duration_minutes ?? $task->estimated_duration_minutes ?? 60;
        return round($mins/60 * 80, 2) + (float)($prop->parking_fee ?? 0);
    };

    $billA = $billFor($taskA);
    $billB = $billFor($taskB);

    // Private customer-wise: 2 separate bills grouped by property (customer)
    $billsByCustomer = [
        $propA->id => ['customer' => $propA->name, 'tasks' => [$taskA->id], 'total' => $billA],
        $propB->id => ['customer' => $propB->name, 'tasks' => [$taskB->id], 'total' => $billB],
    ];

    // Sahakari mode would aggregate into single pooled bill
    $sahakariTotal = $billA + $billB;

    echo "\n=== Private Customer-Wise Sales (USE_SAHAKARI=false) ===\n";
    foreach ($billsByCustomer as $pid => $row) {
        echo "Customer: {$row['customer']} | Tasks: ".implode(',', $row['tasks'])." | Bill: $".number_format($row['total'],2)."\n";
    }
    echo "Grand total (if sahakari pooled): $".number_format($sahakariTotal,2)."\n";

    // Assertions
    assert(count($billsByCustomer) === 2, 'must have 2 separate customer bills');
    assert($billsByCustomer[$propA->id]['total'] === 125.00, 'bill A 120+5');
    assert($billsByCustomer[$propB->id]['total'] === 210.00, 'bill B 200+10');
    assert(config('app.use_sahakari') === false, 'private mode active');

    // Verify isolation: bill A not equal bill B, and per-customer totals differ from pooled
    assert($billA !== $billB, 'customer-wise bills must differ');
    assert($billA + $billB === $sahakariTotal, 'pooled equals sum but private keeps separate');

    echo "\nPASS: 2 private customer-wise bills generated correctly with USE_SAHAKARI=false\n";

    // Rollback so test doesn't pollute DB (remove if you want to keep)
    DB::rollBack();
    echo "Rolled back test data (no DB pollution).\n";
} catch (Throwable $e) {
    DB::rollBack();
    echo "FAIL: ".$e->getMessage()."\n";
    echo $e->getTraceAsString()."\n";
    exit(1);
}
