<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon; // Import Carbon for date handling

class DeyeController extends Controller
{
    public function getDeviceHistory(Request $request)
    {
        // Define the list of device serial numbers
        $deviceSnList = [
            "2404164849",
            "2404164857",
            "2404164854",
            "2404164850",
            "2404164862",
            "2404164858"
        ];

        // Get today's date in the required format
        $today = Carbon::now()->format('Y-m-d');

        // Prepare an array to hold the results
        $results = [];

        // Loop through each device serial number
        foreach ($deviceSnList as $deviceSn) {
            // Prepare the request to the external API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJhdWQiOlsib2F1dGgyLXJlc291cmNlIl0sInVzZXJfbmFtZSI6IjBfcGx0cy5vamkuMjAyNEBnbWFpbC5jb21fMiIsInNjb3BlIjpbImFsbCJdLCJkZXRhaWwiOnsib3JnYW5pemF0aW9uSWQiOjAsInRvcEdyb3VwSWQiOm51bGwsImdyb3VwSWQiOm51bGwsInJvbGVJZCI6LTEsInVzZXJJZCI6MTI5OTczMjIsInZlcnNpb24iOjEwMDAsImlkZW50aWZpZXIiOiJwbHRzLm9qaS4yMDI0QGdtYWlsLmNvbSIsImlkZW50aXR5VHlwZSI6MiwibWRjIjoidWMiLCJhcHBJZCI6IjIwMjUwMTEwNjk3NTAwMiIsIm1mYVN0YXR1cyI6bnVsbCwidGVuYW50IjoiRGV5ZSJ9LCJleHAiOjE3NDE2Nzc4NDUsIm1kYyI6InVjIiwiYXV0aG9yaXRpZXMiOlsiYWxsIl0sImp0aSI6Ijk3NTRlZGIwLWQxMTYtNGZkMS1hZThhLTMyZmZkODM0MDcxNyIsImNsaWVudF9pZCI6InRlc3QifQ.HzLodCpaEkKLyyge1DmylQ2sS9_qSoEwRDjHd51RidJf_2Osh2pv-Vej1hAKakPnuZpqFN7Twnkr_3SlIPqIJ7wfhMRusXedai2wwcqQp4pylhytI2LJGW5X4arvB5oU0rHkqGEE27cEyEoqHFftHV6_hh5Domkr_L5Ry-jQyK90vKKi6LywwoYhnIiqqo477Ylj2W7PzIdF6MwEEKY004zxDi3nnqC7dodMKtWB3vSFWCeFKkqwcxes2HNJgc9BEqdX59NWs0WWeqAb8TbToKRTxfMCGkY-ROB7kyQHKclTHnrDvCu4WkswDO-Lppca4rQq8DYcA-hras7gaBbpxQ', // Replace with your actual token
                'Content-Type' => 'application/json',
            ])->post('https://eu1-developer.deyecloud.com/v1.0/device/history', [
                'deviceSn' => $deviceSn, // Use the current device serial number
                'startAt' => $today,     // Use today's date for startAt
                'endAt' => $today,       // Use today's date for endAt
                'granularity' => 1,
                'measurePoints' => ['TotalConsumption'],
            ]);

            // Collect the response for each device
            $results[] = $response->json(); // Store the response
        }

        // Return the aggregated results
        return response()->json($results);
    }
}
