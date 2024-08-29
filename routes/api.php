<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\lvmdpController;

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
});
