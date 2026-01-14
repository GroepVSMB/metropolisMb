<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Event Configuratie') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex justify-end mb-4">
                <a href="{{ route('events.create') }}" class="bg-metro-darkred text-white font-bold px-4 py-2 rounded shadow hover:bg-red-700 transition">
                    + Nieuw Event
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="p-3">Naam</th>
                            <th class="p-3">Type</th>
                            <th class="p-3">Duur</th>
                            <th class="p-3">Impacts (op Kwaliteit)</th>
                            <th class="p-3 text-right">Acties</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y">
                        @foreach($events as $event)
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 font-medium">{{ $event->name }}</td>
                                <td class="p-3">
                                        <span class="px-2 py-1 rounded text-xs {{ $event->type === 'recurring' ? 'bg-purple-100 text-purple-700' : 'bg-green-100 text-green-700' }}">
                                            {{ $event->type }}
                                        </span>
                                </td>
                                <td class="p-3">{{ $event->duration_minutes }} min</td>
                                <td class="p-3 text-xs text-gray-500">
                                    @foreach($event->impacts as $impact)
                                        {{-- CHANGED: Uses qualityMetric relationship --}}
                                        <div class="mb-1">
                                            <span class="font-semibold">{{ $impact->qualityMetric->name ?? 'Unknown' }}:</span>
                                            <span class="{{ $impact->impact > 0 ? 'text-green-600' : 'text-red-600' }} font-bold">
                                                    {{ $impact->impact > 0 ? '+' : '' }}{{ $impact->impact }}
                                                </span>
                                        </div>
                                    @endforeach
                                </td>
                                <td class="p-3 text-right space-x-2">
                                    <a href="{{ route('events.edit', $event->id) }}" class="text-blue-600 hover:underline">Bewerken</a>
                                    <form action="{{ route('events.destroy', $event->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Weet je het zeker?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Verwijder</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
