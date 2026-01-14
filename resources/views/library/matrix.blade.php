<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Effects Matrix: Impact Configuration') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ searchTerm: '' }">
        <div class="max-w-[95%] mx-auto sm:px-6 lg:px-8">

            {{-- Toolbar --}}
            <div class="flex justify-between items-center mb-6">
                <div class="w-1/3">
                    <input type="text"
                           x-model="searchTerm"
                           placeholder="Search functions..."
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-metro-darkred focus:ring focus:ring-red-200 focus:ring-opacity-50">
                </div>

                <button type="submit" form="matrix-form" class="bg-metro-darkred hover:bg-red-700 text-white font-bold py-2 px-6 rounded shadow-lg transition transform hover:scale-105">
                    💾 Save Changes
                </button>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <form id="matrix-form" action="{{ route('library.matrix.update') }}" method="POST">
                    @csrf

                    <div class="overflow-x-auto max-h-[75vh] relative">
                        <table class="min-w-full divide-y divide-gray-200 text-sm text-left">

                            {{-- Header --}}
                            <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="px-4 py-3 font-bold text-gray-700 uppercase tracking-wider bg-gray-50 sticky left-0 z-20 border-r w-48">
                                    Source Function
                                </th>
                                {{-- Loop through METRICS (Columns) --}}
                                @foreach($metrics as $metric)
                                    <th class="px-2 py-3 font-semibold text-center text-gray-600 w-24 border-l min-w-[100px]">
                                        {{ $metric->name }}
                                    </th>
                                @endforeach
                            </tr>
                            </thead>

                            {{-- Body --}}
                            <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($functions as $func)
                                <tr class="hover:bg-gray-50 transition"
                                    x-show="!searchTerm || '{{ strtolower($func->name) }}'.includes(searchTerm.toLowerCase())">

                                    {{-- Function Name --}}
                                    <td class="px-4 py-2 font-medium text-gray-900 sticky left-0 bg-white z-10 border-r flex items-center gap-2 h-12">
                                        @if($func->image)
                                            <img src="{{ $func->image }}" class="w-6 h-6 rounded object-cover">
                                        @endif
                                        {{ $func->name }}
                                    </td>

                                    {{-- Impact Inputs --}}
                                    @foreach($metrics as $metric)
                                        @php
                                            $val = $func->getImpactOn($metric->id);
                                        @endphp
                                        <td class="p-1 border-l text-center bg-gray-50">
                                            {{-- Added: White background, gray border, hover effect --}}
                                            <input type="number"
                                                   name="matrix[{{ $func->id }}][{{ $metric->id }}]"
                                                   value="{{ $val }}"
                                                   class="w-full border border-gray-300 rounded shadow-sm py-1 px-1 text-center font-mono font-bold focus:ring-2 focus:ring-metro-darkred focus:border-metro-darkred
                                                              {{ $val > 0 ? 'text-green-600 bg-green-50' : ($val < 0 ? 'text-red-600 bg-red-50' : 'text-gray-400 bg-white') }}"
                                                   onfocus="this.select()"
                                            >
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>

            {{-- Helper Text --}}
            @if($metrics->isEmpty())
                <div class="text-center p-10 bg-red-50 border border-red-200 rounded mt-4">
                    <h3 class="text-red-800 font-bold">No Metrics Found</h3>
                    <p class="text-red-600">Please run <code>php artisan db:seed --class=QualityMetricSeeder</code> to create the columns.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
