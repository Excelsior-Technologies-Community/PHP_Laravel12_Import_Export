<?php

namespace App\Http\Controllers;

use App\Models\ImportBatch;
use App\Models\ImportHistory;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportExportStudioController extends Controller
{
    private array $targetFields = [
        'name' => ['label' => 'Product Name*', 'required' => true, 'type' => 'string'],
        'sku' => ['label' => 'SKU Code*', 'required' => true, 'type' => 'string'],
        'price' => ['label' => 'Price ($)*', 'required' => true, 'type' => 'numeric'],
        'quantity' => ['label' => 'Stock Quantity*', 'required' => true, 'type' => 'integer'],
        'category' => ['label' => 'Category*', 'required' => true, 'type' => 'string'],
        'description' => ['label' => 'Description', 'required' => false, 'type' => 'text'],
    ];

    /*
    |--------------------------------------------------------------------------
    | Feature 1: Drag-and-Drop Mapping Studio & Live Pre-Import Inspector
    |--------------------------------------------------------------------------
    */
    public function importStudio(Request $request)
    {
        $batchId = $request->query('batch_id');
        $batch = $batchId ? ImportBatch::where('batch_id', $batchId)->first() : null;

        return view('products.import_studio', [
            'batch' => $batch,
            'targetFields' => $this->targetFields,
        ]);
    }

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,txt|max:10240',
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $path = $file->getRealPath();

        $headers = [];
        $rows = [];

        if (($handle = fopen($path, 'r')) !== false) {
            $rawHeaders = fgetcsv($handle, 1000, ',');
            if ($rawHeaders) {
                foreach ($rawHeaders as $h) {
                    $headers[] = trim((string)$h);
                }
            }

            $lineNum = 1;
            while (($data = fgetcsv($handle, 2000, ',')) !== false) {
                if (count($data) === 1 && trim($data[0]) === '') {
                    continue;
                }
                $rowMap = [];
                foreach ($headers as $idx => $headerName) {
                    $rowMap[$headerName] = isset($data[$idx]) ? trim((string)$data[$idx]) : '';
                }
                $rows[] = [
                    'row_index' => $lineNum,
                    'raw_data' => $rowMap,
                    'mapped_data' => [],
                    'status' => 'pending',
                    'errors' => [],
                ];
                $lineNum++;
            }
            fclose($handle);
        }

        // Auto-detect header column mappings
        $autoMapping = [];
        foreach ($this->targetFields as $field => $config) {
            $matchedHeader = null;
            foreach ($headers as $h) {
                $cleanH = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $h));
                $cleanF = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $field));
                $cleanL = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $config['label']));

                if ($cleanH === $cleanF || $cleanH === $cleanL || str_contains($cleanH, $cleanF) || str_contains($cleanF, $cleanH)) {
                    $matchedHeader = $h;
                    break;
                }
            }
            $autoMapping[$field] = $matchedHeader ?: '';
        }

        $batchId = (string) Str::uuid();
        $validatedRows = $this->validateRowsWithMapping($rows, $autoMapping);

        $batch = ImportBatch::create([
            'batch_id' => $batchId,
            'file_name' => $fileName,
            'total_rows' => count($rows),
            'processed_rows' => 0,
            'successful_rows' => 0,
            'failed_rows' => 0,
            'skipped_rows' => 0,
            'status' => 'mapping',
            'headers' => $headers,
            'field_mapping' => $autoMapping,
            'parsed_rows' => $validatedRows,
            'errors' => [],
        ]);

        return redirect()->route('products.import.studio', ['batch_id' => $batchId])
            ->with('success', "File '{$fileName}' uploaded. Drag & drop or match header columns below.");
    }

    public function applyMapping(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|string|exists:import_batches,batch_id',
            'field_mapping' => 'required|array',
        ]);

        $batch = ImportBatch::where('batch_id', $validated['batch_id'])->firstOrFail();
        $mapping = $validated['field_mapping'];

        $validatedRows = $this->validateRowsWithMapping($batch->parsed_rows, $mapping);

        $batch->update([
            'field_mapping' => $mapping,
            'parsed_rows' => $validatedRows,
            'status' => 'validating',
        ]);

        return redirect()->route('products.import.studio', ['batch_id' => $batch->batch_id])
            ->with('success', "Column mapping updated. Review pre-import errors below.");
    }

    public function repairCell(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|string|exists:import_batches,batch_id',
            'row_index' => 'required|integer',
            'field' => 'required|string',
            'value' => 'nullable|string',
        ]);

        $batch = ImportBatch::where('batch_id', $validated['batch_id'])->firstOrFail();
        $rows = $batch->parsed_rows;

        foreach ($rows as &$row) {
            if ($row['row_index'] == $validated['row_index']) {
                $row['mapped_data'][$validated['field']] = $validated['value'];
                $row['status'] = 'repaired';
                break;
            }
        }

        $validatedRows = $this->validateRowsWithMapping($rows, $batch->field_mapping);
        $batch->update(['parsed_rows' => $validatedRows]);

        return back()->with('success', "Row #{$validated['row_index']} cell updated and re-validated.");
    }

    public function skipRow(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|string|exists:import_batches,batch_id',
            'row_index' => 'required|integer',
        ]);

        $batch = ImportBatch::where('batch_id', $validated['batch_id'])->firstOrFail();
        $rows = $batch->parsed_rows;

        foreach ($rows as &$row) {
            if ($row['row_index'] == $validated['row_index']) {
                $row['status'] = 'skipped';
                $row['errors'] = [];
                break;
            }
        }

        $batch->update(['parsed_rows' => $rows]);

        return back()->with('success', "Row #{$validated['row_index']} marked as skipped.");
    }

    public function executeBatch(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|string|exists:import_batches,batch_id',
        ]);

        $batch = ImportBatch::where('batch_id', $validated['batch_id'])->firstOrFail();
        $rows = $batch->parsed_rows;

        $successful = 0;
        $failed = 0;
        $skipped = 0;
        $processed = 0;

        foreach ($rows as $row) {
            $processed++;
            if (($row['status'] ?? '') === 'skipped') {
                $skipped++;
                continue;
            }

            if (!empty($row['errors'])) {
                $failed++;
                continue;
            }

            try {
                $mapped = $row['mapped_data'];
                Product::create([
                    'name' => $mapped['name'] ?? 'Untitled Product',
                    'sku' => strtoupper(trim($mapped['sku'] ?? 'SKU-' . rand(1000, 9999))),
                    'price' => is_numeric($mapped['price'] ?? null) ? (float)$mapped['price'] : 0.00,
                    'quantity' => is_numeric($mapped['quantity'] ?? null) ? (int)$mapped['quantity'] : 0,
                    'category' => !empty($mapped['category']) ? $mapped['category'] : 'General',
                    'description' => $mapped['description'] ?? '',
                ]);
                $successful++;
            } catch (\Throwable $e) {
                $failed++;
            }
        }

        $status = ($failed === 0) ? 'completed' : ($successful > 0 ? 'partial' : 'failed');

        $batch->update([
            'processed_rows' => $processed,
            'successful_rows' => $successful,
            'failed_rows' => $failed,
            'skipped_rows' => $skipped,
            'status' => $status,
        ]);

        ImportHistory::create([
            'file_name' => $batch->file_name,
            'total_rows' => $batch->total_rows,
            'successful_rows' => $successful,
            'failed_rows' => $failed,
            'status' => $status,
        ]);

        return redirect()->route('products.index')
            ->with('success', "🎉 Batch Import Complete! {$successful} products inserted successfully ({$failed} failed, {$skipped} skipped).");
    }

    /*
    |--------------------------------------------------------------------------
    | Feature 2: Multi-Product Image ZIP Import & Gallery Cover Studio
    |--------------------------------------------------------------------------
    */
    public function zipImportForm()
    {
        return view('products.zip_import');
    }

    public function processZipImport(Request $request)
    {
        $request->validate([
            'zip_file' => 'required|file|mimes:zip|max:51200',
        ]);

        $file = $request->file('zip_file');
        $zip = new ZipArchive();
        $res = $zip->open($file->getRealPath());

        if ($res !== true) {
            return back()->with('error', 'Unable to extract the uploaded ZIP file.');
        }

        $extractPath = storage_path('app/temp_zip_' . time());
        File::makeDirectory($extractPath, 0755, true, true);
        $zip->extractTo($extractPath);
        $zip->close();

        $files = File::allFiles($extractPath);
        $matchedCount = 0;
        $unmatchedCount = 0;
        $logs = [];

        foreach ($files as $imgFile) {
            $ext = strtolower($imgFile->getExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                continue;
            }

            $rawName = pathinfo($imgFile->getFilename(), PATHINFO_FILENAME);
            // Match SKU (e.g. SKU101, SKU-101_1, SKU101-main -> SKU101 or SKU-101)
            $possibleSku = strtoupper(explode('_', explode('-', $rawName)[0])[0]);

            // Try exact SKU match first or containing match
            $product = Product::where('sku', strtoupper($rawName))
                ->orWhere('sku', $possibleSku)
                ->orWhere('sku', 'LIKE', "%{$possibleSku}%")
                ->first();

            if ($product) {
                $newFileName = 'zip_' . Str::random(10) . '.' . $ext;
                $storagePath = 'product-images/' . $newFileName;
                Storage::disk('public')->put($storagePath, File::get($imgFile->getRealPath()));

                $isFirst = $product->images()->count() === 0;

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $storagePath,
                    'is_primary' => $isFirst,
                    'order' => $product->images()->count(),
                ]);

                $matchedCount++;
                $logs[] = "✅ Image '{$imgFile->getFilename()}' auto-matched to Product SKU '{$product->sku}' ({$product->name}).";
            } else {
                $unmatchedCount++;
                $logs[] = "⚠️ Image '{$imgFile->getFilename()}' could not be matched to any existing Product SKU.";
            }
        }

        File::deleteDirectory($extractPath);

        return redirect()->route('products.index')
            ->with('success', "🖼️ ZIP Pack Processed! Attached {$matchedCount} product images automatically ({$unmatchedCount} unmatched).");
    }

    public function setPrimaryImage(ProductImage $image)
    {
        ProductImage::where('product_id', $image->product_id)->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return back()->with('success', "Cover image set successfully for Product #{$image->product_id}.");
    }

    public function deleteImage(ProductImage $image)
    {
        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return back()->with('success', 'Product image removed successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Feature 3: Custom Dynamic Export Studio & PDF Product Catalog
    |--------------------------------------------------------------------------
    */
    public function exportStudio()
    {
        $categories = Product::select('category')->distinct()->pluck('category');
        return view('products.export_studio', compact('categories'));
    }

    public function customExport(Request $request)
    {
        $format = strtolower($request->query('format', 'xlsx'));
        $selectedColumns = $request->query('columns', ['id', 'name', 'sku', 'category', 'price', 'quantity', 'created_at']);
        if (is_string($selectedColumns)) {
            $selectedColumns = explode(',', $selectedColumns);
        }

        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', ''));
        $stockStatus = trim((string) $request->query('stock_status', ''));
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');

        $query = Product::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($category !== '') {
            $query->where('category', $category);
        }

        if ($stockStatus === 'in_stock') {
            $query->where('quantity', '>', 0);
        } elseif ($stockStatus === 'out_of_stock') {
            $query->where('quantity', '<=', 0);
        }

        if (is_numeric($minPrice)) {
            $query->where('price', '>=', (float)$minPrice);
        }
        if (is_numeric($maxPrice)) {
            $query->where('price', '<=', (float)$maxPrice);
        }

        $products = $query->latest()->get();

        $fileName = 'products_custom_' . now()->format('Y_m_d_His') . '.' . ($format === 'csv' ? 'csv' : 'csv');

        $callback = function () use ($products, $selectedColumns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, array_map('ucfirst', $selectedColumns));

            foreach ($products as $p) {
                $row = [];
                foreach ($selectedColumns as $col) {
                    $row[] = $p->{$col} ?? '';
                }
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function pdfCatalog(Request $request)
    {
        $query = Product::with('images');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $products = $query->latest()->get();

        return view('products.pdf_catalog', compact('products'));
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Row Validation
    |--------------------------------------------------------------------------
    */
    private function validateRowsWithMapping(array $rows, array $mapping): array
    {
        $existingSkus = Product::pluck('sku')->map(fn($s) => strtoupper(trim($s)))->toArray();
        $seenSkus = [];

        $validated = [];
        foreach ($rows as $row) {
            $mappedData = [];
            $errors = [];

            $rawData = $row['raw_data'] ?? [];

            foreach ($this->targetFields as $field => $config) {
                $headerName = $mapping[$field] ?? null;
                $val = ($headerName && isset($rawData[$headerName])) ? trim((string)$rawData[$headerName]) : '';

                if ($config['required'] && $val === '') {
                    $errors[] = "Field '{$config['label']}' is required.";
                }

                if ($field === 'price' && $val !== '' && !is_numeric($val)) {
                    $errors[] = "Price must be a valid number.";
                }

                if ($field === 'quantity' && $val !== '' && !is_numeric($val)) {
                    $errors[] = "Quantity must be an integer.";
                }

                if ($field === 'sku' && $val !== '') {
                    $upperSku = strtoupper($val);
                    if (in_array($upperSku, $existingSkus, true)) {
                        $errors[] = "SKU '{$upperSku}' already exists in database.";
                    } elseif (in_array($upperSku, $seenSkus, true)) {
                        $errors[] = "Duplicate SKU '{$upperSku}' found within file.";
                    }
                    $seenSkus[] = $upperSku;
                }

                $mappedData[$field] = $val;
            }

            $currentStatus = $row['status'] ?? 'pending';
            if ($currentStatus !== 'skipped') {
                $currentStatus = empty($errors) ? 'valid' : 'invalid';
            }

            $validated[] = [
                'row_index' => $row['row_index'],
                'raw_data' => $rawData,
                'mapped_data' => $mappedData,
                'status' => $currentStatus,
                'errors' => $errors,
            ];
        }

        return $validated;
    }
}
