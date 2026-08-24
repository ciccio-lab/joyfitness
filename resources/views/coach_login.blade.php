<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Area Riservata Coach - Joy Fitness</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-zinc-900 p-8 rounded-2xl border border-red-600 shadow-2xl">
        <div class="text-center mb-6">
            <a href="{{ route('home') }}" class="text-xs text-zinc-500 hover:text-white font-bold uppercase tracking-wider block mb-3">&larr; Torna al sito</a>
            <h1 class="text-2xl font-black text-white uppercase">Area <span class="text-red-600">Coach</span></h1>
            <p class="text-xs text-zinc-400 mt-1">Accedi per gestire le tue lezioni e disponibilità</p>
        </div>

        @if(session('error'))
            <div class="p-3 mb-4 bg-red-600/20 border border-red-600 text-red-500 rounded-xl text-xs text-center font-bold">
                {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div class="p-3 mb-4 bg-zinc-800 border border-zinc-700 text-zinc-300 rounded-xl text-xs text-center font-bold">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('coach.login.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-zinc-400 mb-1">Seleziona il tuo profilo</label>
                <select name="slug" class="w-full bg-black border border-zinc-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-red-600">
                    @foreach($coaches as $coach)
                        <option value="{{ $coach->slug }}">{{ $coach->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-zinc-400 mb-1">Password</label>
                <input type="password" name="password" required placeholder="••••••••" 
                       class="w-full bg-black border border-zinc-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-red-600">
            </div>

            <button type="submit" class="w-full py-3 bg-red-600 font-black rounded-xl text-white hover:bg-red-700 transition-colors uppercase tracking-wider">
                Accedi
            </button>
        </form>
    </div>
</body>
</html>