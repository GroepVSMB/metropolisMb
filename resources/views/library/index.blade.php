<x-app-layout>
    {{-- Page Header --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Bibliotheek') }}
        </h2>
    </x-slot>

    {{-- 1. Initialize AlpineJS State --}}
    <div class="py-12 bg-gray-50 min-h-screen" x-data="{ showModal: false, activeItem: null }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Loop through the Groups (Categories) --}}
            @foreach($groupedFunctions as $categoryName => $items)
                <div class="mb-12">
                    {{-- Category Title --}}
                    <div class="flex items-center mb-6 border-b border-gray-200 pb-2">
                        <h3 class="text-2xl font-bold text-gray-800 tracking-tight mr-4">
                            {{ $categoryName }}
                        </h3>
                        <span class="text-sm font-medium text-gray-400 bg-gray-100 px-2 py-1 rounded-full">
                            {{ count($items) }} items
                        </span>
                    </div>

                    {{-- Responsive Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

                        @foreach($items as $function)
                            {{-- 2. FIXED CLICK HANDLER --}}
                            {{-- We find the item in the JS array by ID, preventing syntax errors --}}
                            <div @click="activeItem = window.cityFunctions.find(f => f.id === {{ $function->id }}); showModal = true"
                                 class="group bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 overflow-hidden flex flex-col h-full transform hover:-translate-y-1 cursor-pointer">

                                {{-- Image Section --}}
                                <div class="relative h-48 bg-gray-100 overflow-hidden">
                                    @if($function->image)
                                        <img src="{{ $function->image }}"
                                             alt="{{ $function->name }}"
                                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="hidden w-full h-full flex items-center justify-center bg-gray-200 text-gray-400">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gray-200 text-gray-400">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    @endif

                                    <div class="absolute top-3 right-3">
                                        <span class="px-2 py-1 text-xs font-bold uppercase tracking-wider text-white rounded shadow-sm"
                                              style="background-color: {{ $function->category->color_hex ?? '#999' }}">
                                            {{ $function->category->name ?? 'Unknown' }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Content Section --}}
                                <div class="p-5 flex flex-col flex-grow">
                                    <h4 class="font-bold text-lg text-gray-800 mb-2 group-hover:text-metro-darkred transition-colors">
                                        {{ $function->name }}
                                    </h4>

                                    <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                                        <div class="flex items-center text-sm">
                                            <span class="font-medium text-gray-600">Leefbaarheid:</span>
                                            <span class="ml-1 font-bold {{ $function->livability_number >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $function->livability_number }}
                                            </span>
                                        </div>
                                        <span class="text-xs text-blue-500 hover:underline">Details &rarr;</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            @if($groupedFunctions->isEmpty())
                <div class="text-center py-20">
                    <h3 class="text-lg font-medium text-gray-900">Nog geen functies</h3>
                </div>
            @endif

        </div>

        {{-- 3. THE MODAL --}}
        <div x-show="showModal"
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">

            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

                {{-- Backdrop --}}
                <div x-show="showModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                     @click="showModal = false" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal Panel --}}
                <div x-show="showModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">

                                <h3 class="text-2xl leading-6 font-bold text-gray-900 mb-4" id="modal-title" x-text="activeItem?.name"></h3>

                                <div class="mb-4 bg-gray-100 rounded-lg overflow-hidden h-64 border border-gray-200">
                                    <template x-if="activeItem?.image">
                                        <img :src="activeItem?.image" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!activeItem?.image">
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">Geen afbeelding</div>
                                    </template>
                                </div>

                                <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase font-bold">Categorie</p>
                                        {{-- Fixed: matches controller JSON structure (string) --}}
                                        <p class="text-sm font-medium text-gray-900" x-text="activeItem?.category || 'Onbekend'"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase font-bold">Leefbaarheid Impact</p>
                                        {{-- Fixed: matches controller JSON key 'livability' --}}
                                        <p class="text-lg font-bold"
                                           :class="activeItem?.livability >= 0 ? 'text-green-600' : 'text-red-600'"
                                           x-text="activeItem?.livability"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase font-bold">Database ID</p>
                                        <p class="text-sm font-mono text-gray-600" x-text="activeItem?.id"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase font-bold">Aangemaakt op</p>
                                        <p class="text-sm text-gray-600" x-text="activeItem?.created_at ? new Date(activeItem.created_at).toLocaleDateString() : '-'"></p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">

                        @if(Auth::check() && Auth::user()->hasRole('manager'))
                            <a :href="'/library/' + activeItem?.id + '/edit'"
                               class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-metro-darkred text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Bewerken
                            </a>
                        @endif

                        <button type="button"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                @click="showModal = false">
                            Sluiten
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- THIS SCRIPT IS REQUIRED for the modal to work --}}
    <script>
        window.cityFunctions = @json($jsFunctionsData ?? []);
    </script>
</x-app-layout>
