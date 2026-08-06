<div class="card shadow-sm reveal" style="--d: 140ms">
    <div class="card-header mono">Evidence photos</div>
    <div class="card-body">
        <ul class="nav nav-tabs" id="evidence-tabs" role="tablist">
            @foreach (['before', 'during', 'after', 'issue', 'safety', 'access_problem', 'other'] as $index => $type)
                <li class="nav-item" role="presentation">
                    <button class="nav-link small {{ $index === 0 ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#ev-{{ $type }}" type="button" role="tab"
                            data-type="{{ $type }}">
                        {{ ucfirst(str_replace('_', ' ', $type)) }}
                        <span class="badge text-bg-secondary ev-count" id="count-{{ $type }}">{{ $task->evidence->where('evidence_type', $type)->count() }}</span>
                    </button>
                </li>
            @endforeach
        </ul>
        <div class="tab-content pt-3">
            @foreach (['before', 'during', 'after', 'issue', 'safety', 'access_problem', 'other'] as $index => $type)
                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="ev-{{ $type }}" role="tabpanel">
                    <!-- File picker buttons -->
                    <div class="ev-toolbar d-flex flex-wrap gap-2 mb-2">
                        <label class="btn btn-outline-secondary btn-touch mb-0">
                            <i class="bi bi-images me-1" aria-hidden="true"></i>Choose photos
                            <input type="file" accept="image/*" multiple class="visually-hidden ev-file" data-type="{{ $type }}">
                        </label>
                        <label class="btn btn-outline-secondary btn-touch mb-0">
                            <i class="bi bi-camera me-1" aria-hidden="true"></i>Take photo
                            <input type="file" accept="image/*" capture="environment" class="visually-hidden ev-capture" data-type="{{ $type }}">
                        </label>
                    </div>

                    <!-- 1. Draft Image Previews Grid (ABOVE Upload Button) -->
                    <div class="ev-preview-grid d-flex flex-wrap gap-2 mb-2" data-type="{{ $type }}"></div>
                    <div class="text-muted small mb-2 ev-msg" data-type="{{ $type }}"></div>

                    <!-- 2. Upload Button (HIDDEN when no images selected) -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary btn-touch ev-upload d-none" data-type="{{ $type }}">
                            <i class="bi bi-cloud-arrow-up me-1" aria-hidden="true"></i>Upload Photos
                        </button>
                    </div>

                    <!-- 3. Uploaded Evidence Photo Gallery (Delete Button ONLY for Admin & Supervisor) -->
                    <div class="ev-photos d-flex flex-wrap gap-2" data-type="{{ $type }}">
                        @foreach ($task->evidence->where('evidence_type', $type) as $evidence)
                            <div class="position-relative text-center ev-photo-item" data-evidence-id="{{ $evidence->id }}" title="{{ $evidence->original_filename }}">
                                <img src="{{ route('evidence.view', $evidence) }}" alt="{{ $evidence->original_filename }}"
                                     class="rounded border" style="width:84px;height:84px;object-fit:cover;" data-ev-lightbox>
                                
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
</div>

{{-- Lightbox: click a thumbnail to browse every photo in that category. --}}
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
