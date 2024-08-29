<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\LvmdpHour;
use App\Model\LvmdpDays;

class lvmdpController extends Controller
{
    public function getSummary(Request $request) {
        $year = $request->input('year', 2024);
        $month = $request->input('month', 5);

        $result = DB::table('lvmdp_hours')
            ->select(DB::raw('SUM(ttlexp_actven) as total_energy'))
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->first();


        // Debugging
        if (is_null($result)) {
            return response()->json()([
                'total_energy' => 0
            ]);
        }

        return response()->json([
            'total_energy' => $result ? $result->total_energy : 0
        ]);
    }

    // Get Summary Monthly
    public function getMonthlySummary(Request $request) {
        $startDate = $request->input('start_date', '2024-01-01'); // default start date
        $endDate = $request->input('end_date', '2024-12-31'); // default end date

        try {
            $results = DB::table('lvmdp_days')
                ->select(
                    DB::raw('DATE_TRUNC(\'month\', tanggal) as month_year'),
                    DB::raw('SUM(ttlexp_actven) as ttlmonth_energy')
                )
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE_TRUNC(\'month\', tanggal)'))
                ->orderBy(DB::raw('DATE_TRUNC(\'month\', tanggal)'))
                ->get();

            return response()->json($results);
        } catch (\Exception $e) {
            // Log the exact error message to Laravel logs
            \Log::error('Error fetching monthly summary:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred while fetching the monthly summary.'], 500);
        }
    }

    // Get Summary Daily from lvmdp_days
    public function getDailySummary(Request $request) {
        $results = DB::table('lvmdp_days')
            ->select(
                'tanggal',
                DB::raw('SUM(ttlexp_actven) as ttl_energy')
            )
            ->groupBy('tanggal')
            ->havingRaw('SUM(ttlexp_actven) != 0')
            ->orderBy('tanggal')
            ->get();

        return response()->json($results);
    }

}
