<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Coach - {{ $coach->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white min-h-screen p-4 md:p-8">

    <div class="max-w-4xl mx-auto space-y-6">

        <div class="flex items-center justify-between border-b border-zinc-800 pb-4">
            <div>
                <h1 class="text-2xl font-black uppercase">Dashboard <span class="text-red-600">{{ $coach->name }}</span></h1>
                <p class="text-xs text-zinc-400 mt-0.5">Gestione lezioni e prenotazioni allievi</p>
            </div>
            <form action="{{ route('coach.logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-xs text-zinc-400 hover:text-white uppercase font-bold">Logout</button>
            </form>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-500/20 border border-emerald-500 text-emerald-400 rounded-2xl text-center font-bold text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-red-600/20 border border-red-600 text-red-500 rounded-2xl text-center font-bold text-sm">
                {{ session('error') }}
            </div>
        @endif

        <!-- Selettore Giorni -->
        <div class="flex gap-2 overflow-x-auto pb-3">
            @foreach($days as $day)
                @php $isSel = $day->isSameDay($selectedDate); @endphp
                <a href="{{ route('coach.dashboard', ['coach' => $coach->slug ?? $coach->id, 'date' => $day->format('Y-m-d')]) }}"
                   class="flex-shrink-0 px-5 py-3 rounded-2xl text-center border transition-all {{ $isSel ? 'bg-red-600 border-red-600 text-white shadow-lg shadow-red-600/30' : 'bg-zinc-900 border-zinc-800 text-zinc-400' }}">
                    <div class="text-xs uppercase font-bold">{{ $day->translatedFormat('D') }}</div>
                    <div class="text-base font-black mt-0.5">{{ $day->format('d/m') }}</div>
                </a>
            @endforeach
        </div>

        <!-- Lista Orari -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($slots as $slot)
                <div class="p-5 rounded-2xl border bg-zinc-900 border-zinc-800 space-y-3">
                    <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                        <div>
                            <span class="text-2xl font-black text-white tracking-wider">{{ $slot['time'] }}</span>
                            <span class="text-xs font-bold text-zinc-400 ml-2">({{ $slot['count'] }}/2 Iscritti)</span>
                        </div>

                        <form action="{{ route('coach.toggleSlot', $coach->slug ?? $coach->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="date" value="{{ $selectedDate->format('Y-m-d') }}">
                            <input type="hidden" name="start_time" value="{{ $slot['time'] }}">
                            <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-xl border transition-colors {{ $slot['is_blocked'] ? 'bg-red-600/20 border-red-600 text-red-500' : 'bg-zinc-800 border-zinc-700 text-zinc-300 hover:border-zinc-500' }}">
                                {{ $slot['is_blocked'] ? 'Sblocca Slot' : 'Blocca Slot' }}
                            </button>
                        </form>
                    </div>

                    <!-- Nomi Allievi Prenotati -->
                    <div class="space-y-2 pt-1">
                        @forelse($slot['bookings'] as $booking)
                            <div class="flex items-center justify-between bg-black/60 p-3 rounded-xl border border-zinc-800">
                                <div>
                                    <div class="text-sm font-bold text-white">{{ $booking->client_name }}</div>
                                    <div class="text-xs text-zinc-400 font-mono mt-0.5">
                                        Tel: 
                                        @if($booking->client_phone && $booking->client_phone !== 'n/a')
                                            <a href="tel:{{ $booking->client_phone }}" class="text-red-400 hover:underline font-bold">
                                                {{ $booking->client_phone }}
                                            </a>
                                        @else
                                            <span class="text-zinc-600 italic">N/D</span>
                                        @endif
                                    </div>
                                </div>
                                <form action="{{ route('coach.cancelBooking', $booking->id) }}" method="POST" onsubmit="return confirm('Vuoi cancellare questa prenotazione?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-red-500 hover:text-red-400 uppercase tracking-wider">
                                        Elimina
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="text-xs text-zinc-500 italic py-1">Nessun allievo prenotato a quest'ora.</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

    </div>
<script>
    // Salva la posizione dello scroll prima del cambio data o invio form
    document.querySelectorAll('form, a').forEach(element => {
        element.addEventListener('click', function () {
            localStorage.setItem('coachScrollPosition', window.scrollY);
        });
    });

    // Ripristina la posizione dello scroll al ricaricamento
    window.addEventListener('load', function () {
        const scrollPosition = localStorage.getItem('coachScrollPosition');
        if (scrollPosition) {
            window.scrollTo(0, parseInt(scrollPosition));
            localStorage.removeItem('coachScrollPosition');
        }
    });
</script>
</body>
</html>