<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Coach - Joy Fitness</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-zinc-900 p-8 rounded-2xl border border-zinc-800 shadow-2xl space-y-6">
        <div class="text-center">
            <h1 class="text-2xl font-black text-red-600 uppercase">Area Coach</h1>
            <p class="text-xs text-zinc-400 mt-1">Accedi per gestire le tue lezioni e gli orari</p>
        </div>

        @if(session('error'))
            <div class="p-3 bg-red-600/20 border border-red-600 text-red-500 rounded-xl text-center text-xs font-bold">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('coach.login.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-zinc-400 mb-1">Seleziona il tuo profilo</label>
                <select name="slug" required class="w-full bg-black border border-zinc-700 rounded-xl p-3 text-white text-sm focus:border-red-600 focus:outline-none">
                    <option value="" disabled selected>-- Scegli Coach --</option>
                    @foreach($coaches as $coach)
                        <option value="{{ $coach->slug }}">{{ $coach->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-zinc-400 mb-1">Password</label>
                <input type="password" name="password" required placeholder="Inserisci password" 
                       class="w-full bg-black border border-zinc-700 rounded-xl p-3 text-white text-sm focus:border-red-600 focus:outline-none">
            </div>

            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl transition-colors uppercase tracking-wider text-sm shadow-lg shadow-red-600/30">
                Accedi al Pannello
            </button>
        </form>

        <div class="text-center pt-2">
            <a href="{{ route('home') }}" class="text-xs text-zinc-500 hover:text-zinc-300 transition-colors">← Torna alla Home</a>
        </div>
    </div>
</body>
</html>