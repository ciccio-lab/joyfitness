<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\CoachAuthController;
use Illuminate\Support\Facades\Route;

// Area Allievi (Pubblica)
Route::get('/', [BookingController::class, 'index'])->name('home');
Route::get('/coach/{coach:slug}', [BookingController::class, 'show'])->name('calendar');
Route::post('/coach/{coach:slug}/book', [BookingController::class, 'store'])->name('book');

// Login / Logout Coach
Route::get('/login-coach', [CoachAuthController::class, 'showLoginForm'])->name('coach.login');
Route::post('/login-coach', [CoachAuthController::class, 'login'])->name('coach.login.post');
Route::post('/logout-coach', [CoachAuthController::class, 'logout'])->name('coach.logout');

// Area Riservata Coach (Protetta da Middleware)
Route::middleware('coach.auth')->group(function () {
    Route::get('/coach/{coach:slug}/dashboard', [CoachController::class, 'dashboard'])->name('coach.dashboard');
    Route::post('/coach/{coach:slug}/toggle-slot', [CoachController::class, 'toggleSlot'])->name('coach.toggleSlot');
});