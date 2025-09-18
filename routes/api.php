<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// // Protected routes
Route::middleware(['auth:sanctum', 'throttle:custom_api'])->group(function () {

    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Blog routes
    Route::post('/blogs', [BlogController::class, 'store']);
    Route::get('/blogs', [BlogController::class, 'index']);
    Route::get('/blogs/{id}', [BlogController::class, 'show']);
    Route::put('/blogs/{id}', [BlogController::class, 'update']);
    Route::delete('/blogs/{id}', [BlogController::class, 'destroy']);
    Route::post('/blogs/{id}/like', [BlogController::class, 'toggleLike']);
    
    // Get user info
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user()->load(['blogs', 'likes'])
        ]);
    });
});