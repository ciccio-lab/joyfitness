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

        $days = [];
        for ($i = 0; $i < 14; $i++) {
            $days[] = Carbon::today()->addDays($i);
        }

        $startHour = 8;
        $endHour = $selectedDate->isWeekend() ? 19 : 23;

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

        $bookedCounts = $bookings->groupBy('booking_time')->map->count();

        $slots = [];
        for ($hour = $startHour; $hour <= $endHour; $hour++) {
            $timeString = sprintf('%02d:00', $hour);
            $count = $bookedCounts->get($timeString, 0);
            $isBlocked = in_array($timeString, $blockedTimes);

            $slots[] = [
                'time' => $timeString,
                'count' => $count,
                'is_blocked' => $isBlocked,
                'is_full' => $isBlocked || $count >= 2,
            ];
        }

        return view('calendar', compact('coach', 'selectedDate', 'days', 'slots'));
    }

    public function store(Request $request, $coachParam)
    {
        $coach = Coach::where('id', $coachParam)
            ->orWhere('slug', $coachParam)
            ->firstOrFail();

        // Accetta sia start_time che booking_time per retrocompatibilità
        $bookingTime = $request->input('start_time') ?? $request->input('booking_time');

        $request->validate([
            'client_name' => 'required|string|max:255',
            'booking_date' => 'required|date',
        ]);

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
            ->where('booking_time', $bookingTime)
            ->count();

        if ($count >= 2) {
            return back()->with('error', 'Questo orario ha già raggiunto il limite massimo di prenotazioni.');
        }

        Booking::create([
            'coach_id' => $coach->id,
            'client_name' => $request->client_name,
            'client_email' => $request->client_email ?? 'n/a',
            'client_phone' => $request->client_phone ?? 'n/a',
            'booking_date' => $request->booking_date,
            'booking_time' => $bookingTime,
        ]);

        return back()->with('success', 'Prenotazione effettuata con successo!');
    }
}