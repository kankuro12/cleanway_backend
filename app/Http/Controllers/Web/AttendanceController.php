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
}
