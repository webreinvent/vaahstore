<?php

namespace VaahCms\Modules\Store\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use VaahCms\Modules\Store\Models\Cart;
use VaahCms\Modules\Store\Models\Payment;
use VaahCms\Modules\Store\Models\PaymentMethod;

class StripeService
{
    /**
     * Create Payment Intent.
     */
    public function createPaymentIntent(Request $request)
    {
        try {
            $order = $request->order_details;

            $validation = Cart::validateOrderDetails($request);
            if (!$validation['success']) {
                return response()->json([
                    'success' => false,
                    'errors' => $validation['errors'] ?? ['Invalid order data.']
                ]);
            }

            Stripe::setApiKey(config('services.stripe.secret'));

            $currency = $order['currency']['code'] ?? config('services.stripe.currency', 'USD');
            $rate = $order['currency']['rate'] ?? 1;
            $payableAmount = $order['payable'] ?? 0;
            $order_ref = uniqid('vh_');

            $zeroDecimalCurrencies = ['JPY'];

            // Amount calculation (Stripe expects smallest currency unit)
            if (in_array($currency, $zeroDecimalCurrencies)) {
                $amount = (int) round($payableAmount);
            } else {
                $amount = (int) round($payableAmount * 100);
            }

            if ($amount < 50) {
                return response()->json([
                    'success' => false,
                    'message' => "Amount must be at least 50 in smallest currency unit. Currently: $amount ($currency)."
                ]);
            }

            $intent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => $currency,
                'metadata' => [
                    'order_ref' => $order_ref,
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            // Cache order details against PaymentIntent ID
            Cache::put("stripe_payment_intent_{$intent->id}", $order, now()->addMinutes(30));

            return response()->json([
                'success' => true,
                'data' => [
                    'clientSecret' => $intent->client_secret,
                ],
            ]);
        }
        catch (\Exception $e) {
            $response = [
                'success' => false,
                'errors' => [],
            ];
            if (env('APP_DEBUG')) {
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else {
                $response['errors'][] = 'Failed to create payment intent.';
            }
            return response()->json($response);
        }
    }

    /**
     * Confirm Payment Intent.
     */
    public function confirmPayment(Request $request)
    {
        $payment_intent_id = $request->get('payment_intent_id');

        if (!$payment_intent_id) {
            return response()->json([
                'success' => false,
                'errors' => ['Missing payment_intent_id']
            ]);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            // Retrieve PaymentIntent
            $intent = PaymentIntent::retrieve($payment_intent_id);

            if ($intent->status !== 'succeeded') {
                return response()->json([
                    'success' => false,
                    'errors' => ['Payment not successful.'],
                    'status' => $intent->status,
                ]);
            }

            $order_detail = Cache::get("stripe_payment_intent_{$intent->id}");
            if (!$order_detail) {
                return response()->json([
                    'success' => false,
                    'errors' => ['Order expired or not found.']
                ]);
            }

            $placed_order = $this->placeOrder($order_detail);
            if (!$placed_order) {
                return response()->json([
                    'success' => false,
                    'errors' => ['Order placement failed.']
                ]);
            }

            $payment_result = $this->recordPayment($placed_order, $intent, $order_detail);
            if (!$payment_result['success']) {
                return response()->json([
                    'success' => false,
                    'errors' => ['Payment recorded failed.'],
                    'payment_errors' => $payment_result['errors'] ?? []
                ]);
            }

            Cache::forget("stripe_payment_intent_{$intent->id}");

            return response()->json([
                'success' => true,
                'messages' => ['Order placed and payment recorded successfully.'],
                'data' => [
                    'id' => $placed_order['order']['id'] ?? null,
                    'uuid' => $placed_order['order']['uuid'] ?? null,
                    'cart_uuid' => $placed_order['order']['cart_uuid'] ?? null,
                    'cart_products_count' => $placed_order['order']['cart_products_count'] ?? null,
                    'stripe_response' => $intent,
                ]
            ]);
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'errors' => [],
            ];
            if (env('APP_DEBUG')) {
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else {
                $response['errors'][] = 'Unexpected error during payment confirmation.';
            }
            return response()->json($response);
        }
    }

    /**
     * Place order in system.
     */
    protected function placeOrder($order_detail)
    {
        $order_request = new Request(['order_details' => $order_detail]);
        $result = Cart::placeOrder($order_request);
        return $result['success'] ? $result['data'] : null;
    }
    /**
     * Record payment in system.
     */
    protected function recordPayment($placed_order, $intent, $order_detail)
    {
        $payment_method_id = PaymentMethod::where('slug', 'stripe')->value('id');

        // Stripe amount is in minor units, so convert it
        $amount_in_paid_currency = $intent->amount / 100;

        // Convert it to store's default currency using rate from order detail
        $rate = $order_detail['currency']['rate'] ?? 1;

        $amount_in_store_currency = round($amount_in_paid_currency / $rate, 2);
        $orders_payload = [[
            'id' => $placed_order['order']['id'],
            'pay_amount' => $amount_in_store_currency,
            'payable_amount' => $amount_in_store_currency,
            'currency' => $order_detail['currency']??null, // This can still store the original currency info
        ]];

        $payment_request = new Request([
            'vh_st_payment_method_id' => $payment_method_id,
            'gateway_transaction_id' => $intent->id,
            'exchange_rate' => $order_detail['currency']['rate']?? null,
            'payment_currency_code' => $order_detail['currency']['code']?? null,
            'amount' => $amount_in_store_currency, // Important: store currency
            'orders' => $orders_payload,
            'payment_method' => ['slug' => 'stripe'],
            'notes' => 'Paid via Stripe (PaymentIntent)',
        ]);

        return Payment::createItem($payment_request);
    }

}
