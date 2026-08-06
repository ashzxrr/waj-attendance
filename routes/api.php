<?php

use App\Http\Controllers\AttendanceApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FaceRegistrationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
Route::get('/profile', [ProfileController::class, 'profile'])->middleware('auth:sanctum');
Route::post('/profile/change-pin', [ProfileController::class, 'changePin'])->middleware('auth:sanctum');
Route::post('/check-device', [AuthController::class, 'checkDeviceBinding']);

// Face registration — called via fetch() with Authorization: Bearer <token>
Route::post('/face-registration', [FaceRegistrationController::class, 'store'])->middleware('auth:sanctum');

// Attendance — protected by Sanctum token
Route::middleware('auth:sanctum')->prefix('attendance')->group(function () {
    Route::get('/reference-descriptor', [AttendanceApiController::class, 'getReferenceDescriptor']);
    Route::get('/office-location', [AttendanceApiController::class, 'getOfficeLocation']);
    Route::get('/next-type', [AttendanceApiController::class, 'determineNextType']);
    Route::post('/store', [AttendanceApiController::class, 'store']);
    Route::get('/history', [AttendanceApiController::class, 'history']);
});