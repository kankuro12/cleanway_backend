@extends('layouts.app')

@section('title', 'Mass Property Management')

@push('styles')
<style>
    /* Fullscreen Excel Sheet Container */
    .mass-manage-container {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 70px);
        margin: -1.25rem;
        background: #f8fafc;
    }
    .mass-header-bar {
        padding: 8px 16px;
        background: #ffffff;
        border-bottom: 1px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        z-index: 20;
    }
    .mass-grid-scroll {
        flex: 1 1 auto;
        overflow: auto;
        background: #ffffff;
        position: relative;
    }

    /* Spreadsheet Grid Styles */
    .excel-sheet-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        font-size: 0.8125rem;
        background: #ffffff;
    }
    .excel-sheet-table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f1f5f9;
        color: #334155;
        font-family: var(--font-mono, monospace);
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 4px 6px;
        border: 1px solid #cbd5e1;
        white-space: nowrap;
        user-select: none;
        box-shadow: 0 1px 2px rgba(0,0,0,0.06);
    }
    .excel-sheet-table thead th.group-header {
        text-align: center;
        background: #e2e8f0;
        color: #0f172a;
        font-size: 0.75rem;
        padding: 2px 6px;
    }
    .excel-sheet-table thead th.group-bed {
        background: #e0f2fe;
        color: #0369a1;
        border-color: #bae6fd;
    }
    .excel-sheet-table thead th.group-linen {
        background: #fef3c7;
        color: #b45309;
        border-color: #fde68a;
    }
    .excel-sheet-table tbody td {
        padding: 0;
        margin: 0;
        border: 1px solid #e2e8f0;
        height: 28px;
        vertical-align: middle;
        background: #ffffff;
    }
    .excel-sheet-table tbody tr:hover td {
        background: #f8fafc;
    }
    .excel-sheet-table tbody tr.row-deleted td {
        background: #fee2e2 !important;
        text-decoration: line-through;
        opacity: 0.6;
    }

    /* Fixed Row Index Column */
    .cell-idx {
        width: 36px;
        text-align: center;
        background: #f8fafc;
        color: #94a3b8;
        font-family: var(--font-mono, monospace);
        font-size: 0.6875rem;
        font-weight: 600;
        border-right: 1px solid #cbd5e1 !important;
        user-select: none;
    }

    /* Cell Inputs */
    .sheet-input {
        width: 100%;
        height: 28px;
        min-height: 28px;
        border: 1px solid transparent;
        border-radius: 0;
        padding: 1px 6px;
        font-size: 0.8125rem;
        background: transparent;
        outline: none;
        box-shadow: none;
        margin: 0;
        color: #1e293b;
    }
    .sheet-input:hover {
        background: #f1f5f9;
    }
    .sheet-input:focus {
        background: #ffffff !important;
        border: 1px solid #0284c7 !important;
        box-shadow: inset 0 0 0 1px #0284c7 !important;
        outline: none !important;
    }
    .sheet-select {
        width: 100%;
        height: 28px;
        min-height: 28px;
        border: 1px solid transparent;
        border-radius: 0;
        padding: 1px 18px 1px 6px;
        font-size: 0.75rem;
        background: transparent;
        outline: none;
        cursor: pointer;
    }
    .sheet-select:focus {
        background: #ffffff !important;
        border: 1px solid #0284c7 !important;
        box-shadow: inset 0 0 0 1px #0284c7 !important;
        outline: none !important;
    }
    .sheet-input.mono {
        font-family: var(--font-mono, monospace);
    }
    .sheet-input.text-center {
        text-align: center;
    }
    .sheet-input.text-end {
        text-align: right;
    }

    /* Cell Checkbox */
    .cell-checkbox-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 28px;
    }
    .cell-checkbox-wrap input[type="checkbox"] {
        margin: 0;
        width: 14px;
        height: 14px;
        cursor: pointer;
    }

    /* Remove Row Button */
    .btn-row-del {
        width: 24px;
        height: 24px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 3px;
        color: #94a3b8;
        border: none;
        background: transparent;
    }
    .btn-row-del:hover {
        color: #ef4444;
        background: #fee2e2;
    }

    /* Sticky Bottom Toolbar */
    .mass-footer-bar {
        padding: 8px 16px;
        background: #ffffff;
        border-top: 1px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        z-index: 20;
    }
