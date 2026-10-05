@extends('layouts.app')

@section('title', 'Interactive Import Studio & Error Inspector')

@section('content')

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold">
            <i class="bi bi-sliders text-warning me-2"></i> Interactive Excel/CSV Drag & Drop Mapping Studio
        </h5>
        <div>
            <a href="{{ route('products.index') }}" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>

    <div class="card-body p-4">
        <!-- STEP 1: FILE UPLOAD -->
        <div class="mb-4 p-4 border rounded-3 bg-light">
            <h6 class="fw-bold text-primary mb-2">1. Upload Excel / CSV Dataset</h6>
            <form action="{{ route('products.import.upload') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-center">
                @csrf
                <div class="col-md-8">
                    <input type="file" name="file" class="form-control form-control-lg" accept=".csv, .xlsx" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload & Extract Headers
                    </button>
                </div>
            </form>
        </div>

        @if(isset($batch) && $batch)
            <!-- STEP 2: DRAG & DROP / SELECT FIELD MAPPING STUDIO -->
            <div class="card border-primary mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 font-bold">
                        <i class="bi bi bg-light text-primary rounded-circle p-1 me-1"></i>
                        2. Field Mapping Studio (File: <u>{{ $batch->file_name }}</u> - {{ $batch->total_rows }} Rows Found)
                    </h6>
                    <span class="badge bg-light text-primary fw-bold">Auto-Header Matching Active</span>
                </div>

                <div class="card-body">
                    <form action="{{ route('products.import.apply_mapping') }}" method="POST">
                        @csrf
                        <input type="hidden" name="batch_id" value="{{ $batch->batch_id }}">

                        <div class="row g-3 mb-3">
                            @foreach($targetFields as $fieldKey => $config)
                                @php
                                    $currentMapped = $batch->field_mapping[$fieldKey] ?? '';
                                @endphp
                                <div class="col-md-4">
                                    <div class="p-3 border rounded bg-light">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong class="text-dark">{{ $config['label'] }}</strong>
                                            @if($currentMapped)
                                                <span class="badge bg-success-subtle text-success border border-success small">Mapped: {{ $currentMapped }}</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger small">Unmapped</span>
                                            @endif
                                        </div>
                                        <select name="field_mapping[{{ $fieldKey }}]" class="form-select border-secondary">
                                            <option value="">-- Skip Field --</option>
                                            @foreach($batch->headers as $h)
                                                <option value="{{ $h }}" {{ $currentMapped == $h ? 'selected' : '' }}>
                                                    Header: {{ $h }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-emerald btn-success font-bold px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> Apply Mapping & Validate Rows
                        </button>
                    </form>
                </div>
            </div>

            <!-- STEP 3: PRE-IMPORT ROW ERROR INSPECTOR & INLINE REPAIR -->
            @php
                $rows = $batch->parsed_rows ?? [];
                $validCount = collect($rows)->whereIn('status', ['valid', 'repaired'])->count();
                $invalidCount = collect($rows)->where('status', 'invalid')->count();
                $skippedCount = collect($rows)->where('status', 'skipped')->count();
            @endphp

            <div class="card border border-secondary shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-shield-check text-success me-1"></i> 3. Pre-Import Row Inspector & Inline Cell Repair
                        </h6>
                        <small class="text-muted">Edit cell values directly to repair invalid records or click skip.</small>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success px-3 py-2">✅ Valid: {{ $validCount }}</span>
                        <span class="badge bg-danger px-3 py-2">⚠️ Errors: {{ $invalidCount }}</span>
                        <span class="badge bg-secondary px-3 py-2">⏭️ Skipped: {{ $skippedCount }}</span>

                        <form action="{{ route('products.import.execute') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="batch_id" value="{{ $batch->batch_id }}">
                            <button type="submit" class="btn btn-primary font-bold px-4 rounded-pill shadow-sm" {{ $validCount == 0 ? 'disabled' : '' }}>
                                🚀 Execute Batch Import ({{ $validCount }} Rows)
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark text-uppercase small">
                                <tr>
                                    <th>#</th>
                                    <th>Product Name</th>
                                    <th>SKU</th>
                                    <th>Price ($)</th>
                                    <th>Stock</th>
                                    <th>Category</th>
                                    <th>Validation Result</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    @php
                                        $isInvalid = $row['status'] === 'invalid';
                                        $isSkipped = $row['status'] === 'skipped';
                                        $isRepaired = $row['status'] === 'repaired';
                                    @endphp
                                    <tr class="{{ $isInvalid ? 'table-danger' : ($isSkipped ? 'table-secondary opacity-50' : ($isRepaired ? 'table-warning' : '')) }}">
                                        <td class="fw-bold">{{ $row['row_index'] }}</td>

                                        <!-- NAME INLINE FORM -->
                                        <td>
                                            <form action="{{ route('products.import.repair_cell') }}" method="POST" class="d-flex align-items-center gap-1">
                                                @csrf
                                                <input type="hidden" name="batch_id" value="{{ $batch->batch_id }}">
                                                <input type="hidden" name="row_index" value="{{ $row['row_index'] }}">
                                                <input type="hidden" name="field" value="name">
                                                <input type="text" name="value" value="{{ $row['mapped_data']['name'] ?? '' }}" class="form-control form-control-sm" style="min-width: 140px;">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary p-1" title="Save cell"><i class="bi bi-check"></i></button>
                                            </form>
                                        </td>

                                        <!-- SKU INLINE FORM -->
                                        <td>
                                            <form action="{{ route('products.import.repair_cell') }}" method="POST" class="d-flex align-items-center gap-1">
                                                @csrf
                                                <input type="hidden" name="batch_id" value="{{ $batch->batch_id }}">
                                                <input type="hidden" name="row_index" value="{{ $row['row_index'] }}">
                                                <input type="hidden" name="field" value="sku">
                                                <input type="text" name="value" value="{{ $row['mapped_data']['sku'] ?? '' }}" class="form-control form-control-sm font-monospace" style="min-width: 100px;">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary p-1" title="Save cell"><i class="bi bi-check"></i></button>
                                            </form>
                                        </td>

                                        <!-- PRICE INLINE FORM -->
                                        <td>
                                            <form action="{{ route('products.import.repair_cell') }}" method="POST" class="d-flex align-items-center gap-1">
                                                @csrf
                                                <input type="hidden" name="batch_id" value="{{ $batch->batch_id }}">
                                                <input type="hidden" name="row_index" value="{{ $row['row_index'] }}">
                                                <input type="hidden" name="field" value="price">
                                                <input type="text" name="value" value="{{ $row['mapped_data']['price'] ?? '' }}" class="form-control form-control-sm" style="min-width: 80px;">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary p-1" title="Save cell"><i class="bi bi-check"></i></button>
                                            </form>
                                        </td>

                                        <!-- QUANTITY INLINE FORM -->
                                        <td>
                                            <form action="{{ route('products.import.repair_cell') }}" method="POST" class="d-flex align-items-center gap-1">
                                                @csrf
                                                <input type="hidden" name="batch_id" value="{{ $batch->batch_id }}">
                                                <input type="hidden" name="row_index" value="{{ $row['row_index'] }}">
                                                <input type="hidden" name="field" value="quantity">
                                                <input type="text" name="value" value="{{ $row['mapped_data']['quantity'] ?? '' }}" class="form-control form-control-sm" style="min-width: 70px;">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary p-1" title="Save cell"><i class="bi bi-check"></i></button>
                                            </form>
                                        </td>

                                        <!-- CATEGORY -->
                                        <td class="small">{{ $row['mapped_data']['category'] ?? 'General' }}</td>

                                        <!-- VALIDATION STATUS & ERRORS -->
                                        <td>
                                            @if($isInvalid)
                                                <div class="text-danger small font-bold">
                                                    @foreach($row['errors'] as $err)
                                                        <div>⚠️ {{ $err }}</div>
                                                    @endforeach
                                                </div>
                                            @elseif($isSkipped)
                                                <span class="badge bg-secondary">Skipped</span>
                                            @elseif($isRepaired)
                                                <span class="badge bg-warning text-dark">🛠️ Cell Repaired</span>
                                            @else
                                                <span class="badge bg-success">✅ Clean</span>
                                            @endif
                                        </td>

                                        <!-- ACTIONS -->
                                        <td class="text-end">
                                            @if(!$isSkipped)
                                                <form action="{{ route('products.import.skip_row') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="batch_id" value="{{ $batch->batch_id }}">
                                                    <input type="hidden" name="row_index" value="{{ $row['row_index'] }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Skip</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No parsed rows found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection
