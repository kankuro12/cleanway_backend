@php
    $initialSections = $initialSections ?? [];
@endphp

<div class="card shadow-sm mb-3">
    <div class="card-header mono d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-check me-1"></i>Requirements</span>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-add-req-section">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add section
        </button>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">Categorized requirements. Each item: description + photo / comment needs. These auto-carry into every task created for this property as subtasks.</p>
        <input type="hidden" name="requirements_json" id="requirements-json" value="">

        <div class="d-flex gap-2 overflow-auto mb-3 req-tab-bar" style="white-space: nowrap; scrollbar-width: none;">
            @foreach($initialSections as $sectionName => $items)
                <button type="button" class="btn btn-sm req-tab {{ $loop->first ? 'btn-primary active' : 'btn-outline-secondary' }}" data-tab="{{ $loop->index }}">
                    {{ $sectionName }} ({{ count($items) }})
                </button>
            @endforeach
        </div>
        @if(empty($initialSections))
            <p class="text-muted small mb-0" id="req-editor-empty">No requirements yet — add a section to start.</p>
        @endif

        <div id="req-editor">
            @foreach($initialSections as $sectionName => $items)
                <div class="req-section req-pane {{ $loop->first ? '' : 'd-none' }}" data-pane="{{ $loop->index }}">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <input type="text" class="form-control form-control-sm req-section-name" value="{{ $sectionName }}" placeholder="Section name (e.g. Kitchen, Bathroom)">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-add-req-item" title="Add requirement"><i class="bi bi-plus-lg"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-del-req-section" title="Remove section"><i class="bi bi-trash"></i></button>
                    </div>
                    @foreach($items as $item)
                        <div class="req-item row g-2 align-items-center mb-1">
                            <div class="col-12 col-md-6">
                                <input type="text" class="form-control form-control-sm req-item-label" value="{{ $item['label'] }}" placeholder="Requirement description…">
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input req-item-photo" type="checkbox" role="switch" @checked($item['is_photo_required'] ?? false)>
                                    <label class="form-check-label small">Need photo</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input req-item-comment" type="checkbox" role="switch" @checked($item['is_comment_required'] ?? false)>
                                    <label class="form-check-label small">Need comment</label>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-del-req-item" title="Remove requirement"><i class="bi bi-x-lg"></i></button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function ($) {
            var sectionSeq = {{ count($initialSections) }};

            function collectRequirements() {
                var sections = [];
                $('#req-editor .req-section').each(function () {
                    var name = $(this).find('.req-section-name').val().trim() || 'General';
                    $(this).find('.req-item').each(function () {
                        var label = $(this).find('.req-item-label').val().trim();
                        if (!label) return;
                        sections.push({
                            section_name: name,
                            label: label,
                            is_photo_required: $(this).find('.req-item-photo').is(':checked'),
                            is_comment_required: $(this).find('.req-item-comment').is(':checked')
                        });
                    });
                });
                $('#requirements-json').val(JSON.stringify(sections));
                return sections;
            }

            function renderTabs() {
                var tabs = '', panes = [], idx = 0;
                $('#req-editor .req-section').each(function () {
                    var $sec = $(this);
                    var name = $sec.find('.req-section-name').val().trim() || 'Section';
                    var count = $sec.find('.req-item').length;
                    tabs += '<button type="button" class="btn btn-sm req-tab ' + (idx === 0 ? 'btn-primary active' : 'btn-outline-secondary') + '" data-tab="' + idx + '">' + name + ' (' + count + ')</button>';
                    $sec.attr('data-pane', idx).toggleClass('d-none', idx !== 0);
                    idx++;
                });
                $('.req-tab-bar').html(tabs);
                if (!tabs) { $('.req-tab-bar').html(''); $('#req-editor-empty').show(); }
                else { $('#req-editor-empty').hide(); }
            }

            $(document).on('click', '.req-tab', function () {
                var tab = $(this).data('tab');
                $('.req-tab').removeClass('btn-primary active').addClass('btn-outline-secondary');
                $(this).removeClass('btn-outline-secondary').addClass('btn-primary active');
                $('#req-editor .req-pane').addClass('d-none');
                $('#req-editor .req-pane[data-pane="' + tab + '"]').removeClass('d-none');
            });

            // Editing a section name updates its tab header live.
            $(document).on('input', '.req-section-name', function () {
                var $sec = $(this).closest('.req-section');
                var idx = $('#req-editor .req-section').index($sec);
                var name = $(this).val().trim() || 'Section';
                var count = $sec.find('.req-item').length;
                $('.req-tab[data-tab="' + idx + '"]').text(name + ' (' + count + ')');
            });

            function addSection(name) {
                var $section = $('<div class="req-section req-pane d-none"></div>');
                $section.append(
                    '<div class="d-flex align-items-center gap-2 mb-2">' +
                    '<input type="text" class="form-control form-control-sm req-section-name" value="' + (name || '') + '" placeholder="Section name (e.g. Kitchen, Bathroom)">' +
                    '<button type="button" class="btn btn-sm btn-outline-danger btn-add-req-item" title="Add requirement"><i class="bi bi-plus-lg"></i></button>' +
                    '<button type="button" class="btn btn-sm btn-outline-secondary btn-del-req-section" title="Remove section"><i class="bi bi-trash"></i></button>' +
                    '</div>'
                );
                $('#req-editor').append($section);
                addItem($section);
                renderTabs();
                $('.req-tab').last().trigger('click');
            }

            function addItem($section) {
                var $item = $('<div class="req-item row g-2 align-items-center mb-1"></div>');
                $item.append(
                    '<div class="col-12 col-md-6"><input type="text" class="form-control form-control-sm req-item-label" placeholder="Requirement description…"></div>' +
                    '<div class="col-6 col-md-2"><div class="form-check form-switch mt-2"><input class="form-check-input req-item-photo" type="checkbox" role="switch"><label class="form-check-label small">Need photo</label></div></div>' +
                    '<div class="col-6 col-md-2"><div class="form-check form-switch mt-2"><input class="form-check-input req-item-comment" type="checkbox" role="switch"><label class="form-check-label small">Need comment</label></div></div>' +
                    '<div class="col-md-2 text-end"><button type="button" class="btn btn-sm btn-outline-danger btn-del-req-item" title="Remove requirement"><i class="bi bi-x-lg"></i></button></div>'
                );
                $section.append($item);
                renderTabs();
                $item.find('.req-item-label').trigger('focus');
            }

            $(document).on('click', '#btn-add-req-section', function () { addSection(''); });
            $(document).on('click', '.btn-add-req-item', function () { addItem($(this).closest('.req-section')); });
            $(document).on('click', '.btn-del-req-item', function () {
                $(this).closest('.req-item').remove();
                renderTabs();
            });
            $(document).on('click', '.btn-del-req-section', function () {
                $(this).closest('.req-section').remove();
                renderTabs();
                $('.req-tab').first().trigger('click');
            });

            $('form').on('submit', function () { collectRequirements(); });

            // Expose for task-create auto-fill from property.
            window.cleanwayRequirements = {
                collect: collectRequirements,
                addSection: addSection,
                addItem: addItem,
                renderTabs: renderTabs
            };
        })(jQuery);
    </script>
@endpush
