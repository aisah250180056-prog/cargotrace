<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ShipmentController;
use Illuminate\Support\Facades\Route;

// Halaman Utama Publik -> Redirect ke login / tracking publik
Route::get('/', function () {
    return redirect()->route('login');
});

// Autentikasi Web
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Rute yang Dilindungi Autentikasi (Semua Role: Admin, Operator, Customer)
Route::middleware('auth')->group(function () { 
        // Exception Management Routes
    Route::get('/shipments/{shipment}/exceptions', [\App\Http\Controllers\ShipmentExceptionController::class, 'index'])->name('exceptions.index');
    Route::post('/shipments/{shipment}/exceptions', [\App\Http\Controllers\ShipmentExceptionController::class, 'store'])->name('exceptions.store');
    Route::patch('/exceptions/{exception}', [\App\Http\Controllers\ShipmentExceptionController::class, 'update'])->name('exceptions.update');   // Tracking Events Timeline
    Route::get('/shipments/{shipment}/tracking', [\App\Http\Controllers\TrackingEventController::class, 'index'])->name('tracking.index');
    Route::post('/shipments/{shipment}/tracking', [\App\Http\Controllers\TrackingEventController::class, 'store'])->name('tracking.store');
    // 1. Shipment Modules
    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
    Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show');
    Route::patch('/shipments/{shipment}/status', [ShipmentController::class, 'updateStatus'])->name('shipments.updateStatus');

    // 2. Master Data Carriers & Locations (Slide 09 & 11)
    Route::get('/carriers', [CarrierController::class, 'index'])->name('carriers.index');
    Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');

    // Khusus Admin (Role Protection via RoleMiddleware - Slide 05)
    Route::middleware('role:admin')->group(function () {
        Route::post('/carriers', [CarrierController::class, 'store'])->name('carriers.store');
        Route::post('/locations', [LocationController::class, 'store'])->name('locations.store');
    });
});