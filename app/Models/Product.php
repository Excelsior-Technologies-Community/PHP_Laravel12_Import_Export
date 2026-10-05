<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
        'category',
        'sku'
    ];

    protected function price(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => str_replace(',', '', $value)
        );
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderByDesc('is_primary')->orderBy('order');
    }

    public function getFirstImageAttribute()
    {
        $primary = $this->images->where('is_primary', true)->first();
        if ($primary) {
            return $primary->image_path;
        }
        return $this->images->first()->image_path ?? 'default-product.jpg';
    }
}