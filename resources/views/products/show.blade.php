@extends('layouts.app')

@section('title', 'View Product')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Product Details: {{ $product->name }}</h5>
        <div>
            <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">ID</th>
                        <td>{{ $product->id }}</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>{{ $product->name }}</td>
                    </tr>
                    <tr>
                        <th>SKU</th>
                        <td>{{ $product->sku }}</td>
                    </tr>
                    <tr>
                        <th>Price</th>
                        <td>₹{{ number_format($product->price,2) }}</td>
                    </tr>
                    <tr>
                        <th>Quantity</th>
                        <td>{{ $product->quantity }}</td>
                    </tr>
                    <tr>
                        <th>Category</th>
                        <td>{{ $product->category }}</td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>{{ $product->description ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ $product->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                </table>
            </div>
            
            <div class="col-md-6">
                <h6>Product Images</h6>
                @if($product->images->count() > 0)
                <div class="row">
                    @foreach($product->images as $image)
                    <div class="col-md-4 mb-3">
                        <img src="{{ asset('storage/' . $image->image_path) }}" 
                             alt="Product Image" class="img-thumbnail w-100">
                        <small class="text-muted d-block text-center">Image {{ $loop->iteration }}</small>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted">No images uploaded</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection