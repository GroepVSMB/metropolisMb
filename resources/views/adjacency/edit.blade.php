<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Incompatibele Categorie Bewerken') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm rounded-lg">

                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <ul class="text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Formulier voor bewerken --}}
                <form action="{{ route('adjacency.update', $rule->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Categorie --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Categorie
                        </label>
                        <select
                            name="category_id"
                            class="shadow border rounded w-full py-2 px-3 text-gray-700"
                            required
                        >
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $cat->id == $rule->category_id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Incompatibele categorie --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Incompatibel met
                        </label>
                        <select
                            name="incompatible_category_id"
                            class="shadow border rounded w-full py-2 px-3 text-gray-700"
                            required
                        >
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $cat->id == $rule->incompatible_category_id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Acties --}}
                    <div class="flex items-center justify-between">
                        <button
                            type="submit"
                            class="bg-metro-darkred hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Opslaan
                        </button>

                        <a href="{{ route('adjacency.index') }}"
                            class="text-gray-500 text-sm hover:underline">
                            Annuleren
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>
