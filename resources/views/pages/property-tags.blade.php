@extends('layouts.app')

@section('title', 'Property Tags')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Properties · Config</span>
            <h1 class="h3 mt-1 mb-0">Property tags</h1>
        </div>
        <a href="{{ route('properties') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Registry
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-4 reveal" style="--d: 80ms">
        <div class="card-header mono">New tag</div>
        <div class="card-body">
            <form method="POST" action="{{ route('property-tags.store') }}" class="row g-2">
                @csrf
                <div class="col-md-4">
                    <label for="name" class="form-label visually-hidden">Name</label>
                    <input type="text" id="name" name="name" class="form-control form-control-sm" placeholder="Name" required>
                </div>
                <div class="col-md-3">
                    <label for="color" class="form-label visually-hidden">Color</label>
                    <input type="text" id="color" name="color" class="form-control form-control-sm" placeholder="Color (optional)">
                </div>
                <div class="col-md-3">
                    <label for="description" class="form-label visually-hidden">Description</label>
                    <input type="text" id="description" name="description" class="form-control form-control-sm" placeholder="Description (optional)">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-plus me-1" aria-hidden="true"></i>Add tag
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mb-4 reveal" style="--d: 120ms">
        <div class="card-header mono">Merge duplicates</div>
        <div class="card-body">
            <form method="POST" action="{{ route('property-tags.merge') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-5">
                    <label for="keep_tag_id" class="form-label visually-hidden">Keep tag</label>
                    <select name="keep_tag_id" id="keep_tag_id" class="form-select form-select-sm" required>
                        <option value="">Keep this tag…</option>
                        @foreach ($tags as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="merge_tag_id" class="form-label visually-hidden">Merge tag</label>
                    <select name="merge_tag_id" id="merge_tag_id" class="form-select form-select-sm" required>
                        <option value="">…merge this tag into it</option>
                        @foreach ($tags as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-outline-secondary w-100" onclick="return confirm('Merge tags? Pivot rows are repointed, duplicate is deleted.')">
                        <i class="bi bi-shuffle me-1" aria-hidden="true"></i>Merge
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm reveal" style="--d: 160ms">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-cards">
                <thead>
                    <tr>
                        <th>Tag</th>
                        <th>Color</th>
                        <th>Properties</th>
                        <th>Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tags as $tag)
                        <tr>
                            <td data-label="Tag">
                                <form method="POST" action="{{ route('property-tags.update', $tag) }}" class="d-flex gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" value="{{ $tag->name }}" class="form-control form-control-sm" style="max-width: 200px" required>
                                    <input type="text" name="color" value="{{ $tag->color }}" class="form-control form-control-sm" style="max-width: 120px" placeholder="Color">
                                    <div class="form-check d-flex align-items-center m-0">
                                        <input class="form-check-input" type="checkbox" name="active" value="1" id="tag-active-{{ $tag->id }}" @checked($tag->active)>
                                        <label class="form-check-label small ms-1" for="tag-active-{{ $tag->id }}">Active</label>
                                    </div>
                                    <button class="btn btn-sm btn-outline-secondary" title="Save">
                                        <i class="bi bi-check2" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </td>
                            <td data-label="Color">
                                @if($tag->color)
                                    <span class="status-badge" style="--dot: {{ $tag->color }}">{{ $tag->color }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td data-label="Properties">{{ $tag->properties_count }}</td>
                            <td data-label="Active"><span class="status-badge status-{{ $tag->active ? 'active' : 'muted' }}">{{ $tag->active ? 'active' : 'inactive' }}</span></td>
                            <td data-label=""></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-tag"></i></span>
                                    No tags yet.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 reveal" style="--d: 200ms">{{ $tags->links() }}</div>
@endsection
