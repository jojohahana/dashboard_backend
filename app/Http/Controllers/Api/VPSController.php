<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DummyLvmdp;
use App\Models\Cubical;
use Illuminate\Support\Carbon;


class VPSController extends Controller
{
    // public function getSummaryByDate($date) {

    //     //Fetch sum for specified date
    //     $summary = DummyLvmdp::whereDate('date', $date)
    //             -> sum('ttl_active_energy');

    //     return response()->json([
    //         'date' => $date,
    //         'ttl_activenergy_sum' => $summary
    //     ]);
    // }

    // public function getValueGapCons() {
    //     $result = DB::table('cubical')
    //             ->selectRaw("
    //             MAX(CASE WHEN \"Jam_save\" = '00:59:59' AND \"Tanggal_save\" = CURRENT_DATE THEN \"Total_active_Energy\" END) -
    //             MIN(CASE WHEN \"Jam_save\" = '23:59:59' AND \"Tanggal_save\" = CURRENT_DATE - INTERVAL '1 day' THEN \"Total_active_Energy\" END) AS value_gap")
    //             ->whereIn('Tanggal_save', [DB::raw('CURRENT_DATE'), DB::raw('CURRENT_DATE - INTERVAL \'1 Day\'')])
    //             ->first();

    //     return response() ->json([
    //         'value_gap' => $result->value_gap ?? 0
    //     ]);
    // }

    // public function getValueGapInRupiah() {
    //     // Raw query to get the value_gap for both conditions
    //     $result = DB::table('cubical')
    //         ->selectRaw("
    //             MAX(CASE WHEN \"Jam_save\" = '08:59:59' AND \"Tanggal_save\" = CURRENT_DATE THEN \"Total_active_Energy\" END) -
    //             MIN(CASE WHEN \"Jam_save\" = '20:59:59' AND \"Tanggal_save\" = CURRENT_DATE - INTERVAL '1 day' THEN \"Total_active_Energy\" END) AS value_gap
    //         ")
    //         ->whereIn('Tanggal_save', [DB::raw('CURRENT_DATE'), DB::raw('CURRENT_DATE - INTERVAL \'1 day\'')])
    //         ->first();

    //     // Default to 0 if no result is found
    //     $valueGap = $result->value_gap ?? 0;

    //     // Define rate based on conditions for `Jam_save`
    //     $rate = 0;

    //     // Calculate based on the range
    //     if (now()->format('H:i:s') >= '17:59:59' && now()->format('H:i:s') <= '21:59:59') {
    //         $rate = 1035.78;
    //     } elseif (now()->format('H:i:s') >= '21:59:59') {
    //         $rate = 1553.67;
    //     }

    //     // Calculate the result in Rupiah
    //     $valueInRupiah = $valueGap * $rate;

    //     return response()->json([
    //         'value_gap' => $valueGap,
    //         'rate' => $rate,
    //         'value_in_rupiah' => $valueInRupiah
    //     ], 200, [], JSON_PRETTY_PRINT);
    // }


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

    public function getCostRupiah() {
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

        // Define rates
        $WBP_rate = 1553.67;
        $LWBP_rate = 1035.78;

        // Calculate the costs
        $cost_wbp_value = $WBP_value * $WBP_rate;
        $cost_lwbp_value = $LWBP_value * $LWBP_rate;
        $total_value_in_rupiah = $cost_wbp_value + $cost_lwbp_value;
        $total_value = $WBP_value + $LWBP_value;

        // Prepare the response
        return response()->json([
            'WBP_value' => $WBP_value,
            'LWBP_value' => $LWBP_value,
            'total_value' => $total_value,
            'cost_wbp_value' => $cost_wbp_value,
            'cost_lwbp_value' => $cost_lwbp_value,
            'total_value_in_rupiah' => $total_value_in_rupiah,
        ], 200, [], JSON_PRETTY_PRINT);
    }


