<!-- Nel ciclo degli slot della vista allievo -->
@foreach($slots as $slot)
<div class="bg-zinc-900 p-4 rounded-xl border border-zinc-800">
    <div class="flex justify-between items-center mb-2">
        <span class="text-base font-bold text-white">{{ $slot['time'] }}</span>
        <span class="text-xs px-2 py-0.5 rounded font-bold {{ $slot['is_full'] ? 'bg-red-600/20 text-red-500' : 'bg-zinc-800 text-zinc-300' }}">
            {{ $slot['count'] }}/2 Posti
        </span>
    </div>

    <!-- Mostra chi si è già prenotato -->
    <div class="mb-3 space-y-1">
        @foreach($slot['bookings'] as $b)
            <p class="text-xs text-red-400 font-semibold">• Occupato da: {{ $b->client_name }}</p>
        @endforeach
        @if($slot['count'] == 0)
            <p class="text-xs text-zinc-500">Nessuna prenotazione, 2 posti liberi.</p>
        @elseif($slot['count'] == 1)
            <p class="text-xs text-yellow-500">1 posto ancora disponibile!</p>
        @else
            <p class="text-xs text-red-600 font-bold uppercase">Orario Completo</p>
        @endif
    </div>

    <!-- Form di prenotazione visibile se non è pieno -->
    @if(!$slot['is_full'])
        <form action="{{ route('book', $coach->slug) }}" method="POST" class="space-y-2">
            @csrf
            <input type="hidden" name="booking_date" value="{{ $selectedDate->toDateString() }}">
            <input type="hidden" name="start_time" value="{{ $slot['time'] }}">
            <input type="text" name="client_name" placeholder="Il tuo nome e cognome" required 
                   class="w-full p-2 bg-black text-white text-xs rounded border border-zinc-700 focus:border-red-600 focus:outline-none">
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-1.5 rounded text-xs font-bold transition-colors">
                Conferma Prenotazione
            </button>
        </form>
    @endif
</div>
@endforeach