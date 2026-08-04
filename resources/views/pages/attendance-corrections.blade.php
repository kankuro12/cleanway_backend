@extends('layouts.app')

@section('title', 'Corrections')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Attendance · Corrections</span>
            <h2 class="h3 mt-1 mb-0">Correction requests</h2>
        </div>
        <a href="{{ route('attendance') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-clock-history me-1" aria-hidden="true"></i>Events
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif

    <div class="card shadow-sm reveal" style="--d: 100ms">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Original event</th>
                        <th>Reason</th>
                        <th>Decision</th>
                        @if(auth()->user()->hasPermission('6.2'))<th class="text-end">Act</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $request)
                        <tr>
                            <td class="fw-semibold">{{ $request->user?->name }}</td>
                            <td class="small">
                                #{{ $request->original_event_id }} · {{ str_replace('_', ' ', $request->originalEvent?->event_type ?? 'deleted') }}
                                <br><span class="text-muted">{{ $request->originalEvent?->server_timestamp?->format('j M H:i') }}</span>
                            </td>
                            <td class="small">{{ $request->reason }}</td>
                            <td>
                                <span class="status-badge status-{{ $request->decision === 'approved' ? 'active' : ($request->decision === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ $request->decision }}
                                </span>
                                @if($request->decision_remarks)<br><small class="text-muted">{{ $request->decision_remarks }}</small>@endif
                            </td>
                            @if(auth()->user()->hasPermission('6.2'))
                                <td class="text-end">
                                    @if($request->decision === 'pending')
                                        <form method="POST" action="{{ route('attendance.corrections.decide', $request) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="decision" value="approved">
                                            <button class="btn btn-sm btn-outline-success">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('attendance.corrections.decide', $request) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="decision" value="rejected">
                                            <button class="btn btn-sm btn-outline-danger">Reject</button>
                                        </form>
                                    @else
                                        <span class="text-muted small">{{ $request->decidedByUser?->name ?? '—' }}</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-pencil-square"></i></span>
                                    No correction requests.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 reveal">{{ $requests->links() }}</div>
@endsection
