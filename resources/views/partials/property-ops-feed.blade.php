@forelse ($propertyGroups as $group)
    @php
        $meta = $group['meta'];
        $tasks = $group['tasks'];
        $prop = $group['property'];
    @endphp
    <div class="prop-group-card {{ $meta['is_collapsed'] ? 'collapsed' : '' }}" id="prop-card-{{ $meta['id'] ?? 'general' }}">
        <!-- Property Group Header with Signature Hazard Stripe -->
        <div class="prop-group-header" data-toggle="prop-collapse" data-target="#prop-card-{{ $meta['id'] ?? 'general' }}">
            <!-- Signature Diagonal Hazard Stripe Accent Pattern -->
            <div class="prop-hazard-stripe" aria-hidden="true"></div>

            <!-- Left: Leading dot/icon + Address & Code + Drive time badge -->
            <div class="prop-header-left">
                @if($meta['icon_type'] === 'guest')
                    <i class="bi bi-person-fill prop-group-icon" title="Guest / Short-Term Rental"></i>
                @else
                    <span class="prop-group-dot {{ $meta['dot_class'] }}"></span>
                @endif

                <span class="prop-group-title" title="{{ $meta['header_title'] }}">
                    {{ $meta['header_title'] }}
                </span>

                @if(!empty($meta['drive_time']))
                    <span class="prop-group-distance" title="Estimated drive/travel time from branch">- {{ $meta['drive_time'] }}</span>
                @endif
            </div>

            <!-- Right: Status pill (Hold / Guest / Turnover / Ready) + Collapse Chevron -->
            <div class="prop-header-right">
                <span class="prop-status-pill {{ $meta['status_pill_class'] }}">{{ $meta['status_pill_text'] }}</span>
                <i class="bi bi-chevron-down prop-chevron" aria-hidden="true"></i>
            </div>
        </div>

        <!-- Property Tasks List Wrapper -->
        <div class="prop-task-list-wrapper">
            @forelse ($tasks as $task)
                @php
                    $typeSlug = strtolower($task->taskType?->slug ?? '');
                    $typeName = strtolower($task->taskType?->name ?? '');
                    $titleLower = strtolower($task->title);
                    
                    // Category Chip Detection (Dark Green / Orange / Blue)
                    if (str_contains($typeSlug, 'guest') || str_contains($typeName, 'guest') || str_contains($titleLower, 'check-in') || str_contains($titleLower, 'car') || str_contains($titleLower, 'key') || str_contains($titleLower, 'wine')) {
                        $catClass = 'blue';
                        $catIcon = 'bi-person-badge';
                        $catTitle = 'Guest Service';
                    } elseif (str_contains($typeSlug, 'check') || str_contains($typeName, 'check') || str_contains($titleLower, 'checklist') || str_contains($titleLower, 'audit') || str_contains($titleLower, 'safety')) {
                        $catClass = 'orange';
                        $catIcon = 'bi-card-checklist';
                        $catTitle = 'Checklist / Inspection';
                    } else {
                        $catClass = 'green';
                        $catIcon = 'bi-arrow-repeat';
                        $catTitle = 'Recurring / Turnover';
                    }

                    $evidenceCount = $task->evidence ? $task->evidence->count() : 0;
                    $commentCount = $task->comments ? $task->comments->count() : 0;
                    $assigneeNames = $task->assignments->filter(fn($a) => $a->assignee)->pluck('assignee.name')->implode(', ');
                    $isMine = auth()->user()->hasRole(\App\Models\User::ROLE_CLEANER);
                @endphp

                <!-- Two-Column Task Row -->
                <div class="prop-task-row" id="task-row-{{ $task->id }}">
                    <!-- Left Column: Tap target for Date & Due Time -->
                    <div class="prop-time-col btn-due-time-trigger" 
                         data-task-id="{{ $task->id }}" 
                         data-task-title="{{ $task->title }}" 
                         data-current-time="{{ $task->scheduled_start_at?->format('H:i') ?? '' }}" 
                         data-current-date="{{ $task->scheduled_start_at?->format('Y-m-d') ?? today()->toDateString() }}" 
                         title="Click to set or adjust date and time">
                        @if($task->scheduled_start_at)
                            <div class="prop-date-val" id="date-val-{{ $task->id }}">{{ $task->scheduled_start_at->format('M j') }}</div>
                            <div class="prop-time-val" id="time-val-{{ $task->id }}">{{ $task->scheduled_start_at->format('g:i') }}</div>
                            <div class="prop-time-ampm" id="time-ampm-{{ $task->id }}">{{ $task->scheduled_start_at->format('A') }}</div>
                        @else
                            <div class="prop-due-stub" id="stub-wrap-{{ $task->id }}">
                                <span>Set</span>
                                <span>date</span>
                                <span>& time</span>
                            </div>
                        @endif
                    </div>

                    <!-- Right Column: Task Content & Meta -->
                    <div class="prop-task-body">
                        <div class="prop-task-head-line">
                            <a href="{{ $isMine ? route('tasks.work', $task) : route('tasks.edit', $task) }}" class="prop-task-title">
                                {{ $task->title }}
                            </a>

                            <div class="prop-task-actions">
                                @if(($task->latitude_snapshot && $task->longitude_snapshot) || ($task->property?->latitude && $task->property?->longitude))
                                    @php
                                        $lat = $task->latitude_snapshot ?: $task->property->latitude;
                                        $lng = $task->longitude_snapshot ?: $task->property->longitude;
                                    @endphp
                                    <a href="https://www.google.com/maps?q={{ $lat }},{{ $lng }}" target="_blank" rel="noopener" class="prop-btn-work py-1 px-2" title="Navigate to location" aria-label="Directions">
                                        <i class="bi bi-sign-turn-right text-primary"></i>
                                    </a>
                                @endif
                                <a href="{{ $isMine ? route('tasks.work', $task) : route('tasks.edit', $task) }}" class="prop-btn-work">
                                    <i class="bi bi-{{ $isMine ? 'play-fill' : 'pencil' }}"></i> {{ $isMine ? 'Work' : 'Edit' }}
                                </a>
                            </div>
                        </div>

                        <!-- Meta line: Category chip + Priority flag + Attachment count + Comment count + Assignees -->
                        <div class="prop-task-meta">
                            <!-- Category Icon Chip -->
                            <span class="prop-cat-chip {{ $catClass }}" title="{{ $catTitle }}: {{ $task->taskType?->name ?? 'Task' }}">
                                <i class="bi {{ $catIcon }}"></i>
                            </span>

                            <!-- Priority Flag (Green diamond = standard, Orange up-arrow = elevated, Red exclamation = critical) -->
                            @if($task->priority === 'critical')
                                <span class="prop-prio-flag critical" title="Critical Priority">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                </span>
                            @elseif($task->priority === 'high')
                                <span class="prop-prio-flag elevated" title="Elevated Priority">
                                    ↑
                                </span>
                            @else
                                <span class="prop-prio-flag standard" title="Standard Priority">
                                    ⧫
                                </span>
                            @endif

                            <!-- Status Cue if in progress / completed / issue -->
                            @if($task->status === 'in_progress')
                                <span class="status-badge status-in_progress extra-small py-0 px-2" style="font-size: 10px;">In Progress</span>
                            @elseif(in_array($task->status, ['completed', 'submitted_for_approval', 'approved'], true))
                                <span class="status-badge status-ok extra-small py-0 px-2" style="font-size: 10px;">Done</span>
                            @elseif(in_array($task->status, ['unable_to_access', 'correction_requested', 'rejected'], true))
                                <span class="status-badge status-danger extra-small py-0 px-2" style="font-size: 10px;">Issue</span>
                            @endif

                            <!-- Attachment Count (Paperclip) -->
                            @if($evidenceCount > 0)
                                <span class="prop-count-badge" title="{{ $evidenceCount }} Attachments">
                                    <i class="bi bi-paperclip"></i>{{ $evidenceCount }}
                                </span>
                            @endif

                            <!-- Comment Count (Speech Bubble) -->
                            @if($commentCount > 0)
                                <span class="prop-count-badge" title="{{ $commentCount }} Comments">
                                    <i class="bi bi-chat-left-text"></i>{{ $commentCount }}
                                </span>
                            @endif

                            <!-- Assignees -->
                            @if(!empty($assigneeNames))
                                <span class="prop-count-badge text-muted" title="Assigned: {{ $assigneeNames }}">
                                    <i class="bi bi-person"></i>{{ \Illuminate\Support\Str::limit($assigneeNames, 22) }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <!-- Empty state for property group with no tasks on this date -->
                <div class="prop-group-empty">
                    "No tasks for this property on this date"
                </div>
            @endforelse
        </div>
    </div>
@empty
    <div class="card border-0 shadow-sm text-center py-5 bg-white rounded-3">
        <div class="card-body">
            <i class="bi bi-clipboard-check display-4 text-muted mb-3 d-block"></i>
            <h5 class="fw-bold text-dark">No tasks found</h5>
            <p class="text-muted small mb-0">No property tasks match your current date or filter selection.</p>
        </div>
    </div>
@endforelse
