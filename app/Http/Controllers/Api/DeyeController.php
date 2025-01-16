<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Http\Request;

class DeyeController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService) {
        $this->apiService = $apiService;
    }

    public function getDataToken(Request $request) {
        try {
            // Fetch token
            $token = $this->apiService->getToken('plts.oji.2024@gmail.com', '60efbea8ae4e9d3b7260cb2bff82774c7874ab3f17dc36ac7082c7c7ad897979', '479f21864ecb3a9c72773145f34532d1');

            // Fetch data with the token
            $data = $this->apiService->fetchData('desired-endpoint', $token, [
                'param1' => $request->input('param1'),
                'param2' => $request->input('param2'),
            ]);

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getInverterDevices(Request $request) {
        try {
            // Get the token
            $token = $this->apiService->getToken();

            // Fetch station data
            $response = $this->apiService->fetchStationListWithDevice($token);

            // Filter deviceSn where deviceType = "INVERTER"
            $filteredDeviceSn = [];
            foreach ($response['stationList'] as $station) {
                foreach ($station['deviceListItems'] as $device) {
                    if ($device['deviceType'] === 'INVERTER') {
                        $filteredDeviceSn[] = $device['deviceSn'];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $filteredDeviceSn,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
