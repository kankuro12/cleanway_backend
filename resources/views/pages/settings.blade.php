@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">System · Settings</span>
            <h2 class="h3 mt-1 mb-0">Organization settings</h2>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}" class="reveal" style="--d: 80ms">
        @csrf
        <div class="card shadow-sm mb-3">
            <div class="card-header mono">Organization</div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach ($settings->where('scope', 'organization') as $setting)
                        <div class="col-md-6">
                            <label for="settings[organization:{{ $setting->key }}]" class="form-label">{{ ucwords(str_replace('_', ' ', $setting->key)) }}</label>
                            <input type="text" id="settings[organization:{{ $setting->key }}]" name="settings[organization:{{ $setting->key }}]"
                                   value="{{ $setting->value }}" class="form-control form-control-sm">
                            <div class="form-text">{{ $setting->description }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header mono">System</div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach ($settings->where('scope', 'system') as $setting)
                        <div class="col-md-6">
                            <label for="settings[system:{{ $setting->key }}]" class="form-label">{{ ucwords(str_replace('_', ' ', $setting->key)) }}</label>
                            <input type="text" id="settings[system:{{ $setting->key }}]" name="settings[system:{{ $setting->key }}]"
                                   value="{{ $setting->value }}" class="form-control form-control-sm">
                            <div class="form-text">{{ $setting->description }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <button class="btn btn-primary">
            <i class="bi bi-check2 me-1" aria-hidden="true"></i>Save settings
        </button>
    </form>
@endsection
