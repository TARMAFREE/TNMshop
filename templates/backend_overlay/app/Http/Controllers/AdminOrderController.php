<?php

namespace App\Http\Controllers;

use App\Models\Order;

class AdminOrderController extends Controller
{
    public function index()
    {
        $orders = Order::query()->orderByDesc('created_at')->limit(200)->get();
        return response()->json(['data' => $orders]);
    }

    public function show(int $id)
    {
        $order = Order::query()->with('items')->findOrFail($id);
        return response()->json(['data' => $order]);
    }
}
