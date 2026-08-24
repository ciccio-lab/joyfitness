<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $coach->name }} - Joy Fitness</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white min-h-screen pb-12">
    <!-- Header con Logo -->
    <header class="p-4 bg-zinc-900 border-b border-zinc-800 flex justify-between items-center px-6">
        <a href="{{ route('home') }}" class="text-gray-400 hover:text-white font-semibold text-sm">&larr; Torna ai Coach</a>
        <img src="{{ asset('images/logo.png') }}" alt="Joy Fitness" class="h-10 object-contain" onerror="this.style.display='none'">
        <span class="text-xs font-bold text-red-600 uppercase border border-red-600/30 px-3 py-1 rounded-full">Joy Fitness</span>
    </header>

    <main class="max-w-xl mx-auto px-4 mt-6">
        <!-- Titolo Coach -->
        <div class="mb-6 p-5 bg-zinc-900 rounded-2xl border border-zinc-800 text-center">
            <h1 class="text-2xl font-black text-white uppercase tracking-wider mb-1">{{ $coach->name }}</h1>
            <p class="text-xs text-gray-400">Seleziona un giorno e scegli la tua fascia oraria</p>
        </div>

        @if(session('success'))
            <div class="p-4 mb-4 bg-red-600 text-white rounded-xl text-center font-bold shadow-lg">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 mb-4 bg-zinc-800 border border-red-600 text-red-500 rounded-xl text-center font-bold">
                {{ session('error') }}
            </div>
        @endif

        <!-- Carosello Giorni -->
        <div class="flex space-x-3 overflow-x-auto pb-4 scrollbar-none">
            @foreach($daysBar as $day)
                <a href="{{ route('calendar', ['coach' => $coach->slug, 'date' => $day['date_string']]) }}" 
                   class="flex-shrink-0 w-16 py-3 rounded-xl text-center border transition-all {{ $day['is_selected'] ? 'bg-red-600 border-red-600 text-white font-bold scale-105 shadow-lg shadow-red-900/50' : 'bg-zinc-900 border-zinc-800 text-gray-300 hover:border-zinc-600' }}">
                    <span class="block text-[10px] uppercase font-bold">{{ $day['day_name'] }}</span>
                    <span class="block text-xl font-black my-1">{{ $day['day_number'] }}</span>
                    <span class="block text-[10px] uppercase text-gray-400">{{ $day['month_name'] }}</span>
                </a>
            @endforeach
        </div>

        <h2 class="text-xs font-bold uppercase tracking-widest my-4 text-gray-400">Fasce orarie del {{ $selectedDate->format('d/m/Y') }}</h2>

        <!-- Griglia Slot -->
        <div class="grid grid-cols-2 gap-3">
            @foreach($slots as $slot)
                @if($slot['available'])
                    <button onclick="openModal('{{ $slot['start_time'] }}')" 
                            class="p-4 rounded-xl bg-zinc-900 border border-red-600/40 hover:bg-red-600 hover:border-red-600 text-left transition-all group">
                        <span class="text-lg font-black block text-white group-hover:text-white">{{ $slot['start_time'] }} - {{ $slot['end_time'] }}</span>
                        <span class="text-xs font-semibold text-red-500 group-hover:text-white">Disponibile &rarr;</span>
                    </button>
                @else
                    <div class="p-4 rounded-xl bg-zinc-950 border border-zinc-900 text-zinc-600 cursor-not-allowed">
                        <span class="text-lg font-bold block line-through">{{ $slot['start_time'] }} - {{ $slot['end_time'] }}</span>
                        <span class="text-[10px] font-bold uppercase">{{ $slot['status'] }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    </main>

    <!-- Modal Prenotazione -->
    <div id="bookingModal" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center p-4 z-50">
        <div class="bg-zinc-900 rounded-2xl p-6 w-full max-w-md border border-red-600 shadow-2xl">
            <h3 class="text-xl font-black text-white mb-1 uppercase">Conferma Prenotazione</h3>
            <p class="text-gray-400 text-sm mb-4">Lezione con {{ $coach->name }}</p>

            <form action="{{ route('book', $coach->slug) }}" method="POST">
                @csrf
                <input type="hidden" name="booking_date" value="{{ $selectedDate->toDateString() }}">
                <input type="hidden" name="start_time" id="modalStartTime">

                <div class="mb-5">
                    <label class="block text-xs font-bold text-gray-300 uppercase mb-2">Nome e Cognome Allievo</label>
                    <input type="text" name="client_name" required placeholder="Es. Mario Rossi" 
                           class="w-full bg-black border border-zinc-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-red-600">
                </div>

                <div class="flex space-x-3">
                    <button type="button" onclick="closeModal()" class="w-1/2 py-3 bg-zinc-800 text-white rounded-xl font-bold hover:bg-zinc-700 transition-colors">Annulla</button>
                    <button type="submit" class="w-1/2 py-3 bg-red-600 text-white font-black rounded-xl hover:bg-red-700 transition-colors">CONFERMA</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(time) {
            document.getElementById('modalStartTime').value = time;
            document.getElementById('bookingModal').classList.remove('hidden');
        }
        function closeModal() {
            document.getElementById('bookingModal').classList.add('hidden');
        }
    </script>
</body>
</html>