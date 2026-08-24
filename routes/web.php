<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CoachAuthController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\BookingController;
use App\Models\Coach;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home page: reindirizza o mostra i coach disponibili
Route::get('/', function () {
    $coaches = Coach::all();
    return view('home', compact('coaches'));
})->name('home');

// Rotta index di fallback per evitare errori se richiamata
Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');

// Autenticazione Coach
Route::get('/coach/login', [CoachAuthController::class, 'showLoginForm'])->name('coach.login');
Route::post('/coach/login', [CoachAuthController::class, 'login']);
Route::post('/coach/logout', [CoachAuthController::class, 'logout'])->name('coach.logout');

// Area Riservata Coach (Protetto da autenticazione)
Route::middleware(['auth:coach'])->prefix('coach')->name('coach.')->group(function () {
    Route::get('/{coach}/dashboard', [CoachController::class, 'dashboard'])->name('dashboard');
    Route::post('/{coach}/toggle-slot', [CoachController::class, 'toggleSlot'])->name('toggleSlot');
    Route::delete('/bookings/{id}/cancel', [CoachController::class, 'cancelBooking'])->name('bookings.cancel');
});

// Vista Calendario e Prenotazioni per gli Allievi (Pubblico)
Route::get('/coach/{coach}', [BookingController::class, 'show'])->name('calendar');
Route::post('/coach/{coach}/book', [BookingController::class, 'store'])->name('book');