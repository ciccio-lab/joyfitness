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
        // Data selezionata (default oggi)
        $selectedDate = $request->has('date') ? Carbon::parse($request->date) : Carbon::today();

        // Genera i prossimi 14 giorni per la barra orizzontale
        $days = [];
        for ($i = 0; $i < 14; $i++) {
            $days[] = Carbon::today()->addDays($i);
        }

        // Prenotazioni degli allievi per la data selezionata
        $bookings = Booking::where('coach_id', $coach->id)
            ->whereDate('booking_date', $selectedDate)
            ->orderBy('start_time')
            ->get();

        // Recupera gli orari bloccati usando il model BlockedSlot se esiste
        $blockedSlots = [];
        $blockedSlotClass = "\\App\\Models\\BlockedSlot";
        
        if (class_exists($blockedSlotClass)) {
            $blockedSlots = $blockedSlotClass::where('coach_id', $coach->id)
                ->whereDate('blocked_date', $selectedDate)
                ->pluck('start_time')
                ->toArray();
        }

        // Determina l'orario di chiusura in base al giorno della settimana
        // Sabato (6) e Domenica (0) chiudono alle 19:00, dal Lunedì al Venerdì chiudono alle 23:00
        $endHour = $selectedDate->isWeekend() ? 19 : 23;

        // Genera slot orari a scandaglio di un'ora
        $slots = [];
        $startTime = Carbon::createFromTime(8, 0);
        $endTime = Carbon::createFromTime($endHour, 0);

        while ($startTime < $endTime) {
            $formattedTime = $startTime->format('H:i');
            
            // Verifica se lo slot è bloccato
            $isBlocked = in_array($formattedTime . ':00', $blockedSlots) || in_array($formattedTime, $blockedSlots);

            $slots[] = [
                'time' => $formattedTime,
                'is_blocked' => $isBlocked,
            ];

            $startTime->addHour();
        }

        return view('coach_dashboard', compact('coach', 'selectedDate', 'days', 'bookings', 'slots'));
    }

    public function toggleSlot(Coach $coach, Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
        ]);

        $date = $request->date;
        $time = $request->start_time;

        $blockedSlotClass = "\\App\\Models\\BlockedSlot";

        if (class_exists($blockedSlotClass)) {
            $existing = $blockedSlotClass::where('coach_id', $coach->id)
                ->whereDate('blocked_date', $date)
                ->where('start_time', $time)
                ->first();

            if ($existing) {
                $existing->delete();
                $message = "Slot delle {$time} sbloccato!";
            } else {
                $blockedSlotClass::create([
                    'coach_id' => $coach->id,
                    'blocked_date' => $date,
                    'start_time' => $time,
                ]);
                $message = "Slot delle {$time} bloccato!";
            }
        } else {
            $message = "Impossibile aggiornare lo slot: modello BlockedSlot non trovato.";
        }

        return back()->with('success', $message);
    }
}