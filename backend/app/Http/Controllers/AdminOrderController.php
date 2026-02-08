<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $q = Order::query()->orderByDesc('created_at');

        // /admin/orders?status=paid หรือ /admin/orders?status=paid,shipped
        if ($request->filled('status')) {
            $raw = (string) $request->query('status');
            $statuses = collect(explode(',', $raw))
                ->map(fn ($s) => trim($s))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (count($statuses) > 0) {
                $q->whereIn('status', $statuses);
            }
        }

        $orders = $q->limit(200)->get();
        return response()->json(['data' => $orders]);
    }

    public function show(int $id)
    {
        $order = Order::query()->with('items')->findOrFail($id);
        return response()->json(['data' => $order]);
    }

    // PATCH /admin/orders/{id}/ship
    public function ship(Request $request, int $id)
    {
        $data = $request->validate([
            'tracking_number' => 'required|string|max:100',
            'shipping_carrier' => 'required|string|max:100',
        ]);

        return DB::transaction(function () use ($id, $data) {
            $order = Order::query()->lockForUpdate()->findOrFail($id);

            if ($order->status !== 'paid') {
                return response()->json(['message' => 'Only paid orders can be marked as shipped'], 409);
            }

            $order->shipping_carrier = $data['shipping_carrier'];
            $order->tracking_number = $data['tracking_number'];
            $order->shipped_at = now();
            $order->status = 'shipped';
            $order->save();

            return response()->json(['data' => $order]);
        });
    }
}
