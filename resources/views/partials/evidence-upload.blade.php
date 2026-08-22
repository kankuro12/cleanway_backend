@php
    $evidenceTypes = [
        'all' => 'All',
        'before' => 'Before',
        'during' => 'During',
        'after' => 'After',
        'issue' => 'Issue',
        'safety' => 'Safety',
        'access_problem' => 'Access problem',
        'other' => 'Other'
    ];
@endphp

<div>
    <!-- Single-Line Scrollable Category Tabs Bar with "All" selected first -->
    <div class="req-category-tab-bar mb-3">
        @foreach ($evidenceTypes as $type => $label)
            <a class="req-tab-item ev-tab-btn {{ $loop->first ? 'active' : '' }}" data-type="{{ $type }}" style="cursor: pointer;">
                {{ $label }}
                @if($type === 'all')
                    <span class="badge text-bg-secondary ev-count ms-1" id="count-all">{{ $task->evidence->count() }}</span>
                @else
                    <span class="badge text-bg-secondary ev-count ms-1" id="count-{{ $type }}">{{ $task->evidence->where('evidence_type', $type)->count() }}</span>
                @endif
            </a>
        @endforeach
    </div>

    <div class="tab-content">
        @foreach ($evidenceTypes as $type => $label)
            @php
                $uploadType = ($type === 'all') ? 'other' : $type;
                $items = ($type === 'all') ? $task->evidence : $task->evidence->where('evidence_type', $type);
            @endphp
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="ev-{{ $type }}" role="tabpanel">
                @if($canEdit ?? true)
                <!-- File picker buttons -->
                <div class="ev-toolbar d-flex flex-wrap gap-2 mb-3">
                    <label class="btn btn-outline-secondary btn-touch rounded-pill mb-0">
                        <i class="bi bi-images me-1" aria-hidden="true"></i>Choose photos
                        <input type="file" accept="image/*" multiple class="visually-hidden ev-file" data-type="{{ $uploadType }}">
                    </label>
                    <label class="btn btn-outline-primary btn-touch rounded-pill mb-0">
                        <i class="bi bi-camera me-1" aria-hidden="true"></i>Take photo
                        <input type="file" accept="image/*" capture="environment" class="visually-hidden ev-capture" data-type="{{ $uploadType }}">
                    </label>
                </div>

                <!-- 1. Draft Image Previews Grid -->
                <div class="ev-preview-grid d-flex flex-wrap gap-2 mb-2" data-type="{{ $uploadType }}"></div>
                <div class="text-muted small mb-2 ev-msg" data-type="{{ $uploadType }}"></div>

                <!-- 2. Upload Button -->
                <div class="mb-3">
                    <button type="button" class="btn btn-primary btn-touch rounded-pill ev-upload d-none" data-type="{{ $uploadType }}">
                        <i class="bi bi-cloud-arrow-up me-1" aria-hidden="true"></i>Upload Photos
                    </button>
                </div>
                @endif

                <!-- 3. Uploaded Evidence Photo Gallery -->
                <div class="ev-photos d-flex flex-wrap gap-2" data-type="{{ $type }}">
                    @foreach ($items as $evidence)
                        <div class="position-relative text-center ev-photo-item" data-evidence-id="{{ $evidence->id }}" title="{{ $evidence->original_filename }}">
                            <img src="{{ route('evidence.view', $evidence) }}" alt="{{ $evidence->original_filename }}"
                                 class="rounded border shadow-sm" style="width:88px;height:88px;object-fit:cover;" data-ev-lightbox>
                            
                            @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'supervisor'], true))
                                <button type="button" class="btn btn-danger btn-sm p-0 position-absolute top-0 end-0 rounded-circle btn-delete-uploaded-evidence"
                                        style="width: 22px; height: 22px; line-height: 1; transform: translate(30%, -30%); shadow: 0 2px 6px rgba(0,0,0,0.25);"
                                        data-url="{{ route('tasks.evidence.delete', [$task, $evidence]) }}" title="Delete photo">
                                    <i class="bi bi-x" aria-hidden="true"></i>
                                </button>
                            @endif

                            @if($evidence->processing_status !== 'ready')
                                <div class="small text-muted">processing</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Lightbox: click a thumbnail to browse every photo. --}}
<div class="modal fade" id="evLightbox" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-secondary">
                <span class="mono small text-light" id="evLbCaption" role="status"></span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-2">
                <img id="evLbImg" src="" alt="Evidence photo" class="img-fluid mx-auto d-block" style="max-height:70vh;">
                <div class="ev-lb-thumbs d-flex justify-content-center flex-wrap gap-1 mt-2" role="listbox" aria-label="Photo thumbnails"></div>
                <div class="d-flex justify-content-center gap-2 mt-2">
                    <button type="button" class="btn btn-sm btn-outline-light ev-lb-prev" aria-label="Previous photo">
                        <i class="bi bi-chevron-left me-1" aria-hidden="true"></i>Prev
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light ev-lb-next" aria-label="Next photo">
                        Next<i class="bi bi-chevron-right ms-1" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
