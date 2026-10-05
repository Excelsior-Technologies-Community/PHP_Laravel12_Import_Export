<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ImportExportStudioController;

Route::get('/', function () {
    return redirect()->route('products.index');
});

// Product routes
Route::prefix('products')->group(function () {

    Route::get('/', [ProductController::class, 'index'])->name('products.index');
    Route::get('/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/', [ProductController::class, 'store'])->name('products.store');
    Route::get('/dashboard', [ProductController::class, 'dashboard'])->name('products.dashboard');

    // Interactive Drag & Drop Import Studio
    Route::get('/import-studio', [ImportExportStudioController::class, 'importStudio'])->name('products.import.studio');
    Route::post('/import-studio/upload', [ImportExportStudioController::class, 'uploadFile'])->name('products.import.upload');
    Route::post('/import-studio/apply-mapping', [ImportExportStudioController::class, 'applyMapping'])->name('products.import.apply_mapping');
    Route::post('/import-studio/repair-cell', [ImportExportStudioController::class, 'repairCell'])->name('products.import.repair_cell');
    Route::post('/import-studio/skip-row', [ImportExportStudioController::class, 'skipRow'])->name('products.import.skip_row');
    Route::post('/import-studio/execute', [ImportExportStudioController::class, 'executeBatch'])->name('products.import.execute');

    // Multi-Product Image ZIP Import & Gallery
    Route::get('/zip-import', [ImportExportStudioController::class, 'zipImportForm'])->name('products.zip_import.form');
    Route::post('/zip-import', [ImportExportStudioController::class, 'processZipImport'])->name('products.zip_import.process');
    Route::post('/images/{image}/set-primary', [ImportExportStudioController::class, 'setPrimaryImage'])->name('products.images.set_primary');
    Route::delete('/images/{image}', [ImportExportStudioController::class, 'deleteImage'])->name('products.images.delete');

    // Custom Export Studio & PDF Catalog
    Route::get('/export-studio', [ImportExportStudioController::class, 'exportStudio'])->name('products.export.studio');
    Route::get('/export-studio/custom', [ImportExportStudioController::class, 'customExport'])->name('products.export.custom');
    Route::get('/export-pdf-catalog', [ImportExportStudioController::class, 'pdfCatalog'])->name('products.export.pdf_catalog');

    // Standard Export & Import
    Route::get('/export/csv', [ProductController::class, 'exportCsv'])->name('products.export.csv');
    Route::get('/export/excel', [ProductController::class, 'export'])->name('products.export');
    Route::post('/import/excel', [ProductController::class, 'import'])->name('products.import');

    // Trash List & History
    Route::get('/trash/list', [ProductController::class, 'trash'])->name('products.trash');
    Route::get('/import-history', [ProductController::class,'importHistory'])->name('products.import.history');
    Route::get('/export-history', [ProductController::class,'exportHistory'])->name('products.export.history');

    Route::get('/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');
    Route::delete('/{id}/force-delete', [ProductController::class, 'forceDelete'])->name('products.forceDelete');
});