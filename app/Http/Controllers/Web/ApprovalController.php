<?php

namespace App\Http\Controllers\Web;

use App\Domain\Tasks\TaskApprovalActions;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function queue(Request $request): View
    {
        $tasks = Task::with(['taskType:id,name', 'property:id,name', 'assignments.assignee', 'approvals' => fn ($q) => $q->latest()])
            ->whereIn('status', [
                Task::STATUS_SUBMITTED_FOR_APPROVAL,
                Task::STATUS_CORRECTION_REQUESTED,
                Task::STATUS_REJECTED,
                Task::STATUS_REOPENED,
            ])
            ->orderByDesc('updated_at')
            ->paginate(25);

        return view('pages.approval-queue', ['tasks' => $tasks]);
    }

    public function decide(Request $request, Task $task, TaskApprovalActions $actions): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('4.5'), 403);

        $request->validate([
            'action' => ['required', 'in:approve,reject,request_correction,reopen'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'reason_code' => ['nullable', 'string', 'max:50'],
            'requested_corrections' => ['nullable', 'string', 'max:1000'],
            'quality_score' => ['nullable', 'integer', 'min:0', 'max:10'],
        ]);

        try {
            $task = match ($request->string('action')->toString()) {
                'approve' => $actions->approve($task, $request->user(), $request->all()),
                'reject' => $actions->reject($task, $request->user(), $request->all()),
                'request_correction' => $actions->requestCorrection($task, $request->user(), $request->all()),
                'reopen' => $actions->reopen($task, $request->user(), $request->all()),
            };
        } catch (\InvalidArgumentException|\DomainException $e) {
            return back()->withErrors(['task' => $e->getMessage()]);
        }

        return back()->with('status', 'Task '.$request->string('action').'.');
    }
}
