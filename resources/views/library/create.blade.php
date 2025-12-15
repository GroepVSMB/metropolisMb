<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Functie Toevoegen') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm rounded-lg">

                {{-- Removed enctype="multipart/form-data" because we are not uploading files anymore --}}
                <form action="{{ route('library.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Naam</label>
                        <input type="text" name="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Categorie</label>
                        <select name="category_id" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Leefbaarheid Score (Getal)</label>
                        <input type="number" name="livability_number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Afbeelding URL</label>
                        <input type="url"
                               name="image"
                               placeholder="https://example.com/plaatje.jpg"
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Plak hier een link naar een afbeelding op internet.</p>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" class="bg-metro-darkred hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Opslaan
                        </button>
                        <a href="{{ route('library.manage') }}" class="text-gray-500 text-sm hover:underline">Annuleren</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
