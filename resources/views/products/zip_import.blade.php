@extends('layouts.app')

@section('title', 'Multi-Product Image ZIP Import Studio')

@section('content')

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0 fw-bold">
            <i class="bi bi-file-earmark-zip-fill text-warning me-2"></i> Bulk Product Image ZIP Pack Extractor
        </h5>
        <div>
            <a href="{{ route('products.index') }}" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="p-4 border rounded-3 bg-light">
                    <h6 class="fw-bold text-primary mb-2">Upload Image Archive (.ZIP)</h6>
                    <p class="text-muted small mb-4">
                        Upload a ZIP file containing image files (e.g. <code>SKU101.jpg</code>, <code>SKU102_cover.png</code>).
                        The extractor will automatically parse each image filename, match the Product SKU code in the database, and link the images automatically!
                    </p>

                    <form action="{{ route('products.zip_import.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label font-bold text-secondary">Select ZIP Archive File</label>
                            <input type="file" name="zip_file" class="form-control form-control-lg" accept=".zip" required>
                        </div>

                        <button type="submit" class="btn btn-warning btn-lg w-100 font-bold shadow-sm">
                            <i class="bi bi-file-earmark-zip me-1"></i> Extract & Auto-Link Images to Products
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="p-4 border border-info rounded-3 bg-info-subtle">
                    <h6 class="fw-bold text-info-emphasis mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> ZIP Matching Rules
                    </h6>
                    <ul class="small text-secondary mb-0 space-y-2">
                        <li>Images named matching SKU codes (e.g., <code>LAPTOP-01.jpg</code>) link to Product SKU <code>LAPTOP-01</code>.</li>
                        <li>Supports common formats: <code>.jpg</code>, <code>.png</code>, <code>.webp</code>, <code>.gif</code>.</li>
                        <li>The first unzipped image for any product is automatically assigned as its <strong>Main Cover Image</strong>.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
