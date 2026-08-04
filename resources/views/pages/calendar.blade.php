@extends('layouts.app')

@section('title', 'Calendar')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
    <style>
        .fc-task-approved { background: var(--cw-success, #198754); border-color: var(--cw-success, #198754); }
        .fc-task-cancelled, .fc-task-rejected { background: var(--cw-danger, #dc3545); border-color: var(--cw-danger, #dc3545); }
        .fc-task-in_progress, .fc-task-accepted { background: var(--cw-warning, #f0ad4e); border-color: var(--cw-warning, #f0ad4e); color: #222; }
        .fc-task-assigned, .fc-task-scheduled { background: var(--cw-accent-deep, #c2410c); border-color: var(--cw-accent-deep, #c2410c); }
        .fc-task-completed, .fc-task-submitted_for_approval { background: var(--cw-info, #0dcaf0); border-color: var(--cw-info, #0dcaf0); }
    </style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Tasks · Calendar</span>
            <h2 class="h3 mt-1 mb-0">Schedule board</h2>
        </div>
        <a href="{{ route('tasks') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-list-ul me-1" aria-hidden="true"></i>Task register
        </a>
    </div>

    <div class="card shadow-sm reveal" style="--d: 100ms">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
        (function ($) {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek'
                },
                height: 'auto',
                nowIndicator: true,
                events: function (info, success, failure) {
                    axios.get('{{ route('calendar.events') }}', {
                        params: {
                            from: info.start.toISOString().slice(0, 10),
                            to: info.end.toISOString().slice(0, 10)
                        }
                    }).then(function (res) {
                        success(res.data.data || []);
                    }).catch(failure);
                },
                eventClick: function (info) {
                    if (info.event.url) {
                        window.location = info.event.url;
                    }
                }
            });
            calendar.render();
        })(jQuery);
    </script>
@endpush
