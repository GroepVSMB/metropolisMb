<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Event Bewerken: {{ $event->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">
                <form action="{{ route('events.update', $event->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Basis Info --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block font-medium text-sm text-gray-700">Event Naam</label>
                            <input type="text" name="name" value="{{ $event->name }}" required class="w-full border-gray-300 rounded shadow-sm">
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700">Type</label>
                            <select name="type" class="w-full border-gray-300 rounded shadow-sm">
                                <option value="one_off" {{ $event->type == 'one_off' ? 'selected' : '' }}>Eenmalig</option>
                                <option value="recurring" {{ $event->type == 'recurring' ? 'selected' : '' }}>Terugkerend</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700">Duur (minuten)</label>
                            <input type="number" name="duration_minutes" value="{{ $event->duration_minutes }}" required class="w-full border-gray-300 rounded shadow-sm">
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700">Herhaal Interval (optioneel, min)</label>
                            <input type="number" name="recurrence_interval_minutes" value="{{ $event->recurrence_interval_minutes }}" class="w-full border-gray-300 rounded shadow-sm">
                        </div>
                    </div>

                    <hr class="my-6">

                    {{-- Impacts Sectie --}}
                    <h3 class="font-bold text-lg mb-3">Definieer Impact per Kwaliteits Metriek</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {{-- CHANGED: Loop through metrics --}}
                        @foreach($metrics as $metric)
                            <div class="flex items-center justify-between bg-gray-50 p-3 rounded border">
                                <span class="font-medium text-gray-700">{{ $metric->name }}</span>
                                <div class="flex items-center">
                                    <span class="text-gray-400 mr-2 text-xs">Aanpassing:</span>
                                    <input type="number"
                                           name="impacts[{{ $metric->id }}]"
                                           {{-- Uses pre-filled array from controller --}}
                                           value="{{ $currentImpacts[$metric->id] ?? '' }}"
                                           placeholder="0"
                                           class="w-20 border-gray-300 rounded shadow-sm text-right focus:border-metro-darkred focus:ring-metro-darkred">
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('events.index') }}" class="text-gray-600 px-4 py-2 hover:underline flex items-center">Annuleren</a>
                        <button type="submit" class="bg-metro-darkred text-white font-bold px-4 py-2 rounded shadow hover:bg-red-700">
                            Bijwerken
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
