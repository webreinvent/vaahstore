<?php

namespace VaahCms\Modules\Store\Services;

use GuzzleHttp\Client;
use Exception;
use Illuminate\Support\Facades\Http;

class CurrencyConverterService
{
    protected $client;
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('EXCHANGE_RATE_API_KEY', null);
        $this->apiUrl = env('EXCHANGE_RATE_API_URL', 'https://api.exchangerate-api.com/v4/latest'); // Fallback URL if not provided
    }

    public function fetchAllRates($base_currency_code)
    {
        // Example API call to get all conversion rates
        $response = Http::get("https://open.er-api.com/v6/latest/USD");

        if ($response->successful()) {
            return $response->json()['rates'];
        }

        return [];
    }

}
