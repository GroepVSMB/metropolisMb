<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Bibliotheek Beheer</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-end mb-4">
                <a href="{{ route('library.create') }}" class="bg-metro-darkred text-white font-bold px-4 py-2 rounded shadow hover:bg-red-700">
                    + Nieuw Item
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="p-3">Naam</th>
                            <th class="p-3">Categorie</th>
                            <th class="p-3">Impacts (Kwaliteit)</th>
                            <th class="p-3 text-right">Acties</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y">
                        @foreach($functions as $function)
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 font-medium">{{ $function->name }}</td>
                                <td class="p-3">
                                        <span class="px-2 py-1 rounded text-xs font-bold"
                                              style="background-color: {{ $function->category->color_hex }}20; color: {{ $function->category->color_hex }}">
                                            {{ $function->category->name }}
                                        </span>
                                </td>
                                <td class="p-3">
                                    <div class="flex flex-wrap gap-2">
                                        @forelse($function->impacts as $impact)
                                            <span class="px-2 py-1 rounded text-xs border {{ $impact->impact > 0 ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700' }}">
                                                    <strong>{{ $impact->qualityMetric->name ?? '?' }}:</strong>
                                                    {{ $impact->impact > 0 ? '+' : '' }}{{ $impact->impact }}
                                                </span>
                                        @empty
                                            <span class="text-gray-400 italic text-xs">Geen impact</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="p-3 text-right space-x-2">
                                    <a href="{{ route('library.edit', $function->id) }}" class="text-blue-600 hover:underline">Bewerken</a>
                                    <form action="{{ route('library.destroy', $function->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Verwijderen?');">
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
