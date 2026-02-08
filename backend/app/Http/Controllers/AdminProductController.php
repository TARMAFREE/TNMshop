<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    // GET /api/admin/products
    public function index()
    {
        // ปลอดภัยสุด: เรียงด้วย id (ไม่พังถ้าไม่มี updated_at)
        $products = Product::query()
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $products]);
    }

    // POST /api/admin/products
    public function store(Request $request)
    {
        $data = $request->validate([
            'sku' => 'required|string|max:64|unique:tnmshop_products,sku',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'image_url' => 'nullable|url|max:2048',
            'stock_qty' => 'required|integer|min:0',
            'is_enabled' => 'required|boolean',
        ]);

        if (!empty($data['currency'])) {
            $data['currency'] = strtoupper($data['currency']);
        }

        $product = Product::create($data);

        return response()->json(['data' => $product], 201);
    }

    // PUT /api/admin/products/{id}
    public function update(Request $request, int $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'sku' => 'sometimes|required|string|max:64|unique:tnmshop_products,sku,' . $product->id,
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'image_url' => 'nullable|url|max:2048',
            'stock_qty' => 'sometimes|required|integer|min:0',
            'is_enabled' => 'sometimes|required|boolean',
        ]);

        if (array_key_exists('currency', $data) && !empty($data['currency'])) {
            $data['currency'] = strtoupper($data['currency']);
        }

        $product->fill($data)->save();

        return response()->json(['data' => $product]);
    }

    // PATCH /api/admin/products/{id}/toggle
    public function toggle(Request $request, int $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'is_enabled' => 'required|boolean',
        ]);

        $product->is_enabled = $data['is_enabled'];
        $product->save();

        return response()->json(['data' => $product]);
    }

    // DELETE /api/admin/products/{id}
    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
