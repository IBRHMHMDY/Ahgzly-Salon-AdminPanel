<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use Illuminate\Support\Facades\Route;

/*****************Public Routes**********************/
// Routes: Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// Routes: Catalog
Route::prefix('catalog')->group(function () {
    Route::get('/branches', [CatalogController::class, 'branches']);
    Route::get('/services', [CatalogController::class, 'services']);
    Route::get('/staff', [CatalogController::class, 'staff']);
});

/*****************Protected Routes**********************/
Route::middleware('auth:sanctum')->group(function () {
    // Routes: Appointments
    Route::get('/appointments/slots', [AppointmentController::class, 'getAvailableSlots']);
    Route::post('/appointments/create', [AppointmentController::class, 'store']);
    Route::get('/appointments/my-appointments', [AppointmentController::class, 'myAppointments']);
    Route::post('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus']);
    // Route: Customer Profile
    Route::get('/profile', [AuthController::class, 'profile']);
    // Route: Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});
