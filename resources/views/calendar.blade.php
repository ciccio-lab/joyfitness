<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prenotazione - {{ $coach->name }} - Joy Fitness</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-black text-white min-h-screen p-4 sm:p-6">
    <header class="max-w-3xl mx-auto flex justify-between items-center border-b border-zinc-800 pb-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-red-600 uppercase">{{ $coach->name }}</h1>
            <p class="text-xs text-zinc-400">Seleziona un orario e prenota il tuo posto</p>
        </div>
        <a href="{{ route('home') }}" class="bg-zinc-800 hover:bg-zinc-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl border border-zinc-700 transition-colors">
            ← Home
        </a>
    </header>

    <main class="max-w-3xl mx-auto space-y-6">
        @if(session('success'))
            <div class="p-3 bg-red-600/20 border border-red-600 text-red-500 rounded-xl text-center text-xs font-bold">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-3 bg-red-600/20 border border-red-600 text-red-500 rounded-xl text-center text-xs font-bold">
                {{ session('error') }}
            </div>
        @endif

        <!-- BARRA ORIZZONTALE SELEZIONE GIORNO -->
        <div class="bg-zinc-900 p-4 rounded-2xl border border-zinc-800 shadow-xl">
            <h2 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-3">Seleziona Giorno</h2>
            <div class="flex space-x-3 overflow-x-auto no-scrollbar pb-1">
                @foreach($days as $day)
                    @php
                        $isSelected = $selectedDate->isSameDay($day);
                        $isToday = $day->isToday();
                    @endphp
                    <a href="{{ route('calendar', ['coach' => $coach->slug, 'date' => $day->toDateString()]) }}" 
                       class="flex-none w-20 py-3 rounded-xl border text-center transition-all flex flex-col items-center justify-center
                              {{ $isSelected ? 'bg-red-600 border-red-600 text-white font-bold shadow-lg shadow-red-600/30 scale-105' : 'bg-black border-zinc-800 text-zinc-400 hover:border-zinc-700 hover:text-white' }}">
                        <span class="text-[10px] uppercase font-bold tracking-wider {{ $isSelected ? 'text-white' : 'text-zinc-500' }}">
                            {{ $isToday ? 'Oggi' : $day->translatedFormat('D') }}
                        </span>
                        <span class="text-lg font-black my-0.5">{{ $day->format('d') }}</span>
                        <span class="text-[10px] uppercase font-medium">{{ $day->translatedFormat('M') }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- LISTA SLOT ORARI -->
        <section class="bg-zinc-900 p-6 rounded-2xl border border-zinc-800 shadow-xl space-y-4">
            <h2 class="text-base font-bold text-white uppercase mb-2">Orari del {{ $selectedDate->format('d/m/Y') }}</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($slots as $slot)
                    <div class="bg-black p-4 rounded-xl border border-zinc-800 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-lg font-black text-red-500">{{ $slot['time'] }}</span>
                                <span class="text-[10px] uppercase font-bold px-2.5 py-1 rounded-lg {{ $slot['is_full'] ? 'bg-red-600/20 text-red-500 border border-red-600/30' : 'bg-zinc-800 text-zinc-300' }}">
                                    {{ $slot['count'] }}/2 Posti
                                </span>
                            </div>

                            <!-- Nomi di chi si è prenotato -->
                            <div class="mb-3 space-y-1">
                                @foreach($slot['bookings'] as $b)
                                    <p class="text-xs text-zinc-300 bg-zinc-900 px-2 py-1 rounded border border-zinc-800">
                                        👤 Occupato da: <span class="text-white font-bold">{{ $b->client_name }}</span>
                                    </p>
                                @endforeach
                                @if($slot['count'] == 0)
                                    <p class="text-xs text-zinc-500 italic">Nessuna prenotazione (2 posti liberi)</p>
                                @elseif($slot['count'] == 1)
                                    <p class="text-xs text-yellow-500 font-medium">1 posto ancora disponibile</p>
                                @else
                                    <p class="text-xs text-red-500 font-bold uppercase">Orario Completo</p>
                                @endif
                            </div>
                        </div>

                        <!-- Form di prenotazione se c'è posto -->
                        @if(!$slot['is_full'])
                            <form action="{{ route('book', $coach->slug) }}" method="POST" class="space-y-2 mt-2 pt-2 border-t border-zinc-800/80">
                                @csrf
                                <input type="hidden" name="booking_date" value="{{ $selectedDate->toDateString() }}">
                                <input type="hidden" name="start_time" value="{{ $slot['time'] }}">
                                <input type="text" name="client_name" placeholder="Il tuo nome e cognome" required 
                                       class="w-full p-2 bg-zinc-900 text-white text-xs rounded-lg border border-zinc-700 focus:border-red-600 focus:outline-none">
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-xs font-bold transition-colors uppercase tracking-wider">
                                    Prenota Posto
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    </main>
</body>
</html>