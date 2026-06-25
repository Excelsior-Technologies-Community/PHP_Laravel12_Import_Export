@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Product Dashboard</h2>
        <small class="text-muted">
            Inventory Management Overview
        </small>
    </div>

    <a href="{{ route('products.index') }}"
       class="btn btn-dark">
        <i class="bi bi-arrow-left"></i>
        Back to Products
    </a>
</div>

<div class="row g-4">

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">

                <div class="display-5 text-primary mb-2">
                    <i class="bi bi-box-seam"></i>
                </div>

                <h2 class="fw-bold">
                    {{ $totalProducts }}
                </h2>

                <p class="text-muted mb-0">
                    Total Products
                </p>

            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">

                <div class="display-5 text-success mb-2">
                    <i class="bi bi-stack"></i>
                </div>

                <h2 class="fw-bold">
                    {{ $totalQuantity }}
                </h2>

                <p class="text-muted mb-0">
                    Total Quantity
                </p>

            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">

                <div class="display-5 text-warning mb-2">
                    <i class="bi bi-currency-rupee"></i>
                </div>

                <h2 class="fw-bold">
                    ₹ {{ number_format($totalInventoryValue, 2) }}
                </h2>

                <p class="text-muted mb-0">
                    Inventory Value
                </p>

            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">

                <div class="display-5 text-danger mb-2">
                    <i class="bi bi-tags"></i>
                </div>

                <h2 class="fw-bold">
                    {{ $totalCategories }}
                </h2>

                <p class="text-muted mb-0">
                    Categories
                </p>

            </div>
        </div>
    </div>

</div>

<div class="row mt-5">

    <div class="col-md-12">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white">
                <h5 class="mb-0">
                    Dashboard Summary
                </h5>
            </div>

            <div class="card-body">

                <div class="row text-center">

                    <div class="col-md-3">
                        <h3 class="text-primary">
                            {{ $totalProducts }}
                        </h3>
                        <small class="text-muted">
                            Products Available
                        </small>
                    </div>

                    <div class="col-md-3">
                        <h3 class="text-success">
                            {{ $totalQuantity }}
                        </h3>
                        <small class="text-muted">
                            Items In Stock
                        </small>
                    </div>

                    <div class="col-md-3">
                        <h3 class="text-warning">
                            ₹ {{ number_format($totalInventoryValue, 2) }}
                        </h3>
                        <small class="text-muted">
                            Inventory Worth
                        </small>
                    </div>

                    <div class="col-md-3">
                        <h3 class="text-danger">
                            {{ $totalCategories }}
                        </h3>
                        <small class="text-muted">
                            Categories
                        </small>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</div>

@endsection
