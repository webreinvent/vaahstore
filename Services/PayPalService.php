<?php

namespace VaahCms\Modules\Store\Services;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use VaahCms\Modules\Store\Models\Cart;
use VaahCms\Modules\Store\Models\Payment;
use VaahCms\Modules\Store\Models\PaymentMethod;

class PayPalService
{
    /**
     * Get PayPal API access token.
     */
    public function getAccessToken()
    {
        $response = Http::withBasicAuth(
            config('services.paypal.client_id'),
            config('services.paypal.client_secret')
        )->asForm()->post("https://api-m.sandbox.paypal.com/v1/oauth2/token", [
            'grant_type' => 'client_credentials',
        ]);

        // Consider checking for errors in response
        return $response->json()['access_token'] ?? null;
    }

    /**
     * Create a PayPal order.
     */
    public function createOrder(Request $request)
    {
        try {
            $order = $request->order_details;

            // Validate order details
            $validation_response = Cart::validateOrderDetails($request);
            if (!$validation_response['success']) {
                return response()->json($validation_response);
            }

            $payload = $this->buildCreateOrderPayload($order);
            $access_token = $this->getAccessToken();
            if (!$access_token) {
                return response()->json([
                    'success' => false,
                    'errors' => ['Unable to get PayPal access token.'],
                ]);
            }

            $client = new Client();

            $response = $client->post('https://api-m.sandbox.paypal.com/v2/checkout/orders', [
                'headers' => [
                    'Authorization' => "Bearer {$access_token}",
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload
            ]);

            $response_data = json_decode($response->getBody(), true);
            $paypal_order_id = $response_data['id'] ?? null;

            if ($paypal_order_id) {
                Cache::put("paypal_order_{$paypal_order_id}", $order, now()->addMinutes(30));
            }

            return response()->json([
                'success' => true,
                'data' => $response_data,
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
                $response['errors'][] = 'Unable to create PayPal order.';
            }
            return response()->json($response);
        }
    }

    /**
     * Capture a PayPal order.
     */
    public function captureOrder($id)
    {
        try {
            $access_token = $this->getAccessToken();
            if (!$access_token) {
                return response()->json([
                    'success' => false,
                    'errors' => ['Unable to get PayPal access token.'],
                ]);
            }

            $order_detail = Cache::get("paypal_order_{$id}");

            $paypal_data = $this->capturePaypalOrder($id, $access_token);

            if (!$this->isPaymentCompleted($paypal_data)) {
                return [
                    'success' => false,
                    'errors' => ['Payment not completed.'],
                    'paypal_response' => $paypal_data,
                ];
            }

            if (!$order_detail) {
                return [
                    'success' => false,
                    'errors' => ['Order details not found or expired in cache.'],
                ];
            }

            $placed_order = $this->placeOrder($order_detail);

            if (!$placed_order) {
                return [
                    'success' => false,
                    'errors' => ['Order placement failed.'],
                ];
            }

            $payment_result = $this->recordPayment($placed_order, $paypal_data, $order_detail);

            if (!$payment_result['success']) {
                return [
                    'success' => false,
                    'errors' => ['Order placed but failed to record payment.'],
                    'payment_errors' => $payment_result['errors'] ?? [],
                ];
            }

            return [
                'success' => true,
                'messages' => ['Order placed and payment recorded successfully.'],
                'data' => [
                    'id' => $placed_order['order']['id'] ?? null,
                    'uuid' => $placed_order['order']['uuid'] ?? null,
                    'cart_uuid' => $placed_order['order']['cart_uuid'] ?? null,
                    'cart_products_count' => $placed_order['order']['cart_products_count'] ?? null,
                    'paypal_response' => $paypal_data,
                ],
            ];
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
                $response['errors'][] = 'Unexpected error occurred during payment capture.';
            }
            return response()->json($response);
        }
    }

    /**
     * Build payload for PayPal order creation.
     */
    protected function buildCreateOrderPayload(array $order)
    {
        $products = $order['products'];
        $order_currency = $order['currency'] ?? ['code' => 'USD', 'rate' => 1];
        $currency_code = $order_currency['code'];
        $conversion_rate = $order_currency['rate'] ?? 1;

        $total = $order['payable'] ?? 0;
        $merchant_currency_code = config('services.paypal.currency', 'USD');

        $items = [];

        foreach ($products as $product) {
            $price = $product['pivot']['price'];
            $converted_price = $price / $conversion_rate;

            $items[] = [
                'name' => $product['name'],
                'unit_amount' => [
                    'currency_code' => $merchant_currency_code,
                    'value' => number_format($converted_price, 2, '.', ''),
                ],
                'quantity' => $product['pivot']['quantity']
            ];
        }

        $converted_total = $total / $conversion_rate;

        return [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $merchant_currency_code,
                    'value' => number_format($converted_total, 2, '.', ''),
                    'breakdown' => [
                        'item_total' => [
                            'currency_code' => $merchant_currency_code,
                            'value' => number_format($converted_total, 2, '.', '')
                        ]
                    ]
                ],
                'items' => $items,
                'shipping' => [
                    'name' => [
                        'full_name' => 'Nuxt Store'
                    ],
                    'address' => [
                        'address_line_1' => '123 Main St',
                        'admin_area_2' => 'New York',
                        'admin_area_1' => 'NY',
                        'postal_code' => '10001',
                        'country_code' => 'US'
                    ]
                ],
            ]],
            'application_context' => [
                'return_url' => url('/paypal/success'),
                'cancel_url' => url('/paypal/cancel'),
                'shipping_preference' => 'SET_PROVIDED_ADDRESS'
            ]
        ];
    }

    /**
     * Capture PayPal order.
     */
    protected function capturePaypalOrder($id, $access_token)
    {
        $client = new Client();

        $response = $client->post("https://api-m.sandbox.paypal.com/v2/checkout/orders/{$id}/capture", [
            'headers' => [
                'Authorization' => "Bearer {$access_token}",
                'Content-Type' => 'application/json',
            ],
            'body' => '{}',
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Check if payment is completed.
     */
    protected function isPaymentCompleted($data)
    {
        return isset($data['status']) && $data['status'] === 'COMPLETED';
    }

    /**
     * Place order in system.
     */
    protected function placeOrder($order_detail)
    {
        $order_request = new Request(['order_details' => $order_detail]);
        $result = Cart::placeOrder($order_request);

        return $result['success'] ? ($result['data'] ?? null) : null;
    }

    /**
     * Record payment in system.
     */
    protected function recordPayment($placed_order, $paypal_data, $order_detail)
    {
        $paypal_email = $paypal_data['payer']['email_address'] ?? null;
        $payment_amount = $paypal_data['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? null;
        $capture_id = $paypal_data['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;

        $payment_method_id = PaymentMethod::where('slug', 'paypal')->value('id');

        $orders_payload = [
            [
                'id' => $placed_order['order']['id'],
                'pay_amount' => (float) $payment_amount,
                'payable_amount' => (float) $payment_amount,
                'currency' =>  $order_detail['currency'] ?? null,
            ]
        ];

        $payment_request = new Request([
            'vh_st_payment_method_id' => $payment_method_id,
            'gateway_transaction_id' => $capture_id,
            'exchange_rate' => $order_detail['currency']['rate'] ?? null,
            'payment_currency_code' => $order_detail['currency']['code'] ?? null,
            'amount' => (float) $payment_amount,
            'orders' => $orders_payload,
            'payment_method' => ['slug' => 'paypal'],
            'notes' => 'Paid via PayPal by ' . $paypal_email,
        ]);

        return Payment::createItem($payment_request);
    }
}
