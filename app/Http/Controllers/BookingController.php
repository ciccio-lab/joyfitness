<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\Booking;
use App\Models\BlockedSlot;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class BookingController extends Controller
{
    public function index()
    {
        $coaches = Coach::all();
        return view('welcome', compact('coaches'));
    }

    public function show(Request $request, Coach $coach)
    {
        $dateInput = $request->input('date', Carbon::today()->toDateString());
        $selectedDate = Carbon::parse($dateInput);

        // Genera slot orari (dalle 08:00 alle 22:00 / 19:00 nel weekend)
        $startHour = 8;
        $endHour = $selectedDate->isWeekend() ? 19 : 23;

        // Recupera orari bloccati dal coach (con controllo sicurezza se la tabella non è ancora migrata)
        $blockedTimes = [];
        if (Schema::hasTable('blocked_slots')) {
            $blockedTimes = BlockedSlot::where('coach_id', $coach->id)
                ->whereDate('date', $selectedDate)
                ->pluck('start_time')
                ->toArray();
        }

        // Recupera le prenotazioni già effettuate dagli allievi
        $bookings = Booking::where('coach_id', $coach->id)
            ->whereDate('booking_date', $selectedDate)
            ->get();

        $bookedTimes = $bookings->pluck('booking_time')->toArray();

        return view('bookings.show', compact('coach', 'selectedDate', 'startHour', 'endHour', 'bookedTimes', 'blockedTimes'));
    }

    public function store(Request $request, Coach $coach)
    {
        $request->validate([
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'client_phone' => 'nullable|string|max:20',
            'booking_date' => 'required|date',
            'booking_time' => 'required|string',
        ]);

        // Verifica che lo slot non sia già bloccato dal coach
        if (Schema::hasTable('blocked_slots')) {
            $isBlocked = BlockedSlot::where('coach_id', $coach->id)
                ->whereDate('date', $request->booking_date)
                ->where('start_time', $request->booking_time)
                ->exists();

            if ($isBlocked) {
                return back()->with('error', 'Questo orario è stato disattivato dal coach.');
            }
        }

        // Verifica che lo slot non sia già prenotato
        $exists = Booking::where('coach_id', $coach->id)
            ->whereDate('booking_date', $request->booking_date)
            ->where('booking_time', $request->booking_time)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Questo orario è stato già prenotato.');
        }

        Booking::create([
            'coach_id' => $coach->id,
            'client_name' => $request->client_name,
            'client_email' => $request->client_email,
            'client_phone' => $request->client_phone,
            'booking_date' => $request->booking_date,
            'booking_time' => $request->booking_time,
        ]);

        return back()->with('success', 'Prenotazione effettuata con successo!');
    }
}