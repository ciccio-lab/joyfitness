<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\Booking;
use App\Models\BlockedSlot;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CoachController extends Controller
{
    public function dashboard(Request $request, $coachParam)
    {
        $coach = Coach::where('id', $coachParam)
            ->orWhere('slug', $coachParam)
            ->firstOrFail();

        $dateInput = $request->input('date', Carbon::today()->toDateString());
        $selectedDate = Carbon::parse($dateInput);

        $startHour = 8;
        $endHour = $selectedDate->isWeekend() ? 19 : 23;

        $blockedTimes = BlockedSlot::where('coach_id', $coach->id)
            ->whereDate('date', $selectedDate)
            ->pluck('start_time')
            ->toArray();

        $bookings = Booking::where('coach_id', $coach->id)
            ->whereDate('booking_date', $selectedDate)
            ->get();

        return view('coach_dashboard', compact('coach', 'selectedDate', 'startHour', 'endHour', 'bookings', 'blockedTimes'));
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

        return back()->with('success', 'Stato dello slot aggiornato con successo.');
    }

    public function cancelBooking($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return back()->with('success', 'Prenotazione cancellata con successo.');
    }
}