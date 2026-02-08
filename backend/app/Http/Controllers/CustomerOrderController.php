<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    // GET /api/auth/orders?per_page=50
    public function index(Request $request)
    {
        $email = strtolower((string) $request->user()->email);

        $perPage = (int) $request->query('per_page', 20);
        if ($perPage < 1) $perPage = 20;
        if ($perPage > 50) $perPage = 50;

        $orders = Order::query()
            ->with('items')
            ->whereRaw('LOWER(customer_email) = ?', [$email])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    // GET /api/auth/orders/{orderNumber}
    public function show(Request $request, string $orderNumber)
    {
        $email = strtolower((string) $request->user()->email);

        $order = Order::query()
            ->with('items')
            ->where('order_number', $orderNumber)
            ->whereRaw('LOWER(customer_email) = ?', [$email])
            ->firstOrFail();

        return response()->json(['data' => $order]);
    }
}
