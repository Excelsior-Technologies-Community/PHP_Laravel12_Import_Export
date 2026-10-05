<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printable Product Catalog Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
        }
        .catalog-header {
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .product-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            height: 100%;
            background: #f8fafc;
            page-break-inside: avoid;
        }
        .product-thumb {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 8px;
            background: #e2e8f0;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body className="p-4">

<div class="container py-4">
    <!-- PRINT BUTTON BAR -->
    <div class="no-print d-flex justify-content-between align-items-center mb-4 bg-light p-3 rounded-3 border">
        <div>
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i> Printable Product Catalog</h5>
            <small class="text-muted">Generated {{ count($products) }} items for catalog report.</small>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary fw-bold px-4">
                <i class="bi bi-printer-fill me-1"></i> Print / Save as PDF
            </button>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary fw-bold px-3">
                Close
            </a>
        </div>
    </div>

    <!-- CATALOG HEADER -->
    <div class="catalog-header d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-primary mb-1">OFFICIAL PRODUCT CATALOG</h2>
            <p class="text-muted mb-0">Complete Inventory Summary & Price Specifications</p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary fs-6 mb-1">Total: {{ count($products) }} Products</span>
            <div class="small text-muted">{{ now()->format('F d, Y - h:i A') }}</div>
        </div>
    </div>

    <!-- PRODUCT CATALOG GRID -->
    <div class="row g-4">
        @forelse($products as $product)
            <div class="col-md-4 col-sm-6">
                <div class="product-card d-flex flex-column justify-content-between">
                    <div>
                        <img src="{{ asset('storage/' . $product->first_image) }}" class="product-thumb mb-3" alt="{{ $product->name }}" onerror="this.src='https://via.placeholder.com/300x200?text=No+Image';">
                        
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-secondary font-monospace small">SKU: {{ $product->sku }}</span>
                            <span class="badge bg-info text-dark small">{{ $product->category }}</span>
                        </div>

                        <h6 class="fw-bold text-dark mb-2">{{ $product->name }}</h6>
                        <p class="small text-muted mb-3" style="min-height: 40px;">
                            {{ Str::limit($product->description ?? 'No description provided.', 80) }}
                        </p>
                    </div>

                    <div class="border-top pt-2 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Price:</span>
                            <span class="fw-bold text-success fs-5 ms-1">${{ number_format($product->price, 2) }}</span>
                        </div>
                        <div>
                            @if($product->quantity > 0)
                                <span class="badge bg-success-subtle text-success border border-success">In Stock ({{ $product->quantity }})</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger">Out of Stock</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <p class="text-muted fs-5">No products found in catalog.</p>
            </div>
        @endforelse
    </div>
</div>

</body>
</html>
