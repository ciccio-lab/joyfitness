<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\Booking;
use App\Models\BlockedSlot;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Metodo index di fallback per evitare errori di routing.
     */
    public function index()
    {
        return redirect()->route('home');
    }

    /**
     * Mostra il calendario e la disponibilità degli slot per l'allievo.
     */
    public function show($slug, Request $request)
    {
        // Cerca il coach tramite lo slug o restituisce 404
        $coach = Coach::where('slug', $slug)->first();
        
        if (!$coach) {
            // Se per caso viene passato un ID numerico anziché lo slug
            $coach = Coach::findOrFail($slug);
        }

        $selectedDate = $request->has('date') ? Carbon::parse($request->date) : Carbon::today();

        $days = [];
        for ($i = 0; $i < 14; $i++) {
            $days[] = Carbon::today()->addDays($i);
        }

        $endHour = $selectedDate->isWeekend() ? 19 : 23;

        // Recuperiamo i blocchi impostati dal coach per questa data
        $blockedTimes = [];
        if (class_exists(BlockedSlot::class)) {
            $blockedTimes = BlockedSlot::where('coach_id', $coach->id)
                ->whereDate('date', $selectedDate)
                ->pluck('start_time')
                ->toArray();
        }

        // Recuperiamo le prenotazioni esistenti per questa data
        $bookings = Booking::where('coach_id', $coach->id)
            ->whereDate('booking_date', $selectedDate)
            ->get();

        $slots = [];
        $startTime = Carbon::createFromTime(8, 0);
        $endTime = Carbon::createFromTime($endHour, 0);

        while ($startTime < $endTime) {
            $formattedTime = $startTime->format('H:i');
            
            // Verifichiamo se l'orario è bloccato dal coach
            $isBlocked = in_array($formattedTime, $blockedTimes);

            // Filtriamo le prenotazioni per questo specifico orario
            $slotBookings = $bookings->filter(function($b) use ($formattedTime) {
                return Carbon::parse($b->start_time)->format('H:i') === $formattedTime;
            });

            $count = $slotBookings->count();

            $slots[] = [
                'time' => $formattedTime,
                'is_blocked' => $isBlocked,
                'count' => $count,
                'is_full' => $isBlocked || $count >= 2, // Se bloccato o pieno (2 posti)
                'bookings' => $slotBookings,
            ];

            $startTime->addHour();
        }

        return view('calendar', compact('coach', 'selectedDate', 'days', 'slots'));
    }

    /**
     * Gestisce la prenotazione effettuata dall'allievo.
     */
    public function store(Request $request, $slug)
    {
        $coach = Coach::where('slug', $slug)->first();
        if (!$coach) {
            $coach = Coach::findOrFail($slug);
        }
        
        $date = $request->input('booking_date');
        $startTime = $request->input('start_time');

        // Controlliamo se il coach ha bloccato questo slot nel frattempo
        $isBlocked = false;
        if (class_exists(BlockedSlot::class)) {
            $isBlocked = BlockedSlot::where('coach_id', $coach->id)
                ->whereDate('date', $date)
                ->where('start_time', $startTime)
                ->exists();
        }

        if ($isBlocked) {
            return back()->with('error', 'Questo orario è stato chiuso dal coach e non è più disponibile.');
        }

        // Controllo limite massimo 2 posti
        $existingCount = Booking::where('coach_id', $coach->id)
            ->whereDate('booking_date', $date)
            ->where('start_time', 'LIKE', $startTime . '%')
            ->count();

        if ($existingCount >= 2) {
            return back()->with('error', 'Spiacenti, questo slot ha raggiunto il limite massimo di 2 posti.');
        }

        Booking::create([
            'coach_id' => $coach->id,
            'booking_date' => $date,
            'start_time' => $startTime,
            'client_name' => $request->input('client_name'),
        ]);

        return back()->with('success', 'Prenotazione effettuata con successo!');
    }
}