@extends('layouts.app')

@section('title', $isCreate ? 'New Checklist Template' : 'Edit Checklist: ' . $template->name)

@push('styles')
<style>
    /* Font & Hierarchy Styling */
    .checklist-section-box {
        background: #ffffff;
        border: 1px solid var(--cw-border, #e2e8f0);
        border-radius: 6px;
        padding: 10px 12px;
        margin-bottom: 10px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }
    .checklist-item-row {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        padding: 6px 8px;
        margin-bottom: 5px;
        transition: all 0.15s ease;
    }
    .checklist-item-row:hover {
        border-color: #cbd5e1;
        background: #ffffff;
    }
    .item-input {
        font-size: 0.8125rem !important;
        min-height: 28px !important;
        height: 28px !important;
        padding: 2px 8px !important;
    }
    .item-select {
        font-size: 0.75rem !important;
        min-height: 28px !important;
        height: 28px !important;
        padding: 2px 22px 2px 6px !important;
        font-family: var(--font-mono, monospace);
    }
    .toggle-chip {
        cursor: pointer;
        user-select: none;
        font-size: 0.68rem;
        padding: 2px 6px;
        border-radius: 3px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #64748b;
        font-family: var(--font-mono, monospace);
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        height: 26px;
        transition: all 0.15s ease;
    }
    .toggle-chip:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .toggle-chip:has(input:checked) {
        background: #0f172a;
        color: #ffffff;
        border-color: #0f172a;
    }
    .toggle-chip input[type="checkbox"] {
        margin: 0;
        width: 12px;
        height: 12px;
        cursor: pointer;
    }
    .btn-remove-item {
        width: 26px;
        height: 26px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 3px;
        color: #94a3b8;
        border: 1px solid transparent;
        background: transparent;
        font-size: 0.875rem;
    }
    .btn-remove-item:hover {
        color: #ef4444;
        background: #fee2e2;
        border-color: #fecaca;
    }
    .btn-remove-section {
        width: 28px;
        height: 28px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
    }

    /* Universal Sticky Bottom Save Bar */
    .sticky-bottom-bar {
        position: sticky;
        bottom: 0;
        z-index: 1030;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(8px);
        border-top: 1px solid #e2e8f0;
        padding: 10px 16px;
        margin-top: 20px;
        margin-left: -1.25rem;
        margin-right: -1.25rem;
        margin-bottom: -1.25rem;
        box-shadow: 0 -4px 16px rgba(0,0,0,0.06);
    }
    @media (max-width: 991.98px) {
        .sticky-bottom-bar {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            margin: 0;
            padding: 8px 12px;
        }
        .page-content-wrapper {
            padding-bottom: 64px;
        }
        .checklist-item-controls {
            width: 100%;
            justify-content: space-between;
            margin-top: 4px;
        }
        .toggle-chips-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 3px;
        }
        .toggle-chip {
            padding: 2px 5px;
            font-size: 0.65rem;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content-wrapper">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 reveal">
        <div>
            <span class="eyebrow">Checklists · {{ $isCreate ? 'New Template' : 'Edit' }}</span>
            <div class="d-flex align-items-center gap-2 mt-1">
                <h1 class="h5 mb-0 font-weight-bold" id="page-title-name">
                    {{ $isCreate ? 'Create Checklist' : 'Edit: ' . $template->name }}
                </h1>
                @if(!$isCreate && $template->slug)
                    <span class="badge bg-light text-muted border mono extra-small" id="template-slug-badge">{{ $template->slug }}</span>
                @endif
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('checklists') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <button type="submit" form="form-checklist-edit" class="btn btn-primary btn-sm fw-bold d-none d-md-inline-flex align-items-center px-3">
                <i class="bi bi-check2 me-1"></i>{{ $isCreate ? 'Create Checklist' : 'Save Changes' }}
            </button>
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

    <form method="POST" action="{{ $isCreate ? route('checklists.store') : route('checklists.update', $template) }}" id="form-checklist-edit">
        @csrf
        @if(!$isCreate)
            @method('PUT')
        @endif

        <!-- Card 1: Checklist Information -->
        <div class="card shadow-sm mb-3 reveal" style="--d: 60ms">
            <div class="card-body p-2 px-3">
                <div class="row g-2">
                    <div class="col-md-5">
                        <label for="name" class="form-label extra-small mono text-uppercase text-muted mb-1">Checklist Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $template->name) }}" class="form-control form-control-sm fw-semibold" placeholder="e.g. End of Tenancy Clean" required style="font-size: 0.875rem;">
                    </div>
                    <div class="col-md-7">
                        <label for="description" class="form-label extra-small mono text-uppercase text-muted mb-1">Description</label>
                        <input type="text" id="description" name="description" value="{{ old('description', $template->description) }}" class="form-control form-control-sm" placeholder="Optional instructions or notes…" style="font-size: 0.875rem;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Section List Header -->
        <div class="d-flex justify-content-between align-items-center mb-2 px-1 reveal" style="--d: 80ms">
            <span class="extra-small mono text-uppercase fw-bold text-muted">Sections & Inspection Items</span>
            <button type="button" class="btn btn-xs btn-outline-secondary extra-small" id="btn-add-section">
                <i class="bi bi-plus-lg me-1"></i>Add Section
            </button>
        </div>

        <!-- Sections Container -->
        <div id="sections-container" class="d-flex flex-column gap-2 mb-3">
            @php
                $sections = $isCreate ? [
                    [
                        'name' => 'General Inspection',
                        'items' => [
                            ['label' => 'Inspect surfaces, fixtures, and overall cleanliness', 'item_type' => 'yes_no', 'required' => true, 'is_photo_required' => false, 'is_comment_required' => false, 'issue_triggering' => false],
                            ['label' => 'Photograph final cleaned room condition', 'item_type' => 'photo', 'required' => true, 'is_photo_required' => true, 'is_comment_required' => false, 'issue_triggering' => false],
                        ]
                    ]
                ] : $template->sections;
            @endphp

            @forelse ($sections as $sectionIdx => $section)
                @php
                    $sName = is_array($section) ? $section['name'] : $section->name;
                    $items = is_array($section) ? ($section['items'] ?? []) : $section->items;
                @endphp
                <div class="checklist-section-box reveal" data-section-idx="{{ $sectionIdx }}">
                    <!-- Section Header -->
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-secondary text-white mono extra-small section-num-badge">#{{ $loop->iteration }}</span>
                        <input type="text" name="sections[{{ $sectionIdx }}][name]" value="{{ $sName }}" class="form-control form-control-sm fw-bold" placeholder="Section Name (e.g. Kitchen, Bathrooms, Safety)" required style="font-size: 0.8125rem;">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-section" title="Remove Section">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>

                    <!-- Items in this section -->
                    <div class="section-items-container d-flex flex-column gap-1">
                        @forelse ($items as $itemIdx => $item)
                            @php
                                $label = is_array($item) ? $item['label'] : $item->label;
                                $type = is_array($item) ? $item['item_type'] : $item->item_type;
                                $req = is_array($item) ? ($item['required'] ?? false) : $item->required;
                                $photo = is_array($item) ? ($item['is_photo_required'] ?? false) : $item->is_photo_required;
                                $comm = is_array($item) ? ($item['is_comment_required'] ?? false) : $item->is_comment_required;
                                $issue = is_array($item) ? ($item['issue_triggering'] ?? false) : $item->issue_triggering;
                            @endphp
                            <div class="checklist-item-row d-flex align-items-center justify-content-between flex-wrap gap-2" data-item-idx="{{ $itemIdx }}">
                                <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0" style="min-width: 180px;">
                                    <i class="bi bi-grip-vertical text-muted opacity-50" style="font-size: 0.75rem;"></i>
                                    <input type="text" name="sections[{{ $sectionIdx }}][items][{{ $itemIdx }}][label]" value="{{ $label }}" class="form-control item-input flex-grow-1 bg-white" placeholder="Item description / instruction" required>
                                </div>

                                <div class="d-flex align-items-center gap-2 flex-wrap checklist-item-controls">
                                    <!-- Type Dropdown -->
                                    <select name="sections[{{ $sectionIdx }}][items][{{ $itemIdx }}][item_type]" class="form-select item-select bg-white" style="width: 100px;">
                                        <option value="yes_no" @selected($type === 'yes_no')>Yes / No</option>
                                        <option value="pass_fail" @selected($type === 'pass_fail')>Pass / Fail</option>
                                        <option value="text" @selected($type === 'text')>Text Note</option>
                                        <option value="numeric" @selected($type === 'numeric')>Numeric</option>
                                        <option value="photo" @selected($type === 'photo')>Photo Only</option>
                                    </select>

                                    <!-- Requirement Toggles -->
                                    <div class="d-flex align-items-center gap-1 toggle-chips-wrap">
                                        <label class="toggle-chip" title="Mandatory to complete task">
                                            <input type="checkbox" name="sections[{{ $sectionIdx }}][items][{{ $itemIdx }}][required]" value="1" @checked($req)>
                                            <span>Req</span>
                                        </label>
                                        <label class="toggle-chip" title="Photo proof required">
                                            <input type="checkbox" name="sections[{{ $sectionIdx }}][items][{{ $itemIdx }}][is_photo_required]" value="1" @checked($photo)>
                                            <span>📷 Photo</span>
                                        </label>
                                        <label class="toggle-chip" title="Comment note required">
                                            <input type="checkbox" name="sections[{{ $sectionIdx }}][items][{{ $itemIdx }}][is_comment_required]" value="1" @checked($comm)>
                                            <span>💬 Note</span>
                                        </label>
                                        <label class="toggle-chip" title="Raises supervisor issue on failure">
                                            <input type="checkbox" name="sections[{{ $sectionIdx }}][items][{{ $itemIdx }}][issue_triggering]" value="1" @checked($issue)>
                                            <span>⚠️ Issue</span>
                                        </label>
                                    </div>

                                    <button type="button" class="btn-remove-item" title="Remove Item">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted extra-small mb-1 empty-items-msg">No items in this section.</p>
                        @endforelse
                    </div>

                    <!-- Add Item Button -->
                    <div class="mt-2 text-start">
                        <button type="button" class="btn btn-xs btn-outline-primary extra-small btn-add-item">
                            <i class="bi bi-plus me-1"></i>Add Item
                        </button>
                    </div>
                </div>
            @empty
                <p class="text-muted small py-3 empty-sections-msg text-center">No sections configured yet. Click "Add Section" to begin.</p>
            @endforelse
        </div>

        <div class="text-center my-3">
            <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="btn-add-section-bottom">
                <i class="bi bi-plus-lg me-1"></i>Add Section
            </button>
        </div>

        <!-- Sticky Bottom Save Bar (Visible across Desktop & Mobile) -->
        <div class="sticky-bottom-bar d-flex align-items-center justify-content-between gap-2">
            <div class="d-none d-md-flex align-items-center gap-2">
                <span class="extra-small mono text-muted" id="bottom-bar-stats">
                    <i class="bi bi-shield-check me-1 text-success"></i>Saves without page reload.
                </span>
            </div>
            <div class="d-flex align-items-center gap-2 ms-auto w-100 w-md-auto justify-content-end">
                <a href="{{ route('checklists') }}" class="btn btn-outline-secondary btn-sm px-3 flex-fill flex-md-grow-0">Cancel</a>
                <button type="submit" form="form-checklist-edit" class="btn btn-primary btn-sm fw-bold px-4 flex-fill flex-md-grow-0 btn-save-checklist">
                    <i class="bi bi-check2 me-1"></i>{{ $isCreate ? 'Create Checklist' : 'Save Changes' }}
                </button>
            </div>
        </div>
    </form>

    <!-- Floating Feedback Toast -->
    <div id="checklist-ajax-toast" class="position-fixed top-0 start-50 translate-middle-x mt-3 py-2 px-3 rounded shadow-sm mono extra-small d-none" style="z-index: 9999; max-width: 90vw;">
        <span class="toast-msg"></span>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function ($) {
        var isCreate = @json($isCreate);
        var toastTimer = null;

        function showToast(message, type) {
            clearTimeout(toastTimer);
            var $toast = $('#checklist-ajax-toast');
            $toast.removeClass('d-none alert-success alert-danger bg-dark text-white border-0 text-success text-danger');
            
            if (type === 'success') {
                $toast.css({ background: '#0f172a', color: '#38bdf8', border: '1px solid #1e293b' });
                $toast.find('.toast-msg').html('<i class="bi bi-check-circle-fill text-success me-1"></i>' + message);
            } else {
                $toast.css({ background: '#7f1d1d', color: '#fecaca', border: '1px solid #991b1b' });
                $toast.find('.toast-msg').html('<i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>' + message);
            }

            $toast.fadeIn(150);
            toastTimer = setTimeout(function () {
                $toast.fadeOut(200);
            }, 3200);
        }

        function reindexSections() {
            $('#sections-container .checklist-section-box').each(function (sIdx) {
                var $box = $(this);
                $box.attr('data-section-idx', sIdx);
                $box.find('.section-num-badge').text('#' + (sIdx + 1));
                $box.find('input[name^="sections["][name$="[name]"]').attr('name', 'sections[' + sIdx + '][name]');

                $box.find('.checklist-item-row').each(function (iIdx) {
                    var $item = $(this);
                    $item.attr('data-item-idx', iIdx);
                    $item.find('input[name*="[label]"]').attr('name', 'sections[' + sIdx + '][items][' + iIdx + '][label]');
                    $item.find('select[name*="[item_type]"]').attr('name', 'sections[' + sIdx + '][items][' + iIdx + '][item_type]');
                    $item.find('input[name*="[required]"]').attr('name', 'sections[' + sIdx + '][items][' + iIdx + '][required]');
                    $item.find('input[name*="[is_photo_required]"]').attr('name', 'sections[' + sIdx + '][items][' + iIdx + '][is_photo_required]');
                    $item.find('input[name*="[is_comment_required]"]').attr('name', 'sections[' + sIdx + '][items][' + iIdx + '][is_comment_required]');
                    $item.find('input[name*="[issue_triggering]"]').attr('name', 'sections[' + sIdx + '][items][' + iIdx + '][issue_triggering]');
                });
            });
        }

        // AJAX Form Submission via Axios
        $('#form-checklist-edit').on('submit', function (e) {
            e.preventDefault();
            reindexSections();

            var $form = $(this);
            var $saveBtns = $('button[type="submit"][form="form-checklist-edit"], #form-checklist-edit button[type="submit"]');
            var originalBtnHtml = isCreate ? '<i class="bi bi-check2 me-1"></i>Create Checklist' : '<i class="bi bi-check2 me-1"></i>Save Changes';

            $saveBtns.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...');

            var formData = new FormData(this);
            var url = $form.attr('action');

            axios.post(url, formData, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(function (res) {
                if (isCreate && res.data && res.data.redirect) {
                    showToast('Checklist created. Redirecting to manager…', 'success');
                    setTimeout(function () {
                        window.location.href = res.data.redirect;
                    }, 500);
                    return;
                }

                showToast(res.data.message || 'Checklist saved successfully.', 'success');

                // Update Header
                if (res.data.template) {
                    if (res.data.template.name) {
                        $('#page-title-name').text('Edit: ' + res.data.template.name);
                    }
                    if (res.data.template.slug) {
                        $('#template-slug-badge').text(res.data.template.slug);
                    }
                }

                $saveBtns.html('<i class="bi bi-check2-all me-1 text-success"></i>Saved!');
                setTimeout(function () {
                    $saveBtns.prop('disabled', false).html(originalBtnHtml);
                }, 1200);
            })
            .catch(function (err) {
                var errorMsg = 'Failed to save checklist.';
                if (err.response && err.response.data) {
                    if (err.response.data.message) {
                        errorMsg = err.response.data.message;
                    } else if (err.response.data.errors) {
                        var firstErrKey = Object.keys(err.response.data.errors)[0];
                        errorMsg = err.response.data.errors[firstErrKey][0];
                    }
                }
                showToast(errorMsg, 'danger');
                $saveBtns.prop('disabled', false).html(originalBtnHtml);
            });
        });

        // Add Section
        $(document).on('click', '#btn-add-section, #btn-add-section-bottom', function () {
            $('.empty-sections-msg').remove();
            var sIdx = $('#sections-container .checklist-section-box').length;
            var num = sIdx + 1;

            var sectionHtml = '<div class="checklist-section-box" data-section-idx="' + sIdx + '">' +
                '<div class="d-flex align-items-center gap-2 mb-2">' +
                '<span class="badge bg-secondary text-white mono extra-small section-num-badge">#' + num + '</span>' +
                '<input type="text" name="sections[' + sIdx + '][name]" class="form-control form-control-sm fw-bold" placeholder="Section Name (e.g. Kitchen, Restroom)" required style="font-size: 0.8125rem;">' +
                '<button type="button" class="btn btn-outline-danger btn-sm btn-remove-section" title="Remove Section"><i class="bi bi-trash3"></i></button>' +
                '</div>' +
                '<div class="section-items-container d-flex flex-column gap-1"></div>' +
                '<div class="mt-2 text-start">' +
                '<button type="button" class="btn btn-xs btn-outline-primary extra-small btn-add-item"><i class="bi bi-plus me-1"></i>Add Item</button>' +
                '</div>' +
                '</div>';

            var $newSec = $(sectionHtml);
            $('#sections-container').append($newSec);
            $newSec.find('.btn-add-item').trigger('click');
        });

        // Remove Section
        $(document).on('click', '.btn-remove-section', function () {
            if (!confirm('Remove this section?')) return;
            var $box = $(this).closest('.checklist-section-box');
            $box.fadeOut(150, function () {
                $(this).remove();
                if (!$('#sections-container .checklist-section-box').length) {
                    $('#sections-container').append('<p class="text-muted small py-3 empty-sections-msg text-center">No sections configured yet. Click "Add Section" to begin.</p>');
                } else {
                    reindexSections();
                }
            });
        });

        // Add Item
        $(document).on('click', '.btn-add-item', function () {
            var $sec = $(this).closest('.checklist-section-box');
            var $itemsWrap = $sec.find('.section-items-container');
            $itemsWrap.find('.empty-items-msg').remove();

            var sIdx = $sec.data('section-idx');
            var iIdx = $itemsWrap.find('.checklist-item-row').length;

            var itemHtml = '<div class="checklist-item-row d-flex align-items-center justify-content-between flex-wrap gap-2" data-item-idx="' + iIdx + '">' +
                '<div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0" style="min-width: 180px;">' +
                '<i class="bi bi-grip-vertical text-muted opacity-50" style="font-size: 0.75rem;"></i>' +
                '<input type="text" name="sections[' + sIdx + '][items][' + iIdx + '][label]" class="form-control item-input flex-grow-1 bg-white" placeholder="Item description / instruction" required>' +
                '</div>' +
                '<div class="d-flex align-items-center gap-2 flex-wrap checklist-item-controls">' +
                '<select name="sections[' + sIdx + '][items][' + iIdx + '][item_type]" class="form-select item-select bg-white" style="width: 100px;">' +
                '<option value="yes_no" selected>Yes / No</option>' +
                '<option value="pass_fail">Pass / Fail</option>' +
                '<option value="text">Text Note</option>' +
                '<option value="numeric">Numeric</option>' +
                '<option value="photo">Photo Only</option>' +
                '</select>' +
                '<div class="d-flex align-items-center gap-1 toggle-chips-wrap">' +
                '<label class="toggle-chip" title="Mandatory to complete task"><input type="checkbox" name="sections[' + sIdx + '][items][' + iIdx + '][required]" value="1" checked> <span>Req</span></label>' +
                '<label class="toggle-chip" title="Photo proof required"><input type="checkbox" name="sections[' + sIdx + '][items][' + iIdx + '][is_photo_required]" value="1"> <span>📷 Photo</span></label>' +
                '<label class="toggle-chip" title="Comment note required"><input type="checkbox" name="sections[' + sIdx + '][items][' + iIdx + '][is_comment_required]" value="1"> <span>💬 Note</span></label>' +
                '<label class="toggle-chip" title="Raises supervisor issue on failure"><input type="checkbox" name="sections[' + sIdx + '][items][' + iIdx + '][issue_triggering]" value="1"> <span>⚠️ Issue</span></label>' +
                '</div>' +
                '<button type="button" class="btn-remove-item" title="Remove Item"><i class="bi bi-x-lg"></i></button>' +
                '</div>' +
                '</div>';

            $itemsWrap.append(itemHtml);
        });

        // Remove Item
        $(document).on('click', '.btn-remove-item', function () {
            var $row = $(this).closest('.checklist-item-row');
            var $itemsWrap = $row.closest('.section-items-container');
            $row.fadeOut(150, function () {
                $(this).remove();
                if (!$itemsWrap.find('.checklist-item-row').length) {
                    $itemsWrap.append('<p class="text-muted extra-small mb-1 empty-items-msg">No items in this section.</p>');
                } else {
                    reindexSections();
                }
            });
        });
    })(jQuery);
</script>
@endpush
