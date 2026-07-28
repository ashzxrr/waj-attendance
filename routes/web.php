<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FaceRegistrationController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────────
// Token-based SPA-style auth pattern (not Laravel session auth).
// Page routes are publicly routable at the HTTP level — auth is enforced
// client-side via JS checking localStorage for the Sanctum Bearer token.
// Only API routes (routes/api.php) carry 'auth:sanctum' middleware since
// they are called via fetch() with Authorization: Bearer <token> header.
// ─────────────────────────────────────────────────────────────────────────────

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

Route::get('/face-registration', [FaceRegistrationController::class, 'show']);

Route::get('/dashboard', function () {
    return view('dashboard');
});