    public function getConsDaily() {
        $results = DB::table('cubical')
            ->select(
                'Tanggal_save',
                DB::raw("SUM(CASE WHEN Jam_save BETWEEN '18:59:59' AND '22:59:59' THEN gap_value ELSE 0 END) AS WBP_value"),
                DB::raw("SUM(CASE WHEN Jam_save NOT BETWEEN '18:59:59' AND '22:59:59' THEN gap_value ELSE 0 END) AS LWBP_value"),
                DB::raw("SUM(CASE WHEN Jam_save BETWEEN '18:59:59' AND '22:59:59' THEN gap_value ELSE 0 END) * 1553.67 AS cost_wbp_value"),
                DB::raw("SUM(CASE WHEN Jam_save NOT BETWEEN '18:59:59' AND '22:59:59' THEN gap_value ELSE 0 END) * 1035.78 AS cost_lwbp_value")
            )
            ->whereDate('Tanggal_save', '>=', Carbon::now()->subDays(30)) // get data from the last 30 days
            ->groupBy('Tanggal_save')
            ->get();

        return response()->json($results);
    }

    public function getDailyEnergyConsumption() {
        try {
            // Define rates for WBP and LWBP
            $wbpRate = 1553.67;
            $lwbpRate = 1035.78;

            // Step 1: Fetch previous day's last recorded energy for '23:59:59'
            $previousDayEndEnergy = DB::table('cubical')
                ->where('Tanggal_save', Carbon::now()->subDays(2)->toDateString())
                ->where('Jam_save', '23:59:59')
                ->value('Total_active_Energy');

            if ($previousDayEndEnergy === null) {
                $previousDayEndEnergy = 0; // Set a default value if no previous energy is found
            }

            // Step 2: Prepare subquery to calculate the previous energy and gap_value
            $gapData = DB::table('cubical')
                ->select(
                    'Tanggal_save',
                    'Jam_save',
                    'Total_active_Energy',
                    DB::raw("LAG(\"Total_active_Energy\", 1, $previousDayEndEnergy) OVER (ORDER BY \"Tanggal_save\", \"Jam_save\") AS previous_energy"),
                    DB::raw("\"Total_active_Energy\" - COALESCE(LAG(\"Total_active_Energy\", 1, $previousDayEndEnergy) OVER (ORDER BY \"Tanggal_save\", \"Jam_save\"), $previousDayEndEnergy) AS gap_value")
                )
                ->whereRaw('"Tanggal_save" >= CURRENT_DATE - INTERVAL \'7 day\''); // No need for toBase()

            // Step 3: Aggregate the data
            $data = DB::table(DB::raw("({$gapData->toSql()}) AS gap_calculations"))
                ->mergeBindings($gapData) // Merge bindings from the subquery
                ->select(
                    'Tanggal_save',
                    DB::raw('SUM(CASE WHEN "Jam_save" BETWEEN \'18:59:59\' AND \'22:59:59\' THEN gap_value ELSE 0 END) AS WBP_value'),
                    DB::raw('SUM(CASE WHEN "Jam_save" NOT BETWEEN \'18:59:59\' AND \'22:59:59\' THEN gap_value ELSE 0 END) AS LWBP_value'),
                    DB::raw('SUM(CASE WHEN "Jam_save" BETWEEN \'18:59:59\' AND \'22:59:59\' THEN gap_value ELSE 0 END) * ' . $wbpRate . ' AS cost_wbp_value'),
                    DB::raw('SUM(CASE WHEN "Jam_save" NOT BETWEEN \'18:59:59\' AND \'22:59:59\' THEN gap_value ELSE 0 END) * ' . $lwbpRate . ' AS cost_lwbp_value'),
                    DB::raw('SUM(CASE WHEN "Jam_save" BETWEEN \'18:59:59\' AND \'22:59:59\' THEN gap_value ELSE 0 END) + SUM(CASE WHEN "Jam_save" NOT BETWEEN \'18:59:59\' AND \'22:59:59\' THEN gap_value ELSE 0 END) AS total_value'),
                    DB::raw('SUM(CASE WHEN "Jam_save" BETWEEN \'18:59:59\' AND \'22:59:59\' THEN gap_value ELSE 0 END) * ' . $wbpRate . ' + SUM(CASE WHEN "Jam_save" NOT BETWEEN \'18:59:59\' AND \'22:59:59\' THEN gap_value ELSE 0 END) * ' . $lwbpRate . ' AS total_cost')
                )
                ->groupBy('Tanggal_save')
                ->orderBy('Tanggal_save')
                ->get();

            // Explicitly set the content-type to application/json to avoid wrapping issues
            return response()->json($data, 200, [], JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function getDailyCons()
{
    try {
        // Step 1: Fetch previous day's last recorded energy for '23:59:59' (8 days back)
        $previousDayEndEnergy = DB::table('cubical')
            ->where('Tanggal_save', Carbon::now()->subDays(8)->toDateString())
            ->where('Jam_save', '23:59:59')
            ->value('Total_active_Energy');

        // Fallback if no previous day's energy is found
        $previousDayEndEnergy = $previousDayEndEnergy ?? 0;

        // Step 2: Prepare subquery for energy data with COALESCE for previous energy
        $energyData = DB::table('cubical')
            ->select(
                'Tanggal_save',
                'Jam_save',
                'Total_active_Energy',
                DB::raw("COALESCE(LAG(\"Total_active_Energy\") OVER (ORDER BY \"Tanggal_save\", \"Jam_save\"), $previousDayEndEnergy) AS previous_energy")
            )
            ->whereRaw('"Tanggal_save" >= CURRENT_DATE - INTERVAL \'7 day\'');

        // Step 3: Wrap the energy data to calculate `gap_value`
        $gapValues = DB::table(DB::raw("({$energyData->toSql()}) AS energy_data"))
            ->mergeBindings($energyData)
            ->select(
                'Tanggal_save',
                'Jam_save',
                'Total_active_Energy',
                DB::raw("\"Total_active_Energy\" - previous_energy AS gap_value")
            );

        // Step 4: Aggregate `gap_value` by `Tanggal_save`
        $dailyConsumption = DB::table(DB::raw("({$gapValues->toSql()}) AS gap_values"))
            ->mergeBindings($gapValues)
            ->select(
                'Tanggal_save',
                DB::raw('SUM(gap_value) AS total_gap_value')
            )
            ->groupBy('Tanggal_save')
            ->orderBy('Tanggal_save')
            ->get();

        // Step 5: Return the aggregated data as JSON response
        return response()->json($dailyConsumption, 200, [], JSON_UNESCAPED_SLASHES);
    } catch (\Exception $e) {
        // Handle exceptions and return an error response
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    // public function getDailyCons() {
    //     try {
    //         // Define rates for WBP and LWBP
    //         $wbpRate = 1553.67;
    //         $lwbpRate = 1035.78;

    //         // Step 1: Fetch previous day's last recorded energy for '23:59:59'
    //         $previousDayEndEnergy = DB::table('cubical')
    //             ->where('Tanggal_save', Carbon::now()->subDays(2)->toDateString())
    //             ->where('Jam_save', '23:59:59')
    //             ->value('Total_active_Energy');

    //         if ($previousDayEndEnergy === null) {
    //             $previousDayEndEnergy = 0; // Set a default value if no previous energy is found
    //         }

    //         // Step 2: Prepare subquery to calculate the previous energy and gap_value
    //         $gapData = DB::table('cubical')
    //             ->select(
    //                 'Tanggal_save',
    //                 'Jam_save',
    //                 'Total_active_Energy',
    //                 DB::raw("LAG(\"Total_active_Energy\", 1, $previousDayEndEnergy) OVER (ORDER BY \"Tanggal_save\", \"Jam_save\") AS previous_energy"),
    //                 DB::raw("\"Total_active_Energy\" - COALESCE(LAG(\"Total_active_Energy\", 1, $previousDayEndEnergy) OVER (ORDER BY \"Tanggal_save\", \"Jam_save\"), $previousDayEndEnergy) AS gap_value")
    //             )
    //             ->whereRaw('"Tanggal_save" >= CURRENT_DATE - INTERVAL \'7 day\'');

    //         // Step 3: Aggregate the data and limit the result to only Tanggal_save and total_value
    //         $data = DB::table(DB::raw("({$gapData->toSql()}) AS gap_calculations"))
    //             ->mergeBindings($gapData)
    //             ->select(
    //                 'Tanggal_save',
    //                 DB::raw('SUM(CASE WHEN "Jam_save" BETWEEN \'17:59:59\' AND \'21:59:59\' THEN gap_value ELSE 0 END) + SUM(CASE WHEN "Jam_save" NOT BETWEEN \'17:59:59\' AND \'21:59:59\' THEN gap_value ELSE 0 END) AS total_value')
    //             )
    //             ->groupBy('Tanggal_save')
    //             ->orderBy('Tanggal_save')
    //             ->get();

    //         return response()->json($data, 200, [], JSON_UNESCAPED_SLASHES);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }


    //241118 Check Value Cons Today aneh teruss
    public function getCostRupiahToday() {
        try {
            // Define rates for WBP and LWBP
            $wbpRate = 1553.67;
            $lwbpRate = 1035.78;

            // Step 1: Fetch yesterday's last recorded energy for '23:59:59'
            $previousDayEndEnergy = DB::table('cubical')
                ->where('Tanggal_save', Carbon::now()->subDay()->toDateString())
                ->where('Jam_save', '23:59:59')
                ->value('Total_active_Energy');

            if ($previousDayEndEnergy === null) {
                $previousDayEndEnergy = 0; // Set a default value if no previous energy is found
            }

            // Step 2: Prepare subquery to calculate the previous energy and gap_value for today only
            $gapData = DB::table('cubical')
                ->select(
                    'Tanggal_save',
                    'Jam_save',
                    'Total_active_Energy',
                    DB::raw("LAG(\"Total_active_Energy\", 1, $previousDayEndEnergy) OVER (ORDER BY \"Tanggal_save\", \"Jam_save\") AS previous_energy"),
                    DB::raw("\"Total_active_Energy\" - COALESCE(LAG(\"Total_active_Energy\", 1, $previousDayEndEnergy) OVER (ORDER BY \"Tanggal_save\", \"Jam_save\"), $previousDayEndEnergy) AS gap_value")
                )
                ->where('Tanggal_save', '=', Carbon::now()->toDateString()); // Filter by today's date

            // Step 3: Aggregate the data for today only
            $data = DB::table(DB::raw("({$gapData->toSql()}) AS gap_calculations"))
                ->mergeBindings($gapData)
                ->select(
                    'Tanggal_save',
                    DB::raw('SUM(CASE WHEN "Jam_save" BETWEEN \'17:59:59\' AND \'21:59:59\' THEN gap_value ELSE 0 END) + SUM(CASE WHEN "Jam_save" NOT BETWEEN \'17:59:59\' AND \'21:59:59\' THEN gap_value ELSE 0 END) AS total_value')
                )
                ->groupBy('Tanggal_save')
                ->orderBy('Tanggal_save')
                ->get();

            return response()->json($data, 200, [], JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // public function getCostRupiahToday() {
    //     // Get today's date
    //     $today = now()->toDateString();

    //     // Step 1: Fetch energy data for today
    //     $energyData = DB::table('cubical')
    //         ->where('Tanggal_save', $today)
    //         ->orderBy('Jam_save')
    //         ->get(['Tanggal_save', 'Jam_save', 'Total_active_Energy']);

    //     // Ensure there is data for today
    //     if ($energyData->isEmpty()) {
    //         return response()->json([
    //             'error' => 'No data available for today'
    //         ], 404);
    //     }

    //     // Step 2: Calculate the gap values
    //     $gapValues = [];
    //     $previousEnergy = $energyData->first()->Total_active_Energy;

    //     foreach ($energyData as $row) {
    //         $gapValue = $row->Total_active_Energy - $previousEnergy;
    //         $gapValues[] = [
    //             'Tanggal_save' => $row->Tanggal_save,
    //             'Jam_save' => $row->Jam_save,
    //             'gap_value' => $gapValue,
    //         ];
    //         $previousEnergy = $row->Total_active_Energy;
    //     }

    //     // Step 3: Aggregate WBP and LWBP values and calculate costs
    //     $WBP_value = 0;
    //     $LWBP_value = 0;

    //     foreach ($gapValues as $data) {
    //         if ($data['Jam_save'] >= '18:59:59' && $data['Jam_save'] <= '22:59:59') {
    //             $WBP_value += $data['gap_value'];
    //         } else {
    //             $LWBP_value += $data['gap_value'];
    //         }
    //     }

    //     // Define rates
    //     $WBP_rate = 1553.67;
    //     $LWBP_rate = 1035.78;

    //     // Calculate the costs
    //     $cost_wbp_value = $WBP_value * $WBP_rate;
    //     $cost_lwbp_value = $LWBP_value * $LWBP_rate;
    //     $total_value_in_rupiah = $cost_wbp_value + $cost_lwbp_value;
    //     $total_value = $WBP_value + $LWBP_value;

    //     // Prepare the response
    //     return response()->json([
    //         'WBP_value' => $WBP_value,
    //         'LWBP_value' => $LWBP_value,
    //         'total_value' => $total_value,
    //         'cost_wbp_value' => $cost_wbp_value,
    //         'cost_lwbp_value' => $cost_lwbp_value,
    //         'total_value_in_rupiah' => $total_value_in_rupiah,
    //     ], 200, [], JSON_PRETTY_PRINT);
    // }



    // public function getDailyEnergyConsumption() {
    //     try {
    //         // Define rates for WBP and LWBP
    //         $wbpRate = 1553.67;
    //         $lwbpRate = 1035.78;

    //         // Step 1: Fetch previous day's last recorded energy for '23:59:59'
    //         $previousDayEndEnergy = DB::table('cubical')
    //             ->where('Tanggal_save', Carbon::now()->subDays(2)->toDateString())
    //             ->where('Jam_save', '23:59:59')
    //             ->value('Total_active_Energy');

    //         if ($previousDayEndEnergy === null) {
    //             $previousDayEndEnergy = 0; // Set a default value if no previous energy is found
    //         }

    //         // Step 2: Prepare subquery to calculate the previous energy and gap_value
    //         $gapData = DB::table('cubical')
    //             ->select(
    //                 'Tanggal_save',
    //                 'Jam_save',
    //                 'Total_active_Energy',
    //                 DB::raw("LAG(\"Total_active_Energy\", 1, $previousDayEndEnergy) OVER (ORDER BY \"Tanggal_save\", \"Jam_save\") AS previous_energy"),
    //                 DB::raw("\"Total_active_Energy\" - COALESCE(LAG(\"Total_active_Energy\", 1, $previousDayEndEnergy) OVER (ORDER BY \"Tanggal_save\", \"Jam_save\"), $previousDayEndEnergy) AS gap_value")
    //             )
    //             ->whereRaw('"Tanggal_save" >= CURRENT_DATE - INTERVAL \'7 day\''); // No need for toBase()

    //         // Step 3: Aggregate the data
    //         $data = DB::table(DB::raw("({$gapData->toSql()}) AS gap_calculations"))
    //             ->mergeBindings($gapData) // Merge bindings from the subquery
    //             ->select(
    //                 'Tanggal_save',
    //                 DB::raw('SUM(CASE WHEN "Jam_save" BETWEEN \'18:59:59\' AND \'22:59:59\' THEN gap_value ELSE 0 END) AS WBP_value'),
    //                 DB::raw('SUM(CASE WHEN "Jam_save" NOT BETWEEN \'18:59:59\' AND \'22:59:59\' THEN gap_value ELSE 0 END) AS LWBP_value'),
    //                 DB::raw('SUM(CASE WHEN "Jam_save" BETWEEN \'18:59:59\' AND \'22:59:59\' THEN gap_value ELSE 0 END) * ' . $wbpRate . ' AS cost_wbp_value'),
    //                 DB::raw('SUM(CASE WHEN "Jam_save" NOT BETWEEN \'18:59:59\' AND \'22:59:59\' THEN gap_value ELSE 0 END) * ' . $lwbpRate . ' AS cost_lwbp_value'),
    //                 DB::raw('SUM(CASE WHEN "Jam_save" BETWEEN \'18:59:59\' AND \'22:59:59\' THEN gap_value ELSE 0 END) + SUM(CASE WHEN "Jam_save" NOT BETWEEN \'18:59:59\' AND \'22:59:59\' THEN gap_value ELSE 0 END) AS total_value'),
    //                 DB::raw('SUM(CASE WHEN "Jam_save" BETWEEN \'18:59:59\' AND \'22:59:59\' THEN gap_value ELSE 0 END) * ' . $wbpRate . ' + SUM(CASE WHEN "Jam_save" NOT BETWEEN \'18:59:59\' AND \'22:59:59\' THEN gap_value ELSE 0 END) * ' . $lwbpRate . ' AS total_cost')
    //             )
    //             ->groupBy('Tanggal_save')
    //             ->orderBy('Tanggal_save')
    //             ->get();

    //         return response()->json($data);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }


}
