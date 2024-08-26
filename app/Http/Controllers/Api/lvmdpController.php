<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\LvmdpHour;

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
}
