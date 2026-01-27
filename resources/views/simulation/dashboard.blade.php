<x-app-layout>

    <style>
        .simulation-container { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .scroller::-webkit-scrollbar { width: 6px; }
        .scroller::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 4px; }
        .scroller::-webkit-scrollbar-track { background-color: #f1f5f9; }

        .dragging { opacity: 0.5; }

        .drag-over-valid {
            border-color: #22c55e !important; background-color: #f0fdf4 !important;
            border-width: 2px !important; transform: scale(1.02);
        }

        .drag-over-invalid {
            border-color: #ef4444 !important; background-color: #fef2f2 !important;
            border-width: 2px !important; transform: scale(1.02); cursor: not-allowed;
        }

        .function-item { position: relative; }

         .function-item img
        {
            width: min(160px, 20vw);
            height: min(160px, 20vw);
        }
        .new-badge {
            position: absolute; top: -5px; right: -5px;
            background-color: #ff4757; color: white;
            font-size: 10px; font-weight: bold; padding: 2px 6px;
            border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            z-index: 10; pointer-events: none;
        }

        .metric-bar { transition: width 0.5s ease-in-out, background-color 0.5s; }

        /* --- PERFORMANCE FIX: LICHTERE HIGHLIGHTS --- */
        .neighbor-highlight {
            position: relative;
            z-index: 1;
        }

        .neighbor-highlight::after {
            content: '';
            position: absolute;
            inset: 0;
            background-color: rgba(255, 255, 0, 0.25); /* Gele gloed (Statisch) */
            border: 2px solid rgba(255, 215, 0, 0.5);
            z-index: 5; 
            pointer-events: none; /* Muis clicks gaan er doorheen */
            border-radius: 0.125rem;
        }

        /* NEW: Critical for tooltips/overlays to work */
        .pointer-events-none {
            pointer-events: none !important;
        }

        /* Ensure tooltip is always on top but doesn't block mouse */
        #hover-tooltip {
            z-index: 9999 !important;
            pointer-events: none !important;
        }

       @keyframes flashHighlight {
            0% { 
                background-color: rgba(157, 26, 26, 0.1);
                border-color: rgb(157, 26, 26);
                transform: scale(1.02); 
                box-shadow: 0 4px 6px -1px rgba(157, 26, 26, 0.2);
            }
            50% { 
                background-color: rgba(157, 26, 26, 0.2);
                border-color: rgb(157, 26, 26); 
                transform: scale(1.02);
            }
            100% { 
                background-color: #f9fafb;
                border-color: #e5e7eb;
                transform: scale(1); 
            }
        }

        .flash-target {
            animation: flashHighlight 2s ease-out forwards;
            z-index: 50;
            position: relative;
        }

        /* --- HOLD TO DELETE STYLES --- */
        .cell-content {
            position: relative;
            overflow: hidden;
        }

        /* De rode overlay die groeit */
        .delete-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 0%; /* Start leeg */
            background-color: rgba(220, 38, 38, 0.8); /* Metro Rood */
            z-index: 20;
            transition: height 0s; /* Standaard geen animatie bij reset */
            pointer-events: none; /* ESSENTIEEL: Blokkeert mouseleave niet */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Icon in de overlay */
        .delete-overlay::after {
            content: '🗑️';
            font-size: 1.5rem;
            opacity: 0;
            transition: opacity 0.2s;
        }

        /* De active state: Dit wordt via JS toegevoegd */
        .is-holding .delete-overlay {
            height: 100%; /* Groei naar vol */
            transition: height 0.8s linear; /* Duurt 0.8 seconden */
        }

        .is-holding .delete-overlay::after {
            opacity: 1;
        }

        /* Klein schud-effect tijdens het vasthouden */
        .is-holding {
            animation: shake 0.2s infinite;
        }

        @keyframes shake {
            0% { transform: rotate(0deg); }
            25% { transform: rotate(1deg); }
            75% { transform: rotate(-1deg); }
            100% { transform: rotate(0deg); }
        }

        /* --- KEYBOARD ACCESSIBILITY STYLES --- */
/* Duidelijke focus ring voor keyboard navigatie */
.function-item:focus-visible, 
.cell-content:focus-visible {
    outline: 3px solid #3b82f6; /* Helder blauw */
    outline-offset: 2px;
    z-index: 50;
}

/* Wanneer een item in de lijst is 'geselecteerd' met Enter */
.keyboard-selected {
    background-color: #fee2e2 !important; /* Licht rood */
    border-color: #b91c1c !important;     /* Donker rood */
    box-shadow: 0 0 0 3px rgba(185, 28, 28, 0.3);
}