</style>
@endpush

@section('content')
<div class="mass-manage-container">
    <!-- Top Action Bar -->
    <div class="mass-header-bar">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('properties') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center">
                <i class="bi bi-arrow-left me-1"></i>Back to Properties
            </a>
            <div>
                <span class="eyebrow d-none d-sm-inline">Properties · Spreadsheet Console</span>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h3 mb-0">Mass Property Manager</h1>
                    <span class="badge bg-light text-muted border mono extra-small" id="prop-count-badge">{{ $properties->count() }} properties</span>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-success btn-sm fw-bold d-inline-flex align-items-center" id="btn-add-row-top">
                <i class="bi bi-plus-lg me-1"></i>Add Property Row
            </button>
            <button type="button" class="btn btn-primary btn-sm fw-bold px-3 d-inline-flex align-items-center" id="btn-save-all-top">
                <i class="bi bi-check2 me-1"></i>Save All Changes
            </button>
        </div>
    </div>

    <!-- Spreadsheet Grid -->
    <div class="mass-grid-scroll">
        <form id="form-mass-properties">
            @csrf
            <table class="excel-sheet-table" id="mass-table">
                <thead>
                    <!-- Top Category Header -->
                    <tr>
                        <th colspan="8" class="group-header">Core Property Information & Specifications</th>
                        @if($bedTypes->count() > 0)
                            <th colspan="{{ $bedTypes->count() }}" class="group-header group-bed">Bed Configurations (Quantities)</th>
                        @endif
                        @if($linenTypes->count() > 0)
                            <th colspan="{{ $linenTypes->count() }}" class="group-header group-linen">Linen Requirements (Quantities)</th>
                        @endif
                        <th colspan="2" class="group-header">Status & Action</th>
                    </tr>
                    <!-- Column Header -->
                    <tr>
                        <th class="cell-idx">#</th>
                        <th style="min-width: 170px;">Client / Owner</th>
                        <th style="min-width: 190px;">Property Name <span class="text-danger">*</span></th>
                        <th style="min-width: 240px;">Address <span class="text-danger">*</span></th>
                        <th style="width: 70px;" class="text-center" title="Bedrooms count">Bed</th>
                        <th style="width: 70px;" class="text-center" title="Bathrooms count">Bath</th>
                        <th style="min-width: 120px;">Parking Type</th>
                        <th style="width: 75px;" class="text-center" title="Parking spaces">Spaces</th>

                        <!-- Bed Types -->
                        @foreach ($bedTypes as $bt)
                            <th style="width: 75px;" class="text-center group-bed" title="{{ $bt->name }}">{{ $bt->name }}</th>
                        @endforeach

                        <!-- Linen Types -->
                        @foreach ($linenTypes as $lt)
                            <th style="width: 85px;" class="text-center group-linen" title="{{ $lt->name }} (${{ number_format($lt->rate, 2) }})">
                                {{ $lt->name }}
                                <div class="extra-small opacity-75">${{ number_format($lt->rate, 2) }}</div>
                            </th>
                        @endforeach

                        <th style="width: 55px;" class="text-center">Active</th>
                        <th style="width: 45px;" class="text-center">Del</th>
                    </tr>
                </thead>
                <tbody id="mass-tbody">
                    @forelse ($properties as $pIdx => $p)
                        @php
                            $pBeds = $p->beds->keyBy('bed_type_id');
                            $pLinens = $p->linens->keyBy('linen_type_id');
                        @endphp
                        <tr data-row-idx="{{ $pIdx }}">
                            <td class="cell-idx">
                                <span class="row-num">{{ $loop->iteration }}</span>
                                <input type="hidden" name="properties[{{ $pIdx }}][id]" value="{{ $p->id }}">
                                <input type="hidden" name="properties[{{ $pIdx }}][_delete]" value="0" class="input-delete-flag">
                            </td>
                            <td>
                                <select name="properties[{{ $pIdx }}][client_id]" class="sheet-select">
                                    <option value="">(No Client)</option>
                                    @foreach ($clients as $cl)
                                        <option value="{{ $cl->id }}" @selected($p->client_id == $cl->id)>
                                            {{ $cl->name }} {{ $cl->company_name ? "({$cl->company_name})" : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="properties[{{ $pIdx }}][name]" value="{{ $p->name }}" class="sheet-input fw-semibold" placeholder="Property name" required>
                            </td>
                            <td>
                                <input type="text" name="properties[{{ $pIdx }}][address]" value="{{ $p->address }}" class="sheet-input" placeholder="Property address" required>
                            </td>
                            <td>
                                <input type="number" min="0" max="50" name="properties[{{ $pIdx }}][bedrooms_count]" value="{{ $p->bedrooms_count ?? 1 }}" class="sheet-input mono text-center" placeholder="1">
                            </td>
                            <td>
                                <input type="number" min="0" max="50" step="0.5" name="properties[{{ $pIdx }}][bathrooms_count]" value="{{ $p->bathrooms_count ?? 1.0 }}" class="sheet-input mono text-center" placeholder="1.0">
                            </td>
                            <td>
                                <select name="properties[{{ $pIdx }}][parking_type]" class="sheet-select">
                                    <option value="none" @selected(($p->parking_type ?? 'none') === 'none')>None</option>
                                    <option value="garage" @selected($p->parking_type === 'garage')>Garage</option>
                                    <option value="driveway" @selected($p->parking_type === 'driveway')>Driveway</option>
                                    <option value="street" @selected($p->parking_type === 'street')>Street</option>
                                    <option value="dedicated_bay" @selected($p->parking_type === 'dedicated_bay')>Bay</option>
                                    <option value="carport" @selected($p->parking_type === 'carport')>Carport</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" min="0" max="50" name="properties[{{ $pIdx }}][parking_spaces_count]" value="{{ $p->parking_spaces_count ?? 0 }}" class="sheet-input mono text-center" placeholder="0">
                            </td>

                            <!-- Beds Columns -->
                            @foreach ($bedTypes as $bIdx => $bt)
                                @php
                                    $bedQty = $pBeds->get($bt->id)?->quantity ?? 0;
                                @endphp
                                <td>
                                    <input type="hidden" name="properties[{{ $pIdx }}][beds][{{ $bIdx }}][bed_type_id]" value="{{ $bt->id }}">
                                    <input type="number" min="0" max="50" name="properties[{{ $pIdx }}][beds][{{ $bIdx }}][quantity]" value="{{ $bedQty }}" class="sheet-input mono text-center" placeholder="0">
                                </td>
                            @endforeach

                            <!-- Linen Columns -->
                            @foreach ($linenTypes as $lIdx => $lt)
                                @php
                                    $linenQty = $pLinens->get($lt->id)?->quantity ?? 0;
                                @endphp
                                <td>
                                    <input type="hidden" name="properties[{{ $pIdx }}][linens][{{ $lIdx }}][linen_type_id]" value="{{ $lt->id }}">
                                    <input type="number" min="0" max="200" name="properties[{{ $pIdx }}][linens][{{ $lIdx }}][quantity]" value="{{ $linenQty }}" class="sheet-input mono text-center" placeholder="0">
                                </td>
                            @endforeach

                            <!-- Active -->
                            <td>
                                <div class="cell-checkbox-wrap">
                                    <input type="checkbox" name="properties[{{ $pIdx }}][active]" value="1" @checked($p->active)>
                                </div>
                            </td>

                            <!-- Action -->
                            <td class="text-center">
                                <button type="button" class="btn-row-del" title="Archive / Remove Row">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr id="empty-row-placeholder">
                            <td colspan="{{ 10 + $bedTypes->count() + $linenTypes->count() }}" class="text-center py-4 text-muted">
                                No properties found. Click <strong>Add Property Row</strong> to start adding properties.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </form>
    </div>

    <!-- Bottom Toolbar -->
    <div class="mass-footer-bar">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-add-row-bottom">
                <i class="bi bi-plus-lg me-1"></i>Add Row
            </button>
            <span class="extra-small mono text-muted d-none d-md-inline">
                <i class="bi bi-info-circle me-1 text-primary"></i>Direct spreadsheet editing · Tab key moves between cells · Saves all properties in one click.
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('properties') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
            <button type="button" class="btn btn-primary btn-sm fw-bold px-4" id="btn-save-all-bottom">
                <i class="bi bi-check2 me-1"></i>Save All Changes
            </button>
        </div>
    </div>

    <!-- Floating Feedback Toast -->
    <div id="mass-ajax-toast" class="position-fixed top-0 start-50 translate-middle-x mt-3 py-2 px-3 rounded shadow-sm mono extra-small d-none" style="z-index: 9999; max-width: 90vw;">
        <span class="toast-msg"></span>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function ($) {
        var clients = @json($clients);
        var bedTypes = @json($bedTypes);
        var linenTypes = @json($linenTypes);
        var toastTimer = null;

        function showToast(message, type) {
            clearTimeout(toastTimer);
            var $toast = $('#mass-ajax-toast');
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

        function reindexRows() {
            var count = 0;
            $('#mass-tbody tr').each(function (idx) {
                var $row = $(this);
                if ($row.attr('id') === 'empty-row-placeholder') return;
                count++;
                $row.attr('data-row-idx', idx);
                $row.find('.row-num').text(count);

                // Update input names for indexed array
                $row.find('input, select').each(function () {
                    var name = $(this).attr('name');
                    if (name) {
                        var updated = name.replace(/^properties\[\d+\]/, 'properties[' + idx + ']');
                        $(this).attr('name', updated);
                    }
                });
            });

            $('#prop-count-badge').text(count + (count === 1 ? ' property' : ' properties'));
        }

        function buildNewRowHtml(idx) {
            var clientOptions = '<option value="">(No Client)</option>';
            clients.forEach(function (cl) {
                var comp = cl.company_name ? ' (' + cl.company_name + ')' : '';
                clientOptions += '<option value="' + cl.id + '">' + cl.name + comp + '</option>';
            });

            var bedsHtml = '';
            bedTypes.forEach(function (bt, bIdx) {
                bedsHtml += '<td>' +
                    '<input type="hidden" name="properties[' + idx + '][beds][' + bIdx + '][bed_type_id]" value="' + bt.id + '">' +
                    '<input type="number" min="0" max="50" name="properties[' + idx + '][beds][' + bIdx + '][quantity]" value="0" class="sheet-input mono text-center" placeholder="0">' +
                    '</td>';
            });

            var linensHtml = '';
            linenTypes.forEach(function (lt, lIdx) {
                linensHtml += '<td>' +
                    '<input type="hidden" name="properties[' + idx + '][linens][' + lIdx + '][linen_type_id]" value="' + lt.id + '">' +
                    '<input type="number" min="0" max="200" name="properties[' + idx + '][linens][' + lIdx + '][quantity]" value="0" class="sheet-input mono text-center" placeholder="0">' +
                    '</td>';
            });

            return '<tr data-row-idx="' + idx + '" class="table-info">' +
                '<td class="cell-idx">' +
                '<span class="row-num">' + (idx + 1) + '</span>' +
                '<input type="hidden" name="properties[' + idx + '][id]" value="">' +
                '<input type="hidden" name="properties[' + idx + '][_delete]" value="0" class="input-delete-flag">' +
                '</td>' +
                '<td><select name="properties[' + idx + '][client_id]" class="sheet-select">' + clientOptions + '</select></td>' +
                '<td><input type="text" name="properties[' + idx + '][name]" value="" class="sheet-input fw-semibold" placeholder="New Property Name" required></td>' +
                '<td><input type="text" name="properties[' + idx + '][address]" value="" class="sheet-input" placeholder="Property Address" required></td>' +
                '<td><input type="number" min="0" max="50" name="properties[' + idx + '][bedrooms_count]" value="1" class="sheet-input mono text-center" placeholder="1"></td>' +
                '<td><input type="number" min="0" max="50" step="0.5" name="properties[' + idx + '][bathrooms_count]" value="1.0" class="sheet-input mono text-center" placeholder="1.0"></td>' +
                '<td><select name="properties[' + idx + '][parking_type]" class="sheet-select">' +
                '<option value="none" selected>None</option>' +
                '<option value="garage">Garage</option>' +
                '<option value="driveway">Driveway</option>' +
                '<option value="street">Street</option>' +
                '<option value="dedicated_bay">Bay</option>' +
                '<option value="carport">Carport</option>' +
                '</select></td>' +
                '<td><input type="number" min="0" max="50" name="properties[' + idx + '][parking_spaces_count]" value="0" class="sheet-input mono text-center" placeholder="0"></td>' +
                bedsHtml +
                linensHtml +
                '<td><div class="cell-checkbox-wrap"><input type="checkbox" name="properties[' + idx + '][active]" value="1" checked></div></td>' +
                '<td class="text-center"><button type="button" class="btn-row-del" title="Remove Row"><i class="bi bi-x-lg"></i></button></td>' +
                '</tr>';
        }

        // Add Row Handler
        $(document).on('click', '#btn-add-row-top, #btn-add-row-bottom', function () {
            $('#empty-row-placeholder').remove();
            var nextIdx = $('#mass-tbody tr').length;
            var newRowHtml = buildNewRowHtml(nextIdx);
            var $newRow = $(newRowHtml);
            $('#mass-tbody').append($newRow);
            reindexRows();

            // Auto-focus property name
            $newRow.find('input[name*="[name]"]').focus().select();

            // Scroll to bottom
            $('.mass-grid-scroll').scrollTop($('.mass-grid-scroll')[0].scrollHeight);
        });

        // Remove Row Handler
        $(document).on('click', '.btn-row-del', function () {
            var $row = $(this).closest('tr');
            var propId = $row.find('input[name$="[id]"]').val();

            if (!propId) {
                // Newly added row that isn't in DB yet
                $row.remove();
                reindexRows();
            } else {
                // Existing row in DB
                var isDeleted = $row.hasClass('row-deleted');
                if (isDeleted) {
                    $row.removeClass('row-deleted');
                    $row.find('.input-delete-flag').val('0');
                    $row.find('input, select').not('.input-delete-flag').prop('disabled', false);
                } else {
                    $row.addClass('row-deleted');
                    $row.find('.input-delete-flag').val('1');
                }
            }
        });

        // Save All Handler
        $(document).on('click', '#btn-save-all-top, #btn-save-all-bottom', function () {
            var $saveBtns = $('#btn-save-all-top, #btn-save-all-bottom');
            var originalHtml = $saveBtns.first().html();

            reindexRows();

            // Check validation
            var form = document.getElementById('form-mass-properties');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            $saveBtns.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving All…');

            var formData = new FormData(form);

            axios.post('{{ route("properties.mass-save") }}', formData, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(function (res) {
                showToast(res.data.message || 'All properties saved successfully.', 'success');
                $saveBtns.html('<i class="bi bi-check2-all me-1 text-success"></i>Saved All!');

                // Remove highlighted new rows style and remove deleted rows
                $('#mass-tbody tr.row-deleted').remove();
                $('#mass-tbody tr.table-info').removeClass('table-info');
                reindexRows();

                setTimeout(function () {
                    $saveBtns.prop('disabled', false).html(originalHtml);
                }, 1500);
            })
            .catch(function (err) {
                var msg = 'Failed to save properties.';
                if (err.response && err.response.data) {
                    if (err.response.data.message) {
                        msg = err.response.data.message;
                    } else if (err.response.data.errors) {
                        var first = Object.keys(err.response.data.errors)[0];
                        msg = err.response.data.errors[first][0];
                    }
                }
                showToast(msg, 'danger');
                $saveBtns.prop('disabled', false).html(originalHtml);
            });
        });
    })(jQuery);
</script>
@endpush
