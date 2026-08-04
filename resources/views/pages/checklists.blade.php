@extends('layouts.app')

@section('title', 'Checklists')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Tasks · Config</span>
            <h2 class="h3 mt-1 mb-0">Checklist templates</h2>
        </div>
        <a href="{{ route('task-types') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-clipboard2-pulse me-1" aria-hidden="true"></i>Task types
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-4 reveal" style="--d: 80ms">
        <div class="card-header mono">New checklist</div>
        <div class="card-body">
            <form method="POST" action="{{ route('checklists.store') }}" class="row g-2" id="new-checklist-form">
                @csrf
                <div class="col-md-4">
                    <label for="name" class="form-label visually-hidden">Name</label>
                    <input type="text" id="name" name="name" class="form-control form-control-sm" placeholder="Name" required>
                </div>
                <div class="col-md-6">
                    <label for="description" class="form-label visually-hidden">Description</label>
                    <input type="text" id="description" name="description" class="form-control form-control-sm" placeholder="Description (optional)">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-plus me-1" aria-hidden="true"></i>Create
                    </button>
                </div>
                <div class="col-12 text-muted small">
                    Sections and items are added after creation.
                </div>
            </form>
        </div>
    </div>

    @foreach ($templates as $template)
        <div class="card shadow-sm mb-3 reveal" style="--d: 120ms">
            <div class="card-header mono">{{ $template->name }}</div>
            <div class="card-body">
                <form method="POST" action="{{ route('checklists.update', $template) }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-2 mb-3">
                        <div class="col-md-5">
                            <label for="name-{{ $template->id }}" class="form-label visually-hidden">Name</label>
                            <input type="text" id="name-{{ $template->id }}" name="name" value="{{ $template->name }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-5">
                            <label for="description-{{ $template->id }}" class="form-label visually-hidden">Description</label>
                            <input type="text" id="description-{{ $template->id }}" name="description" value="{{ $template->description }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-sm btn-outline-secondary w-100">Save</button>
                        </div>
                    </div>

                    <div class="checklist-editor" data-template="{{ $template->id }}">
                        @php $sectionIdx = 0; @endphp
                        @foreach ($template->sections as $section)
                            <div class="checklist-section border rounded p-2 mb-2">
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text">Section</span>
                                    <input type="text" name="sections[{{ $sectionIdx }}][name]" value="{{ $section->name }}" class="form-control" required>
                                    <button type="button" class="btn btn-outline-danger remove-section">
                                        <i class="bi bi-x" aria-hidden="true"></i>
                                    </button>
                                </div>
                                @foreach ($section->items as $item)
                                    <div class="input-group input-group-sm mb-1">
                                        <input type="text" name="sections[{{ $sectionIdx }}][items][{{ $loop->index }}][label]" value="{{ $item->label }}" class="form-control" placeholder="Item" required>
                                        <select name="sections[{{ $sectionIdx }}][items][{{ $loop->index }}][item_type]" class="form-select" style="max-width: 120px">
                                            @foreach (['yes_no', 'pass_fail', 'text', 'numeric', 'photo'] as $type)
                                                <option value="{{ $type }}" @selected($item->item_type === $type)>{{ str_replace('_', ' ', $type) }}</option>
                                            @endforeach
                                        </select>
                                        <label class="input-group-text"><input type="checkbox" name="sections[{{ $sectionIdx }}][items][{{ $loop->index }}][required]" value="1" @checked($item->required)> req</label>
                                        <label class="input-group-text"><input type="checkbox" name="sections[{{ $sectionIdx }}][items][{{ $loop->index }}][issue_triggering]" value="1" @checked($item->issue_triggering)> issue</label>
                                        <button type="button" class="btn btn-outline-danger remove-item">
                                            <i class="bi bi-x" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                @endforeach
                                <button type="button" class="btn btn-sm btn-outline-secondary add-item">
                                    <i class="bi bi-plus me-1" aria-hidden="true"></i>Item
                                </button>
                            </div>
                        @endforeach
                        <button type="button" class="btn btn-sm btn-outline-secondary add-section">
                            <i class="bi bi-plus me-1" aria-hidden="true"></i>Section
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <div class="mt-3 reveal">{{ $templates->links() }}</div>
@endsection

@push('scripts')
    <script>
        (function ($) {
            $('.add-section').on('click', function () {
                var editor = $(this).closest('.checklist-editor');
                var idx = editor.find('.checklist-section').length;
                var html = '<div class="checklist-section border rounded p-2 mb-2">' +
                    '<div class="input-group input-group-sm mb-2">' +
                    '<span class="input-group-text">Section</span>' +
                    '<input type="text" name="sections[' + idx + '][name]" class="form-control" required>' +
                    '<button type="button" class="btn btn-outline-danger remove-section"><i class="bi bi-x" aria-hidden="true"></i></button>' +
                    '</div>' +
                    '<button type="button" class="btn btn-sm btn-outline-secondary add-item"><i class="bi bi-plus me-1" aria-hidden="true"></i>Item</button>' +
                    '</div>';
                $(html).insertBefore(this);
            });

            $(document).on('click', '.remove-section', function () {
                $(this).closest('.checklist-section').remove();
            });

            $(document).on('click', '.add-item', function () {
                var section = $(this).closest('.checklist-section');
                var sectionIdx = section.index('.checklist-section');
                var itemIdx = section.find('.input-group').length;
                var html = '<div class="input-group input-group-sm mb-1">' +
                    '<input type="text" name="sections[' + sectionIdx + '][items][' + itemIdx + '][label]" class="form-control" placeholder="Item" required>' +
                    '<select name="sections[' + sectionIdx + '][items][' + itemIdx + '][item_type]" class="form-select" style="max-width: 120px">' +
                    '<option value="yes_no">yes no</option><option value="pass_fail">pass fail</option>' +
                    '<option value="text">text</option><option value="numeric">numeric</option><option value="photo">photo</option></select>' +
                    '<label class="input-group-text"><input type="checkbox" name="sections[' + sectionIdx + '][items][' + itemIdx + '][required]" value="1" checked> req</label>' +
                    '<label class="input-group-text"><input type="checkbox" name="sections[' + sectionIdx + '][items][' + itemIdx + '][issue_triggering]" value="1"> issue</label>' +
                    '<button type="button" class="btn btn-outline-danger remove-item"><i class="bi bi-x" aria-hidden="true"></i></button>' +
                    '</div>';
                $(html).insertBefore(this);
            });

            $(document).on('click', '.remove-item', function () {
                $(this).closest('.input-group').remove();
            });
        })(jQuery);
    </script>
@endpush
