<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->where('is_enabled', true);

        if ($request->filled('q')) {
            $q = trim((string) $request->get('q'));
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%")
                  ->orWhere('sku', 'like', "%{$q}%");
            });
        }

        $products = $query->orderBy('name')->get([
            'id','sku','name','description','price','currency','image_url','stock_qty','is_enabled','created_at','updated_at'
        ]);

        return response()->json(['data' => $products]);
    }

    public function show(int $id)
    {
        $product = Product::query()->where('is_enabled', true)->findOrFail($id);
        return response()->json(['data' => $product]);
    }
}
