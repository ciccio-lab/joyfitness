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

    public function show(Request $request, $coachParam)
    {
        $coach = Coach::where('id', $coachParam)
            ->orWhere('slug', $coachParam)
            ->firstOrFail();

        $dateInput = $request->input('date', Carbon::today()->toDateString());
        $selectedDate = Carbon::parse($dateInput);
        $now = Carbon::now();

        $days = [];
        for ($i = 0; $i < 14; $i++) {
            $days[] = Carbon::today()->addDays($i);
        }

        $startHour = 8;
        $endHour = $selectedDate->isWeekend() ? 19 : 23;

        // Trova il nome della colonna orario usata nel database
        $timeColumn = Schema::hasColumn('bookings', 'start_time') ? 'start_time' : (Schema::hasColumn('bookings', 'booking_time') ? 'booking_time' : 'time');

        $blockedTimes = [];
        if (Schema::hasTable('blocked_slots')) {
            $blockedTimes = BlockedSlot::where('coach_id', $coach->id)
                ->whereDate('date', $selectedDate)
                ->pluck('start_time')
                ->map(fn($t) => substr($t, 0, 5))
                ->toArray();
        }

        // Recupera le prenotazioni per il coach e per la data selezionata
        $bookings = Booking::where('coach_id', $coach->id)
            ->whereDate('booking_date', $selectedDate)
            ->get();

        // Raggruppa forzando il formato HH:MM (es. 09:00:00 -> 09:00)
        $dayBookings = $bookings->groupBy(function ($item) use ($timeColumn) {
            return Carbon::parse($item->$timeColumn)->format('H:i');
        });

        $slots = [];
        for ($hour = $startHour; $hour <= $endHour; $hour++) {
            $timeString = sprintf('%02d:00', $hour);
            $slotDateTime = Carbon::parse($selectedDate->toDateString() . ' ' . $timeString);
            $isPast = $slotDateTime->lt($now);

            // Recupera le prenotazioni corrispondenti a quest'ora
            $slotBookings = $dayBookings->get($timeString, collect());
            $count = $slotBookings->count();
            $isBlocked = in_array($timeString, $blockedTimes);
            $isFull = $isBlocked || $isPast || ($count >= 2);

            $slots[] = [
                'time'       => $timeString,
                'count'      => $count,
                'is_blocked' => $isBlocked,
                'is_past'    => $isPast,
                'is_full'    => $isFull,
                'bookings'  => $slotBookings,
            ];
        }

        return view('calendar', compact('coach', 'selectedDate', 'days', 'slots'));
    }

    public function store(Request $request, $coachParam)
    {
        $coach = Coach::where('id', $coachParam)
            ->orWhere('slug', $coachParam)
            ->firstOrFail();

        $bookingTime = $request->input('start_time') ?? $request->input('booking_time');
        $bookingTime = Carbon::parse($bookingTime)->format('H:i');

        $request->validate([
            'client_name'  => 'required|string|max:255',
            'booking_date' => 'required|date',
            'start_time'   => 'required|string',
        ]);

        $slotDateTime = Carbon::parse($request->booking_date . ' ' . $bookingTime);
        if ($slotDateTime->lt(Carbon::now())) {
            return back()->with('error', 'Questo orario è già passato e non può essere prenotato.');
        }

        $timeColumn = Schema::hasColumn('bookings', 'start_time') ? 'start_time' : (Schema::hasColumn('bookings', 'booking_time') ? 'booking_time' : 'time');

        if (Schema::hasTable('blocked_slots')) {
            $isBlocked = BlockedSlot::where('coach_id', $coach->id)
                ->whereDate('date', $request->booking_date)
                ->where('start_time', 'LIKE', $bookingTime . '%')
                ->exists();

            if ($isBlocked) {
                return back()->with('error', 'Questo orario è stato disattivato dal coach.');
            }
        }

        // Conteggio prenotazioni nello slot
        $count = Booking::where('coach_id', $coach->id)
            ->whereDate('booking_date', $request->booking_date)
            ->where($timeColumn, 'LIKE', $bookingTime . '%')
            ->count();

        if ($count >= 2) {
            return back()->with('error', 'Questo orario ha già raggiunto il limite massimo (2/2).');
        }

        $endTime = Carbon::parse($bookingTime)->addHour()->format('H:i');

        $bookingData = [
            'coach_id'     => $coach->id,
            'client_name'  => $request->client_name,
            'client_email' => $request->client_email ?? 'n/a',
            'client_phone' => $request->client_phone ?? 'n/a',
            'booking_date' => $request->booking_date,
            $timeColumn    => $bookingTime,
        ];

        if (Schema::hasColumn('bookings', 'end_time')) {
            $bookingData['end_time'] = $endTime;
        }

        Booking::create($bookingData);

        return back()->with('success', 'Prenotazione effettuata con successo!');
    }
}