<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ShipmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// REST API Publik (Slide 12: Authentication)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// REST API Terproteksi Token Sanctum (Slide 12 & 13)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Shipments Endpoints
    Route::get('/shipments', [ShipmentController::class, 'index']);
    Route::post('/shipments', [ShipmentController::class, 'store']);
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'show']);
    Route::patch('/shipments/{shipment}/status', [ShipmentController::class, 'updateStatus']);

    // Carriers & Locations Endpoints
    Route::get('/carriers', [CarrierController::class, 'index']);
    Route::get('/locations', [LocationController::class, 'index']);
    
    Route::middleware('role:admin')->group(function () {
        Route::post('/carriers', [CarrierController::class, 'store']);
        Route::post('/locations', [LocationController::class, 'store']);
    });
});