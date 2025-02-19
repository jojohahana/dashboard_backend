<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class AreaController extends Controller
{
    // HVAC
    public function getTodayHVAC1() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'hvac1_chiller',
            'hvac1_chiller_pump',
            'hvac1_fan_ahu1',
            'hvac1_fan_ahu2',
            'hvac1_fan_ahu3',
            'hvac1_heater_ahu1',
            'hvac1_heater_ahu2',
            'hvac1_heater_ahu3',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    public function getTodayHVAC2() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'hvac2_chiller',
            'hvac2_chiller_pump',
            'hvac2_ahu_heater_ivd',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    public function getTodayHVAC3() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'hvac3_chiller',
            'hvac3_chiller_pump',
            'hvac3_fan_ahu1',
            'hvac3_fan_ahu2',
            'hvac3_fan_ahu3',
            'hvac3_heater_ahu1',
            'hvac3_heater_ahu2',
            'hvac3_heater_ahu3',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    public function getTodayTtlHVAC() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'hvac1_chiller',
            'hvac1_chiller_pump',
            'hvac1_fan_ahu1',
            'hvac1_fan_ahu2',
            'hvac1_fan_ahu3',
            'hvac1_heater_ahu1',
            'hvac1_heater_ahu2',
            'hvac1_heater_ahu3',
            'hvac2_chiller',
            'hvac2_chiller_pump',
            'hvac2_ahu_heater_ivd',
            'hvac3_chiller',
            'hvac3_chiller_pump',
            'hvac3_fan_ahu1',
            'hvac3_fan_ahu2',
            'hvac3_fan_ahu3',
            'hvac3_heater_ahu1',
            'hvac3_heater_ahu2',
            'hvac3_heater_ahu3',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    // INJECTION
    public function getTodayInjection1() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'injection1',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    public function getTodayInjection2() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'injection2',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    public function getTodayInjection3() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'injection3',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    public function getTodayInjection4() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'injection4',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    public function getTodayTtlInjection() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'injection1',
            'injection2',
            'injection3',
            'injection4',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    // COMPRESSOR
    public function getTodayCompressor1() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'compressor1',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    public function getTodayCompressor2() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'compressor2',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    public function getTodayCompressor3() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'compressor3',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    public function getTodayTtlCompressor() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'compressor1',
            'compressor2',
            'compressor3',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    // LVMDP
    public function getTodayLvmdp1() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'lvmdp1',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    public function getTodayLvmdp2() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'lvmdp2',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    public function getTodayTtlLVMDP() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'lvmdp1',
            'lvmdp2',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    //BLOOD BAG
    public function getTodayBb1() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'bb1',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    public function getTodayBb2() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'bb2',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    public function getTodayBB() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'bb1',
            'bb2',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }


    // BOILER, CUBICAL, EBEAM, ETO, NUMEDIK

    public function getTodayBoiler() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'boiler',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    public function getTodayCubical() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'cubical',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    public function getTodayEbeam() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'ebeam',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    public function getTodayEto() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'eto',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }

    public function getTodayNumedik() {
        $currentDate = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Define an array of tables
        $tables = [
            'numedik_hvac',
            'numedik_pp',
        ];

        $combinedData = collect();

        foreach ($tables as $table) {
            $data = DB::table($table)
                ->selectRaw("'$table' as source_table, \"Tanggal_save\", \"Jam_save\", \"Total_active_Energy\", COALESCE(LAG(\"Total_active_Energy\") OVER (PARTITION BY \"Tanggal_save\" ORDER BY \"Jam_save\"), (SELECT \"Total_active_Energy\" FROM $table WHERE \"Tanggal_save\" = ? ORDER BY \"Jam_save\" DESC LIMIT 1)) AS previous_energy", [$yesterday])
                ->where('Tanggal_save', $currentDate)
                ->get();

            $combinedData = $combinedData->merge($data);
        }

        // Process combined data
        $result = $combinedData
            ->groupBy('Tanggal_save')
            ->map(function ($group) {
                $totalGapValue = $group->sum(function ($row) {
                    return $row->Total_active_Energy - $row->previous_energy;
                });

                $totalCostValue = $group->sum(function ($row) {
                    $gap = $row->Total_active_Energy - $row->previous_energy;
                    return $row->Jam_save >= '17:59:59' && $row->Jam_save <= '21:59:59'
                        ? $gap * 1553.67
                        : $gap * 1035.78;
                });

                return [
                    'total_gap_value' => round($totalGapValue),
                    'total_cost_value' => round($totalCostValue, 0), // Rounded to 2 decimal places
                ];
            });

        return response()->json($result);
    }
}
