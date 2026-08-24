<?php
namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\Booking;
use App\Models\CoachUnavailability;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingController extends Controller
{
    // Homepage con selezione del coach
    public function index()
    {
        $coaches = Coach::all();
        return view('welcome', compact('coaches'));
    }

    // Vista calendario lato cliente
    public function show(Coach $coach, Request $request)
    {
        $selectedDate = $request->query('date') 
            ? Carbon::parse($request->query('date')) 
            : Carbon::today();

        // Prossimi 14 giorni
        $daysBar = [];
        for ($i = 0; $i < 14; $i++) {
            $day = Carbon::today()->addDays($i);
            $daysBar[] = [
                'date_string' => $day->toDateString(),
                'day_name'    => strtoupper($day->locale('it')->shortDayName),
                'day_number'  => $day->format('d'),
                'month_name'  => strtoupper($day->locale('it')->shortMonthName),
                'is_selected' => $day->isSameDay($selectedDate),
            ];
        }

        // Orari: Lun-Ven 8-23 | Sab-Dom 8-19
        $endHour = $selectedDate->isWeekend() ? 19 : 23;
        $startHour = 8;

        // Prenotazioni attive
        $existingBookings = Booking::where('coach_id', $coach->id)
            ->where('booking_date', $selectedDate->toDateString())
            ->pluck('start_time')
            ->map(fn($t) => Carbon::parse($t)->format('H:i'))
            ->toArray();

        // Slot bloccati dal coach (ricorrenti o data specifica)
        $blockedSlots = CoachUnavailability::where('coach_id', $coach->id)
            ->where(function ($q) use ($selectedDate) {
                $q->where('specific_date', $selectedDate->toDateString())
                  ->orWhere(function ($sub) use ($selectedDate) {
                      $sub->whereNull('specific_date')
                          ->where('day_of_week', $selectedDate->dayOfWeekIso);
                  });
            })
            ->pluck('start_time')
            ->map(fn($t) => Carbon::parse($t)->format('H:i'))
            ->toArray();

        $slots = [];
        $now = Carbon::now();

        for ($hour = $startHour; $hour < $endHour; $hour++) {
            $slotStart = $selectedDate->copy()->setTime($hour, 0);
            $timeLabel = $slotStart->format('H:i');
            $timeEndLabel = $slotStart->copy()->addHour()->format('H:i');

            // Blocco slot passati nell'ora corrente del giorno stesso
            $isPast = $selectedDate->isToday() && $slotStart->lte($now);
            $isBooked = in_array($timeLabel, $existingBookings);
            $isBlocked = in_array($timeLabel, $blockedSlots);

            $slots[] = [
                'start_time' => $timeLabel,
                'end_time'   => $timeEndLabel,
                'available'  => !$isPast && !$isBooked && !$isBlocked,
                'status'     => $isBooked ? 'Prenotato' : ($isBlocked ? 'Non disponibile' : ($isPast ? 'Scaduto' : 'Libero'))
            ];
        }

        return view('calendar', compact('coach', 'daysBar', 'slots', 'selectedDate'));
    }

    // Salvataggio prenotazione cliente
    public function store(Request $request, Coach $coach)
    {
        $request->validate([
            'client_name'  => 'required|string|max:255',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time'   => 'required',
        ]);

        $startTime = Carbon::parse($request->start_time);
        $bookingDateTime = Carbon::parse($request->booking_date)->setTimeFrom($startTime);

        // Protezione contro prenotazioni retroattive
        if ($bookingDateTime->lte(Carbon::now())) {
            return back()->with('error', 'Impossibile prenotare un orario già passato!');
        }

        $endTime = $startTime->copy()->addHour();

        $exists = Booking::where('coach_id', $coach->id)
            ->where('booking_date', $request->booking_date)
            ->where('start_time', $startTime->format('H:i:s'))
            ->exists();

        if ($exists) {
            return back()->with('error', 'Orario già prenotato!');
        }

        Booking::create([
            'coach_id'     => $coach->id,
            'client_name'  => $request->client_name,
            'booking_date' => $request->booking_date,
            'start_time'   => $startTime->format('H:i:s'),
            'end_time'     => $endTime->format('H:i:s'),
        ]);

        return back()->with('success', 'Prenotazione confermata!');
    }
}