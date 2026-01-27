<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">



    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                    {{-- EVERYONE --}}
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"
                                class="border-transparent text-gray-500 hover:text-metro-darkred hover:border-metro-darkred focus:text-metro-darkred focus:border-metro-darkred {{ request()->routeIs('dashboard') ? '!border-metro-darkred !text-metro-darkred' : '' }}">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <x-nav-link :href="route('library.index')" :active="request()->routeIs('library.index')"
                                class="border-transparent text-gray-500 hover:text-metro-darkred hover:border-metro-darkred focus:text-metro-darkred focus:border-metro-darkred {{ request()->routeIs('library.index') ? '!border-metro-darkred !text-metro-darkred' : '' }}">
                        {{ __('Bibliotheek') }}
                    </x-nav-link>

                    {{-- MANAGER ONLY --}}
                    @if(Auth::check() && Auth::user()->hasRole('manager'))
                        <x-nav-link :href="route('library.manage')" :active="request()->routeIs('library.manage')"
                                    class="border-transparent text-gray-500 hover:text-metro-darkred hover:border-metro-darkred focus:text-metro-darkred focus:border-metro-darkred {{ request()->routeIs('library.manage') ? '!border-metro-darkred !text-metro-darkred' : '' }}">
                            {{ __('Beheer Lijst') }}
                        </x-nav-link>

                        <x-nav-link :href="route('adjacency.index')" :active="request()->routeIs('adjacency.index')"
                                    class="border-transparent text-gray-500 hover:text-metro-darkred hover:border-metro-darkred focus:text-metro-darkred focus:border-metro-darkred {{ request()->routeIs('adjacency.index') ? '!border-metro-darkred !text-metro-darkred' : '' }}">
                            {{ __('Beheer Regels') }}
                        </x-nav-link>

                        <x-nav-link :href="route('library.matrix')" :active="request()->routeIs('library.matrix')"
                                    class="border-transparent text-gray-500 hover:text-metro-darkred hover:border-metro-darkred focus:text-metro-darkred focus:border-metro-darkred {{ request()->routeIs('library.matrix') ? '!border-metro-darkred !text-metro-darkred' : '' }}">
                            {{ __('Effects Matrix') }}
                        </x-nav-link>
                    @endif

                    {{-- PLANNER ONLY --}}
                    @if(Auth::check() && (Auth::user()->hasRole('planner') || Auth::user()->hasRole('policy_maker')))
                        <x-nav-link :href="route('simulation.dashboard')" :active="request()->routeIs('simulation.dashboard')"
                                    class="border-transparent text-gray-500 hover:text-metro-darkred hover:border-metro-darkred focus:text-metro-darkred focus:border-metro-darkred {{ request()->routeIs('simulation.dashboard') ? '!border-metro-darkred !text-metro-darkred' : '' }}">
                            {{ __('Simulatie') }}
                        </x-nav-link>

                    @endif


                    @if(Auth::check() && Auth::user()->hasRole('planner'))
                          <x-nav-link :href="route('events.index')" :active="request()->routeIs('events.index')"
                                    class="border-transparent text-gray-500 hover:text-metro-darkred hover:border-metro-darkred focus:text-metro-darkred focus:border-metro-darkred {{ request()->routeIs('events.index') ? '!border-metro-darkred !text-metro-darkred' : '' }}">
                            {{ __('Events') }}
                        </x-nav-link>
                    @endif

                  
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                
                {{-- === HIER KOMT JE NOTIFICATIE CODE === --}}
                 @if(Auth::check() && Auth::user()->hasRole('planner'))
                    <div class="hidden sm:flex sm:items-center sm:mr-3 relative" x-data="{ open: false }">
                        <button @click="open = ! open" class="relative p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-metro-darkred transition-colors">
                            <span class="sr-only">View notifications</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if(Auth::user()->unreadNotifications->count() > 0)
                                <span class="absolute top-0 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-white bg-red-600 animate-pulse"></span>
                            @endif
                        </button>

                        <div x-show="open" 
                            @click.away="open = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg overflow-hidden z-50 top-8 border border-gray-200 origin-top-right"
                            style="display: none;">
                            
                            <div class="px-4 py-2 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                                <span class="text-sm font-semibold text-gray-700">Meldingen</span>
                                @if(Auth::user()->unreadNotifications->count() > 0)
                                    <form action="{{ route('notifications.markAll') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs text-blue-600 hover:underline hover:text-blue-800">Alles lezen</button>
                                    </form>
                                @endif
                            </div>

                            <div class="max-h-64 overflow-y-auto">
                                @forelse(Auth::user()->unreadNotifications as $notification)
                                    <a href="{{ route('notifications.read', $notification->id) }}" class="block px-4 py-3 hover:bg-blue-50 transition border-b border-gray-50 last:border-0 group">
                                        <p class="text-sm font-bold text-gray-800 group-hover:text-blue-700">
                                            {{ $notification->data['title'] ?? 'Melding' }}
                                        </p>
                                        <p class="text-xs text-gray-500 truncate">
                                            {{ $notification->data['message'] ?? '' }}
                                        </p>
                                        <p class="text-[10px] text-gray-400 mt-1">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                    </a>
                                @empty
                                    <div class="px-4 py-6 text-center text-sm text-gray-500 italic">
                                        Geen nieuwe meldingen.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                 @endif
                {{-- === EINDE NOTIFICATIE CODE === --}}

              
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div class="text-right mr-2">
                                <div class="font-bold">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-gray-400 uppercase tracking-wider">{{ Auth::user()->getRoleLabel() }}</div>
                            </div>
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                             onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('library.index')" :active="request()->routeIs('library.index')">
                {{ __('Bibliotheek') }}
            </x-responsive-nav-link>

            @if(Auth::check() && Auth::user()->hasRole('manager'))
                <x-responsive-nav-link :href="route('library.manage')" :active="request()->routeIs('library.manage')">
                    {{ __('Beheer Lijst') }}
                </x-responsive-nav-link>


                  <x-responsive-nav-link :href="route('adjacency.index')" :active="request()->routeIs('adjacency.index')">
                    {{ __('Beheer Regels') }}
                </x-responsive-nav-link>
            @endif



           @if(Auth::check() && Auth::user()->hasRole(checkRole: 'planner'))
                <x-responsive-nav-link :href="route('simulation.dashboard')" :active="request()->routeIs('simulation.dashboard')">
                    {{ __('Simulatie') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
