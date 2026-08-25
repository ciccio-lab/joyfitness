<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\Booking;
use App\Models\BlockedSlot;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CoachController extends Controller
{
    public function dashboard(Coach $coach, Request $request)
    {
        $selectedDate = $request->has('date') ? Carbon::parse($request->date) : Carbon::today();

        // Genera i prossimi 14 giorni per la navigazione
        $days = [];
        for ($i = 0; $i < 14; $i++) {
            $days[] = Carbon::today()->addDays($i);
        }

        $endHour = $selectedDate->isWeekend() ? 19 : 23;

        // Recupera gli orari bloccati dal coach per questo giorno
        $blockedTimes = BlockedSlot::where('coach_id', $coach->id)
            ->whereDate('date', $selectedDate)
            ->pluck('start_time')
            ->toArray();

        // Recupera le prenotazioni esistenti per questo giorno
        $bookings = Booking::where('coach_id', $coach->id)
            ->whereDate('booking_date', $selectedDate)
            ->get();

        $slots = [];
        $startTime = Carbon::createFromTime(8, 0);
        $endTime = Carbon::createFromTime($endHour, 0);

        while ($startTime < $endTime) {
            $formattedTime = $startTime->format('H:i');
            $isBlocked = in_array($formattedTime, $blockedTimes);

            $slotBookings = $bookings->filter(function($b) use ($formattedTime) {
                return Carbon::parse($b->start_time)->format('H:i') === $formattedTime;
            });

            $slots[] = [
                'time' => $formattedTime,
                'is_blocked' => $isBlocked,
                'count' => $slotBookings->count(),
                'bookings' => $slotBookings,
            ];

            $startTime->addHour();
        }

        return view('coach_dashboard', compact('coach', 'selectedDate', 'days', 'slots'));
    }

    public function toggleSlot(Coach $coach, Request $request)
    {
        $date = $request->input('date');
        $time = $request->input('start_time');

        $existing = BlockedSlot::where('coach_id', $coach->id)
            ->whereDate('date', $date)
            ->where('start_time', $time)
            ->first();

        if ($existing) {
            $existing->delete();
            $message = "Slot {$time} riaperto con successo.";
        } else {
            BlockedSlot::create([
                'coach_id' => $coach->id,
                'date' => $date,
                'start_time' => $time,
            ]);
            $message = "Slot {$time} bloccato con successo.";
        }

        return back()->with('success', $message);
    }

    public function cancelBooking($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return back()->with('success', 'Prenotazione cancellata.');
    }
}