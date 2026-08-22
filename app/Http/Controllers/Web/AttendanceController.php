<?php

namespace App\Http\Controllers\Web;

use App\Domain\Attendance\SubmitAttendanceCorrection;
use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceEvent;
use App\Models\User;
use App\Services\Attendance\AttendanceRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $events = AttendanceEvent::with(['user:id,name', 'property:id,name'])
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('event_type'), fn ($q) => $q->where('event_type', $request->string('event_type')))
            ->when($request->filled('from'), fn ($q) => $q->where('server_timestamp', '>=', $request->date('from')->startOfDay()))
            ->when($request->filled('to'), fn ($q) => $q->where('server_timestamp', '<=', $request->date('to')->endOfDay()))
            ->orderByDesc('server_timestamp')
            ->paginate(25)
            ->withQueryString();

        return view('pages.attendance', [
            'events' => $events,
            'workers' => User::whereIn('role', [User::ROLE_SUPERVISOR, User::ROLE_CLEANER])->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function corrections(Request $request): View
    {
        $requests = AttendanceCorrectionRequest::with(['user:id,name', 'originalEvent', 'decidedByUser:id,name'])
            ->orderByDesc('id')
            ->paginate(25);

        return view('pages.attendance-corrections', ['requests' => $requests]);
    }

    public function decideCorrection(Request $request, AttendanceCorrectionRequest $correction, SubmitAttendanceCorrection $service): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('6.2'), 403);

        $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $service->decide($correction, $request->string('decision'), $request->user(), $request->input('corrected', []), $request->string('remarks'));

        return back()->with('status', 'Correction request '.$request->string('decision').'.');
    }

    public function officePunch(Request $request, \App\Domain\Attendance\RecordAttendanceEvent $recorder): RedirectResponse
    {
        $validated = $request->validate([
            'event_type' => ['required', 'string', 'in:clock_in,break_start,break_end,clock_out'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'gps_accuracy_meters' => ['nullable', 'integer', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $event = $recorder->execute($request->user(), $validated['event_type'], $validated);

        $statusMsg = match ($validated['event_type']) {
            'clock_in' => 'Punched in to office successfully.',
            'clock_out' => 'Punched out from office successfully.',
            'break_start' => 'Break started.',
            'break_end' => 'Break ended.',
            default => 'Attendance event recorded.',
        };

        if ($event->inside_geofence === false) {
            $statusMsg .= ' (Note: Recorded outside office geofence range — '.$event->distance_from_property_meters.'m away).';
        }

        return back()->with('status', $statusMsg);
    }
}
