<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-200 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="min-h-[70vh] flex items-center justify-center bg-gradient-to-br from-slate-100 via-slate-50 to-slate-200 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 py-12 px-4">
        <div class="w-full max-w-md">
            <div class="bg-white dark:bg-slate-800 shadow-xl rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700">
                
                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100">
                        Welkom terug 👋
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Je bent succesvol ingelogt
                    </p>
                </div>

                <!-- Body -->
                <div class="px-6 py-6 text-center">
                    <div class="mx-auto mb-4 flex items-center justify-center w-14 h-14 rounded-full bg-blue-100 dark:bg-blue-900/30">
                        <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>

                    <p class="text-slate-700 dark:text-slate-300 text-sm">
                        Alles staat voor je klaar, en je hebt nu toegang tot je functies en data!
                    </p>
                </div>

                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/40 flex justify-center">
                        @if (Auth::check())
                            @if (Auth::user()->name == 'planner')
                                <a href="{{ route('simulation.dashboard') }}"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium shadow transition">
                                    Ga naar simulatie →
                                </a>

                            @elseif ( Auth::user()->name == 'Manager')
                                <a href="{{ route('library.manage') }}"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium shadow transition">
                                    ga naar de functie bibliotheek →
                                </a>

                            @elseif (Auth::user()->name == 'policy_maker')
                                <a href="{{ route('simulation.dashboard') }}"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium shadow transition">
                                Ga naar simulatie →
                                </a>

                            @elseif (Auth::user()->name == 'admin')
                                <a href="{{ route('simulation.dashboard') }}"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium shadow transition">
                                    Ga naar simulatie →
                                </a>
                            @else
                                <span>{{ Auth::user()->name }}    
                            @endif
                        @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
