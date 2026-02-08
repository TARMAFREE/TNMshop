<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentIntent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function show(string $intentId)
    {
        $intent = PaymentIntent::query()->where('intent_id', $intentId)->firstOrFail();
        $order = Order::query()->with('items')->findOrFail($intent->order_id);

        return response()->json([
            'data' => [
                'payment_intent' => $intent,
                'order' => $order,
            ]
        ]);
    }

    public function confirm(Request $request, string $intentId)
    {
        $data = $request->validate([
            'card_number' => 'required|string',
            'exp_month' => 'required|integer|min:1|max:12',
            'exp_year' => 'required|integer|min:2000|max:2100',
            'cvc' => 'required|string|min:3|max:4',
            'simulate' => 'nullable|in:success,fail'
        ]);

        return DB::transaction(function () use ($intentId, $data) {
            $intent = PaymentIntent::query()
                ->where('intent_id', $intentId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($intent->status === 'succeeded') {
                $order = Order::query()->with('items')->findOrFail($intent->order_id);
                return response()->json(['data' => ['payment_intent' => $intent, 'order' => $order]]);
            }

            if ($intent->status === 'failed') {
                return response()->json(['message' => 'Payment already failed'], 409);
            }

            $result = $data['simulate'] ?? null;
            if (!$result) {
                $result = $this->isValidCard($data['card_number']) ? 'success' : 'fail';
            }

            $intent->status = $result === 'success' ? 'succeeded' : 'failed';
            $intent->save();

            $order = Order::query()->lockForUpdate()->findOrFail($intent->order_id);
            $order->status = $result === 'success' ? 'paid' : 'payment_failed';
            $order->save();

            $order = Order::query()->with('items')->findOrFail($intent->order_id);

            return response()->json([
                'data' => [
                    'payment_intent' => $intent,
                    'order' => $order
                ]
            ]);
        });
    }

    private function isValidCard(string $cardNumber): bool
    {
        $digits = preg_replace('/\D+/', '', $cardNumber) ?? '';
        if (strlen($digits) < 12 || strlen($digits) > 19) {
            return false;
        }

        if (str_ends_with($digits, '0000')) {
            return false;
        }

        $sum = 0;
        $alt = false;
        for ($i = strlen($digits) - 1; $i >= 0; $i--) {
            $n = intval($digits[$i]);
            if ($alt) {
                $n *= 2;
                if ($n > 9) $n -= 9;
            }
            $sum += $n;
            $alt = !$alt;
        }
        return $sum % 10 === 0;
    }
}
