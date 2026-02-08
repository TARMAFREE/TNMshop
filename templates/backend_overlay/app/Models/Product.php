<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'tnmshop_products';

    protected $fillable = [
        'sku',
        'name',
        'description',
        'price',
        'currency',
        'image_url',
        'is_enabled',
        'stock_qty'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_enabled' => 'boolean',
        'stock_qty' => 'integer',
    ];
}
