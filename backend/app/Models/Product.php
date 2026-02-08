<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // ชี้ตารางจริง
    protected $table = 'tnmshop_products';

    // ถ้าตารางไม่มี created_at / updated_at ให้ปิด
    public $timestamps = false;

    // อนุญาตให้ mass assign
    protected $fillable = [
        'sku',
        'name',
        'description',
        'price',
        'currency',
        'image_url',
        'stock_qty',
        'is_enabled',
      ];      

    // แปลงชนิดข้อมูล
    protected $casts = [
        'price' => 'decimal:2',
        'is_enabled' => 'boolean',
        'stock_qty' => 'integer',
    ];
}
