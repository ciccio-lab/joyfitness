<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CoachAuthController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\BookingController;
use App\Models\Coach;

Route::get('/', function () {
    $coaches = Coach::all();
    return view('welcome', compact('coaches'));
})->name('home');

Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');

// Rotte Autenticazione Coach
Route::get('/coach/login', [CoachAuthController::class, 'showLoginForm'])->name('coach.login');
Route::post('/coach/login', [CoachAuthController::class, 'login'])->name('coach.login.post');
Route::post('/coach/logout', [CoachAuthController::class, 'logout'])->name('coach.logout');

Route::get('/login', function () {
    return redirect()->route('coach.login');
})->name('login');

// Area Dashboard Coach Autenticata
Route::middleware(['auth:coach'])->prefix('coach')->name('coach.')->group(function () {
    Route::get('/{coach}/dashboard', [CoachController::class, 'dashboard'])->name('dashboard');
    Route::post('/{coach}/toggle-slot', [CoachController::class, 'toggleSlot'])->name('toggleSlot');
    Route::delete('/booking/{id}', [CoachController::class, 'cancelBooking'])->name('cancelBooking');
});

// Vista Calendario Pubblica per gli Allievi
Route::get('/coach/{coach}', [BookingController::class, 'show'])->name('calendar');
Route::post('/coach/{coach}/book', [BookingController::class, 'store'])->name('book');