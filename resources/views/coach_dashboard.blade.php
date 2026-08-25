<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Coach - {{ $coach->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white min-h-screen p-4 md:p-8 font-sans">
    <div class="max-w-5xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-zinc-900 p-6 rounded-2xl border border-zinc-800 shadow-xl gap-4">
            <div>
                <h1 class="text-3xl font-black text-red-600 uppercase tracking-wide">Pannello Coach</h1>
                <p class="text-zinc-400 text-sm">Benvenuto, <span class="text-white font-bold">{{ $coach->name }}</span></p>
            </div>
            <form action="{{ route('coach.logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-zinc-800 hover:bg-red-600/20 hover:text-red-500 border border-zinc-700 hover:border-red-600 text-zinc-300 font-bold px-4 py-2 rounded-xl text-xs uppercase tracking-wider transition">
                    Logout
                </button>
            </form>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-950/60 border border-emerald-500/50 text-emerald-400 rounded-2xl text-center text-sm font-bold shadow-lg">
                {{ session('success') }}
            </div>
        @endif

        <!-- Selettore Giorni -->
        <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-none">
            @foreach($days as $day)
                @php $isSel = $day->isSameDay($selectedDate); @endphp
                <a href="{{ route('coach.dashboard', ['coach' => $coach->id, 'date' => $day->format('Y-m-d')]) }}" 
                   class="flex-shrink-0 px-4 py-3 rounded-2xl text-center border transition-all duration-200 {{ $isSel ? 'bg-red-600 border-red-500 text-white shadow-lg shadow-red-600/30 scale-105' : 'bg-zinc-900 border-zinc-800 text-zinc-400 hover:border-zinc-700 hover:text-white' }}">
                    <div class="text-xs uppercase font-bold">{{ $day->translatedFormat('D') }}</div>
                    <div class="text-lg font-black">{{ $day->format('d/m') }}</div>
                </a>
            @endforeach
        </div>

        <!-- Legenda -->
        <div class="flex items-center justify-end gap-4 text-xs font-bold uppercase tracking-wider text-zinc-400 bg-zinc-950 p-3 rounded-xl border border-zinc-900">
            <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50"></span> Aperto / Disponibile</span>
            <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-red-600 shadow-sm shadow-red-600/50"></span> Chiuso / Bloccato</span>
        </div>

        <!-- Griglia Slot Orari -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($slots as $slot)
                <div class="p-5 rounded-2xl border transition-all shadow-md flex flex-col justify-between space-y-4 {{ $slot['is_blocked'] ? 'bg-red-950/20 border-red-900/50' : 'bg-zinc-900 border-zinc-800' }}">
                    
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl font-black">{{ $slot['time'] }}</span>
                            @if($slot['is_blocked'])
                                <span class="bg-red-600/20 border border-red-500/30 text-red-400 text-[10px] uppercase font-bold px-2 py-0.5 rounded-full">
                                    Chiuso
                                </span>
                            @else
                                <span class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-[10px] uppercase font-bold px-2 py-0.5 rounded-full">
                                    Attivo ({{ $slot['count'] }}/2)
                                </span>
                            @endif
                        </div>

                        <!-- Bottone Toggle Blocco -->
                        <form action="{{ route('coach.toggleSlot', ['coach' => $coach->id]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="date" value="{{ $selectedDate->format('Y-m-d') }}">
                            <input type="hidden" name="start_time" value="{{ $slot['time'] }}">
                            <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all border shadow-sm {{ $slot['is_blocked'] ? 'bg-emerald-600 hover:bg-emerald-500 border-emerald-400 text-white shadow-emerald-600/20' : 'bg-red-600 hover:bg-red-500 border-red-400 text-white shadow-red-600/20' }}">
                                {{ $slot['is_blocked'] ? 'Riapri Slot' : 'Chiudi Slot' }}
                            </button>
                        </form>
                    </div>

                    <!-- Lista Prenotazioni per questo Slot -->
                    <div class="space-y-2 border-t border-zinc-800/80 pt-3">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">Prenotati:</div>
                        @forelse($slot['bookings'] as $booking)
                            <div class="flex justify-between items-center bg-black/50 p-2.5 rounded-xl border border-zinc-800 text-xs">
                                <span class="font-bold text-zinc-200">👤 {{ $booking->client_name }}</span>
                                <form action="{{ route('coach.bookings.cancel', $booking->id) }}" method="POST" onsubmit="return confirm('Cancellare prenotazione?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-400 text-xs font-bold uppercase">Elimina</button>
                                </form>
                            </div>
                        @empty
                            <p class="text-xs text-zinc-600 italic">Nessuna prenotazione presente</p>
                        @endforelse
                    </div>

                </div>
            @endforeach
        </div>

    </div>
</body>
</html>