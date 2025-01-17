<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\lvmdpController;
use App\Http\Controllers\Api\VPSController;
use App\Http\Controllers\Api\DeyeController;

Route::controller(VPSController::class)->group(function () {
    Route::get('/getvalcost', 'getCostConsumptionEnergy');
    Route::get('/getvaltoday', 'getTodayEnergyData');
    Route::get('/getconsday', 'getConsDaily');
    Route::get('/getconsdaily', 'getDailyEnergyConsumption');
    Route::get('/getdaily', 'getDailyCons');
    Route::get('/getdayebeam', 'getTodayEbeam');
    Route::get('/getdayeto', 'getTodayEto');
    Route::get('/hvacone', 'getTodayHVAC1');
    Route::get('/hvactwo', 'getTodayHVAC2');
    Route::get('/hvacthree', 'getTodayHVAC3');

});

Route::controller(DeyeController::class)->group(function () {
    Route::get('/inverter-devices', 'getInverterDevices');
});


// ====== TRIAL EVENT =======
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

