<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Models\DummyLvmdp;
use App\Models\Cubical;
use App\Models\Eto;
use App\Models\Ebeam;
use App\Models\HVAC1_chiller_pump;
use App\Models\HVAC1_chiller;


class VPSController extends Controller
{

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


// New Version
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


    public function getTodayEnergyData() {
        try {
            // Step 1: Fetch the previous day's last energy value
            $previousEnergyEnd = DB::table('cubical')
                ->where('Tanggal_save', Carbon::now()->subDay()->toDateString())
                ->orderBy('Jam_save', 'desc')
                ->value('Total_active_Energy');

            // Default to 0 if no previous energy value is found
            $previousEnergyEnd = $previousEnergyEnd ?? 0;

            // Step 2: Fetch today's energy data with the previous energy calculated
            $energyData = DB::table('cubical')
                ->select(
                    'Tanggal_save',
                    'Jam_save',
                    'Total_active_Energy',
                    DB::raw("COALESCE(LAG(\"Total_active_Energy\") OVER (ORDER BY \"Jam_save\"), $previousEnergyEnd) AS previous_energy"),
                    DB::raw("\"Total_active_Energy\" - COALESCE(LAG(\"Total_active_Energy\") OVER (ORDER BY \"Jam_save\"), $previousEnergyEnd) AS gap_value")
                )
                ->where('Tanggal_save', Carbon::now()->toDateString())
                ->get();

            // Step 3: Calculate total gap value and total cost value
            $totalGapValue = 0;
            $totalCostValue = 0;

            foreach ($energyData as $row) {
                $gapValue = $row->gap_value;

                $totalGapValue += $gapValue;

                // Calculate cost based on time
                if ($row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59') {
                    $totalCostValue += $gapValue * 1553.67; // Peak hours rate
                } else {
                    $totalCostValue += $gapValue * 1035.78; // Off-peak rate
                }
            }

            // Round the total cost value
            $totalCostValue = round($totalCostValue);

            // Step 4: Return the aggregated result as JSON
            return response()->json([
                'total_gap_value' => $totalGapValue,
                'total_cost_value' => $totalCostValue,
            ], 200, [], JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            // Handle exceptions and return error response
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


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


    public function getTodayEbeam() {
        try {
            // Step 1: Fetch the previous day's last energy value
            $previousEnergyEnd = DB::table('ebeam')
                ->where('Tanggal_save', Carbon::now()->subDay()->toDateString())
                ->orderBy('Jam_save', 'desc')
                ->value('Total_active_Energy');

            // Default to 0 if no previous energy value is found
            $previousEnergyEnd = $previousEnergyEnd ?? 0;

            // Step 2: Fetch today's energy data with the previous energy calculated
            $energyData = DB::table('ebeam')
                ->select(
                    'Tanggal_save',
                    'Jam_save',
                    'Total_active_Energy',
                    DB::raw("COALESCE(LAG(\"Total_active_Energy\") OVER (ORDER BY \"Jam_save\"), $previousEnergyEnd) AS previous_energy"),
                    DB::raw("\"Total_active_Energy\" - COALESCE(LAG(\"Total_active_Energy\") OVER (ORDER BY \"Jam_save\"), $previousEnergyEnd) AS gap_value")
                )
                ->where('Tanggal_save', Carbon::now()->toDateString())
                ->get();

            // Step 3: Calculate total gap value and total cost value
            $totalGapValue = 0;
            $totalCostValue = 0;

            foreach ($energyData as $row) {
                $gapValue = $row->gap_value;

                $totalGapValue += $gapValue;

                // Calculate cost based on time
                if ($row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59') {
                    $totalCostValue += $gapValue * 1553.67; // Peak hours rate
                } else {
                    $totalCostValue += $gapValue * 1035.78; // Off-peak rate
                }
            }

            // Round the total cost value
            $totalCostValue = round($totalCostValue);

            // Step 4: Return the aggregated result as JSON
            return response()->json([
                'total_cons_ebeam' => $totalGapValue,
                'total_cost_ebeam' => $totalCostValue,
            ], 200, [], JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            // Handle exceptions and return error response
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function getTodayEto() {
        try {
            // Step 1: Fetch the previous day's last energy value
            $previousEnergyEnd = DB::table('eto')
                ->where('Tanggal_save', Carbon::now()->subDay()->toDateString())
                ->orderBy('Jam_save', 'desc')
                ->value('Total_active_Energy');

            // Default to 0 if no previous energy value is found
            $previousEnergyEnd = $previousEnergyEnd ?? 0;

            // Step 2: Fetch today's energy data with the previous energy calculated
            $energyData = DB::table('eto')
                ->select(
                    'Tanggal_save',
                    'Jam_save',
                    'Total_active_Energy',
                    DB::raw("COALESCE(LAG(\"Total_active_Energy\") OVER (ORDER BY \"Jam_save\"), $previousEnergyEnd) AS previous_energy"),
                    DB::raw("\"Total_active_Energy\" - COALESCE(LAG(\"Total_active_Energy\") OVER (ORDER BY \"Jam_save\"), $previousEnergyEnd) AS gap_value")
                )
                ->where('Tanggal_save', Carbon::now()->toDateString())
                ->get();

            // Step 3: Calculate total gap value and total cost value
            $totalGapValue = 0;
            $totalCostValue = 0;

            foreach ($energyData as $row) {
                $gapValue = $row->gap_value;

                $totalGapValue += $gapValue;

                // Calculate cost based on time
                if ($row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59') {
                    $totalCostValue += $gapValue * 1553.67; // Peak hours rate
                } else {
                    $totalCostValue += $gapValue * 1035.78; // Off-peak rate
                }
            }

            // Round the total cost value
            $totalCostValue = round($totalCostValue);

            // Step 4: Return the aggregated result as JSON
            return response()->json([
                'total_cons_eto' => $totalGapValue,
                'total_cost_eto' => $totalCostValue,
            ], 200, [], JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            // Handle exceptions and return error response
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // public function getTodayHVAC1() {
    //     $currentDate = now()->toDateString();
    //     $yesterday = now()->subDay()->toDateString();

    //     // Define an array of tables
    //     $tables = [
    //         'hvac1_chiller',
    //         'hvac1_chiller_pump',
    //         'hvac1_fan_ahu1',
    //         'hvac1_fan_ahu2',
    //         'hvac1_fan_ahu3',
    //         'hvac1_heater_ahu1',
    //         'hvac1_heater_ahu2',
    //         'hvac1_heater_ahu3',
    //     ];

    //     $combinedData = collect();

    //     foreach ($tables as $table) {
    //         $data = DB::table($table)
    //             ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
    //             ->where('Tanggal_save', $currentDate)
    //             ->get();

    //         $combinedData = $combinedData->merge($data);
    //     }

    //     // Process combined data
    //     $result = $combinedData
    //         ->groupBy('Tanggal_save')
    //         ->map(function ($group) {
    //             $totalGapValue = $group->sum(function ($row) {
    //                 return $row->Total_active_Energy - $row->previous_energy;
    //             });

    //             $totalCostValue = $group->sum(function ($row) {
    //                 $gap = $row->Total_active_Energy - $row->previous_energy;
    //                 return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
    //                     ? $gap * 1553.67
    //                     : $gap * 1035.78;
    //             });

    //             return [
    //                 'total_gap_value' => round($totalGapValue),
    //                 'total_cost_value' => round($totalCostValue, 2), // Rounded to 2 decimal places
    //             ];
    //         });

    //     return response()->json($result);
    // }

    // public function getTodayHVAC2() {
    //     $currentDate = now()->toDateString();
    //     $yesterday = now()->subDay()->toDateString();

    //     // Define an array of tables
    //     $tables = [
    //         'hvac2_chiller',
    //         'hvac2_chiller_pump',
    //         'hvac2_fan_ahu_ivd',
    //     ];

    //     $combinedData = collect();

    //     foreach ($tables as $table) {
    //         $data = DB::table($table)
    //             ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
    //             ->where('Tanggal_save', $currentDate)
    //             ->get();

    //         $combinedData = $combinedData->merge($data);
    //     }

    //     // Process combined data
    //     $result = $combinedData
    //         ->groupBy('Tanggal_save')
    //         ->map(function ($group) {
    //             $totalGapValue = $group->sum(function ($row) {
    //                 return $row->Total_active_Energy - $row->previous_energy;
    //             });

    //             $totalCostValue = $group->sum(function ($row) {
    //                 $gap = $row->Total_active_Energy - $row->previous_energy;
    //                 return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
    //                     ? $gap * 1553.67
    //                     : $gap * 1035.78;
    //             });

    //             return [
    //                 'total_gap_value' => round($totalGapValue),
    //                 'total_cost_value' => round($totalCostValue, 2), // Rounded to 2 decimal places
    //             ];
    //         });

    //     return response()->json($result);
    // }

    // public function getTodayHVAC3() {
    //     $currentDate = now()->toDateString();
    //     $yesterday = now()->subDay()->toDateString();

    //     // Define an array of tables
    //     $tables = [
    //         'hvac3_chiller',
    //         'hvac3_chiller_pump',
    //         'hvac3_fan_ahu1',
    //         'hvac3_fan_ahu2',
    //         'hvac3_fan_ahu3',
    //         'hvac3_heater_ahu1',
    //         'hvac3_heater_ahu2',
    //         'hvac3_heater_ahu3',
    //     ];

    //     $combinedData = collect();

    //     foreach ($tables as $table) {
    //         $data = DB::table($table)
    //             ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
    //             ->where('Tanggal_save', $currentDate)
    //             ->get();

    //         $combinedData = $combinedData->merge($data);
    //     }

    //     // Process combined data
    //     $result = $combinedData
    //         ->groupBy('Tanggal_save')
    //         ->map(function ($group) {
    //             $totalGapValue = $group->sum(function ($row) {
    //                 return $row->Total_active_Energy - $row->previous_energy;
    //             });

    //             $totalCostValue = $group->sum(function ($row) {
    //                 $gap = $row->Total_active_Energy - $row->previous_energy;
    //                 return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
    //                     ? $gap * 1553.67
    //                     : $gap * 1035.78;
    //             });

    //             return [
    //                 'total_gap_value' => round($totalGapValue),
    //                 'total_cost_value' => round($totalCostValue, 2), // Rounded to 2 decimal places
    //             ];
    //         });

    //     return response()->json($result);
    // }


}
