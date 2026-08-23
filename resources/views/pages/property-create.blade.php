@extends('layouts.app')

@section('title', 'New Property')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        #map{height:300px;border-radius:6px;background:#eef1f5}
        @media(max-width:576px){#map{height:220px}}
        .leaflet-control-attribution{font-size:10px}

        /* Excel-like dense grid table */
        .excel-grid-wrap {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            overflow-x: auto;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            font-size: 0.8125rem;
        }
        .excel-table th {
            background: #f1f5f9;
            color: #475569;
            font-family: var(--font-mono, monospace);
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 4px 6px;
            border: 1px solid #cbd5e1;
            white-space: nowrap;
            user-select: none;
        }
        .excel-table td {
            padding: 0;
            margin: 0;
            border: 1px solid #e2e8f0;
            height: 26px;
            vertical-align: middle;
            background: #ffffff;
        }
        .excel-table tr:hover td {
            background: #f8fafc;
        }
        .excel-table td.excel-idx {
            width: 32px;
            text-align: center;
            background: #f8fafc;
            color: #94a3b8;
            font-family: var(--font-mono, monospace);
            font-size: 0.6875rem;
            border-right: 1px solid #cbd5e1;
            user-select: none;
        }
        .excel-table td.excel-label {
            padding: 2px 6px;
            font-weight: 600;
            color: #1e293b;
            white-space: nowrap;
            border-right: 1px solid #e2e8f0;
        }
        .excel-table td.excel-rate {
            padding: 2px 6px;
            background: #f8fafc;
            font-family: var(--font-mono, monospace);
            font-weight: 700;
            color: #059669;
            text-align: right;
            white-space: nowrap;
            border-right: 1px solid #e2e8f0;
        }
        .excel-input {
            width: 100%;
            height: 26px;
            min-height: 26px;
            border: 1px solid transparent;
            border-radius: 0;
            padding: 1px 6px;
            font-size: 0.8125rem;
            background: transparent;
            outline: none;
            box-shadow: none;
            transition: background 0.1s ease;
        }
        .excel-input:hover {
            background: #f1f5f9;
        }
        .excel-input:focus {
            background: #ffffff !important;
            border: 1px solid #0284c7 !important;
            box-shadow: inset 0 0 0 1px #0284c7 !important;
            outline: none !important;
        }
        .excel-input.mono {
            font-family: var(--font-mono, monospace);
        }
        .excel-input.text-center {
            text-align: center;
        }
        .excel-input.text-end {
            text-align: right;
        }

        /* Sticky Bottom Save Bar */
        .sticky-bottom-bar {
            position: fixed !important;
            bottom: 0 !important;
            left: 240px !important;
            right: 0 !important;
            z-index: 1045 !important;
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            border-top: 1px solid #cbd5e1 !important;
            padding: 10px 24px !important;
            margin: 0 !important;
            box-shadow: 0 -4px 18px rgba(0,0,0,0.08) !important;
        }
        .page-form-wrapper {
            padding-bottom: 76px !important;
        }
        @media (max-width: 991.98px) {
            .mobile-bottom-nav {
                display: none !important;
            }
            .sticky-bottom-bar {
                left: 0 !important;
                padding: 10px 14px !important;
                box-shadow: 0 -4px 20px rgba(0,0,0,0.15) !important;
            }
        }
    </style>
@endpush

@section('content')
<div class="page-form-wrapper">
    <div class="mb-3 reveal">
        <span class="eyebrow">Properties · Fast Create</span>
        <h1 class="h4 mt-1 mb-1 font-weight-bold">Create Property</h1>
        <p class="text-muted small mb-0">Configure basic details, client association, beds, linen, and specifications.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('properties.store') }}" id="property-create-form">
        @csrf
        
        <!-- Card 1: Core Property Details -->
        <div class="card shadow-sm mb-3">
            <div class="card-header mono">General Information & Location</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="name" class="form-label">Property Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control" placeholder="e.g. Harbourview House" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="client_id" class="form-label">Client / Owner</label>
                        <select name="client_id" id="client_id" class="form-select">
                            <option value="">No Client (Unassigned)</option>
                            @foreach ($clients as $cl)
                                <option value="{{ $cl->id }}" @selected(old('client_id') == $cl->id)>
                                    {{ $cl->name }} {{ $cl->company_name ? "({$cl->company_name})" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="property_code" class="form-label">Property Code</label>
                        <input type="text" id="property_code" name="property_code" value="{{ old('property_code') }}" class="form-control mono" placeholder="e.g. PROP-940">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="property_category_id" class="form-label">Category</label>
                        <select name="property_category_id" id="property_category_id" class="form-select">
                            <option value="">None</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}" @selected(old('property_category_id') == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="service_frequency" class="form-label">Service Frequency</label>
                        <select name="service_frequency" id="service_frequency" class="form-select">
                            <option value="">None</option>
                            @foreach (['daily','weekly','fortnightly','monthly','quarterly','one_off'] as $f)
                                <option value="{{ $f }}" @selected(old('service_frequency') === $f)>{{ ucfirst(str_replace('_',' ',$f)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                        <input type="text" id="address" name="address" value="{{ old('address') }}" class="form-control" required autocomplete="off" placeholder="Search address…">
                        <div id="place-suggestions" class="list-group position-absolute w-100" style="z-index:1050;display:none;max-height:220px;overflow:auto"></div>
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" id="btn-geocode" class="btn btn-sm btn-outline-secondary flex-shrink-0"><i class="bi bi-geo-alt me-1"></i>Locate from address</button>
                            <button type="button" id="btn-my-location" class="btn btn-sm btn-outline-secondary"><i class="bi bi-crosshair me-1"></i>Use my location</button>
                        </div>
                    </div>
                    <div class="col-12">
                        <div id="map" class="border"></div>
                        <div class="d-flex gap-2 mt-2 small">
                            <span class="text-muted">Drag pin to fine-tune. </span>
                            <span class="mono" id="coord-label">—</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="number" step="any" id="latitude" name="latitude" value="{{ old('latitude') }}" class="form-control" placeholder="auto">
                    </div>
                    <div class="col-6 col-md-4">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="number" step="any" id="longitude" name="longitude" value="{{ old('longitude') }}" class="form-control" placeholder="auto">
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="permitted_check_in_radius_meters" class="form-label">Check-in Radius (m)</label>
                        <input type="number" min="0" id="permitted_check_in_radius_meters" name="permitted_check_in_radius_meters" value="{{ old('permitted_check_in_radius_meters', 100) }}" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Property Specifications (Bedrooms, Bathrooms, Parking) -->
        <div class="card shadow-sm mb-3">
            <div class="card-header mono">Property Specifications & Parking</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <label for="bedrooms_count" class="form-label">Bedrooms Count</label>
                        <input type="number" min="0" max="100" id="bedrooms_count" name="bedrooms_count" value="{{ old('bedrooms_count', 1) }}" class="form-control">
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="bathrooms_count" class="form-label">Bathrooms Count</label>
                        <input type="number" min="0" max="100" step="0.5" id="bathrooms_count" name="bathrooms_count" value="{{ old('bathrooms_count', 1.0) }}" class="form-control">
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="parking_type" class="form-label">Parking Type</label>
                        <select name="parking_type" id="parking_type" class="form-select">
                            <option value="none" @selected(old('parking_type')==='none')>No Parking</option>
                            <option value="garage" @selected(old('parking_type')==='garage')>Garage</option>
                            <option value="driveway" @selected(old('parking_type')==='driveway')>Driveway</option>
                            <option value="street" @selected(old('parking_type')==='street')>Street Parking</option>
                            <option value="dedicated_bay" @selected(old('parking_type')==='dedicated_bay')>Dedicated Bay</option>
                            <option value="carport" @selected(old('parking_type')==='carport')>Carport</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="parking_spaces_count" class="form-label">Parking Spaces</label>
                        <input type="number" min="0" max="50" id="parking_spaces_count" name="parking_spaces_count" value="{{ old('parking_spaces_count', 0) }}" class="form-control">
                    </div>
                    <div class="col-12 col-md-6 d-flex align-items-center">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="needs_parking" value="1" id="needs_parking" @checked(old('needs_parking'))>
                            <label class="form-check-label" for="needs_parking">Needs Cleaner Parking Reimbursement</label>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="parking_fee" class="form-label">Parking Fee / Allowance ($)</label>
                        <input type="number" step="0.01" min="0" id="parking_fee" name="parking_fee" value="{{ old('parking_fee', '0.00') }}" class="form-control">
                    </div>
                    <div class="col-12">
                        <label for="parking_instructions" class="form-label">Parking Instructions & Access</label>
                        <textarea id="parking_instructions" name="parking_instructions" rows="2" class="form-control" placeholder="Where to park, permit required, access code for garage…">{{ old('parking_instructions') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Bed Configuration Grid (Excel-like interface) -->
        <div class="card shadow-sm mb-3">
            <div class="card-header mono d-flex justify-content-between align-items-center py-2 px-3">
                <span><i class="bi bi-layout-sidebar me-1"></i>Bed Configurations</span>
                <span class="badge bg-light text-muted border mono extra-small">Default 0 loaded</span>
            </div>
            <div class="card-body p-0">
                <div class="excel-grid-wrap">
                    <table class="excel-table">
                        <thead>
                            <tr>
                                <th style="width: 32px;" class="text-center">#</th>
                                <th style="min-width: 160px;">Bed Type</th>
                                <th style="width: 80px;" class="text-center">Qty</th>
                                <th>Room / Placement (Optional)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bedTypes as $idx => $bt)
                                <tr>
                                    <td class="excel-idx">{{ $loop->iteration }}</td>
                                    <td class="excel-label">
                                        <input type="hidden" name="beds[{{ $idx }}][bed_type_id]" value="{{ $bt->id }}">
                                        <span>{{ $bt->name }}</span>
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="50" name="beds[{{ $idx }}][quantity]" value="{{ old("beds.{$idx}.quantity", 0) }}" class="excel-input mono text-center" placeholder="0">
                                    </td>
                                    <td>
                                        <input type="text" name="beds[{{ $idx }}][room_name]" value="{{ old("beds.{$idx}.room_name", '') }}" class="excel-input" placeholder="e.g. Master, Bedroom 2…">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-2 text-muted small">
                                        No bed types configured. <a href="{{ route('bed-types') }}">Add Bed Types</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Card 4: Linen Requirements & Rates Grid (Excel-like interface) -->
        <div class="card shadow-sm mb-3">
            <div class="card-header mono d-flex justify-content-between align-items-center py-2 px-3">
                <span><i class="bi bi-tag me-1"></i>Linen Requirements & Rates</span>
                <span class="badge bg-light text-muted border mono extra-small">Default rates loaded</span>
            </div>
            <div class="card-body p-0">
                <div class="excel-grid-wrap">
                    <table class="excel-table">
                        <thead>
                            <tr>
                                <th style="width: 32px;" class="text-center">#</th>
                                <th style="min-width: 160px;">Linen Item</th>
                                <th style="width: 90px;" class="text-end">Std Rate</th>
                                <th style="width: 80px;" class="text-center">Qty</th>
                                <th style="width: 100px;" class="text-end">Custom ($)</th>
                                <th>Notes / Instructions (Optional)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($linenTypes as $idx => $lt)
                                <tr>
                                    <td class="excel-idx">{{ $loop->iteration }}</td>
                                    <td class="excel-label">
                                        <input type="hidden" name="linens[{{ $idx }}][linen_type_id]" value="{{ $lt->id }}">
                                        <span>{{ $lt->name }}</span>
                                    </td>
                                    <td class="excel-rate">
                                        ${{ number_format($lt->rate, 2) }}
                                    </td>
                                    <td>
                                        <input type="number" min="0" max="200" name="linens[{{ $idx }}][quantity]" value="{{ old("linens.{$idx}.quantity", 0) }}" class="excel-input mono text-center" placeholder="0">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="linens[{{ $idx }}][custom_rate]" value="{{ old("linens.{$idx}.custom_rate", '') }}" class="excel-input mono text-end" placeholder="${{ number_format($lt->rate, 2) }}">
                                    </td>
                                    <td>
                                        <input type="text" name="linens[{{ $idx }}][notes]" value="{{ old("linens.{$idx}.notes", '') }}" class="excel-input" placeholder="e.g. 2 per bed, folding note…">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-2 text-muted small">
                                        No linen types configured. <a href="{{ route('linen-types') }}">Add Linen Types</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Card 5: Optional Details & Access Instructions -->
        <div class="card shadow-sm mb-3">
            <button type="button" class="card-header mono w-100 text-start bg-transparent border-0 d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#optionalSection">
                <span><i class="bi bi-plus-circle me-1"></i>Optional Contacts & Access Details</span><i class="bi bi-chevron-down"></i>
            </button>
            <div id="optionalSection" class="collapse">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-4"><label for="contact_name" class="form-label">Contact Name</label><input type="text" id="contact_name" name="contact_name" value="{{ old('contact_name') }}" class="form-control"></div>
                        <div class="col-12 col-md-4"><label for="contact_phone" class="form-label">Contact Phone</label><input type="text" id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}" class="form-control"></div>
                        <div class="col-12 col-md-4"><label for="contact_email" class="form-label">Contact Email</label><input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email') }}" class="form-control"></div>
                        <div class="col-6 col-md-4"><label for="postal_code" class="form-label">Postal Code</label><input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" class="form-control"></div>
                        <div class="col-6 col-md-8"><label for="tags" class="form-label">Tags</label><select name="tags[]" id="tags" class="form-select" multiple size="2">@foreach ($tags as $t)<option value="{{ $t->id }}" @selected(in_array($t->id, old('tags',[])))>{{ $t->name }}</option>@endforeach</select></div>
                        <div class="col-12 col-md-6"><label for="access_instructions" class="form-label">Access Instructions</label><textarea id="access_instructions" name="access_instructions" rows="2" class="form-control">{{ old('access_instructions') }}</textarea></div>
                        <div class="col-12 col-md-6"><label for="safety_instructions" class="form-label">Safety Instructions</label><textarea id="safety_instructions" name="safety_instructions" rows="2" class="form-control">{{ old('safety_instructions') }}</textarea></div>
                        <div class="col-12"><label for="internal_notes" class="form-label">Internal Notes</label><textarea id="internal_notes" name="internal_notes" rows="2" class="form-control">{{ old('internal_notes') }}</textarea></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 6: Billing & Cleaner Rates -->
        <div class="card shadow-sm mb-3">
            <div class="card-header mono">Billing & Payout</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3"><label class="form-label">Hours</label><input type="number" min="0" max="240" name="cleaning_duration_hours" value="{{ old('cleaning_duration_hours') }}" class="form-control" placeholder="H"></div>
                    <div class="col-6 col-md-3"><label class="form-label">Minutes</label><input type="number" min="0" max="59" name="cleaning_duration_minutes" value="{{ old('cleaning_duration_minutes') }}" class="form-control" placeholder="M"></div>
                    <div class="col-6 col-md-3"><label for="client_fixed_amount" class="form-label">Client Invoiced ($)</label><input type="number" step="0.01" min="0" id="client_fixed_amount" name="client_fixed_amount" value="{{ old('client_fixed_amount') }}" class="form-control"></div>
                    <div class="col-6 col-md-3"><label for="cleaner_pay_type" class="form-label">Cleaner Pay Type</label><select name="cleaner_pay_type" id="cleaner_pay_type" class="form-select"><option value="per_hour" @selected(old('cleaner_pay_type','per_hour')==='per_hour')>Per hour</option><option value="fixed" @selected(old('cleaner_pay_type')==='fixed')>Fixed</option></select></div>
                    <div class="col-6 col-md-6" id="cf-fixed"><label for="cleaner_fixed_amount" class="form-label">Fixed Amount ($)</label><input type="number" step="0.01" min="0" id="cleaner_fixed_amount" name="cleaner_fixed_amount" value="{{ old('cleaner_fixed_amount') }}" class="form-control"></div>
                    <div class="col-6 col-md-6" id="cf-rate"><label for="cleaner_rate_per_hour" class="form-label">Cleaner Rate / Hour ($)</label><input type="number" step="0.01" min="0" id="cleaner_rate_per_hour" name="cleaner_rate_per_hour" value="{{ old('cleaner_rate_per_hour') }}" class="form-control"></div>
                </div>
            </div>
        </div>
    </form>

    <!-- Sticky Bottom Save Bar -->
    <div class="sticky-bottom-bar d-flex align-items-center justify-content-end gap-2">
        <a href="{{ route('properties') }}" class="btn btn-outline-secondary btn-sm px-3 flex-fill flex-md-grow-0">Cancel</a>
        <button type="submit" form="property-create-form" class="btn btn-primary btn-sm fw-bold px-4 flex-fill flex-md-grow-0">
            <i class="bi bi-building-check me-1"></i>Create Property
        </button>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function($){
            // Cleaner Pay Type toggle
            function syncPay(){ var t=$('#cleaner_pay_type').val(); $('#cf-fixed').toggle(t==='fixed'); $('#cf-rate').toggle(t==='per_hour'); }
            $('#cleaner_pay_type').on('change',syncPay); syncPay();

            // Map and geocoding setup
            var $address=$('#address'), $suggestions=$('#place-suggestions'), $lat=$('#latitude'), $lng=$('#longitude'), $placeId=$('#google_place_id'), $formatted=$('#formatted_address'), $coordLabel=$('#coord-label');
            var debounce=null, map=null, marker=null;

            function updateLabel(lat,lng){ $coordLabel.text(lat.toFixed(5)+', '+lng.toFixed(5)); }
            function initMap(lat,lng){
                if(!window.L) return;
                if(!map){
                    map=L.map('map').setView([lat||-40.9,lng||174.9], lat?16:5);
                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'© OSM'}).addTo(map);
                    map.on('click',function(e){ setPin(e.latlng.lat,e.latlng.lng,true); });
                }
                setPin(lat,lng,false);
            }
            function setPin(lat,lng,pan){
                if(!lat||!lng) return;
                $lat.val(lat.toFixed(7)); $lng.val(lng.toFixed(7));
                updateLabel(lat,lng);
                if(marker) map.removeLayer(marker);
                marker=L.marker([lat,lng],{draggable:true}).addTo(map);
                if(pan) map.setView([lat,lng],16);
                marker.on('dragend',function(){ var p=marker.getLatLng(); $lat.val(p.lat.toFixed(7)); $lng.val(p.lng.toFixed(7)); updateLabel(p.lat,p.lng); });
            }

            initMap(parseFloat($lat.val()),parseFloat($lng.val()));
        })(jQuery);
    </script>
@endpush
