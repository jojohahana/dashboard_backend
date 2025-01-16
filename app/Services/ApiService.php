<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ApiService
{
    protected $baseUrl;

    public function __construct() {
        $this->baseUrl = "https://eu1-developer.deyecloud.com/v1.0";
    }


    // public function __construct()
    // {
    //     $this->baseUrl = env('API_BASE_URL'); // Store API base URL in .env
    //     if (!$this->baseUrl) {
    //         throw new \Exception('API_BASE_URL is not set in .env file');
    //     }
    // }

    // Function to get the token
    public function getToken() {
        $credentials = [
            "appSecret" => "479f21864ecb3a9c72773145f34532d1",
            "email"     => "plts.oji.2024@gmail.com",
            "password"  => "60efbea8ae4e9d3b7260cb2bff82774c7874ab3f17dc36ac7082c7c7ad897979",
        ];

        $endpoint = "/account/token?appId=202501106975002";
        $url = "{$this->baseUrl}{$endpoint}";

        \Log::info('Making request to URL: ' . $url);

        $response = Http::post($url, $credentials);

        \Log::info('Response: ' . $response->body());

        if ($response->successful()) {
            return $response->json('token');
        }

        throw new \Exception('Failed to fetch token: ' . $response->body());
    }




    // Function to make an API request with the token
    public function fetchData($endpoint, $token, $body = [])
    {
        $response = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$this->baseUrl}/{$endpoint}", $body);

        if ($response->successful()) {
            return $response->json(); // Adjust based on the API response structure
        }

        throw new \Exception('Failed to fetch data: ' . $response->body());
    }
}
