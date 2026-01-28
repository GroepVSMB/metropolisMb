<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nieuwe Functie</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">
                <form action="{{ route('library.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Basic Info --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block font-medium text-sm text-gray-700">Naam</label>
                            <input type="text" name="name" required class="w-full border-gray-300 rounded shadow-sm">
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700">Categorie</label>
                            <select name="category_id" class="w-full border-gray-300 rounded shadow-sm">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                       <div class="md:col-span-2">
                            <label class="block font-medium text-sm text-gray-700">Afbeelding Uploaden</label>
                            <input type="file" 
                                name="image" 
                                required 
                                accept="image/png, image/jpeg, image/jpg"
                                class="w-full border-gray-300 rounded shadow-sm">
                            <p class="text-xs text-gray-500 mt-1">Toegestane formaten: JPG, PNG. Max 2MB.</p>
                        </div>
                    </div>

                    <hr class="my-6">

                    {{-- NEW: Metrics Inputs --}}
                    <h3 class="font-bold text-lg mb-3">Impact op Kwaliteit & Tijd</h3>
                    <div class="grid grid-cols-1 gap-4">
                        @foreach($metrics as $metric)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between bg-gray-50 p-3 rounded border">
                                <span class="font-medium text-gray-700 w-1/3" title="{{ $metric->name }}">
                                    {{ $metric->name }}
                                </span>

                                <div class="flex items-center gap-2 mt-2 sm:mt-0">
                                    <input type="number"
                                           name="impacts[{{ $metric->id }}][value]"
                                           placeholder="0"
                                           class="w-24 border-gray-300 rounded shadow-sm text-right focus:border-metro-darkred focus:ring-metro-darkred"
                                    >
                                    
                                    <select name="impacts[{{ $metric->id }}][condition]" class="text-sm border-gray-300 rounded shadow-sm">
                                        <option value="always">Altijd</option>
                                        <option value="day_only">☀️ Alleen Dag</option>
                                        <option value="night_only">🌙 Alleen Nacht</option>
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-metro-darkred text-white font-bold px-4 py-2 rounded shadow hover:bg-red-700">Opslaan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
