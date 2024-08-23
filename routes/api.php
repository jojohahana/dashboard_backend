<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(Authenticate::using('sanctum'));

//posts
Route::apiResource('/posts', App\Http\Controllers\Api\PostController::class);

Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

Route::get('/summary', [App\Http\Controllers\Api\lvmdpController::class, 'getSummary']);
