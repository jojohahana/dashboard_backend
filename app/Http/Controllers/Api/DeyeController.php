<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class DeyeController extends Controller
{
    public function getDeviceHistory(Request $request) {
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

        // Get the current epoch time
        $currentEpochTime = time();

        // Loop through each device serial number
        foreach ($deviceSnList as $deviceSn) {
            // Prepare the request to the external API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJhdWQiOlsib2F1dGgyLXJlc291cmNlIl0sInVzZXJfbmFtZSI6IjBfcGx0cy5vamkuMjAyNEBnbWFpbC5jb21fMiIsInNjb3BlIjpbImFsbCJdLCJkZXRhaWwiOnsib3JnYW5pemF0aW9uSWQiOjAsInRvcEdyb3VwSWQiOm51bGwsImdyb3VwSWQiOm51bGwsInJvbGVJZCI6LTEsInVzZXJJZCI6MTI5OTczMjIsInZlcnNpb24iOjEwMDAsImlkZW50aWZpZXIiOiJwbHRzLm9qaS4yMDI0QGdtYWlsLmNvbSIsImlkZW50aXR5VHlwZSI6MiwibWRjIjoidWMiLCJhcHBJZCI6IjIwMjUwMTEwNjk3NTAwMiIsIm1mYVN0YXR1cyI6bnVsbCwidGVuYW50IjoiRGV5ZSJ9LCJleHAiOjE3NDE2Nzc4NDUsIm1kYyI6InVjIiwiYXV0aG9yaXRpZXMiOlsiYWxsIl0sImp0aSI6Ijk3NTRlZGIwLWQxMTYtNGZkMS1hZThhLTMyZmZkODM0MDcxNyIsImNsaWVudF9pZCI6InRlc3QifQ.HzLodCpaEkKLyyge1DmylQ2sS9_qSoEwRDjHd51RidJf_2Osh2pv-Vej1hAKakPnuZpqFN7Twnkr_3SlIPqIJ7wfhMRusXedai2wwcqQp4pylhytI2LJGW5X4arvB5oU0rHkqGEE27cEyEoqHFftHV6_hh5Domkr_L5Ry-jQyK90vKKi6LywwoYhnIiqqo477Ylj2W7PzIdF6MwEEKY004zxDi3nnqC7dodMKtWB3vSFWCeFKkqwcxes2HNJgc9BEqdX59NWs0WWeqAb8TbToKRTxfMCGkY-ROB7kyQHKclTHnrDvCu4WkswDO-Lppca4rQq8DYcA-hras7gaBbpxQ',
                'Content-Type' => 'application/json',
            ])->post('https://eu1-developer.deyecloud.com/v1.0/device/history', [
                'deviceSn' => $deviceSn,
                'startAt' => $today,     // Use today's date for startAt
                'endAt' => $today,       // Use today's date for endAt
                'granularity' => 1,
                'measurePoints' => ['DailyActiveProduction'],
            ]);

            // Check if the response is successful
            if ($response->successful()) {
                $data = $response->json();

                // Initialize variables to hold the most recent consumption data
                $latestConsumption = 0;
                $latestTime = null;

                // Loop through the dataList to find the most recent time
                foreach ($data['dataList'] as $dataPoint) {
                    // Check if the time is less than or equal to the current epoch time
                    if ($dataPoint['time'] <= $currentEpochTime) {
                        // Extract the TotalConsumption value
                        foreach ($dataPoint['itemList'] as $item) {
                            if ($item['key'] === 'DailyActiveProduction') {
                                // Update the latest consumption and time if this is the most recent
                                $latestConsumption = $item['value'];
                                $latestTime = $dataPoint['time'];
                            }
                        }
                    }
                }

                // Store the result for the current device
                $results[] = [
                    'deviceSn' => $deviceSn,
                    'totalConsumption' => $latestConsumption,
                    'time' => $latestTime, // Use the latest time found
                ];
            }
        }

        // Return the aggregated results
        return response()->json($results);
    }

    public function getSumDeviceHistory(Request $request) {
        $deviceSnList = [
            "2404164849",
            "2404164857",
            "2404164854",
            "2404164850",
            "2404164862",
            "2404164858"
        ];

        // Prepare a variable to hold the total consumption
        $totalConsumption = 0;

        // Get today's date in the required format
        $today = Carbon::now()->format('Y-m-d');

        foreach ($deviceSnList as $deviceSn) {
            // Make the API call to get the device history
            $response = Http::withHeaders([
                'Authorization' => 'Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJhdWQiOlsib2F1dGgyLXJlc291cmNlIl0sInVzZXJfbmFtZSI6IjBfcGx0cy5vamkuMjAyNEBnbWFpbC5jb21fMiIsInNjb3BlIjpbImFsbCJdLCJkZXRhaWwiOnsib3JnYW5pemF0aW9uSWQiOjAsInRvcEdyb3VwSWQiOm51bGwsImdyb3VwSWQiOm51bGwsInJvbGVJZCI6LTEsInVzZXJJZCI6MTI5OTczMjIsInZlcnNpb24iOjEwMDAsImlkZW50aWZpZXIiOiJwbHRzLm9qaS4yMDI0QGdtYWlsLmNvbSIsImlkZW50aXR5VHlwZSI6MiwibWRjIjoidWMiLCJhcHBJZCI6IjIwMjUwMTEwNjk3NTAwMiIsIm1mYVN0YXR1cyI6bnVsbCwidGVuYW50IjoiRGV5ZSJ9LCJleHAiOjE3NDE2Nzc4NDUsIm1kYyI6InVjIiwiYXV0aG9yaXRpZXMiOlsiYWxsIl0sImp0aSI6Ijk3NTRlZGIwLWQxMTYtNGZkMS1hZThhLTMyZmZkODM0MDcxNyIsImNsaWVudF9pZCI6InRlc3QifQ.HzLodCpaEkKLyyge1DmylQ2sS9_qSoEwRDjHd51RidJf_2Osh2pv-Vej1hAKakPnuZpqFN7Twnkr_3SlIPqIJ7wfhMRusXedai2wwcqQp4pylhytI2LJGW5X4arvB5oU0rHkqGEE27cEyEoqHFftHV6_hh5Domkr_L5Ry-jQyK90vKKi6LywwoYhnIiqqo477Ylj2W7PzIdF6MwEEKY004zxDi3nnqC7dodMKtWB3vSFWCeFKkqwcxes2HNJgc9BEqdX59NWs0WWeqAb8TbToKRTxfMCGkY-ROB7kyQHKclTHnrDvCu4WkswDO-Lppca4rQq8DYcA-hras7gaBbpxQ', // Replace with your actual token
            ])->post('https://eu1-developer.deyecloud.com/v1.0/device/history', [
                'deviceSn' => $deviceSn,
                'startAt' => $today,
                'endAt' => $today,
                'measurePoints' => ['TotalConsumptionPower'], // Change to the desired measure point
            ]);

            // Check if the response is successful
            if ($response->successful()) {
                $data = $response->json();

                // Check if dataList exists and is not empty
                if (isset($data['dataList']) && !empty($data['dataList'])) {
                    // Get the last entry in dataList
                    $lastEntry = end($data['dataList']);
                    $totalConsumption += (float) $lastEntry['itemList'][0]['value']; // Assuming the first item is TotalConsumptionPower
                }
            } else {
                // Log the error or handle it as needed
                \Log::error("Error fetching data for device $deviceSn: " . $response->body());
            }
        }

        return response()->json([
            'success' => true,
            'totalConsumption' => $totalConsumption,
        ]);
    }

    public function getAmountDevice(Request $request) {
        // Define the list of device serial numbers
        $deviceSnList = [
            "2404164849",
            "2404164857",
            "2404164854",
            "2404164850",
            "2404164862",
            "2404164858"
        ];

        // Prepare a variable to hold the total consumption
        $totalConsumption = 0;

        // Loop through each device serial number
        foreach ($deviceSnList as $deviceSn) {
            // Make the API call to the external service
            $response = Http::withHeaders([
                'Authorization' => 'Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJhdWQiOlsib2F1dGgyLXJlc291cmNlIl0sInVzZXJfbmFtZSI6IjBfcGx0cy5vamkuMjAyNEBnbWFpbC5jb21fMiIsInNjb3BlIjpbImFsbCJdLCJkZXRhaWwiOnsib3JnYW5pemF0aW9uSWQiOjAsInRvcEdyb3VwSWQiOm51bGwsImdyb3VwSWQiOm51bGwsInJvbGVJZCI6LTEsInVzZXJJZCI6MTI5OTczMjIsInZlcnNpb24iOjEwMDAsImlkZW50aWZpZXIiOiJwbHRzLm9qaS4yMDI0QGdtYWlsLmNvbSIsImlkZW50aXR5VHlwZSI6MiwibWRjIjoidWMiLCJhcHBJZCI6IjIwMjUwMTEwNjk3NTAwMiIsIm1mYVN0YXR1cyI6bnVsbCwidGVuYW50IjoiRGV5ZSJ9LCJleHAiOjE3NDE2Nzc4NDUsIm1kYyI6InVjIiwiYXV0aG9yaXRpZXMiOlsiYWxsIl0sImp0aSI6Ijk3NTRlZGIwLWQxMTYtNGZkMS1hZThhLTMyZmZkODM0MDcxNyIsImNsaWVudF9pZCI6InRlc3QifQ.HzLodCpaEkKLyyge1DmylQ2sS9_qSoEwRDjHd51RidJf_2Osh2pv-Vej1hAKakPnuZpqFN7Twnkr_3SlIPqIJ7wfhMRusXedai2wwcqQp4pylhytI2LJGW5X4arvB5oU0rHkqGEE27cEyEoqHFftHV6_hh5Domkr_L5Ry-jQyK90vKKi6LywwoYhnIiqqo477Ylj2W7PzIdF6MwEEKY004zxDi3nnqC7dodMKtWB3vSFWCeFKkqwcxes2HNJgc9BEqdX59NWs0WWeqAb8TbToKRTxfMCGkY-ROB7kyQHKclTHnrDvCu4WkswDO-Lppca4rQq8DYcA-hras7gaBbpxQ', // Replace with your actual token
            ])->post('https://eu1-developer.deyecloud.com/v1.0/device/history', [
                'deviceSn' => $deviceSn,
                'startAt' => now()->subDay()->timestamp, // Adjust as needed
                'endAt' => now()->timestamp,
                'measurePoints' => ['TotalConsumptionPower'], // Change to your required measure point
            ]);

            // Check if the response is successful
            if ($response->successful()) {
                $data = $response->json();

                // Assuming the data structure contains a 'dataList' array
                foreach ($data['dataList'] as $item) {
                    // Sum the total consumption
                    $totalConsumption += (float) $item['totalConsumption'];
                }
            }
        }

        // Return the total consumption
        return response()->json([
            'success' => true,
            'totalConsumption' => $totalConsumption,
        ]);
    }


}
