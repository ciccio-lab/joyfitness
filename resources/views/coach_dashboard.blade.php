<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pannello {{ $coach->name }} - Joy Fitness</title>
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
            <p class="text-xs text-zinc-400">Pannello Gestione Lezioni</p>
        </div>

        <!-- Tasti Azione Header -->
        <div class="flex items-center space-x-2">
            <!-- Pulsante Torna alla Vista Allievo -->
            <a href="{{ route('calendar', $coach->slug) }}" 
               class="bg-zinc-800 hover:bg-red-600 text-white p-2.5 sm:px-4 sm:py-2.5 rounded-xl transition-colors flex items-center space-x-2 border border-zinc-700" 
               title="Vedi Calendario Allievo">
                <svg class="w-5 h-5 text-red-500 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <span class="text-xs font-bold hidden sm:inline uppercase">Vista Allievo</span>
            </a>

            <!-- Tasto Logout -->
            <form action="{{ route('coach.logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-zinc-800 hover:bg-red-600 text-white p-2.5 rounded-xl transition-colors flex items-center space-x-2 border border-zinc-700" title="Logout">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="text-xs font-bold hidden sm:inline uppercase">Esci</span>
                </button>
            </form>
        </div>
    </header>

    <main class="max-w-3xl mx-auto space-y-6">
        @if(session('success'))
            <div class="p-3 bg-red-600/20 border border-red-600 text-red-500 rounded-xl text-center text-xs font-bold">
                {{ session('success') }}
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
                    <a href="{{ route('coach.dashboard', ['coach' => $coach->slug, 'date' => $day->toDateString()]) }}" 
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

        <!-- ALLIEVI PRENOTATI NELLA DATA SELEZIONATA -->
        <section class="bg-zinc-900 p-6 rounded-2xl border border-zinc-800 shadow-xl">
            <h2 class="text-base font-bold text-white uppercase mb-4 flex items-center justify-between">
                <span>Prenotazioni del <span class="text-red-500">{{ $selectedDate->format('d/m/Y') }}</span></span>
                <span class="text-xs bg-red-600/20 text-red-500 border border-red-600/40 px-3 py-1 rounded-full font-mono">{{ $bookings->count() }} Allievi</span>
            </h2>

            @if($bookings->isEmpty())
                <p class="text-zinc-500 text-sm py-4 text-center">Nessuna prenotazione ricevuta per questa data.</p>
            @else
                <div class="space-y-3">
                    @foreach($bookings as $booking)
                    <form action="{{ route('coach.bookings.cancel', $booking->id) }}" method="POST" onsubmit="return confirm('Sei sicuro di voler annullare questa prenotazione?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm">
                    Annulla
                    </button>
                    </form>
                        <div class="flex justify-between items-center bg-black p-4 rounded-xl border border-zinc-800">
                            <div>
                                <span class="text-lg font-black text-red-500 block">{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</span>
                                <span class="text-white font-bold">{{ $booking->client_name }}</span>
                            </div>
                            <span class="text-xs bg-zinc-800 text-zinc-300 font-bold px-3 py-1 rounded-lg">Confermato</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <!-- BLOCCO/SBLOCCO MANUALI DEGLI ORARI -->
        <section class="bg-zinc-900 p-6 rounded-2xl border border-zinc-800 shadow-xl">
            <h2 class="text-base font-bold text-white uppercase mb-1">Gestione Orari Disponibili</h2>
            <p class="text-xs text-zinc-400 mb-4">Clicca su uno slot per bloccarlo o sbloccarlo per la data selezionata.</p>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach($slots as $slot)
                    <form action="{{ route('coach.toggleSlot', $coach->slug) }}" method="POST">
                        @csrf
                        <input type="hidden" name="date" value="{{ $selectedDate->toDateString() }}">
                        <input type="hidden" name="start_time" value="{{ $slot['time'] }}">
                        <button type="submit" 
                                class="w-full p-3 rounded-xl border text-center transition-all font-bold 
                                       {{ $slot['is_blocked'] 
                                          ? 'bg-red-950/40 border-red-600 text-red-500' 
                                          : 'bg-black border-zinc-800 text-white hover:border-zinc-600' }}">
                            <span class="block text-base">{{ $slot['time'] }}</span>
                            <span class="text-[10px] uppercase block tracking-wider mt-0.5 {{ $slot['is_blocked'] ? 'text-red-500 font-extrabold' : 'text-zinc-500' }}">
                                {{ $slot['is_blocked'] ? 'NON DISPONIBILE' : 'DISPONIBILE' }}
                            </span>
                        </button>
                    </form>
                @endforeach
            </div>
        </section>
    </main>
</body>
</html>