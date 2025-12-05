@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>Edit Product: {{ $product->name }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" value="{{ old('name', $product->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="sku" class="form-label">SKU *</label>
                            <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku"
                                value="{{ old('sku', $product->sku) }}" required>
                            @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label">Price *</label>
                            <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror"
                                id="price" name="price" value="{{ old('price', $product->price) }}" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity *</label>
                            <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity"
                                name="quantity" value="{{ old('quantity', $product->quantity) }}" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="category" class="form-label">Category *</label>
                            <input type="text" class="form-control @error('category') is-invalid @enderror" id="category"
                                name="category" value="{{ old('category', $product->category) }}" required>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                                name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="images" class="form-label">Add More Images</label>
                            <input type="file" class="form-control @error('images.*') is-invalid @enderror" id="images"
                                name="images[]" multiple accept="image/*">
                            <div class="form-text">Select additional images</div>
                            @error('images.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div id="imagePreview" class="mt-2"></div>
                        </div>
                    </div>
                </div>

                <!-- Existing Images -->
<!-- In the existing images section -->
@if($product->images->count() > 0)
    <div class="mb-4">
        <label class="form-label">Existing Images</label>
        <div class="row">
            @foreach($product->images as $image)
                <div class="col-md-2 mb-3">
                    <div class="image-container position-relative">
                        <!-- Custom checkbox with better styling -->
                        <div class="form-check position-absolute top-0 start-0 m-2">
                            <input type="checkbox" 
                                   class="form-check-input image-checkbox" 
                                   name="deleted_images[]" 
                                   value="{{ $image->id }}"
                                   id="image-{{ $image->id }}">
                            <label class="form-check-label" for="image-{{ $image->id }}"></label>
                        </div>
                        
                        <!-- Remove button overlay -->
                        <button type="button" 
                                class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 remove-image-btn"
                                data-image-id="{{ $image->id }}"
                                style="width: 30px; height: 30px; border-radius: 50%; padding: 0; font-size: 18px; line-height: 1;">
                            &times;
                        </button>
                        
                        <img src="{{ asset('storage/' . $image->image_path) }}" 
                             alt="Product Image"
                             class="img-thumbnail w-100" 
                             style="height: 150px; object-fit: cover;">
                        
                        <div class="text-center mt-1">
                            <small class="text-muted">Image {{ $loop->iteration }}</small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<!-- In scripts -->


                <div class="d-flex justify-content-between">
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Toggle checkbox when remove button is clicked
    document.querySelectorAll('.remove-image-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const imageId = this.dataset.imageId;
            const checkbox = document.getElementById(`image-${imageId}`);
            const imageContainer = this.closest('.image-container');
            
            // Toggle checkbox
            checkbox.checked = !checkbox.checked;
            
            // Visual feedback
            if (checkbox.checked) {
                imageContainer.style.opacity = '0.5';
                imageContainer.style.transform = 'scale(0.95)';
                this.innerHTML = '↺'; // Change to undo icon
                this.classList.remove('btn-danger');
                this.classList.add('btn-warning');
            } else {
                imageContainer.style.opacity = '1';
                imageContainer.style.transform = 'scale(1)';
                this.innerHTML = '&times;'; // Back to X
                this.classList.remove('btn-warning');
                this.classList.add('btn-danger');
            }
        });
    });

    // Checkbox change event
    document.querySelectorAll('.image-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const imageContainer = this.closest('.image-container');
            const removeBtn = imageContainer.querySelector('.remove-image-btn');
            
            if (this.checked) {
                imageContainer.style.opacity = '0.5';
                imageContainer.style.transform = 'scale(0.95)';
                removeBtn.innerHTML = '↺';
                removeBtn.classList.remove('btn-danger');
                removeBtn.classList.add('btn-warning');
            } else {
                imageContainer.style.opacity = '1';
                imageContainer.style.transform = 'scale(1)';
                removeBtn.innerHTML = '&times;';
                removeBtn.classList.remove('btn-warning');
                removeBtn.classList.add('btn-danger');
            }
        });
    });
</script>
@endsection