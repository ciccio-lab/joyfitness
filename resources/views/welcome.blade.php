<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Joy Fitness - Prenotazione Lezioni</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full text-center">
        <!-- Logo Joy Fitness -->
        <div class="mb-10">
           <img src="{{ asset('images/logo.png') }}" alt="Joy Fitness Logo">
            <h1 class="text-3xl font-black text-white tracking-widest uppercase">JOY <span class="text-red-600">FITNESS</span></h1>
            <p class="text-gray-400 text-sm mt-2">Scegli il tuo Personal Trainer per prenotare la lezione</p>
        </div>

        <!-- Cards dei Coach -->
        <div class="space-y-4">
            @foreach($coaches as $coach)
                <a href="{{ route('calendar', $coach->slug) }}" 
                   class="block p-6 bg-zinc-900 rounded-2xl border border-zinc-800 hover:border-red-600 transition-all text-center shadow-2xl group hover:-translate-y-1">
                    <h2 class="text-2xl font-black text-white group-hover:text-red-600 transition-colors uppercase tracking-wide">{{ $coach->name }}</h2>
                    <span class="inline-block mt-3 text-xs font-bold text-red-600 border border-red-600/40 px-4 py-2 rounded-full uppercase tracking-wider group-hover:bg-red-600 group-hover:text-white transition-all">
                        Prenota Ora &rarr;
                    </span>
                </a>
            @endforeach
        </div>

        <!-- Footer / Link Area Riservata Coach -->
        <div class="mt-12 pt-6 border-t border-zinc-800 text-center">
            <a href="{{ route('coach.login') }}" 
               class="inline-flex items-center text-xs font-bold text-zinc-500 hover:text-red-600 transition-colors uppercase tracking-widest">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Area Riservata Coach
            </a>
        </div>
    </div>
</body>
</html>