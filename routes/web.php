<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FaceRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLoginForm']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/face-registration', [FaceRegistrationController::class, 'show']);
    Route::post('/face-registration', [FaceRegistrationController::class, 'store']);

    // Protected pages that require face registration
    Route::middleware('face.registered')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        });
    });
});
