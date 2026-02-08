<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentIntent;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function create(Request $request)
    {
        $payload = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'shipping_address' => 'required|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:tnmshop_products,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        $currency = strtoupper(env('SHOP_CURRENCY', 'THB'));
        $taxRate = (float) env('SHOP_TAX_RATE', 0.0);
        $shippingFee = (float) env('SHOP_SHIPPING_FEE', 0.0);

        return DB::transaction(function () use ($payload, $currency, $taxRate, $shippingFee) {
            $productIds = collect($payload['items'])->pluck('product_id')->unique()->values();
            $products = Product::query()->whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            $lineItems = [];
            $subtotal = 0.0;

            foreach ($payload['items'] as $item) {
                $product = $products->get($item['product_id']);
                if (!$product || !$product->is_enabled) {
                    return response()->json(['message' => 'One or more products are not available'], 409);
                }

                $qty = (int) $item['quantity'];
                if ($qty > $product->stock_qty) {
                    return response()->json(['message' => "Insufficient stock for {$product->name}"], 409);
                }

                $unit = (float) $product->price;
                $line = $unit * $qty;
                $subtotal += $line;

                $lineItems[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'line_total' => $line,
                ];
            }

            $tax = round($subtotal * $taxRate, 2);
            $total = round($subtotal + $tax + $shippingFee, 2);

            $order = Order::create([
                'order_number' => 'TNM-'.strtoupper(Str::random(8)),
                'status' => 'pending_payment',
                'customer_name' => $payload['customer_name'],
                'customer_email' => $payload['customer_email'],
                'customer_phone' => $payload['customer_phone'] ?? null,
                'shipping_address' => $payload['shipping_address'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_fee' => $shippingFee,
                'total' => $total,
                'currency' => $currency,
            ]);

            foreach ($lineItems as $li) {
                $p = $li['product'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $p->id,
                    'sku' => $p->sku,
                    'name' => $p->name,
                    'unit_price' => $li['unit_price'],
                    'quantity' => $li['quantity'],
                    'line_total' => $li['line_total'],
                ]);

                $p->stock_qty = $p->stock_qty - $li['quantity'];
                $p->save();
            }

            $intentId = 'pi_'.Str::random(24);
            $returnUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/').'/payment?intent='.$intentId;

            PaymentIntent::create([
                'intent_id' => $intentId,
                'order_id' => $order->id,
                'status' => 'requires_confirmation',
                'amount' => $total,
                'currency' => $currency,
                'return_url' => $returnUrl,
            ]);

            return response()->json([
                'data' => [
                    'order_number' => $order->order_number,
                    'payment_intent_id' => $intentId,
                    'amount' => $total,
                    'currency' => $currency,
                    'redirect_url' => $returnUrl
                ]
            ], 201);
        });
    }
}
