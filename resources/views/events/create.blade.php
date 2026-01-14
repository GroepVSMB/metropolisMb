<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nieuw Event</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">
                <form action="{{ route('events.store') }}" method="POST">
                    @csrf

                    {{-- Basis Info --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block font-medium text-sm text-gray-700">Event Naam</label>
                            <input type="text" name="name" required class="w-full border-gray-300 rounded shadow-sm focus:ring-metro-darkred focus:border-metro-darkred">
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700">Type</label>
                            <select name="type" class="w-full border-gray-300 rounded shadow-sm">
                                <option value="one_off">Eenmalig</option>
                                <option value="recurring">Terugkerend</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700">Duur (minuten)</label>
                            <input type="number" name="duration_minutes" value="60" required class="w-full border-gray-300 rounded shadow-sm">
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700">Herhaal Interval (optioneel, min)</label>
                            <input type="number" name="recurrence_interval_minutes" class="w-full border-gray-300 rounded shadow-sm">
                        </div>
                    </div>

                    <hr class="my-6">

                    {{-- Impacts Sectie --}}
                    <h3 class="font-bold text-lg mb-3">Definieer Impact per Kwaliteits Metriek</h3>
                    <p class="text-sm text-gray-500 mb-4">Vul een getal in (bijv. 10 of -20) bij de metrieken die beïnvloed worden.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {{-- CHANGED: Loop through metrics --}}
                        @foreach($metrics as $metric)
                            <div class="flex items-center justify-between bg-gray-50 p-3 rounded border">
                                <span class="font-medium text-gray-700">{{ $metric->name }}</span>
                                <div class="flex items-center">
                                    <span class="text-gray-400 mr-2 text-xs">Aanpassing:</span>
                                    <input type="number"
                                           name="impacts[{{ $metric->id }}]"
                                           placeholder="0"
                                           class="w-20 border-gray-300 rounded shadow-sm text-right focus:border-metro-darkred focus:ring-metro-darkred">
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-metro-darkred text-white px-4 py-2 rounded shadow hover:bg-red-700 font-bold">
                            Opslaan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </x-app-layout>
