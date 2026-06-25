<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Product::with('images')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Description',
            'Price',
            'Quantity',
            'Category',
            'SKU',
            'Images',
            'Created At'
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->description ?? '',
            number_format($product->price,2),
            $product->quantity,
            $product->category,
            $product->sku,
            $product->images->pluck('image_path')->implode(', '),
            $product->created_at
        ];
    }
}