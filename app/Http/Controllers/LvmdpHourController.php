<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\LvmdpHour;

class LvmdpHourController extends Controller
{
    public function getSummary(Request $request) {
        $year = $request->input('year', 2024);
        $month = $request->input('month', 5);

        $result = DB::table('lvmdp_hours')
            ->select(DB::raw('SUM(ttlappr_en) as total_energy'))
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->first();

        return response()->json([
            'total_energy' => $result ? $result->total_energy : 0
        ]);
    }

}

