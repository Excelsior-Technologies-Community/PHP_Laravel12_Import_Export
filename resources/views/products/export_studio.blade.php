@extends('layouts.app')

@section('title', 'Custom Dynamic Export Studio')

@section('content')

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold">
            <i class="bi bi-funnel-fill text-warning me-2"></i> Custom Dynamic Export Studio & Configurator
        </h5>
        <div>
            <a href="{{ route('products.index') }}" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>

    <div class="card-body p-4">
        <form action="{{ route('products.export.custom') }}" method="GET" target="_blank">
            <div class="row g-4">
                <!-- FORMAT SELECTOR -->
                <div class="col-md-12">
                    <label class="form-label font-bold text-uppercase text-secondary small">1. Export Format</label>
                    <div class="d-flex gap-3">
                        <div class="form-check form-check-inline border p-3 rounded bg-light flex-grow-1">
                            <input class="form-check-input" type="radio" name="format" id="formatXlsx" value="xlsx" checked>
                            <label class="form-check-label font-bold text-dark ms-2" for="formatXlsx">
                                📊 Excel Spreadsheet (XLSX)
                            </label>
                        </div>
                        <div class="form-check form-check-inline border p-3 rounded bg-light flex-grow-1">
                            <input class="form-check-input" type="radio" name="format" id="formatCsv" value="csv">
                            <label class="form-check-label font-bold text-dark ms-2" for="formatCsv">
                                📑 CSV Data Format
                            </label>
                        </div>
                    </div>
                </div>

                <!-- COLUMN SELECTOR -->
                <div class="col-md-12">
                    <label class="form-label font-bold text-uppercase text-secondary small">2. Select Active Export Columns</label>
                    <div class="p-3 border rounded bg-light d-flex flex-wrap gap-3">
                        @php
                            $columns = [
                                'id' => 'ID',
                                'name' => 'Product Name',
                                'sku' => 'SKU Code',
                                'category' => 'Category',
                                'price' => 'Price ($)',
                                'quantity' => 'Stock Quantity',
                                'description' => 'Description',
                                'created_at' => 'Created Date'
                            ];
                        @endphp
                        @foreach($columns as $key => $label)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="{{ $key }}" id="col_{{ $key }}" checked>
                                <label class="form-check-label font-bold text-dark" for="col_{{ $key }}">
                                    {{ $label }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- FILTERS -->
                <div class="col-md-12">
                    <label class="form-label font-bold text-uppercase text-secondary small">3. Filter Dataset</label>
                    <div class="row g-3 p-3 border rounded bg-light">
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Keyword Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search name or SKU...">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small text-muted">Category</label>
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small text-muted">Stock Status</label>
                            <select name="stock_status" class="form-select">
                                <option value="">All Stock Levels</option>
                                <option value="in_stock">In Stock (> 0)</option>
                                <option value="out_of_stock">Out of Stock (= 0)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted">Min Price ($)</label>
                            <input type="number" step="0.01" name="min_price" class="form-control" placeholder="0.00">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted">Max Price ($)</label>
                            <input type="number" step="0.01" name="max_price" class="form-control" placeholder="9999.00">
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <button type="submit" class="btn btn-warning btn-lg font-bold px-5 rounded-3 shadow">
                        <i class="bi bi-download me-1"></i> Download Filtered Export File
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
