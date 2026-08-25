<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prenota con {{ $coach->name }} - Joy Fitness</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-black text-white min-h-screen p-4 md:p-8" x-data="{ openModal: false, selectedTime: '' }">

    <div class="max-w-3xl mx-auto space-y-6">
        
       <!-- Logo e Intestazione Centrati -->
        <div class="flex flex-col items-center justify-center text-center mb-8">
            <a href="{{ route('home') }}" class="inline-block mb-3">
                <img src="{{ asset('images/logo.png') }}" alt="Joy Fitness Logo" class="h-20 w-auto mx-auto block object-contain">
            </a>
            <h1 class="text-3xl font-black uppercase tracking-wide text-white">
                Coach <span class="text-red-600">{{ trim(preg_replace('/(?i)\bcoach\b/', '', $coach->name)) }}</span>
            </h1>
            <p class="text-xs text-zinc-400 mt-1 uppercase tracking-widest">Seleziona un giorno e un orario per la tua lezione</p>
        </div>

        <!-- Feedback Messages -->
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
        <div class="flex gap-2 overflow-x-auto pb-3 scrollbar-none">
            @foreach($days as $day)
                @php $isSel = $day->isSameDay($selectedDate); @endphp
                <a href="{{ route('calendar', ['coach' => $coach->slug ?? $coach->id, 'date' => $day->format('Y-m-d')]) }}"
                   class="flex-shrink-0 px-5 py-3 rounded-2xl text-center border transition-all duration-200 {{ $isSel ? 'bg-red-600 border-red-600 text-white shadow-lg shadow-red-600/30' : 'bg-zinc-900 border-zinc-800 text-zinc-400 hover:border-zinc-700' }}">
                    <div class="text-xs uppercase font-bold">{{ $day->translatedFormat('D') }}</div>
                    <div class="text-base font-black mt-0.5">{{ $day->format('d/m') }}</div>
                </a>
            @endforeach
        </div>

        <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
            <h2 class="text-lg font-bold text-zinc-200 capitalize">
                {{ $selectedDate->translatedFormat('l d F Y') }}
            </h2>
            <span class="text-xs text-zinc-500 uppercase font-semibold">Turni da 1 Ora</span>
        </div>

        <!-- Griglia Slot Orari -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($slots as $slot)
                <div class="p-4 rounded-2xl border flex items-center justify-between transition-all {{ $slot['is_full'] ? 'bg-zinc-950/50 border-zinc-900 opacity-60' : 'bg-zinc-900 border-zinc-800 hover:border-zinc-700' }}">
                    <div>
                        <div class="text-xl font-black text-white tracking-wider">{{ $slot['time'] }}</div>
                        <div class="text-xs font-bold uppercase mt-1">
<div class="text-xs font-bold uppercase mt-1">
    @if($slot['is_past'] ?? false)
        <span class="text-zinc-600">Scaduto</span>
    @elseif($slot['is_blocked'] ?? false)
        <span class="text-red-500">Non disponibile</span>
    @elseif(($slot['count'] ?? 0) >= 2)
        <span class="text-amber-500">Completo (2/2)</span>
    @else
        <span class="text-emerald-400">Disponibile ({{ 2 - ($slot['count'] ?? 0) }} {{ (2 - ($slot['count'] ?? 0)) == 1 ? 'posto' : 'posti' }})</span>
    @endif
</div>                

                    <div>
                        @if(!$slot['is_full'])
                            <button @click="openModal = true; selectedTime = '{{ $slot['time'] }}'"
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all shadow-md shadow-red-600/20">
                                Prenota
                            </button>
                        @else
                            <button disabled class="px-4 py-2 bg-zinc-800 text-zinc-600 font-bold text-xs uppercase tracking-wider rounded-xl cursor-not-allowed">
                                Bloccato
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center pt-6">
            <a href="{{ route('home') }}" class="text-xs text-zinc-500 hover:text-zinc-300 transition-colors uppercase font-bold tracking-wider">
                ← Scegli un altro coach
            </a>
        </div>

        <!-- Modal Prenotazione -->
        <div x-show="openModal" 
             x-cloak
             class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.away="openModal = false" class="bg-zinc-900 border border-zinc-800 w-full max-w-md p-6 rounded-2xl shadow-2xl relative space-y-4">
                
                <div class="flex justify-between items-center border-b border-zinc-800 pb-3">
                    <h3 class="text-lg font-black uppercase text-white">Conferma Prenotazione</h3>
                    <button @click="openModal = false" class="text-zinc-500 hover:text-white font-bold text-xl">&times;</button>
                </div>

                <div class="bg-black/50 p-3 rounded-xl text-xs space-y-1 text-zinc-400">
                    <div><span class="font-bold text-zinc-200">Coach:</span> {{ $coach->name }}</div>
                    <div><span class="font-bold text-zinc-200">Data:</span> {{ $selectedDate->format('d/m/Y') }}</div>
                    <div><span class="font-bold text-zinc-200">Orario:</span> <span class="text-red-500 font-black text-sm" x-text="selectedTime"></span></div>
                </div>

                <form action="{{ route('book', $coach->slug ?? $coach->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="booking_date" value="{{ $selectedDate->format('Y-m-d') }}">
                    <input type="hidden" name="start_time" :value="selectedTime">

                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-400 mb-1">Nome e Cognome *</label>
                        <input type="text" name="client_name" required placeholder="Es. Mario Rossi"
                               class="w-full bg-black border border-zinc-700 rounded-xl p-3 text-white text-sm focus:border-red-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-400 mb-1">Telefono (Opzionale)</label>
                        <input type="text" name="client_phone" placeholder="Es. 3331234567"
                               class="w-full bg-black border border-zinc-700 rounded-xl p-3 text-white text-sm focus:border-red-600 focus:outline-none">
                    </div>

                    <div class="pt-2 flex gap-2">
                        <button type="button" @click="openModal = false" 
                                class="w-1/2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-bold py-3 rounded-xl transition-colors uppercase text-xs">
                            Annulla
                        </button>
                        <button type="submit" 
                                class="w-1/2 bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl transition-colors uppercase text-xs shadow-lg shadow-red-600/30">
                            Conferma
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</body>
</html>