/* Wanneer een item geselecteerd is, verander de cursor op het grid */
.keyboard-mode-active .cell-content {
    cursor: copy; /* Visuele hint */
}
    </style>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-metro-darkred leading-tight uppercase tracking-wide">
            {{ __('Simulation Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 simulation-container">
        <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8">

          {{-- MELDING TOAST (Dynamisch: Rood of Blauw) --}}
            <div id="toast-message" class="hidden fixed top-20 right-5 border px-4 py-3 rounded shadow-lg z-50 transition-opacity duration-300 flex items-center bg-red-100 border-red-400 text-red-700">
                <svg id="toast-icon-error" class="w-6 h-6 mr-2 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <svg id="toast-icon-info" class="w-6 h-6 mr-2 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                
                <div>
                    <strong id="toast-title" class="font-bold">Melding</strong>
                    <span class="block text-sm" id="toast-text">Tekst hier.</span>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-6 items-start">

                {{-- KOLOM 1: Sidebar --}}
                <aside class="w-full lg:w-1/4 min-w-[250px] bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 h-[calc(100vh-200px)] flex flex-col">
                    <h2 class="text-metro-darkred text-lg font-bold border-b-2 border-gray-100 pb-2 mb-4">
                        Beschikbare Functies
                    </h2>

                    <div class="overflow-y-auto scroller flex-1 pr-2">
                        <div class="space-y-6">
                            @foreach($groupedFunctions as $categoryName => $catFunctions)
                                <div>
                                    <h3 class="text-xs uppercase font-bold text-gray-400 mb-2 tracking-wider">{{ $categoryName }}</h3>
                                    <ul class="space-y-2">
                                        @foreach($catFunctions as $function)
                                          <li draggable="true"
    id="function-{{ $function['id'] }}"
    {{-- ACCESSIBILITY ATTRIBUTES --}}
    tabindex="0"
    role="button"
    aria-label="{{ $function['name'] }}. Druk op Enter om te selecteren."
    onkeydown="handleSidebarKey(event, {{ $function['id'] }})"
    {{-- END ACCESSIBILITY --}}
    
    ondragstart="drag(event, {{ $function['id'] }})"
    onmousedown="acknowledgeFunction({{ $function['id'] }})"
    class="function-item group flex items-center p-2 bg-gray-50 rounded border border-gray-200 cursor-grab active:cursor-grabbing hover:border-metro-darkred hover:shadow-sm transition-all select-none relative focus:outline-none">

                                                @php
                                                    $showBadge = false;
                                                    if (isset($function['is_new'])) {
                                                        $showBadge = $function['is_new'];
                                                    } elseif (isset($function['acknowledged_by_users_exists'])) {
                                                        $showBadge = !$function['acknowledged_by_users_exists'];
                                                    }
                                                @endphp

                                                @if($showBadge)
                                                    <span id="badge-{{ $function['id'] }}" class="new-badge">NIEUW</span>
                                                @endif

                                                @if($function['image'])
                                                    <img src="{{ $function['image'] }}" class="w-10 h-10 rounded mr-3 object-cover border border-gray-200">
                                                @else
                                                    <span class="w-10 h-10 rounded mr-3 bg-gray-200 block"></span>
                                                @endif

                                                <span class="font-medium text-gray-700 text-sm group-hover:text-metro-darkred">{{ $function['name'] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </aside>

                {{-- KOLOM 2: The Grid --}}
               {{-- KOLOM 2: The Grid --}}
                <section class="w-full lg:w-2/4 flex flex-col bg-white shadow-sm sm:rounded-lg p-6 relative" x-data="{ showHelp: false }">
                    
                    {{-- HEADER & HELP KNOP --}}
                    <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-2">
                        <h3 class="font-bold text-gray-700 text-lg">Stadsindeling</h3>
                        <button @click="showHelp = !showHelp" 
                                class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center bg-blue-50 px-3 py-1 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Instructies & Controls
                        </button>
                    </div>

                    {{-- INSTRUCTIE PANEEL (Inklapbaar) --}}
                    <div x-show="showHelp" 
                         style="display: none;"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-4 text-sm text-gray-700 shadow-inner">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Muis Instructies --}}
                            <div>
                                <h4 class="font-bold text-blue-800 mb-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
                                    Muis
                                </h4>
                                <ul class="list-disc list-inside space-y-1 text-xs text-gray-600 ml-1">
                                    <li><strong>Slepen:</strong> Sleep functies van links naar het grid.</li>
                                    <li><strong>Verwijderen:</strong> Houd een kavel <span class="font-bold text-red-600">ingedrukt</span> (long-press).</li>
                                    <li><strong>Details:</strong> Beweeg muis over een kavel voor info.</li>
                                </ul>
                            </div>

                            {{-- Toetsenbord Instructies --}}
                            <div>
                                <h4 class="font-bold text-blue-800 mb-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                                    Toetsenbord
                                </h4>
                                <ul class="space-y-1 text-xs text-gray-600">
                                    <li><kbd class="bg-white border rounded px-1 font-mono">Tab</kbd> Navigeren tussen items.</li>
                                    <li><kbd class="bg-white border rounded px-1 font-mono">Enter</kbd> Selecteer item (links) of Plaats item (grid).</li>
                                    <li><kbd class="bg-white border rounded px-1 font-mono">Del</kbd> Kavel leegmaken.</li>
                                    <li><kbd class="bg-white border rounded px-1 font-mono">Esc</kbd> Selectie annuleren.</li>
                                </ul>
                            </div>
                        </div>
                        <p class="text-[10px] text-blue-400 mt-2 italic border-t border-blue-200 pt-1">
                            Tip: Gebruik de toegankelijkheidsknop rechtsonder voor vergroting of voorleeshulp.
                        </p>
                    </div>

                    {{-- LIVE FEEDBACK (Bestaand) --}}
                    <div id="live-feedback" class="fixed top-28 left-1/2 transform -translate-x-1/2 z-[100] w-auto min-w-[300px] text-center hidden pointer-events-none transition-all duration-200"></div>
                    
                    {{-- THE GRID CONTAINER --}}
                    <div class="bg-[#eef2f5] p-2 lg:p-5 rounded-lg shadow-inner w-full box-border border border-gray-200 flex flex-col items-center justify-center">
                        <div class="grid grid-cols-4 grid-rows-3 gap-2 w-full aspect-[4/3]">
                            @for($i = 0; $i < 12; $i++)
                                <div id="cell-{{ $i }}"
                                     {{-- ACCESSIBILITY ATTRIBUTES --}}
                                     tabindex="0"
                                     role="button"
                                     aria-label="Kavel {{ $i + 1 }}. Druk op Enter om te plaatsen, Delete om te verwijderen."
                                     onkeydown="handleGridKey(event, {{ $i }})"
                                     
                                     {{-- MOUSE EVENTS --}}
                                     onmousedown="startHold(event, {{ $i }})"
                                     onmouseup="cancelHold({{ $i }})"
                                     onmouseleave="cancelHold({{ $i }}); hideTooltip()"
                                     
                                     {{-- TOUCH EVENTS --}}
                                     ontouchstart="startHold(event, {{ $i }})"
                                     ontouchend="cancelHold({{ $i }})"

                                     {{-- DROP EVENTS --}}
                                     ondrop="drop(event, {{ $i }})"
                                     ondragover="allowDrop(event, {{ $i }})"
                                     ondragleave="leaveDrag({{ $i }})"

                                     {{-- TOOLTIP --}}
                                     onmouseenter="showTooltip(event, {{ $i }})"
                                     
                                     class="bg-white border border-gray-300 flex flex-col items-center justify-center text-center cursor-pointer text-xs lg:text-sm text-gray-400 transition-all select-none p-1 overflow-hidden active:scale-95 relative rounded-sm shadow-sm hover:border-metro-darkred cell-content focus:outline-none">
                                    
                                    <div id="overlay-{{ $i }}" class="delete-overlay"></div>
                                    <span class="z-10 pointer-events-none">Kavel {{ $i + 1 }}</span>
                                </div>
                            @endfor
                        </div>
                    </div>
                </section>

                {{-- KOLOM 3: Score & Metrics --}}
                <aside class="w-full lg:w-1/4 min-w-[250px] flex flex-col gap-5">

                    {{-- 1. GLOBAL AVERAGE SCORE CARD --}}
                    <article class="bg-[#448a28] p-5 text-white font-bold flex justify-between items-center shadow-lg rounded-lg transform hover:scale-105 transition-transform relative overflow-hidden">
                        <div class="flex flex-col z-10">
                            <span class="uppercase text-xs opacity-80 tracking-wider">Gemiddelde</span>
                            <span class="text-xl">Leefbaarheid</span>
                        </div>

                        <div class="flex flex-col items-end z-10">
                            <span id="global-score-val" class="text-4xl font-black transition-all duration-300">100</span>
                            <span id="score-delta" class="text-sm font-bold opacity-0 transition-all duration-500 transform translate-y-2 bg-white/20 px-2 rounded backdrop-blur-sm">-</span>
                        </div>

                        <div id="score-flash" class="absolute inset-0 bg-white opacity-0 pointer-events-none transition-opacity duration-300"></div>
                    </article>

                    {{-- 2. DETAILED METRICS --}}
                    <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">Details</h3>
                        <div class="space-y-4" id="metrics-container">
                            @foreach($metrics as $metric)
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="font-medium text-gray-600">{{ $metric->name }}</span>
                                        <span class="font-bold text-gray-900" id="metric-val-{{ $metric->id }}">100</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div id="metric-bar-{{ $metric->id }}"
                                             class="metric-bar bg-green-500 h-2.5 rounded-full"
                                             style="width: 50%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 3. EVENTS --}}
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                        <h4 class="font-bold text-gray-800 mb-2 border-b pb-1">Event Simulatie</h4>
                        <div id="active-event-display" class="hidden bg-blue-50 text-blue-800 p-2 rounded text-sm mb-3 border border-blue-200 text-center font-bold animate-pulse">
                            <span id="event-name">Geen Event</span> Actief!
                        </div>
                        <div class="space-y-2">
                            @foreach($jsEventsData as $event)
                                <button id="btn-event-{{ $event['id'] }}" onclick="triggerEvent({{ $event['id'] }})" class="w-full text-left px-3 py-2 text-xs font-medium bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded flex justify-between items-center transition-all">
                                    <span class="font-bold text-gray-700">{{ $event['name'] }}</span>
                                    <span class="text-xs text-gray-400 bg-white px-1 rounded border">{{ $event['duration'] }} min</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- 4. RULES --}}
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 text-sm text-gray-600">
                        <h4 class="font-bold text-gray-800 mb-2 border-b pb-1">Actieve Regels:</h4>
                        @if(!empty($incompatibilityRules))
                            <ul class="list-disc pl-4 space-y-2 text-xs">
                                @foreach($incompatibilityRules as $category => $enemies)
                                    <li><span class="font-bold text-gray-700">{{ $category }}</span> botst met <span class="text-red-500">{{ implode(', ', $enemies) }}</span></li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </div>

    {{-- JAVASCRIPT LOGIC --}}
    <script>

       // --- KEYBOARD ACCESSIBILITY LOGIC (UPDATED) ---
        let keyboardSourceId = null;

        // 1. GLOBALE ESCAPE LISTENER (Werkt overal)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (keyboardSourceId !== null) {
                    cancelSelection();
                }
            }
        });

        function cancelSelection() {
            keyboardSourceId = null;
            document.querySelectorAll('.keyboard-selected').forEach(el => el.classList.remove('keyboard-selected'));
            document.body.classList.remove('keyboard-mode-active');
            showNotification("Selectie geannuleerd.", "info");
        }

        function handleSidebarKey(e, dbId) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                
                // Reset vorige
                document.querySelectorAll('.keyboard-selected').forEach(el => el.classList.remove('keyboard-selected'));
                
                // Selecteer nieuwe
                keyboardSourceId = dbId;
                const el = document.getElementById(`function-${dbId}`);
                if (el) el.classList.add('keyboard-selected');

                document.body.classList.add('keyboard-mode-active');
                
                // HIER IS DE FIX: Gebruik 'info' type (Blauw)
                showNotification("Item geselecteerd. Druk op Enter in het grid om te plaatsen.", "info");
                
                playSynthSound('success');
            }
        }

        function handleGridKey(e, cellIndex) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();

                if (keyboardSourceId === null) {
                    showNotification("Selecteer eerst een functie uit de linker lijst.", "error");
                    return;
                }

                const func = availableFunctions.find(f => f.id === keyboardSourceId);
                const funcIndex = availableFunctions.indexOf(func);
                const check = checkAdjacency(cellIndex, funcIndex);

                if (!check.valid) {
                    showNotification(check.message, "error"); // Rood
                    playSynthSound('failure');
                    return;
                }

                applyFunctionToCell(cellIndex, funcIndex);
                playSynthSound('success');
                
                // Optioneel: reset na plaatsen
                // cancelSelection(); 
            }

            if (e.key === 'Delete' || e.key === 'Backspace') {
                if (gridState[cellIndex] !== 0) {
                    deleteItem(cellIndex);
                    showNotification(`Kavel ${cellIndex + 1} leeggemaakt.`, "info");
                }
            }
        }
        const availableFunctions = [
            { id: 'empty', name: 'Kavel', color_hex: '#ffffff', category: 'Leeg', livability: 0, image: null, impacts: {} },
            ...(@json($jsFunctionsData))
        ];

        const metrics = @json($metrics);
        const incompatibilityRules = @json($incompatibilityRules ?? []);
        const eventDefinitions = @json($jsEventsData);

        let gridState = Array(12).fill(0);
        let currentDragIndex = null;
        let activeEvents = [];

        // GLOBAL SCORE TRACKING
        let currentAverageScore = 100;
        let deltaTimeout = null;

        // --- 1. MAIN CALCULATION ENGINE ---
        function calculateMetrics() {
            let currentScores = {};
            if (!metrics || !Array.isArray(metrics)) return;

            metrics.forEach(m => currentScores[m.id] = 100);

            // Add Grid Impacts
            gridState.forEach(funcIndex => {
                if (!funcIndex || funcIndex === 0) return;
                const func = availableFunctions[funcIndex];
                if (!func) return;

                if (func.impacts && typeof func.impacts === 'object') {
                    for (const [metricId, value] of Object.entries(func.impacts)) {
                        if (currentScores[metricId] !== undefined) {
                            currentScores[metricId] += (Number(value) || 0);
                        }
                    }
                }
            });

            // Add Event Impacts
            activeEvents.forEach(event => {
                if(event.impacts && Array.isArray(event.impacts)) {
                    event.impacts.forEach(impact => {
                        const metric = metrics.find(m => m.name === impact.metric_name);
                        if(metric && currentScores[metric.id] !== undefined) {
                            currentScores[metric.id] += (Number(impact.adjustment) || 0);
                        }
                    });
                }
            });

            // Update UI Bars & Calculate Average
            let totalScore = 0;
            metrics.forEach(m => {
                let val = currentScores[m.id];
                if (isNaN(val)) val = 100;
                totalScore += val;

                // Update Bars
                let widthPercentage = (val / 200) * 100;
                widthPercentage = Math.max(0, Math.min(100, widthPercentage));

                const textEl = document.getElementById(`metric-val-${m.id}`);
                const barEl = document.getElementById(`metric-bar-${m.id}`);

                if (textEl) textEl.innerText = Math.round(val);
                if (barEl) {
                    barEl.style.width = `${widthPercentage}%`;
                    barEl.className = 'metric-bar h-2.5 rounded-full';
                    if (val >= 100) barEl.classList.add('bg-green-500');
                    else if (val >= 60) barEl.classList.add('bg-yellow-500');
                    else barEl.classList.add('bg-red-500');
                }
            });

            let newAverage = totalScore / metrics.length;
            updateGlobalScore(newAverage);
        }

        // --- 2. GLOBAL SCORE & VISUAL FEEDBACK LOGIC ---
        function updateGlobalScore(newScore) {
            const diff = newScore - currentAverageScore;
            const scoreEl = document.getElementById('global-score-val');
            const deltaEl = document.getElementById('score-delta');
            const flashEl = document.getElementById('score-flash');

            if (Math.abs(diff) > 0.5) {
                const isPositive = diff > 0;
                const arrow = isPositive ? '▲' : '▼';
                const colorClass = isPositive ? 'text-green-100' : 'text-red-100';

                playSynthSound(isPositive ? 'success' : 'failure');

                deltaEl.innerText = `${arrow} ${Math.abs(diff).toFixed(1)}`;
                deltaEl.className = `text-sm font-bold transition-all duration-500 px-2 rounded backdrop-blur-sm ${colorClass}`;

                requestAnimationFrame(() => {
                    deltaEl.classList.remove('opacity-0', 'translate-y-2');
                });

                flashEl.classList.replace('opacity-0', 'opacity-20');
                setTimeout(() => flashEl.classList.replace('opacity-20', 'opacity-0'), 300);

                if (deltaTimeout) clearTimeout(deltaTimeout);
                deltaTimeout = setTimeout(() => {
                    deltaEl.classList.add('opacity-0', 'translate-y-2');
                }, 3000);
            }

            scoreEl.innerText = Math.round(newScore);
            currentAverageScore = newScore;
        }

        // --- 3. DRAG & DROP LOGIC ---
        function drag(ev, dbId) {
            // FIX: Direct tooltips verbergen bij start slepen
            hideTooltip();

            const func = availableFunctions.find(f => f.id === dbId);
            const indexInArray = availableFunctions.indexOf(func);
            currentDragIndex = indexInArray;
            ev.dataTransfer.setData("funcIndex", indexInArray);
            ev.dataTransfer.effectAllowed = "copy";
            setGhostImage(ev, func);
        }

        function setGhostImage(ev, func) {
            const ghost = document.getElementById('drag-ghost');
            const ghostImg = document.getElementById('ghost-img');
            const ghostBlock = document.getElementById('ghost-color-block');
            const ghostLabel = document.getElementById('ghost-label');
            const referenceCell = document.getElementById('cell-0');
            const width = referenceCell.offsetWidth;
            const height = referenceCell.offsetHeight;

            ghost.style.width = `${width}px`;
            ghost.style.height = `${height}px`;

            if (func) {
                ghostLabel.innerText = func.name;
                ghostLabel.style.borderBottom = `3px solid ${func.color_hex}`;
                if (func.image) {
                    ghostImg.src = func.image;
                    ghostImg.classList.remove('hidden');
                    ghostBlock.classList.add('hidden');
                } else {
                    ghostImg.classList.add('hidden');
                    ghostBlock.classList.remove('hidden');
                    ghostBlock.style.backgroundColor = func.color_hex || '#ccc';
                }
                ev.dataTransfer.setDragImage(ghost, width / 2, height / 2);
            }
        }

        function checkAdjacency(targetCellIndex, functionIndex) {
            const incomingFunc = availableFunctions[functionIndex];
            const incomingCat = incomingFunc.category;
            const neighbors = getNeighbors(targetCellIndex);
            for (let neighborIndex of neighbors) {
                const neighborFuncIndex = gridState[neighborIndex];
                if (neighborFuncIndex === 0) continue;
                const neighborFunc = availableFunctions[neighborFuncIndex];
                const neighborCat = neighborFunc.category;

                if (incompatibilityRules[incomingCat] && incompatibilityRules[incomingCat].includes(neighborCat)) {
                    return { valid: false, message: `CONFLICT: ${incomingCat} mag niet naast ${neighborCat}!` };
                }
                if (incompatibilityRules[neighborCat] && incompatibilityRules[neighborCat].includes(incomingCat)) {
                    return { valid: false, message: `CONFLICT: ${neighborCat} staat geen ${incomingCat} toe!` };
                }
            }
            return { valid: true };
        }

        function getNeighbors(i) {
            let neighbors = [];
            const col = i % 4;
            if (i >= 4) neighbors.push(i - 4);
            if (i < 8)  neighbors.push(i + 4);
            if (col > 0) neighbors.push(i - 1);
            if (col < 3) neighbors.push(i + 1);
            return neighbors;
        }

        function allowDrop(ev, cellIndex) {
            ev.preventDefault();
            const cell = document.getElementById(`cell-${cellIndex}`);
            const feedbackBar = document.getElementById('live-feedback');
            const check = checkAdjacency(cellIndex, currentDragIndex);

            if (check.valid) {
                cell.classList.add('drag-over-valid');
                cell.classList.remove('drag-over-invalid');
                ev.dataTransfer.dropEffect = "copy";
                feedbackBar.classList.add('hidden');
            } else {
                cell.classList.add('drag-over-invalid');
                cell.classList.remove('drag-over-valid');
                ev.dataTransfer.dropEffect = "none";
                feedbackBar.innerHTML = `<div class="inline-block bg-red-100 text-red-700 border-2 border-red-400 px-6 py-3 rounded-lg shadow-2xl font-bold text-sm animate-bounce pointer-events-auto">️ ${check.message}</div>`;
                feedbackBar.classList.remove('hidden');
            }
        }

        function leaveDrag(index) {
            const cell = document.getElementById(`cell-${index}`);
            cell.classList.remove('drag-over-valid', 'drag-over-invalid');
            document.getElementById('live-feedback').classList.add('hidden');
        }

        function drop(ev, cellIndex) {
            ev.preventDefault();
            leaveDrag(cellIndex);
            const funcIndex = parseInt(ev.dataTransfer.getData("funcIndex"));
            const check = checkAdjacency(cellIndex, funcIndex);

            if (!check.valid) {
                showError(check.message);
                playSynthSound('failure');
                return;
            }
            applyFunctionToCell(cellIndex, funcIndex);
        }

        function applyFunctionToCell(cellIndex, funcIndex) {
            gridState[cellIndex] = funcIndex;
            updateCellUI(cellIndex, availableFunctions[funcIndex]);
            calculateMetrics();
        }

        function updateCellUI(index, func) {
            const cell = document.getElementById(`cell-${index}`);
            cell.innerHTML = ''; 

            // FIX: Altijd de overlay terugplaatsen
            const overlay = document.createElement('div');
            overlay.id = `overlay-${index}`;
            overlay.className = 'delete-overlay';
            cell.appendChild(overlay);

            if(func.id === 'empty') {
                const textSpan = document.createElement('span');
                textSpan.className = 'z-10 pointer-events-none';
                textSpan.innerText = `Kavel ${index + 1}`;
                cell.appendChild(textSpan);

                cell.classList.add('border-gray-300');
                cell.classList.remove('shadow-sm', 'border-metro-darkred', 'neighbor-highlight');
            } else {
                cell.classList.remove('border-gray-300');
                cell.classList.add('shadow-sm');

                if (func.image) {
                    const img = document.createElement('img');
                    img.src = func.image;
                    img.className = 'w-full h-full object-cover absolute top-0 left-0 pointer-events-none';
                    cell.appendChild(img);
                }

                const span = document.createElement('span');
                span.className = 'font-bold text-[0.7rem] lg:text-sm relative z-10 bg-white/90 px-2 py-0.5 rounded mt-auto mb-1 pointer-events-none shadow-sm';
                span.innerText = func.name;
                span.style.borderBottom = `3px solid ${func.color_hex}`;
                cell.appendChild(span);
            }
        }

        function acknowledgeFunction(id) {
            const badge = document.getElementById(`badge-${id}`);
            if (badge) {
                badge.remove();
                fetch(`/simulation/acknowledge/${id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
                });
            }
        }

        function triggerEvent(eventId) {
            const eventDef = eventDefinitions.find(e => e.id === eventId);
            if(!eventDef) return;
            [...activeEvents].forEach(existingEvent => endEvent(existingEvent.id));
            activeEvents.push(eventDef);

            document.getElementById('event-name').innerText = eventDef.name;
            document.getElementById('active-event-display').classList.remove('hidden');
            const btn = document.getElementById(`btn-event-${eventId}`);
            if(btn) btn.classList.add('bg-red-50', 'border-metro-darkred', 'text-metro-darkred', 'ring-1', 'ring-metro-darkred');

            calculateMetrics();
            setTimeout(() => { endEvent(eventId); }, 5000);
        }

        function endEvent(eventId) {
            activeEvents = activeEvents.filter(e => e.id !== eventId);
            if(activeEvents.length === 0) document.getElementById('active-event-display').classList.add('hidden');
            const btn = document.getElementById(`btn-event-${eventId}`);
            if(btn) btn.classList.remove('bg-red-50', 'border-metro-darkred', 'text-metro-darkred', 'ring-1', 'ring-metro-darkred');
            calculateMetrics();
        }

        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        function playSynthSound(type) {
            if (audioCtx.state === 'suspended') audioCtx.resume();
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            const now = audioCtx.currentTime;

            if (type === 'success') {
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(600, now);
                oscillator.frequency.exponentialRampToValueAtTime(900, now + 0.1);
                gainNode.gain.setValueAtTime(0.1, now);
                gainNode.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
                oscillator.start(now);
                oscillator.stop(now + 0.5);
            } else {
                oscillator.type = 'triangle';
                oscillator.frequency.setValueAtTime(150, now);
                oscillator.frequency.linearRampToValueAtTime(100, now + 0.2);
                gainNode.gain.setValueAtTime(0.15, now);
                gainNode.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
                oscillator.start(now);
                oscillator.stop(now + 0.3);
            }
        }

        function showError(msg) {
            const toast = document.getElementById('error-toast');
            document.getElementById('error-text').innerText = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 5000);
        }

        // --- TOOLTIP LOGICA ---
        let hoverTimeout = null;

        function getMetricName(id) {
            const m = metrics.find(x => x.id == id);
            return m ? m.name : 'Onbekend';
        }

        function getSurroundingIndices(index) {
            const row = Math.floor(index / 4);
            const col = index % 4;
            let indices = [];
            for (let r = row - 1; r <= row + 1; r++) {
                for (let c = col - 1; c <= col + 1; c++) {
                    if (r >= 0 && r < 3 && c >= 0 && c < 4) {
                        const neighborIndex = r * 4 + c;
                        if (neighborIndex !== index) indices.push(neighborIndex);
                    }
                }
            }
            return indices;
        }

        function showTooltip(event, cellIndex) {
            if (hoverTimeout) clearTimeout(hoverTimeout);
            hoverTimeout = setTimeout(() => {
                executeShowTooltip(cellIndex);
            }, 500);
        }

        function executeShowTooltip(cellIndex) {
            const funcIndex = gridState[cellIndex];
            const func = availableFunctions[funcIndex];
            if (!func) return;

            const tooltip = document.getElementById('hover-tooltip');
            const titleEl = document.getElementById('tooltip-title');
            const impactsList = document.getElementById('tooltip-impacts');
            const synergyList = document.getElementById('tooltip-synergy');

            titleEl.innerText = func.name;
            impactsList.innerHTML = '';
            let hasImpacts = false;

            if (func.impacts && typeof func.impacts === 'object') {
                for (const [metricId, value] of Object.entries(func.impacts)) {
                    if(value == 0) continue;
                    hasImpacts = true;
                    const valNum = Number(value);
                    const name = getMetricName(metricId);
                    const colorClass = valNum > 0 ? 'text-green-600' : 'text-red-500';
                    const sign = valNum > 0 ? '+' : '';
                    impactsList.innerHTML += `<li class="flex justify-between items-center border-b border-gray-50 pb-1 last:border-0"><span class="text-gray-600">${name}</span><span class="font-bold ${colorClass} text-xs">${sign}${valNum}</span></li>`;
                }
            }
            if (!hasImpacts) impactsList.innerHTML = '<li class="text-gray-400 italic text-xs">Geen uitgaande effecten</li>';

            synergyList.innerHTML = '';
            const neighbors = getSurroundingIndices(cellIndex);
            let receivedEffects = {};

            neighbors.forEach(nIndex => {
                const nFuncIndex = gridState[nIndex];
                if (nFuncIndex && nFuncIndex !== 0) {
                    const neighborFunc = availableFunctions[nFuncIndex];
                    if (neighborFunc.impacts) {
                        for (const [metricId, value] of Object.entries(neighborFunc.impacts)) {
                            const valNum = Number(value);
                            if (valNum === 0) continue;
                            const mName = getMetricName(metricId);
                            if (!receivedEffects[mName]) receivedEffects[mName] = { value: 0, sources: [] };
                            receivedEffects[mName].value += valNum;
                            if (!receivedEffects[mName].sources.includes(neighborFunc.name)) {
                                receivedEffects[mName].sources.push(neighborFunc.name);
                            }
                        }
                    }
                }
            });

            let hasSynergy = false;
            for (const [metricName, data] of Object.entries(receivedEffects)) {
                hasSynergy = true;
                const colorClass = data.value > 0 ? 'text-green-600' : 'text-red-500';
                const sign = data.value > 0 ? '+' : '';
                const sourceText = data.sources.join(', ');
                synergyList.innerHTML += `<li class="flex justify-between items-start border-b border-gray-50 pb-1 last:border-0"><div class="flex flex-col leading-tight"><span class="text-gray-600">${metricName}</span><span class="text-[10px] text-gray-400 italic">van: ${sourceText}</span></div><span class="font-bold ${colorClass} text-xs mt-1">${sign}${data.value}</span></li>`;
            }
            if (!hasSynergy) synergyList.innerHTML = '<li class="text-gray-400 italic text-xs">Geen invloed van buren</li>';

            neighbors.forEach(nIndex => {
                const el = document.getElementById(`cell-${nIndex}`);
                if(el) el.classList.add('neighbor-highlight');
            });

            const cell = document.getElementById(`cell-${cellIndex}`);
            const rect = cell.getBoundingClientRect();
            tooltip.classList.remove('hidden');
            let top = rect.top;
            let left = rect.right + 10;
            if (left + 250 > window.innerWidth) left = rect.left - 270;
            if (top + 350 > window.innerHeight) top = window.innerHeight - 370;

            tooltip.style.top = `${top}px`;
            tooltip.style.left = `${left}px`;
            requestAnimationFrame(() => {
                tooltip.classList.remove('opacity-0', 'scale-95');
                tooltip.classList.add('opacity-100', 'scale-100');
            });
        }

        // --- UPDATED CLEANUP FUNCTION ---
        function hideTooltip() {
            if (hoverTimeout) clearTimeout(hoverTimeout);
            const tooltip = document.getElementById('hover-tooltip');
            tooltip.classList.remove('opacity-100', 'scale-100');
            tooltip.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                if(tooltip.classList.contains('opacity-0')) tooltip.classList.add('hidden');
            }, 200);

            // FORCE CLEAN: Verwijder highlights van alle cellen
            for(let i=0; i<12; i++) {
                const el = document.getElementById(`cell-${i}`);
                if(el) el.classList.remove('neighbor-highlight');
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            if (window.location.hash) {
                const targetId = window.location.hash.substring(1);
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    targetElement.classList.add('flash-target');
                    setTimeout(() => targetElement.classList.remove('flash-target'), 2000);
                }
            }
        });

        // --- 5. HOLD TO DELETE LOGIC ---
        let holdTimer = null;
        const HOLD_DURATION = 800;
        let isHolding = false;

        function startHold(event, cellIndex) {
            if (event.type === 'mousedown' && event.button !== 0) return;
            if (gridState[cellIndex] === 0) return;

            // FIX: Verberg tooltip direct als we beginnen met klikken
            hideTooltip();

            isHolding = true;
            const cell = document.getElementById(`cell-${cellIndex}`);
            cell.classList.add('is-holding');

            holdTimer = setTimeout(() => {
                if (isHolding) {
                    deleteItem(cellIndex);
                    isHolding = false;
                }
            }, HOLD_DURATION);
        }

        function cancelHold(cellIndex) {
            if (!isHolding) return;
            isHolding = false;
            clearTimeout(holdTimer);
            const cell = document.getElementById(`cell-${cellIndex}`);
            if (cell) cell.classList.remove('is-holding');
        }

        function deleteItem(cellIndex) {
            const cell = document.getElementById(`cell-${cellIndex}`);
            cell.classList.remove('is-holding');
            playSynthSound('failure'); 
            applyFunctionToCell(cellIndex, 0); 
        }

        function showNotification(msg, type = 'error') {
            const toast = document.getElementById('toast-message');
            const title = document.getElementById('toast-title');
            const text = document.getElementById('toast-text');
            const iconError = document.getElementById('toast-icon-error');
            const iconInfo = document.getElementById('toast-icon-info');

            // Reset classes
            toast.className = 'fixed top-20 right-5 border px-4 py-3 rounded shadow-lg z-50 transition-opacity duration-300 flex items-center';

            if (type === 'success' || type === 'info') {
                // BLAUWE STIJL (Voor selectie)
                toast.classList.add('bg-blue-100', 'border-blue-400', 'text-blue-700');
                title.innerText = "Info";
                iconError.classList.add('hidden');
                iconInfo.classList.remove('hidden');
            } else {
                // RODE STIJL (Voor fouten)
                toast.classList.add('bg-red-100', 'border-red-400', 'text-red-700');
                title.innerText = "Niet toegestaan!";
                iconError.classList.remove('hidden');
                iconInfo.classList.add('hidden');
            }

            text.innerText = msg;
            toast.classList.remove('hidden');
            
            // Verberg na 4 seconden
            if (window.toastTimer) clearTimeout(window.toastTimer);
            window.toastTimer = setTimeout(() => toast.classList.add('hidden'), 4000);
        }
    </script>

    {{-- DRAG GHOST --}}
    <div id="drag-ghost" class="fixed top-[-9999px] left-[-9999px] bg-white border border-gray-300 flex flex-col items-center shadow-lg rounded-sm overflow-hidden z-50">
        <img id="ghost-img" src="" class="absolute top-0 left-0 w-full h-full object-cover hidden">
        <div id="ghost-color-block" class="absolute top-0 left-0 w-full h-full hidden"></div>
        <span id="ghost-label" class="font-bold text-sm leading-tight relative z-10 drop-shadow-md bg-white/90 px-2 py-0.5 rounded mt-auto mb-2 max-w-[90%] truncate text-center">Label</span>
    </div>

    <div id="hover-tooltip" class="fixed hidden z-[9999] w-64 bg-white rounded-lg shadow-xl border border-gray-200 pointer-events-none transition-opacity duration-200 opacity-0 transform scale-95">
        <div class="p-4">
            <h3 id="tooltip-title" class="text-lg font-bold text-gray-800 mb-2 border-b pb-2">Title</h3>
            <div class="mb-3">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Effect op Omgeving (Radius)</h4>
                <ul id="tooltip-impacts" class="space-y-1 text-sm"></ul>
            </div>
            <div id="tooltip-synergy-section" class="border-t pt-2">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Ontvangt van Buren</h4>
                <ul id="tooltip-synergy" class="space-y-1 text-sm"></ul>
            </div>
        </div>
    </div>

</x-app-layout>