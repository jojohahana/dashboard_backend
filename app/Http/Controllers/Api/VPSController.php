<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DummyLvmdp;
use App\Models\Cubical;

class VPSController extends Controller
{
    public function getSummaryByDate($date) {

        //Fetch sum for specified date
        $summary = DummyLvmdp::whereDate('date', $date)
                -> sum('ttl_active_energy');

        return response()->json([
            'date' => $date,
            'ttl_activenergy_sum' => $summary
        ]);
    }

    public function getValueGapCons() {
        $result = DB::table('cubical')
                ->selectRaw("
                MAX(CASE WHEN \"Jam_save\" = '00:59:59' AND \"Tanggal_save\" = CURRENT_DATE THEN \"Total_active_Energy\" END) -
                MIN(CASE WHEN \"Jam_save\" = '23:59:59' AND \"Tanggal_save\" = CURRENT_DATE - INTERVAL '1 day' THEN \"Total_active_Energy\" END) AS value_gap")
                ->whereIn('Tanggal_save', [DB::raw('CURRENT_DATE'), DB::raw('CURRENT_DATE - INTERVAL \'1 Day\'')])
                ->first();

        return response() ->json([
            'value_gap' => $result->value_gap ?? 0
        ]);
    }

    public function getValueGapInRupiah() {
        // Raw query to get the value_gap for both conditions
        $result = DB::table('cubical')
            ->selectRaw("
                MAX(CASE WHEN \"Jam_save\" = '08:59:59' AND \"Tanggal_save\" = CURRENT_DATE THEN \"Total_active_Energy\" END) -
                MIN(CASE WHEN \"Jam_save\" = '20:59:59' AND \"Tanggal_save\" = CURRENT_DATE - INTERVAL '1 day' THEN \"Total_active_Energy\" END) AS value_gap
            ")
            ->whereIn('Tanggal_save', [DB::raw('CURRENT_DATE'), DB::raw('CURRENT_DATE - INTERVAL \'1 day\'')])
            ->first();

        // Default to 0 if no result is found
        $valueGap = $result->value_gap ?? 0;

        // Define rate based on conditions for `Jam_save`
        $rate = 0;

        // Calculate based on the range
        if (now()->format('H:i:s') >= '17:59:59' && now()->format('H:i:s') <= '21:59:59') {
            $rate = 1035.78;
        } elseif (now()->format('H:i:s') >= '21:59:59') {
            $rate = 1553.67;
        }

        // Calculate the result in Rupiah
        $valueInRupiah = $valueGap * $rate;

        return response()->json([
            'value_gap' => $valueGap,
            'rate' => $rate,
            'value_in_rupiah' => $valueInRupiah
        ], 200, [], JSON_PRETTY_PRINT);
    }


    public function getCostConsumptionEnergy()
    {
        // Get yesterday's date
        $yesterday = now()->subDay()->toDateString();
        $twoDaysAgo = now()->subDays(2)->toDateString();

        // Step 1: Get the previous day's end value for the 23:59:59 record
        $previousDayEnd = DB::table('cubical')
            ->where('Tanggal_save', $twoDaysAgo)
            ->where('Jam_save', '23:59:59')
            ->value('Total_active_Energy');

        // Step 2: Fetch energy data for yesterday and add previous energy
        $energyData = DB::table('cubical')
            ->where('Tanggal_save', $yesterday)
            ->orderBy('Jam_save')
            ->get(['Tanggal_save', 'Jam_save', 'Total_active_Energy']);

        // Step 3: Calculate the gap values
        $gapValues = [];
        $previousEnergy = $previousDayEnd;

        foreach ($energyData as $row) {
            $gapValue = $row->Total_active_Energy - $previousEnergy;
            $gapValues[] = [
                'Tanggal_save' => $row->Tanggal_save,
                'Jam_save' => $row->Jam_save,
                'gap_value' => $gapValue,
            ];
            $previousEnergy = $row->Total_active_Energy;
        }

        // Step 4: Aggregate WBP and LWBP values and calculate costs
        $WBP_value = 0;
        $LWBP_value = 0;

        foreach ($gapValues as $data) {
            if ($data['Jam_save'] >= '18:59:59' && $data['Jam_save'] <= '22:59:59') {
                $WBP_value += $data['gap_value'];
            } else {
                $LWBP_value += $data['gap_value'];
            }
        }

        // Step 5: Calculate the costs
        $cost_wbp_value = $WBP_value * 1553.67;
        $cost_lwbp_value = $LWBP_value * 1035.78;

        // Prepare the response
        $result = [
            'Tanggal_save' => $yesterday,
            'WBP_value' => $WBP_value,
            'LWBP_value' => $LWBP_value,
            'cost_wbp_value' => $cost_wbp_value,
            'cost_lwbp_value' => $cost_lwbp_value,
        ];

        // Return the result as JSON
        return response()->json($result);
    }
}
