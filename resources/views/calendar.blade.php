<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prenota con {{ $coach->name }} - Joy Fitness</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white min-h-screen p-4 md:p-8 font-sans">
    <div class="max-w-4xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="bg-zinc-900 p-6 rounded-2xl border border-zinc-800 shadow-xl text-center space-y-2">
            <h1 class="text-3xl font-black text-red-600 uppercase tracking-wide">Prenota Sessione</h1>
            <p class="text-zinc-400 text-sm">Coach: <span class="text-white font-bold">{{ $coach->name }}</span></p>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-950/60 border border-emerald-500/50 text-emerald-400 rounded-2xl text-center text-sm font-bold shadow-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-red-950/60 border border-red-500/50 text-red-400 rounded-2xl text-center text-sm font-bold shadow-lg">
                {{ session('error') }}
            </div>
        @endif

        <!-- Selettore Giorni -->
        <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-none">
            @foreach($days as $day)
                @php $isSel = $day->isSameDay($selectedDate); @endphp
                <a href="{{ route('calendar', ['coach' => $coach->slug, 'date' => $day->format('Y-m-d')]) }}" 
                   class="flex-shrink-0 px-4 py-3 rounded-2xl text-center border transition-all duration-200 {{ $isSel ? 'bg-red-600 border-red-500 text-white shadow-lg shadow-red-600/30 scale-105' : 'bg-zinc-900 border-zinc-800 text-zinc-400 hover:border-zinc-700 hover:text-white' }}">
                    <div class="text-xs uppercase font-bold">{{ $day->translatedFormat('D') }}</div>
                    <div class="text-lg font-black">{{ $day->format('d/m') }}</div>
                </a>
            @endforeach
        </div>

        <!-- Lista Slot Orari -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($slots as $slot)
                <div class="p-5 rounded-2xl border transition-all flex justify-between items-center {{ $slot['is_full'] ? 'bg-zinc-950/80 border-zinc-900 opacity-60' : 'bg-zinc-900 border-zinc-800 hover:border-zinc-700' }}">
                    
                    <div>
                        <div class="text-2xl font-black">{{ $slot['time'] }}</div>
                        <div class="text-xs font-bold uppercase mt-1">
                            @if($slot['is_blocked'])
                                <span class="text-red-500">Non disponibile</span>
                            @elseif($slot['count'] >= 2)
                                <span class="text-amber-500">Completo (2/2)</span>
                            @else
                                <span class="text-emerald-400">Disponibile ({{ 2 - $slot['count'] }} posti rimasti)</span>
                            @endif
                        </div>
                    </div>

                    <!-- Pulsante o Form Prenotazione -->
                    @if(!$slot['is_full'])
                        <form action="{{ route('book', ['coach' => $coach->slug]) }}" method="POST" class="flex gap-2">
                            @csrf
                            <input type="hidden" name="booking_date" value="{{ $selectedDate->format('Y-m-d') }}">
                            <input type="hidden" name="start_time" value="{{ $slot['time'] }}">
                            
                            <input type="text" name="client_name" required placeholder="Tuo Nome" 
                                   class="bg-black border border-zinc-700 rounded-xl px-3 py-2 text-xs text-white focus:border-red-600 focus:outline-none w-32">
                            
                            <button type="submit" class="bg-red-600 hover:bg-red-500 text-white font-bold px-4 py-2 rounded-xl text-xs uppercase tracking-wider transition shadow-md shadow-red-600/20">
                                Prenota
                            </button>
                        </form>
                    @else
                        <button disabled class="bg-zinc-800 text-zinc-600 font-bold px-4 py-2 rounded-xl text-xs uppercase tracking-wider cursor-not-allowed">
                            Chiuso
                        </button>
                    @endif

                </div>
            @endforeach
        </div>

        <div class="text-center pt-4">
            <a href="{{ route('home') }}" class="text-xs text-zinc-500 hover:text-zinc-300 transition-colors">← Torna alla Selezione Coach</a>
        </div>

    </div>
</body>
</html>