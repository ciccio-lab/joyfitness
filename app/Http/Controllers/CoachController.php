<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\Booking;
use App\Models\BlockedSlot;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class CoachController extends Controller
{
    public function dashboard(Request $request, $coachParam)
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

        $dayBookings = Booking::where('coach_id', $coach->id)
            ->whereDate('booking_date', $selectedDate)
            ->get()
            ->groupBy($timeColumn);

        $slots = [];
        for ($hour = $startHour; $hour <= $endHour; $hour++) {
            $timeString = sprintf('%02d:00', $hour);
            $slotDateTime = Carbon::parse($selectedDate->toDateString() . ' ' . $timeString);
            $isPast = $slotDateTime->lt($now);

            $slotBookings = $dayBookings->get($timeString, collect());
            $isBlocked = in_array($timeString, $blockedTimes);

            $slots[] = [
                'time' => $timeString,
                'count' => $slotBookings->count(),
                'is_blocked' => $isBlocked,
                'is_past' => $isPast,
                'is_full' => $isBlocked || $isPast || $slotBookings->count() >= 2,
                'bookings' => $slotBookings,
            ];
        }

        return view('coach_dashboard', compact('coach', 'selectedDate', 'days', 'slots'));
    }

    public function toggleSlot(Request $request, $coachParam)
    {
        $coach = Coach::where('id', $coachParam)
            ->orWhere('slug', $coachParam)
            ->firstOrFail();

        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|string',
        ]);

        if (!Schema::hasTable('blocked_slots')) {
            return back()->with('error', 'La tabella blocked_slots non esiste.');
        }

        $slot = BlockedSlot::where('coach_id', $coach->id)
            ->whereDate('date', $request->date)
            ->where('start_time', $request->start_time)
            ->first();

        if ($slot) {
            $slot->delete();
        } else {
            BlockedSlot::create([
                'coach_id' => $coach->id,
                'date' => $request->date,
                'start_time' => $request->start_time,
            ]);
        }

        return back()->with('success', 'Stato dello slot aggiornato.');
    }

    public function cancelBooking($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return back()->with('success', 'Prenotazione cancellata.');
    }
}