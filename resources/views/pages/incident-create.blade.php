@extends('layouts.app')

@section('title', 'Raise Incident')

@section('content')
    <div class="mb-4 reveal">
        <span class="eyebrow">Safety · Incidents</span>
        <h1 class="h3 mt-1 mb-0">Raise incident</h1>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('incidents.store') }}" enctype="multipart/form-data" class="reveal" style="--d: 80ms">
        @csrf
        <div class="card shadow-sm mb-3">
            <div class="card-header mono">Details</div>
            <div class="card-body">
                <div class="row g-3">
                    @if($task)
                        <div class="col-md-6">
                            <label for="task_id" class="form-label">Task</label>
                            <select name="task_id" id="task_id" class="form-select">
                                <option value="{{ $task->id }}" selected>{{ $task->title }} (#{{ $task->reference_number }})</option>
                            </select>
                        </div>
                    @endif
                    <div class="col-md-4">
                        <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category" id="category" class="form-select" required>
                            @foreach (['property_access_problem', 'missing_key', 'incorrect_access_code', 'damaged_equipment', 'property_damage', 'safety_hazard', 'missing_supplies', 'unsafe_situation', 'task_cannot_be_completed', 'other'] as $category)
                                <option value="{{ $category }}">{{ ucfirst(str_replace('_', ' ', $category)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="severity" class="form-label">Severity <span class="text-danger">*</span></label>
                        <select name="severity" id="severity" class="form-select" required>
                            @foreach (['low', 'medium', 'high', 'critical'] as $severity)
                                <option value="{{ $severity }}">{{ ucfirst($severity) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="property_id" class="form-label">Property</label>
                        <select name="property_id" id="property_id" class="form-select">
                            <option value="">None</option>
                            @foreach (\App\Models\Property::with('client:id,name,company_name')->orderBy('name')->get() as $property)
                                <option value="{{ $property->id }}">{{ $property->dropdown_label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea id="description" name="description" rows="4" class="form-control" required>{{ old('description') }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="number" step="any" min="-90" max="90" id="latitude" name="latitude" class="form-control" placeholder="-90 to 90">
                    </div>
                    <div class="col-md-4">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="number" step="any" min="-180" max="180" id="longitude" name="longitude" class="form-control" placeholder="-180 to 180">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" id="btn-gps" class="btn btn-outline-secondary btn-touch w-100">
                            <i class="bi bi-crosshair me-1" aria-hidden="true"></i>Use my location
                        </button>
                    </div>
                    <div class="col-md-4">
                        <label for="evidence" class="form-label">Photos</label>
                        <input type="file" id="evidence" name="evidence[]" class="form-control" multiple accept="image/*" capture="environment">
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>Raise incident
        </button>
        <a href="{{ route('incidents') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
    </form>
@endsection

@push('scripts')
    <script>
        (function ($) {
            $('#btn-gps').on('click', function () {
                var btn = $(this);
                if (!navigator.geolocation) {
                    alert('Geolocation is not supported by this browser.');
                    return;
                }
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Locating…');
                navigator.geolocation.getCurrentPosition(function (pos) {
                    $('#latitude').val(pos.coords.latitude.toFixed(6));
                    $('#longitude').val(pos.coords.longitude.toFixed(6));
                    btn.prop('disabled', false).html('<i class="bi bi-crosshair me-1" aria-hidden="true"></i>Use my location');
                }, function () {
                    btn.prop('disabled', false).html('<i class="bi bi-crosshair me-1" aria-hidden="true"></i>Use my location');
                });
            });
        })(jQuery);
    </script>
@endpush
