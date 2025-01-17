<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;

// Public routes
// users are limited to 10 requests per minute (1 minute)
Route::middleware('throttle:10,1')->post('register', [AuthController::class, 'register']);
Route::middleware('throttle:10,1')->post('login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::apiResource('posts', PostController::class);

    // activity log endpoint
    Route::get('activity', [AuthController::class, 'getActivityLogs']);
});

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
