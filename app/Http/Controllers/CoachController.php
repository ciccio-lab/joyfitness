<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CoachController extends Controller
{
    public function dashboard(Coach $coach, Request $request)
    {
        $selectedDate = $request->has('date') ? Carbon::parse($request->date) : Carbon::today();

        $days = [];
        for ($i = 0; $i < 14; $i++) {
            $days[] = Carbon::today()->addDays($i);
        }

        $bookings = Booking::where('coach_id', $coach->id)
            ->whereDate('booking_date', $selectedDate)
            ->orderBy('start_time')
            ->get();

        $endHour = $selectedDate->isWeekend() ? 19 : 23;

        $slots = [];
        $startTime = Carbon::createFromTime(8, 0);
        $endTime = Carbon::createFromTime($endHour, 0);

        while ($startTime < $endTime) {
            $formattedTime = $startTime->format('H:i');
            
            // Definiamo 'is_blocked' a false per evitare errori nella vista
            $slots[] = [
                'time' => $formattedTime,
                'is_blocked' => false,
            ];

            $startTime->addHour();
        }

        return view('coach_dashboard', compact('coach', 'selectedDate', 'days', 'bookings', 'slots'));
    }

    public function toggleSlot(Coach $coach, Request $request)
    {
        return back()->with('success', 'Stato dello slot aggiornato!');
    }

    public function cancelBooking($id)
    {
        $booking = Booking::find($id);

        if ($booking) {
            $booking->delete();
            return redirect()->back()->with('success', 'Prenotazione annullata con successo!');
        }

        return redirect()->back()->with('error', 'Prenotazione non trovata.');
    }
}