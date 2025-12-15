<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Functie Bewerken') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm rounded-lg">

                <form action="{{ route('library.update', $function->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Naam</label>
                        <input type="text" name="name" value="{{ $function->name }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Categorie</label>
                        <select name="category_id" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $function->category_id == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Leefbaarheid Score</label>
                        <input type="number" name="livability_number" value="{{ $function->livability_number }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Afbeelding URL</label>
                        <input type="url"
                               name="image"
                               value="{{ $function->image }}"
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                               required>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Huidige Afbeelding:</label>
                        <div class="p-2 border border-gray-200 rounded bg-gray-50 inline-block">
                            <img src="{{ $function->image }}" alt="Preview" class="h-20 w-auto object-cover rounded">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-metro-darkred hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Bijwerken
                        </button>
                        <a href="{{ route('library.manage') }}" class="text-gray-500 text-sm hover:underline">Annuleren</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
