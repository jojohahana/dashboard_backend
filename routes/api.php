<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\lvmdpController;
use App\Http\Controllers\Api\VPSController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(Authenticate::using('sanctum'));

//posts
Route::apiResource('/posts', App\Http\Controllers\Api\PostController::class);

Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

Route::controller(lvmdpController::class)->group(function () {
    Route::get('/summary', 'getSummary');
    Route::get('/monthly-summary', 'getMonthlySummary');
    Route::get('/daily-summary', 'getDailySummary');
    Route::get('/shift-summary', 'getShiftSummary');
    // Dummy VPS
    Route::get('/dummy-lvmdp1', 'getDummyVPS');
});

Route::controller(VPSController::class)->group(function () {
    Route::get('/getvalcost', 'getCostConsumptionEnergy');
    Route::get('/gettrial', 'getCostRupiah');
    Route::get('/getconsday', 'getConsDaily');
    Route::get('/getconsdaily', 'getDailyEnergyConsumption');
    Route::get('/getdaily', 'getDailyCons');
    // Route::get('/summary/{date}', 'getSummaryByDate');
    // Route::get('/gapvalcons', 'getValueGapCons');
    // Route::get('/gapvalcost', 'getValueGapInRupiah');
});
