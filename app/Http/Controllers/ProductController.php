<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('images');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $products = $query->oldest()
            ->paginate(4)
            ->withQueryString();

        $categories = Product::select('category')
            ->distinct()
            ->pluck('category');

        return view('products.index', compact(
            'products',
            'categories'
        ));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'category' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $product = Product::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $key => $image) {
                // Store in public disk (storage/app/public/product-images)
                $path = $image->store('product-images', 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path, // Store only the relative path
                    'order' => $key
                ]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load('images');
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load('images');
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'category' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'deleted_images' => 'nullable|array'
        ]);

        $product->update($validated);

        // Delete selected images
        if ($request->has('deleted_images')) {
            foreach ($request->deleted_images as $imageId) {
                $image = ProductImage::find($imageId);
                if ($image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
            }
        }

        // Add new images
        if ($request->hasFile('images')) {
            $order = $product->images()->max('order') ?? 0;
            foreach ($request->file('images') as $image) {
                $path = $image->store('product-images', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'order' => ++$order
                ]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product moved to trash.');
    }

    public function trash()
    {
        $products = Product::onlyTrashed()->with('images')->latest()->paginate(10);
        return view('products.trash', compact('products'));
    }

    public function restore($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();
        return redirect()->route('products.trash')->with('success', 'Product restored successfully.');
    }

    public function forceDelete($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        // Delete all images
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $product->forceDelete();
        return redirect()->route('products.trash')->with('success', 'Product permanently deleted.');
    }

    public function export()
    {
        return Excel::download(new ProductsExport, 'products_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            $import = new ProductsImport;
            Excel::import($import, $request->file('file'));

            $message = 'Products imported successfully.';

            // Check if we have import statistics
            if (session()->has('import_stats')) {
                $stats = session('import_stats');
                $message .= " Imported: {$stats['imported']}, Skipped: {$stats['skipped']}, Total: {$stats['total']}";
            }

            return redirect()->route('products.index')->with('success', $message);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Row {$failure->row()}: {$failure->errors()[0]}";
            }

            return redirect()->back()
                ->with('error', 'Validation errors occurred:')
                ->with('errors', $errorMessages);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    public function dashboard()
    {
        $totalProducts = Product::count();

        $totalQuantity = Product::sum('quantity');

        $totalInventoryValue = Product::sum(
            \DB::raw('price * quantity')
        );

        $totalCategories = Product::distinct('category')->count();

        return view('products.dashboard', compact(
            'totalProducts',
            'totalQuantity',
            'totalInventoryValue',
            'totalCategories'
        ));
    }

    public function exportCsv()
    {
        return Excel::download(
            new ProductsExport,
            'products.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }
}