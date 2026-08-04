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
                    <div class="ev-toolbar d-flex flex-wrap gap-2 mb-2">
                        <label class="btn btn-outline-secondary btn-sm mb-0">
                            <i class="bi bi-images me-1" aria-hidden="true"></i>Choose photos
                            <input type="file" accept="image/*" multiple class="visually-hidden ev-file" data-type="{{ $type }}">
                        </label>
                        <label class="btn btn-outline-secondary btn-sm mb-0">
                            <i class="bi bi-camera me-1" aria-hidden="true"></i>Take photo
                            <input type="file" accept="image/*" capture="environment" class="visually-hidden ev-capture" data-type="{{ $type }}">
                        </label>
                        <button type="button" class="btn btn-primary btn-sm ev-upload" data-type="{{ $type }}" disabled>
                            <i class="bi bi-cloud-arrow-up me-1" aria-hidden="true"></i>Upload
                        </button>
                    </div>
                    <div class="text-muted small mb-2 ev-msg" data-type="{{ $type }}"></div>
                    <div class="ev-preview-grid d-flex flex-wrap gap-2 mb-3" data-type="{{ $type }}"></div>
                    <div class="ev-photos d-flex flex-wrap gap-2" data-type="{{ $type }}">
                        @foreach ($task->evidence->where('evidence_type', $type) as $evidence)
                            <div class="text-center" title="{{ $evidence->original_filename }}">
                                <img src="{{ route('evidence.view', $evidence) }}" alt="{{ $evidence->original_filename }}"
                                     class="rounded border" style="width:84px;height:84px;object-fit:cover;">
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
