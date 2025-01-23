<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\lvmdpController;
use App\Http\Controllers\Api\VPSController;
use App\Http\Controllers\Api\DeyeController;
use App\Http\Controllers\Api\AreaController;

Route::controller(VPSController::class)->group(function () {
    Route::get('/getvalcost', 'getCostConsumptionEnergy');
    Route::get('/getvaltoday', 'getTodayEnergyData');
    Route::get('/getconsday', 'getConsDaily');
    Route::get('/getconsdaily', 'getDailyEnergyConsumption');
    Route::get('/getdaily', 'getDailyCons');
    // Route::get('/getdayebeam', 'getTodayEbeam');
    // Route::get('/getdayeto', 'getTodayEto');


});

Route::controller(AreaController::class)->group(function (){
    Route::get('/hvacone', 'getTodayHVAC1');
    Route::get('/hvactwo', 'getTodayHVAC2');
    Route::get('/hvacthree', 'getTodayHVAC3');
    Route::get('/hvacttl', 'getTodayTtlHVAC');
    Route::get('/injectone', 'getTodayInjection1');
    Route::get('/injecttwo', 'getTodayInjection2');
    Route::get('/injectthree', 'getTodayInjection3');
    Route::get('/injectfour', 'getTodayInjection4');
    Route::get('/injectttl', 'getTodayTtlInjection');
    Route::get('/compresone', 'getTodayCompressor1');
    Route::get('/comprestwo', 'getTodayCompressor2');
    Route::get('/compresthree', 'getTodayCompressor3');
    Route::get('/compressttl', 'getTodayTtlCompressor');
    Route::get('/lvmdpone', 'getTodayLvmdp1');
    Route::get('/lvmdptwo', 'getTodayLvmdp2');
    Route::get('/lvmdpttl', 'getTodayTtlLVMDP');
    Route::get('/boiler', 'getTodayBoiler');
    Route::get('/cubical', 'getTodayCubical');
    Route::get('/ebeam', 'getTodayEbeam');
    Route::get('/eto', 'getTodayEto');
    Route::get('/numedik', 'getTodayNumedik');

});

Route::controller(DeyeController::class)->group(function () {
    Route::post('/device/history', 'getDeviceHistory');
    // Route::get('/inverter-devices', 'getInverterDevices');
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

