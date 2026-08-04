<?php

namespace Tests\Feature;

use App\Domain\Reports\DashboardWidgets;
use App\Jobs\GenerateExport;
use App\Models\ExportJob;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use App\Services\Settings\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportsModuleTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function supervisor(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
    }

    private function cleaner(): User
    {
        return User::factory()->create(['role' => User::ROLE_CLEANER]);
    }

    public function test_dashboard_widgets_per_role(): void
    {
        $admin = $this->admin();
        $cleaner = $this->cleaner();
        Task::create(['title' => 'X', 'status' => Task::STATUS_SCHEDULED, 'scheduled_start_at' => now()->addDay(), 'scheduled_end_at' => now()->addDay()->addHour()]);

        $widgets = app(DashboardWidgets::class)->for($admin);
        $this->assertNotEmpty($widgets['stats']);
        $this->assertSame(1, $widgets['stats'][0]['value'] ?? null);

        $cleanerWidgets = app(DashboardWidgets::class)->for($cleaner);
        $this->assertArrayHasKey('today', $cleanerWidgets);

        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee('Tasks today');
        $this->actingAs($cleaner)->get(route('dashboard'))->assertOk();
    }

    public function test_reports_page_and_rows(): void
    {
        $admin = $this->admin();
        Task::create(['title' => 'Reportable', 'status' => Task::STATUS_APPROVED, 'scheduled_start_at' => now()->addDay(), 'scheduled_end_at' => now()->addDay()->addHour()]);

        $response = $this->actingAs($admin)->get(route('reports').'?type=tasks');
        $response->assertOk()->assertSee('Reportable');

        $response = $this->actingAs($admin)->get(route('reports').'?type=attendance')->assertOk();
        $this->assertStringContainsString('Event ID', $response->getContent());
    }

    public function test_export_job_writes_csv(): void
    {
        Storage::fake('evidence');
        Queue::fake();

        $admin = $this->admin();

        $this->actingAs($admin)->post(route('reports.export'), ['type' => 'tasks'])->assertRedirect();

        $job = ExportJob::first();
        $this->assertNotNull($job);
        $this->assertSame('pending', $job->status);
        Queue::assertPushed(GenerateExport::class);

        // Run the job synchronously.
        (new GenerateExport($job->id))->handle(app(\App\Domain\Reports\ReportService::class));

        $job->refresh();
        $this->assertSame(ExportJob::STATUS_DONE, $job->status);
        Storage::disk('evidence')->assertExists($job->file_path);
    }

    public function test_export_download_requires_owner_and_permission(): void
    {
        Storage::fake('evidence');

        $admin = $this->admin();
        $other = $this->admin();
        $job = ExportJob::create([
            'type' => 'tasks',
            'status' => ExportJob::STATUS_DONE,
            'file_path' => 'exports/test.csv',
            'requested_by' => $admin->id,
            'requested_at' => now(),
        ]);
        Storage::disk('evidence')->put('exports/test.csv', 'a,b\n1,2');

        $this->actingAs($other)->get(route('reports.download', $job))->assertForbidden();
        $this->actingAs($admin)->get(route('reports.download', $job))->assertOk();
    }

    public function test_settings_service_cache_and_write(): void
    {
        $service = app(SettingsService::class);

        $this->assertNull($service->get('test_key'));
        $service->set('test_key', 'value1');
        $this->assertSame('value1', $service->get('test_key'));
        $this->assertDatabaseHas('settings', ['key' => 'test_key', 'value' => 'value1']);

        // Write invalidates cache.
        $service->set('test_key', 'value2');
        $this->assertSame('value2', $service->get('test_key'));
    }

    public function test_settings_admin_page(): void
    {
        $admin = $this->admin();
        Setting::create(['scope' => Setting::SCOPE_ORGANIZATION, 'key' => 'default_check_in_radius_meters', 'value' => '150', 'description' => 'Fallback radius']);

        $this->actingAs($admin)->get(route('settings'))->assertOk()->assertSee('default_check_in_radius_meters');

        $this->actingAs($admin)->post(route('settings.update'), [
            'settings' => ['organization:default_check_in_radius_meters' => '250'],
        ])->assertRedirect(route('settings'));

        $this->assertSame('250', app(SettingsService::class)->get('default_check_in_radius_meters'));
    }

    public function test_audit_viewer_permission_gated(): void
    {
        $admin = $this->admin();
        $supervisor = $this->supervisor();

        $this->actingAs($supervisor)->get(route('audit'))->assertForbidden();
        $this->actingAs($admin)->get(route('audit'))->assertOk();
    }
}
