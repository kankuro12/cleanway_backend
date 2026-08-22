@extends('layouts.app')

@section('title', 'Checklist Templates')

@push('styles')
<style>
    .checklist-row-card {
        border: 1px solid var(--cw-border, #e2e8f0);
        border-radius: 8px;
        background: #ffffff;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    }
    .checklist-row-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .checklist-stat-pill {
        background: #f1f5f9;
        color: #475569;
        font-family: var(--font-mono, monospace);
        font-size: 0.75rem;
        padding: 3px 8px;
        border-radius: 4px;
        font-weight: 600;
    }
    @media (max-width: 575.98px) {
        .checklist-row-card .card-body {
            padding: 12px !important;
        }
        .checklist-actions-wrap {
            width: 100%;
            justify-content: space-between;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed #e2e8f0;
        }
        .btn-manage-checklist {
            flex-grow: 1;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 reveal">
        <div>
            <span class="eyebrow">Tasks · Configuration</span>
            <div class="d-flex align-items-center gap-2 mt-1">
                <h1 class="h4 mb-0 font-weight-bold">Checklist Templates</h1>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill mono extra-small" id="total-checklists-count">
                    {{ $templates->total() }} {{ Str::plural('template', $templates->total()) }}
                </span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('task-types') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-clipboard2-pulse me-1" aria-hidden="true"></i>Task Types
            </a>
            <a href="{{ route('tasks') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-list-check me-1" aria-hidden="true"></i>Task Register
            </a>
            @if(auth()->user()->hasPermission('4.8'))
                <a href="{{ route('checklists.create') }}" class="btn btn-primary btn-sm fw-bold d-inline-flex align-items-center">
                    <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add Checklist
                </a>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show py-2 reveal" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 reveal" role="alert">
            <i class="bi bi-exclamation-octagon-fill me-1"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Search / Filter Bar -->
    <div class="card border shadow-sm mb-3 reveal">
        <div class="card-body p-2 px-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-6 col-lg-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="checklist-search-input" class="form-control border-start-0" placeholder="Filter checklists by name or description…">
                        <button type="button" class="btn btn-outline-secondary d-none" id="clear-checklist-search"><i class="bi bi-x"></i></button>
                    </div>
                </div>
                <div class="col-md-6 col-lg-7 text-md-end text-muted extra-small mono">
                    <i class="bi bi-info-circle me-1"></i>Checklists attach structured, repeatable SOPs to tasks and properties.
                </div>
            </div>
        </div>
    </div>

    <!-- Templates Cards List -->
    <div id="checklists-container" class="d-flex flex-column gap-2">
        @forelse ($templates as $template)
            @php
                $sectionCount = $template->sections->count();
                $itemCount = $template->sections->sum(fn($s) => $s->items->count());
            @endphp
            <div class="card shadow-sm checklist-row-card reveal" data-name="{{ strtolower($template->name) }}" data-desc="{{ strtolower($template->description ?? '') }}" style="--d: {{ 60 + ($loop->index * 25) }}ms">
                <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex flex-column gap-1 min-w-0 flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h2 class="h6 mb-0 fw-bold text-dark">{{ $template->name }}</h2>
                            <span class="badge bg-light text-muted border mono extra-small">{{ $template->slug }}</span>
                        </div>
                        @if($template->description)
                            <p class="text-muted small mb-1">{{ $template->description }}</p>
                        @endif
                        <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
                            <span class="checklist-stat-pill">
                                <i class="bi bi-folder2 me-1 text-primary"></i>{{ $sectionCount }} {{ Str::plural('section', $sectionCount) }}
                            </span>
                            <span class="checklist-stat-pill">
                                <i class="bi bi-check2-square me-1 text-success"></i>{{ $itemCount }} {{ Str::plural('item', $itemCount) }}
                            </span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 checklist-actions-wrap">
                        @if(auth()->user()->hasPermission('4.8'))
                            <a href="{{ route('checklists.edit', $template) }}" class="btn btn-sm btn-outline-primary fw-semibold d-inline-flex align-items-center px-3 btn-manage-checklist">
                                <i class="bi bi-sliders2 me-1"></i>Manage Checklist
                            </a>
                            <form method="POST" action="{{ route('checklists.destroy', $template) }}" class="d-inline" onsubmit="return confirm('Delete checklist template &ldquo;{{ $template->name }}&rdquo;? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger px-2" title="Delete checklist">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card shadow-sm py-5 text-center text-muted reveal">
                <i class="bi bi-card-checklist fs-1 mb-2 text-secondary"></i>
                <h5 class="h6 fw-bold text-dark">No checklist templates found</h5>
                <p class="small text-muted mb-3">Create your first checklist template to define standard operating procedures and inspection requirements.</p>
                <div>
                    <a href="{{ route('checklists.create') }}" class="btn btn-sm btn-primary fw-bold px-3">
                        <i class="bi bi-plus-lg me-1"></i>Add Checklist
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($templates->hasPages())
        <div class="mt-3 reveal">{{ $templates->links() }}</div>
    @endif
@endsection

@push('scripts')
<script>
    (function ($) {
        // Instant Search Filter
        $('#checklist-search-input').on('input', function () {
            var query = $(this).val().toLowerCase().trim();
            $('#clear-checklist-search').toggleClass('d-none', query === '');

            $('.checklist-row-card').each(function () {
                var name = $(this).data('name') || '';
                var desc = $(this).data('desc') || '';
                if (name.includes(query) || desc.includes(query)) {
                    $(this).removeClass('d-none');
                } else {
                    $(this).addClass('d-none');
                }
            });
        });

        $('#clear-checklist-search').on('click', function () {
            $('#checklist-search-input').val('').trigger('input');
        });
    })(jQuery);
</script>
@endpush

