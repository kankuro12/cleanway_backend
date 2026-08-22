<?php

namespace Tests\Feature;

use App\Models\AttendanceEvent;
use App\Models\Branch;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficeAttendanceAndShiftReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_personnel_can_punch_in_to_office_within_geofence(): void
    {
        $branch = Branch::create([
            'name' => 'HQ Office',
            'address' => '123 Main St',
            'latitude' => 27.7172,
            'longitude' => 85.3240,
            'geofence_radius_meters' => 100,
            'active' => true,
        ]);

        $user = User::factory()->create([
            'role' => User::ROLE_CLEANER,
            'status' => User::STATUS_ACTIVE,
            'branch_id' => $branch->id,
        ]);

        $response = $this->actingAs($user)->post('/admin/attendance/office-punch', [
            'event_type' => 'clock_in',
            'latitude' => 27.71725,
            'longitude' => 85.32405,
            'gps_accuracy_meters' => 10,
            'remarks' => 'Starting morning shift at office',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendance_events', [
            'user_id' => $user->id,
            'event_type' => 'clock_in',
            'task_id' => null,
            'inside_geofence' => true,
        ]);
    }

    public function test_office_punch_outside_geofence_flags_inside_false(): void
    {
        $branch = Branch::create([
            'name' => 'HQ Office',
            'latitude' => 27.7172,
            'longitude' => 85.3240,
            'geofence_radius_meters' => 100,
            'active' => true,
        ]);

        $user = User::factory()->create([
            'role' => User::ROLE_CLEANER,
            'status' => User::STATUS_ACTIVE,
            'branch_id' => $branch->id,
        ]);

        // Far away coordinates (1km+)
        $response = $this->actingAs($user)->post('/admin/attendance/office-punch', [
            'event_type' => 'clock_in',
            'latitude' => 27.7300,
            'longitude' => 85.3400,
            'gps_accuracy_meters' => 10,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendance_events', [
            'user_id' => $user->id,
            'event_type' => 'clock_in',
            'inside_geofence' => false,
        ]);
    }

    public function test_shift_report_web_page_and_api_return_metrics(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $shift = Shift::create([
            'user_id' => $admin->id,
            'date' => today()->toDateString(),
            'scheduled_start_at' => now()->subHours(8),
            'scheduled_end_at' => now(),
            'status' => Shift::STATUS_COMPLETED,
        ]);

        AttendanceEvent::create([
            'user_id' => $admin->id,
            'shift_id' => $shift->id,
            'event_type' => AttendanceEvent::TYPE_CLOCK_IN,
            'server_timestamp' => now()->subHours(8),
            'latitude' => 27.7172,
            'longitude' => 85.3240,
            'inside_geofence' => true,
        ]);

        AttendanceEvent::create([
            'user_id' => $admin->id,
            'shift_id' => $shift->id,
            'event_type' => AttendanceEvent::TYPE_CLOCK_OUT,
            'server_timestamp' => now(),
            'latitude' => 27.7172,
            'longitude' => 85.3240,
            'inside_geofence' => true,
        ]);

        $webResponse = $this->actingAs($admin)->get('/admin/reports/shifts');
        $webResponse->assertStatus(200);
        $webResponse->assertSee('Shift Report');

        $apiResponse = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/reports/shifts');
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonStructure([
            'metrics' => [
                'total_shifts',
                'completed_shifts',
                'total_worked_hours',
                'on_time_rate',
                'geofence_compliance_rate',
            ],
            'data',
            'pagination',
        ]);
    }
}
