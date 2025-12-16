<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Library Management') }}
            </h2>
            <a href="{{ route('adjacency.create') }}" class="bg-metro-darkred hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">
                + Nieuwe Regel
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categorie</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Incompatibel met</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acties</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($rules as $rule)
                            <tr>
                                {{-- Linker categorie --}}
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full text-white"
                                        style="background-color: {{ $rule->category->color_hex ?? '#ccc' }}">
                                        {{ $rule->category->name }}
                                    </span>
                                </td>

                                {{-- Incompatibele categorie --}}
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full text-white"
                                        style="background-color: {{ $rule->incompatibleCategory->color_hex ?? '#ccc' }}">
                                        {{ $rule->incompatibleCategory->name }}
                                    </span>
                                </td>

                                {{-- Acties --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('adjacency.edit', $rule->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Bewerken</a>
                                    <form
                                        action="{{ route('adjacency.destroy', $rule->id) }}"
                                        method="POST"
                                        class="inline-block"
                                        onsubmit="return confirm('Weet je zeker dat je deze regel wilt verwijderen?');">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            Verwijderen
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
