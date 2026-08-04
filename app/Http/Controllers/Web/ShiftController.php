<?php

namespace App\Http\Controllers\Web;

use App\Domain\Attendance\SubmitAttendanceCorrection;
use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceEvent;
use App\Models\Shift;
use App\Models\User;
use App\Services\Attendance\AttendanceRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function index(Request $request): View
    {
        $shifts = Shift::with(['user:id,name', 'property:id,name'])
            ->when($request->filled('date'), fn ($q) => $q->where('date', $request->string('date')))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('date')
            ->paginate(25)
            ->withQueryString();

        return view('pages.shifts', [
            'shifts' => $shifts,
            'workers' => User::whereIn('role', [User::ROLE_SUPERVISOR, User::ROLE_CLEANER])->orderBy('name')->get(['id', 'name']),
            'rules' => app(AttendanceRules::class),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('5.2'), 403);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date'],
            'scheduled_start_at' => ['required', 'date'],
            'scheduled_end_at' => ['required', 'date', 'after:scheduled_start_at'],
            'property_id' => ['nullable', 'exists:properties,id'],
            'status' => ['sometimes', 'in:'.implode(',', Shift::STATUSES)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        Shift::create($request->all());

        return redirect()->route('shifts')->with('status', 'Shift created.');
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('5.2'), 403);

        $request->validate([
            'status' => ['required', 'in:'.implode(',', Shift::STATUSES)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $shift->update($request->only(['status', 'notes']));

        return redirect()->route('shifts')->with('status', 'Shift updated.');
    }
}
