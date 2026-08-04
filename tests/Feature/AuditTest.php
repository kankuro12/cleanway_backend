<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_create_writes_audit_entry(): void
    {
        $actor = User::factory()->create();

        $this->actingAs($actor)->post(route('login'), [
            'email' => $actor->email,
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login',
            'entity_type' => User::class,
            'entity_id' => $actor->id,
        ]);
    }

    public function test_audit_logger_records_context(): void
    {
        $actor = User::factory()->create();

        $this->actingAs($actor);

        app(AuditLogger::class)->log('test', User::class, $actor->id, [
            'before' => ['role' => 2],
            'after' => ['role' => 0],
            'source' => 'web',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'test',
            'entity_type' => User::class,
            'entity_id' => $actor->id,
            'source' => 'web',
        ]);

        $log = AuditLog::latest('id')->first();
        $this->assertEquals(['role' => 2], $log->before);
        $this->assertEquals(['role' => 0], $log->after);
        $this->assertEquals($actor->id, $log->actor_id);
    }

    public function test_audit_is_disabled_by_config(): void
    {
        config(['audit.enabled' => false]);

        app(AuditLogger::class)->log('test', User::class, 1);

        $this->assertDatabaseCount('audit_logs', 0);
    }
}
