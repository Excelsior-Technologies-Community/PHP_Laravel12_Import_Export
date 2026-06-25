@extends('layouts.app')

@section('title', 'Products List')

@section('content')

<div class="card">


<div class="card-header d-flex justify-content-between align-items-center">
    <h5>Products List</h5>

    <div>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Product
        </a>

        <button type="button"
                class="btn btn-success"
                data-bs-toggle="modal"
                data-bs-target="#importModal">
            <i class="bi bi-upload"></i> Import
        </button>

        <a href="{{ route('products.export') }}"
           class="btn btn-warning">
            <i class="bi bi-file-earmark-excel"></i> Excel Export
        </a>

        <a href="{{ route('products.export.csv') }}"
           class="btn btn-info">
            <i class="bi bi-filetype-csv"></i> CSV Export
        </a>

        <a href="{{ route('products.dashboard') }}"
           class="btn btn-dark">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </div>
</div>

<div class="card-body">

    <form method="GET" action="{{ route('products.index') }}" class="row mb-4">

        <div class="col-md-4">
            <input type="text"
                   name="search"
                   class="form-control"
                   placeholder="Search by Name or SKU"
                   value="{{ request('search') }}">
        </div>

        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">All Categories</option>

                @foreach($categories as $category)
                    <option value="{{ $category }}"
                        {{ request('category') == $category ? 'selected' : '' }}>
                        {{ $category }}
                    </option>
                @endforeach

            </select>
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                Search
            </button>
        </div>

        <div class="col-md-3">
            <a href="{{ route('products.index') }}"
               class="btn btn-secondary w-100">
                Reset
            </a>
        </div>

    </form>

    <table class="table table-striped table-bordered">

        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Category</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>

            @forelse($products as $product)

                <tr>

                    <td>{{ $product->id }}</td>

                    <td>
                        @if($product->images->count() > 0)

                            <img src="{{ asset('storage/' . $product->images->first()->image_path) }}"
                                 alt="{{ $product->name }}"
                                 style="height:50px;width:auto;object-fit:cover;">

                        @else

                            <img src="{{ asset('images/default-product.jpg') }}"
                                 alt="No Image"
                                 style="height:50px;width:auto;object-fit:cover;">

                        @endif
                    </td>

                    <td>{{ $product->name }}</td>

                    <td>${{ number_format((float) $product->price, 2) }}</td>

                    <td>{{ $product->quantity }}</td>

                    <td>{{ $product->category }}</td>

                    <td>

                        <a href="{{ route('products.show', $product) }}"
                           class="btn btn-sm btn-info">
                            <i class="bi bi-eye"></i>
                        </a>

                        <a href="{{ route('products.edit', $product) }}"
                           class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil"></i>
                        </a>

                        <form action="{{ route('products.destroy', $product) }}"
                              method="POST"
                              class="d-inline"
                              onsubmit="return confirm('Move to trash?')">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i>
                            </button>

                        </form>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="7" class="text-center">
                        No products found.
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

  <div class="d-flex justify-content-center mt-3">
    <nav>
        <ul class="pagination">

            @for ($i = 1; $i <= $products->lastPage(); $i++)
                <li class="page-item {{ $products->currentPage() == $i ? 'active' : '' }}">
                    <a class="page-link"
                       href="{{ $products->url($i) }}">
                        {{ $i }}
                    </a>
                </li>
            @endfor

        </ul>
    </nav>
</div>

</div>


</div>

<!-- Import Modal -->

<div class="modal fade"
     id="importModal"
     tabindex="-1">


<div class="modal-dialog modal-lg">

    <div class="modal-content">

        <div class="modal-header">
            <h5 class="modal-title">Import Products</h5>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
            </button>
        </div>

        <form action="{{ route('products.import') }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf

            <div class="modal-body">

                <div class="alert alert-info">

                    <h6>
                        <i class="bi bi-info-circle"></i>
                        Import Instructions:
                    </h6>

                    <ul class="mb-0">
                        <li>SKU must be unique</li>
                        <li>Duplicate SKU will be skipped</li>
                        <li>Required: Name, SKU</li>
                        <li>Optional: Description, Price, Quantity, Category</li>
                    </ul>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Select File
                    </label>

                    <input type="file"
                           name="file"
                           class="form-control"
                           accept=".xlsx,.xls,.csv"
                           required>

                </div>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Close
                </button>

                <button type="submit"
                        class="btn btn-primary">
                    Import Products
                </button>

            </div>

        </form>

    </div>

</div>


</div>

@endsection
