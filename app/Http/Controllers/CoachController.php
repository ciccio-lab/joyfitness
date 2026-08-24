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

        $days = [];
        for ($i = 0; $i < 14; $i++) {
            $days[] = Carbon::today()->addDays($i);
        }

        $bookings = Booking::where('coach_id', $coach->id)
            ->whereDate('booking_date', $selectedDate)
            ->orderBy('start_time')
            ->get();

        $endHour = $selectedDate->isWeekend() ? 19 : 23;

        // Recuperiamo gli slot bloccati per questa data
        $blockedTimes = []; 
        if (class_exists(\App\Models\BlockedSlot::class)) {
            $blockedTimes = BlockedSlot::where('coach_id', $coach->id)
                ->whereDate('date', $selectedDate)
                ->pluck('start_time')
                ->toArray();
        }

        $slots = [];
        $startTime = Carbon::createFromTime(8, 0);
        $endTime = Carbon::createFromTime($endHour, 0);

        while ($startTime < $endTime) {
            $formattedTime = $startTime->format('H:i');
            
            // Di default è disponibile (is_blocked = false), a meno che non sia in blockedTimes
            $slots[] = [
                'time' => $formattedTime,
                'is_blocked' => in_array($formattedTime, $blockedTimes),
            ];

            $startTime->addHour();
        }

        return view('coach_dashboard', compact('coach', 'selectedDate', 'days', 'bookings', 'slots'));
    }

    public function toggleSlot(Coach $coach, Request $request)
    {
        $date = $request->input('date');
        $startTime = $request->input('start_time');

        if (class_exists(\App\Models\BlockedSlot::class)) {
            $existing = BlockedSlot::where('coach_id', $coach->id)
                ->whereDate('date', $date)
                ->where('start_time', $startTime)
                ->first();

            if ($existing) {
                // Se era bloccato, lo sblocchiamo (torna disponibile)
                $existing->delete();
                $message = 'Slot reso nuovamente DISPONIBILE!';
            } else {
                // Se era disponibile, lo blocchiamo
                BlockedSlot::create([
                    'coach_id' => $coach->id,
                    'date' => $date,
                    'start_time' => $startTime,
                ]);

                // Eliminiamo eventuali prenotazioni esistenti in questo slot per questa data
                Booking::where('coach_id', $coach->id)
                    ->whereDate('booking_date', $date)
                    ->where('start_time', 'LIKE', $startTime . '%')
                    ->delete();

                $message = 'Slot impostato su NON DISPONIBILE (prenotazioni rimosse).';
            }
        } else {
            $message = 'Stato aggiornato!';
        }

        return back()->with('success', $message);
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