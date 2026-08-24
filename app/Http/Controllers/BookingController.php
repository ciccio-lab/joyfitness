<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index()
    {
        $coaches = Coach::all();
        return view('welcome', compact('coaches'));
    }

    public function show(Coach $coach, Request $request)
    {
        $selectedDate = $request->has('date') ? Carbon::parse($request->date) : Carbon::today();

        $days = [];
        for ($i = 0; $i < 14; $i++) {
            $days[] = Carbon::today()->addDays($i);
        }

        $bookings = Booking::where('coach_id', $coach->id)
            ->whereDate('booking_date', $selectedDate)
            ->get();

        $endHour = $selectedDate->isWeekend() ? 19 : 23;

        $slots = [];
        $startTime = Carbon::createFromTime(8, 0);
        $endTime = Carbon::createFromTime($endHour, 0);

        while ($startTime < $endTime) {
            $formattedTime = $startTime->format('H:i');
            
            // Filtra le prenotazioni per questo specifico orario
            $slotBookings = $bookings->filter(fn($b) => Carbon::parse($b->start_time)->format('H:i') === $formattedTime);

            $slots[] = [
                'time' => $formattedTime,
                'bookings' => $slotBookings,
                'count' => $slotBookings->count(),
                'is_full' => $slotBookings->count() >= 2,
            ];

            $startTime->addHour();
        }

        return view('calendar', compact('coach', 'selectedDate', 'days', 'slots'));
    }

    public function store(Request $request, Coach $coach)
    {
        $request->validate([
            'booking_date' => 'required|date',
            'start_time' => 'required',
            'client_name' => 'required|string|max:255',
        ]);

        // Conta quante persone sono già prenotate in quello slot
        $existingCount = Booking::where('coach_id', $coach->id)
            ->whereDate('booking_date', $request->booking_date)
            ->where('start_time', $request->start_time)
            ->count();

        if ($existingCount >= 2) {
            return back()->with('error', 'Questo orario ha già raggiunto il massimo di 2 partecipanti.');
        }

        Booking::create([
            'coach_id' => $coach->id,
            'booking_date' => $request->booking_date,
            'start_time' => $request->start_time,
            'end_time' => Carbon::parse($request->start_time)->addHour()->format('H:i:s'),
            'client_name' => $request->client_name,
        ]);

        return back()->with('success', 'Prenotazione effettuata con successo!');
    }
}