<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return redirect()->route('products.index');
});

// Product routes
Route::prefix('products')->group(function () {

    Route::get('/', [ProductController::class, 'index'])->name('products.index');

    Route::get('/create', [ProductController::class, 'create'])->name('products.create');

    Route::post('/', [ProductController::class, 'store'])->name('products.store');

    Route::get('/dashboard', [ProductController::class, 'dashboard'])
        ->name('products.dashboard');

    Route::get('/export/csv', [ProductController::class, 'exportCsv'])
        ->name('products.export.csv');

    Route::get('/trash/list', [ProductController::class, 'trash'])
        ->name('products.trash');

    Route::get('/export/excel', [ProductController::class, 'export'])
        ->name('products.export');

    Route::post('/import/excel', [ProductController::class, 'import'])
        ->name('products.import');

    Route::get('/{product}', [ProductController::class, 'show'])
        ->name('products.show');

    Route::get('/{product}/edit', [ProductController::class, 'edit'])
        ->name('products.edit');

    Route::put('/{product}', [ProductController::class, 'update'])
        ->name('products.update');

    Route::delete('/{product}', [ProductController::class, 'destroy'])
        ->name('products.destroy');

    Route::post('/{id}/restore', [ProductController::class, 'restore'])
        ->name('products.restore');

    Route::delete('/{id}/force-delete', [ProductController::class, 'forceDelete'])
        ->name('products.forceDelete');
});