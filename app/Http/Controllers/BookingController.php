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

        $timeColumn = Schema::hasColumn('bookings', 'start_time') ? 'start_time' : 'booking_time';

        $blockedTimes = [];
        if (Schema::hasTable('blocked_slots')) {
            $blockedTimes = BlockedSlot::where('coach_id', $coach->id)
                ->whereDate('date', $selectedDate)
                ->pluck('start_time')
                ->toArray();
        }

        $bookings = Booking::where('coach_id', $coach->id)
            ->whereDate('booking_date', $selectedDate)
            ->get();

        // Normalizza la chiave orario eliminando eventuali secondi (es. "08:00:00" -> "08:00")
        $dayBookings = $bookings->groupBy(function ($item) use ($timeColumn) {
            return substr($item->$timeColumn, 0, 5);
        });

        $slots = [];
        for ($hour = $startHour; $hour <= $endHour; $hour++) {
            $timeString = sprintf('%02d:00', $hour);
            $slotDateTime = Carbon::parse($selectedDate->toDateString() . ' ' . $timeString);
            $isPast = $slotDateTime->lt($now);

            $slotBookings = $dayBookings->get($timeString, collect());
            $count = $slotBookings->count();
            $isBlocked = in_array($timeString, $blockedTimes);

            $slots[] = [
                'time' => $timeString,
                'count' => $count,
                'is_blocked' => $isBlocked,
                'is_past' => $isPast,
                'is_full' => $isBlocked || $isPast || $count >= 2,
                'bookings' => $slotBookings,
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
        $bookingTime = substr($bookingTime, 0, 5);

        $request->validate([
            'client_name' => 'required|string|max:255',
            'booking_date' => 'required|date',
            'start_time' => 'required|string',
        ]);

        $slotDateTime = Carbon::parse($request->booking_date . ' ' . $bookingTime);
        if ($slotDateTime->lt(Carbon::now())) {
            return back()->with('error', 'Questo orario è già passato e non può essere prenotato.');
        }

        $timeColumn = Schema::hasColumn('bookings', 'start_time') ? 'start_time' : 'booking_time';

        if (Schema::hasTable('blocked_slots')) {
            $isBlocked = BlockedSlot::where('coach_id', $coach->id)
                ->whereDate('date', $request->booking_date)
                ->where('start_time', $bookingTime)
                ->exists();

            if ($isBlocked) {
                return back()->with('error', 'Questo orario è stato disattivato dal coach.');
            }
        }

        $count = Booking::where('coach_id', $coach->id)
            ->whereDate('booking_date', $request->booking_date)
            ->where($timeColumn, 'LIKE', $bookingTime . '%')
            ->count();

        if ($count >= 2) {
            return back()->with('error', 'Questo orario ha già raggiunto il limite massimo di prenotazioni.');
        }

        $endTime = Carbon::parse($bookingTime)->addHour()->format('H:i');

        $bookingData = [
            'coach_id' => $coach->id,
            'client_name' => $request->client_name,
            'client_email' => $request->client_email ?? 'n/a',
            'client_phone' => $request->client_phone ?? 'n/a',
            'booking_date' => $request->booking_date,
            $timeColumn => $bookingTime,
        ];

        if (Schema::hasColumn('bookings', 'end_time')) {
            $bookingData['end_time'] = $endTime;
        }

        Booking::create($bookingData);

        return back()->with('success', 'Prenotazione effettuata con successo!');
    }
}