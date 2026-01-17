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

        .neighbor-highlight {
            background-color: rgba(255, 255, 0, 0.15) !important; /* Gele gloed */
            box-shadow: inset 0 0 10px rgba(255, 200, 0, 0.5);
            transition: background-color 0.3s ease;
        }

        /* NEW: Critical for tooltips to work */
        .pointer-events-none {
            pointer-events: none !important;
        }

        /* Ensure tooltip is always on top */
        #hover-tooltip {
            z-index: 9999 !important;
        }

        .neighbor-highlight {
            position: relative; /* Ensure ::after is positioned relative to this cell */
        }

        .neighbor-highlight::after {
            content: '';
            position: absolute;
            inset: 0; /* Cover the whole cell */
            background-color: rgba(255, 255, 0, 0.2); /* The Yellow Glow */
            box-shadow: inset 0 0 15px rgba(255, 215, 0, 0.8);
            z-index: 5; /* Sit ON TOP of the image (z=0) but BELOW text (z=10) */
            pointer-events: none; /* Let clicks pass through */
            border-radius: 0.125rem; /* Match rounded-sm */
            animation: pulseGlow 2s infinite;
        }

        @keyframes pulseGlow {
            0% { opacity: 0.6; }
            50% { opacity: 1; }
            100% { opacity: 0.6; }
        }
    </style>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-metro-darkred leading-tight uppercase tracking-wide">
            {{ __('Simulatie Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 simulation-container">
        <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8">

            {{-- ERROR TOAST --}}
            <div id="error-toast" class="hidden fixed top-20 right-5 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg z-50 transition-opacity duration-300 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <strong class="font-bold">Niet toegestaan!</strong>
                    <span class="block text-sm" id="error-text">Reden onbekend.</span>
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
                                                ondragstart="drag(event, {{ $function['id'] }})"
                                                onmousedown="acknowledgeFunction({{ $function['id'] }})"
                                                class="function-item group flex items-center p-2 bg-gray-50 rounded border border-gray-200 cursor-grab active:cursor-grabbing hover:border-metro-darkred hover:shadow-sm transition-all select-none relative">

                                                @if(isset($function['is_new']) && $function['is_new'])
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
                <section class="w-full lg:w-2/4 flex flex-col items-center bg-white shadow-sm sm:rounded-lg p-6 relative">
                    {{-- TIME CONTROLS BAR --}}
                    <div class="w-full flex flex-col gap-2 bg-gray-100 p-3 rounded mb-4 border border-gray-200 shadow-sm select-none">
                        
                        {{-- Top Row: Buttons & Clock --}}
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center space-x-2">
                                {{-- Play/Pause --}}
                                <button id="btn-play" onclick="setPlayState(true)" class="p-2 bg-green-500 text-white rounded hover:bg-green-600 transition shadow-sm">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </button>
                                <button id="btn-pause" onclick="setPlayState(false)" class="p-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition hidden shadow-sm">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                </button>

                                {{-- Speed --}}
                                <div class="flex bg-white rounded border border-gray-300 overflow-hidden shadow-sm">
                                    <button onclick="setSpeed(1)" id="btn-speed-1" class="px-3 py-1 text-xs font-bold hover:bg-gray-100 bg-gray-200 transition-colors">1x</button>
                                    <button onclick="setSpeed(2)" id="btn-speed-2" class="px-3 py-1 text-xs font-bold hover:bg-gray-100 transition-colors">2x</button>
                                    <button onclick="setSpeed(5)" id="btn-speed-5" class="px-3 py-1 text-xs font-bold hover:bg-gray-100 transition-colors">5x</button>
                                </div>
                            </div>

                            {{-- Clock --}}
                            <div class="flex items-center text-sm font-mono font-bold text-gray-700 bg-white px-3 py-1 rounded border border-gray-300 shadow-inner">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span id="clock-display">Dag 1, 00:00</span>
                            </div>

                            {{-- Planner Button --}}
                            <button onclick="togglePlannerModal(true)" class="ml-2 flex items-center bg-blue-600 text-white px-3 py-1 rounded shadow hover:bg-blue-700 text-xs font-bold uppercase tracking-wide">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Weekplanner
                            </button>
                        </div>

                        {{-- Bottom Row: The Timeline --}}
                        <div class="relative w-full h-8 group">
                            {{-- Timeline Track --}}
                            <div id="timeline-track" 
                                 class="absolute top-2 bottom-2 left-0 right-0 bg-gray-300 rounded-full cursor-pointer overflow-hidden border border-gray-400 shadow-inner"
                                 onmousedown="startScrub(event)">
                                
                                {{-- Background: Day/Night Gradient (Optional visual flair) --}}
                                <div class="absolute inset-0 opacity-20 pointer-events-none" 
                                     style="background: linear-gradient(to right, #1a202c 0%, #f6e05e 25%, #f6e05e 75%, #1a202c 100%);">
                                </div>

                                {{-- Event Markers Container --}}
                                <div id="timeline-events" class="absolute inset-0 pointer-events-none"></div>

                                {{-- Progress Bar (Past) --}}
                                <div id="timeline-progress" class="h-full bg-metro-darkred opacity-30 w-0 pointer-events-none transition-all duration-75 ease-linear"></div>
                            </div>

                            {{-- Playhead (The Knob) --}}
                            <div id="timeline-playhead" 
                                 class="absolute top-0 w-1 h-full bg-red-600 cursor-ew-resize hover:w-2 hover:bg-red-500 transition-all shadow-md z-10"
                                 style="left: 0%"
                                 onmousedown="startScrub(event)">
                                 {{-- Tooltip on Hover --}}
                                 <div class="absolute bottom-full mb-1 left-1/2 transform -translate-x-1/2 bg-black text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition pointer-events-none whitespace-nowrap">
                                    <span id="scrub-time-tooltip">00:00</span>
                                 </div>
                            </div>
                        </div>
                    </div>

                    <div id="live-feedback" class="fixed top-28 left-1/2 transform -translate-x-1/2 z-[100] w-auto min-w-[300px] text-center hidden pointer-events-none transition-all duration-200"></div>
                    
                    {{-- GRID CONTROLS --}}
                    <div class="w-full flex justify-between items-center mb-2 px-1">
                        <div class="flex gap-2">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider self-center mr-2">Grid:</span>
                            <button onclick="modifyGrid(1, 0)" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-2 py-1 rounded text-xs font-bold transition" title="Kolom Toevoegen">+ Kol</button>
                            <button onclick="modifyGrid(-1, 0)" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-2 py-1 rounded text-xs font-bold transition" title="Kolom Verwijderen">- Kol</button>
                            <button onclick="modifyGrid(0, 1)" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-2 py-1 rounded text-xs font-bold transition" title="Rij Toevoegen">+ Rij</button>
                            <button onclick="modifyGrid(0, -1)" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-2 py-1 rounded text-xs font-bold transition" title="Rij Verwijderen">- Rij</button>
                        </div>
                        <span id="grid-size-display" class="text-xs font-mono text-gray-400">4x3</span>
                    </div>

                    <div class="bg-[#eef2f5] p-2 lg:p-5 rounded-lg shadow-inner w-full box-border border border-gray-200 overflow-auto">
                        <div id="city-grid" class="grid gap-2 w-full min-h-[400px]" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
                            @for($i = 0; $i < 12; $i++)
                                <div id="cell-{{ $i }}"
                                     onclick="handleCellClick({{ $i }})"
                                     ondrop="drop(event, {{ $i }})"
                                     ondragover="allowDrop(event, {{ $i }})"
                                     ondragleave="leaveDrag({{ $i }})"

                                     {{-- NEW: Tooltip Triggers --}}
                                     onmouseenter="showTooltip(event, {{ $i }})"
                                     onmouseleave="hideTooltip()"

                                     class="bg-white border border-gray-300 flex flex-col items-center justify-center text-center cursor-pointer text-xs lg:text-sm text-gray-400 transition-all select-none p-1 overflow-hidden active:scale-95 relative rounded-sm shadow-sm hover:border-metro-darkred">
                                    Kavel {{ $i + 1 }}
                                </div>
                            @endfor
                        </div>
                    </div>

                    <p class="text-center text-xs text-gray-500 mt-2 italic">
                        Sleep functies naar de kavels. Let op de regels!
                    </p>
                </section>

                {{-- KOLOM 3: Score & Metrics --}}
                <aside class="w-full lg:w-1/4 min-w-[250px] flex flex-col gap-5">

                    {{-- 1. GLOBAL AVERAGE SCORE CARD (Visual Feedback) --}}
                    <article class="bg-[#448a28] p-5 text-white font-bold flex justify-between items-center shadow-lg rounded-lg transform hover:scale-105 transition-transform relative overflow-hidden">
                        <div class="flex flex-col z-10">
                            <span class="uppercase text-xs opacity-80 tracking-wider">Gemiddelde</span>
                            <span class="text-xl">Leefbaarheid</span>
                        </div>

                        <div class="flex flex-col items-end z-10">
                            {{-- Global Score --}}
                            <span id="global-score-val" class="text-4xl font-black transition-all duration-300">100</span>

                            {{-- The Delta Indicator (Arrow Up/Down) --}}
                            <span id="score-delta" class="text-sm font-bold opacity-0 transition-all duration-500 transform translate-y-2 bg-white/20 px-2 rounded backdrop-blur-sm">
                                -
                            </span>
                        </div>

                        {{-- Flash Effect --}}
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
        const availableFunctions = [
            { id: 'empty', name: 'Kavel', color_hex: '#ffffff', category: 'Leeg', livability: 0, image: null, impacts: {} },
            ...(@json($jsFunctionsData))
        ];

        const metrics = @json($metrics);
        const incompatibilityRules = @json($incompatibilityRules ?? []);
        const eventDefinitions = @json($jsEventsData);

        // GRID STATE
        let gridWidth = 4;
        let gridHeight = 3;
        let gridState = Array(gridWidth * gridHeight).fill(0);
        
        let currentDragIndex = null;
        let activeEvents = [];

        // --- TIME SYSTEM STATE ---
        let simState = {
            tick: 0,        // Minutes passed total
            dayTick: 0,     // Minutes passed in current day (0-1439)
            lastRenderedDay: -1, // Track which day's events are currently shown
            isPlaying: false,
            isScrubbing: false,
            speed: 1,       // 1x, 2x, 5x
            timer: null,
            schedule: []    // [{ id: 1, day: 0, start_time: 600, duration: 60, template_id: 5 }]
        };

        // GLOBAL SCORE TRACKING
        let currentAverageScore = 100;
        let deltaTimeout = null;

        // --- INIT ---
        document.addEventListener('DOMContentLoaded', () => {
            initTimeline();
            initPlanner(); // NEW
            updateClockDisplay();
            
            // Initial Render of Grid (overwriting PHP static loop if needed, or just attaching events)
            // Ideally we stick to PHP rendered for SEO/Speed, but since this is an app, JS render is fine.
            // Let's force a JS render to ensure state matches.
            renderGridUI();
            
            calculateMetrics(); // Initial calc
        });

        // --- DYNAMIC GRID LOGIC ---
        function modifyGrid(dCol, dRow) {
            const newWidth = Math.max(2, gridWidth + dCol); // Min 2x2
            const newHeight = Math.max(2, gridHeight + dRow);
            
            if (newWidth === gridWidth && newHeight === gridHeight) return;

            // Re-map existing cells to new grid
            let newGridState = Array(newWidth * newHeight).fill(0);
            
            for (let r = 0; r < Math.min(gridHeight, newHeight); r++) {
                for (let c = 0; c < Math.min(gridWidth, newWidth); c++) {
                    const oldIndex = r * gridWidth + c;
                    const newIndex = r * newWidth + c;
                    if (gridState[oldIndex] !== undefined) {
                        newGridState[newIndex] = gridState[oldIndex];
                    }
                }
            }

            gridWidth = newWidth;
            gridHeight = newHeight;
            gridState = newGridState;

            renderGridUI();
            calculateMetrics();
        }

        function renderGridUI() {
            const container = document.getElementById('city-grid');
            container.style.gridTemplateColumns = `repeat(${gridWidth}, minmax(0, 1fr))`;
            
            // Update Display
            document.getElementById('grid-size-display').innerText = `${gridWidth}x${gridHeight}`;

            container.innerHTML = '';
            
            for(let i=0; i < gridState.length; i++) {
                const funcIndex = gridState[i];
                const func = availableFunctions[funcIndex] || availableFunctions[0];
                
                const cell = document.createElement('div');
                cell.id = `cell-${i}`;
                cell.className = "bg-white border border-gray-300 flex flex-col items-center justify-center text-center cursor-pointer text-xs lg:text-sm text-gray-400 transition-all select-none p-1 overflow-hidden active:scale-95 relative rounded-sm shadow-sm hover:border-metro-darkred aspect-square";
                
                // Attach Events
                cell.onclick = () => handleCellClick(i);
                cell.ondrop = (e) => drop(e, i);
                cell.ondragover = (e) => allowDrop(e, i);
                cell.ondragleave = () => leaveDrag(i);
                cell.onmouseenter = (e) => showTooltip(e, i);
                cell.onmouseleave = () => hideTooltip();

                // Inner Content
                if (func.id === 'empty') {
                    cell.innerText = `Kavel ${i + 1}`;
                } else {
                    updateCellContent(cell, func);
                }

                container.appendChild(cell);
            }
        }
        
        function updateCellContent(cell, func) {
            cell.innerHTML = '';
            cell.classList.remove('border-gray-300');
            cell.classList.add('shadow-sm');
            if (func.id === 'empty') {
                 cell.innerText = `Kavel ${parseInt(cell.id.split('-')[1]) + 1}`;
                 cell.classList.add('border-gray-300');
                 cell.classList.remove('shadow-sm');
                 return;
            }

            if (func.image) {
                const img = document.createElement('img');
                img.src = func.image;
                img.className = 'w-full h-full object-cover absolute top-0 left-0 pointer-events-none';
                cell.appendChild(img);
            } else {
                 // For no-image functions (like Roads maybe?), show color block
                 const block = document.createElement('div');
                 block.className = 'w-full h-full absolute top-0 left-0 pointer-events-none opacity-50';
                 block.style.backgroundColor = func.color_hex;
                 cell.appendChild(block);
            }

            const span = document.createElement('span');
            span.className = 'font-bold text-[0.7rem] lg:text-sm relative z-10 bg-white/90 px-2 py-0.5 rounded mt-auto mb-1 pointer-events-none shadow-sm';
            span.innerText = func.name;
            span.style.borderBottom = `3px solid ${func.color_hex}`;
            cell.appendChild(span);
        }

        // --- PLANNER LOGIC ---
        function initPlanner() {
            const list = document.getElementById('planner-event-list');
            list.innerHTML = '';
            
            eventDefinitions.forEach(def => {
                const item = document.createElement('div');
                item.className = 'bg-white p-2 rounded border border-gray-200 shadow-sm cursor-grab hover:border-metro-darkred hover:shadow-md transition text-sm font-bold text-gray-700 select-none';
                item.draggable = true;
                item.innerText = `${def.name} (${def.duration} min)`;
                item.ondragstart = (e) => {
                    e.dataTransfer.setData('templateId', def.id);
                    e.dataTransfer.effectAllowed = 'copy';
                };
                list.appendChild(item);
            });
        }

        function togglePlannerModal(show) {
            const modal = document.getElementById('planner-modal');
            if(show) {
                modal.classList.remove('hidden');
                renderPlannerSchedule();
            } else {
                modal.classList.add('hidden');
            }
        }

        function allowPlannerDrop(ev) {
            ev.preventDefault();
        }

        function dropOnPlanner(ev, dayIndex) {
            ev.preventDefault();
            const templateId = parseInt(ev.dataTransfer.getData('templateId'));
            if(!templateId) return;

            // Calculate Time from Y position
            const rect = ev.currentTarget.getBoundingClientRect();
            const y = ev.clientY - rect.top;
            const pct = y / rect.height;
            const minutesInDay = Math.round(pct * 1440);
            
            // Snap to 15 min
            const snappedTime = Math.round(minutesInDay / 15) * 15;

            addEventToSchedule(templateId, dayIndex, snappedTime);
        }

        function addEventToSchedule(templateId, dayIndex, startTime) {
            const template = eventDefinitions.find(e => e.id === templateId);
            if(!template) return;

            // Create Instance
            simState.schedule.push({
                instance_id: Date.now(), // simple unique id
                template_id: templateId,
                day: dayIndex,
                start_time: startTime,
                duration: template.duration,
                name: template.name
            });

            renderPlannerSchedule();
            
            // Force refresh of timeline if we modified the current day's schedule
            const currentDayIndex = Math.floor(simState.tick / 1440) % 7;
            if (dayIndex === currentDayIndex) {
                renderTimelineEvents();
            }
        }

        function renderPlannerSchedule() {
            // Clear all columns
            for(let d=0; d<7; d++) {
                document.getElementById(`day-events-${d}`).innerHTML = '';
            }

            // --- 1. RENDER SYSTEM/STATIC EVENTS (Gray) ---
            eventDefinitions.forEach(def => {
                let occurrences = []; // Array of { day: 0-6, start: 0-1440 }

                if (def.type === 'recurring') {
                    const cycle = def.duration + (def.recurrence || 0);
                    
                    if (Math.abs(cycle - 1440) < 10) {
                        // DAILY Event: Occurs every day
                        for(let d=0; d<7; d++) {
                            occurrences.push({ day: d, start: (def.start_minute || 0) % 1440 });
                        }
                    } else if (Math.abs(cycle - 10080) < 100) {
                        // WEEKLY Event: Occurs once a week
                        const dayIndex = Math.floor((def.start_minute || 0) / 1440) % 7;
                        occurrences.push({ day: dayIndex, start: (def.start_minute || 0) % 1440 });
                    }
                } else if (def.type === 'one_off') {
                    // ONE-OFF: Show if it falls within the first week (for generic planner view)
                    // Or ideally relative to simulation start, but Planner is usually a "Template Week".
                    // Let's show One-offs if they fall in Days 0-6.
                    const dayIndex = Math.floor((def.start_minute || 0) / 1440);
                    if (dayIndex >= 0 && dayIndex < 7) {
                        occurrences.push({ day: dayIndex, start: (def.start_minute || 0) % 1440 });
                    }
                }

                // Render Occurrences
                occurrences.forEach(occ => {
                    const container = document.getElementById(`day-events-${occ.day}`);
                    if(!container) return;

                    const topPct = (occ.start / 1440) * 100;
                    const heightPct = (def.duration / 1440) * 100;

                    const el = document.createElement('div');
                    // Gray/Slate style for System Events (read-only, Full Width, Striped)
                    el.className = 'absolute left-0 right-0 border-l-4 border-slate-500 text-slate-800 text-[9px] p-1 rounded-sm overflow-hidden pointer-events-auto z-0 font-bold';
                    // Striped Background to indicate "System/Fixed"
                    el.style.background = 'repeating-linear-gradient(45deg, #e2e8f0, #e2e8f0 10px, #f1f5f9 10px, #f1f5f9 20px)';
                    el.style.top = `${topPct}%`;
                    el.style.height = `${heightPct}%`;
                    el.innerText = def.name;
                    el.title = `Systeem Event: ${def.name}`;
                    // No onclick handler (Read-only)

                    container.appendChild(el);
                });
            });

            // --- 2. RENDER USER SCHEDULE EVENTS (Blue) ---
            simState.schedule.forEach(item => {
                const container = document.getElementById(`day-events-${item.day}`);
                if(!container) return;

                const topPct = (item.start_time / 1440) * 100;
                const heightPct = (item.duration / 1440) * 100;

                const el = document.createElement('div');
                // Blue style for User Events (z-index higher, Indented)
                el.className = 'absolute left-6 right-1 bg-blue-100 border-l-4 border-blue-600 text-blue-900 text-[10px] p-1 rounded shadow-md pointer-events-auto cursor-pointer hover:bg-blue-200 z-10 font-bold';
                el.style.top = `${topPct}%`;
                el.style.height = `${heightPct}%`;
                el.innerText = item.name;
                el.title = `Klik om te verwijderen`;
                el.onclick = (e) => {
                    e.stopPropagation();
                    if(confirm('Verwijder dit event?')) {
                        simState.schedule = simState.schedule.filter(i => i.instance_id !== item.instance_id);
                        renderPlannerSchedule();
                        // Refresh timeline if needed
                        const currentDayIndex = Math.floor(simState.tick / 1440) % 7;
                        if (item.day === currentDayIndex) {
                            renderTimelineEvents();
                        }
                    }
                };

                container.appendChild(el);
            });
        }
        
        function saveSimulationState() {
            const name = prompt("Geef dit scenario een naam:", "Mijn Scenario");
            if(!name) return;

            const payload = {
                name: name,
                gridState: gridState,
                gridWidth: gridWidth,
                gridHeight: gridHeight,
                scheduleState: simState.schedule,
                currentTick: simState.tick,
                status: simState.isPlaying ? 'playing' : 'paused',
                speed: simState.speed
            };

            fetch('{{ route("simulation.store") }}', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                alert('Scenario opgeslagen!');
                togglePlannerModal(false);
            })
            .catch(err => {
                console.error(err);
                alert('Fout bij opslaan.');
            });
        }

        // --- LOAD SCENARIO LOGIC ---
        function toggleLoadModal(show) {
            const modal = document.getElementById('load-modal');
            if(show) {
                modal.classList.remove('hidden');
                loadSimulationsList();
            } else {
                modal.classList.add('hidden');
            }
        }

        function loadSimulationsList() {
            const container = document.getElementById('simulation-list');
            container.innerHTML = '<div class="text-center text-gray-400 py-4">Laden...</div>';

            fetch('{{ route("simulation.list") }}')
                .then(res => res.json())
                .then(data => {
                    container.innerHTML = '';
                    if(data.length === 0) {
                        container.innerHTML = '<div class="text-center text-gray-500 py-4">Geen opgeslagen scenario\'s gevonden.</div>';
                        return;
                    }
                    data.forEach(sim => {
                        const item = document.createElement('div');
                        item.className = 'flex justify-between items-center bg-gray-50 p-3 mb-2 rounded border border-gray-200 hover:bg-gray-100 transition';
                        item.innerHTML = `
                            <div>
                                <div class="font-bold text-gray-800">${sim.name}</div>
                                <div class="text-xs text-gray-500">${new Date(sim.created_at).toLocaleString()}</div>
                            </div>
                            <button onclick="loadSimulation(${sim.id})" class="bg-blue-600 text-white px-3 py-1 rounded text-sm font-bold shadow hover:bg-blue-700">Laden</button>
                        `;
                        container.appendChild(item);
                    });
                })
                .catch(err => {
                    console.error(err);
                    container.innerHTML = '<div class="text-center text-red-500 py-4">Fout bij laden lijst.</div>';
                });
        }

        function loadSimulation(id) {
            fetch(`/simulations/${id}`)
                .then(res => res.json())
                .then(data => {
                    applySimulationState(data);
                    toggleLoadModal(false);
                    togglePlannerModal(false); // also close planner if open
                    alert(`Scenario "${data.name}" geladen!`);
                })
                .catch(err => {
                    console.error(err);
                    alert('Fout bij laden scenario details.');
                });
        }

        function applySimulationState(data) {
            // 0. GRID DIMENSIONS
            if(data.grid_width) gridWidth = parseInt(data.grid_width);
            if(data.grid_height) gridHeight = parseInt(data.grid_height);

            // 1. GRID STATE
            if(data.grid_state && Array.isArray(data.grid_state)) {
                gridState = data.grid_state;
                // Re-render Grid
                renderGridUI();
            }

            // 2. SCHEDULE & TIME
            if(data.schedule_state && Array.isArray(data.schedule_state)) {
                simState.schedule = data.schedule_state;
            } else {
                simState.schedule = [];
            }

            if(data.current_tick !== undefined) simState.tick = parseInt(data.current_tick);
            if(data.speed !== undefined) setSpeed(parseInt(data.speed));
            
            // Force reset of rendered day so timeline updates
            simState.lastRenderedDay = -1;

            // 3. REFRESH EVERYTHING
            updateClockDisplay();
            renderPlannerSchedule();
            // initTimeline call inside updateClockDisplay via renderTimelineEvents check? No, explicit call.
            // Actually updateClockDisplay calls updateTimelineUI, but we need to re-render markers.
            renderTimelineEvents(); // Force initial render
            calculateMetrics(); // Re-calculate scores based on new grid
            
            // Optional: Pause on load
            setPlayState(false);
        }


        // --- TIME CONTROLS ---
        function setPlayState(play) {
            simState.isPlaying = play;
            document.getElementById('btn-play').classList.toggle('hidden', play);
            document.getElementById('btn-pause').classList.toggle('hidden', !play);

            if (play) startTimer();
            else stopTimer();
        }

        function setSpeed(speed) {
            simState.speed = speed;
            // Update UI
            [1, 2, 5].forEach(s => {
                const btn = document.getElementById(`btn-speed-${s}`);
                if (s === speed) btn.classList.add('bg-gray-200');
                else btn.classList.remove('bg-gray-200');
            });
            // Restart timer if playing
            if (simState.isPlaying) {
                stopTimer();
                startTimer();
            }
        }

        function startTimer() {
            if (simState.timer) clearInterval(simState.timer);
            const interval = 1000 / simState.speed; // 1 real sec = 10 game mins at 1x
            simState.timer = setInterval(tick, interval);
        }

        function stopTimer() {
            if (simState.timer) clearInterval(simState.timer);
            simState.timer = null;
        }

        function tick() {
            if (simState.isScrubbing) return; // Don't auto-advance while scrubbing
            simState.tick += 10;
            updateClockDisplay();
            checkEvents();
        }

        function updateClockDisplay() {
            // Calc Time
            const totalMinutes = simState.tick;
            const days = Math.floor(totalMinutes / 1440); // 0-based day count (Day 0, Day 1...)
            // Wait, UI says "Dag 1", so visually days + 1
            const currentDayIndex = days % 7; // 0-6 (Mon-Sun)
            
            simState.dayTick = totalMinutes % 1440; // 0 - 1439
            const hours = Math.floor(simState.dayTick / 60);
            const minutes = simState.dayTick % 60;

            const timeStr = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
            document.getElementById('clock-display').innerText = `Dag ${days + 1}, ${timeStr}`;

            // Check if we entered a new day -> Refresh Timeline Markers
            if (currentDayIndex !== simState.lastRenderedDay) {
                simState.lastRenderedDay = currentDayIndex;
                renderTimelineEvents();
            }

            // Update Timeline UI
            updateTimelineUI(simState.dayTick, timeStr);
        }

        // --- TIMELINE LOGIC ---
        function initTimeline() {
            simState.lastRenderedDay = -1; // Force render on first update
            renderTimelineEvents();
            // Global listeners for dragging (so you can drag outside the bar)
            document.addEventListener('mousemove', handleScrub);
            document.addEventListener('mouseup', stopScrub);
        }

        function renderTimelineEvents() {
            const container = document.getElementById('timeline-events');
            container.innerHTML = '';
            
            // Get current day index (0-6)
            const currentDayIndex = Math.floor(simState.tick / 1440) % 7;
            const currentDayStart = Math.floor(simState.tick / 1440) * 1440;
            const currentDayEnd = currentDayStart + 1440;

            // --- 1. RENDER SYSTEM/STATIC EVENTS (Gray/Blue) ---
            eventDefinitions.forEach(def => {
                let showOnTimeline = false;
                let dayStartMinute = 0;

                if (def.type === 'recurring') {
                    const cycle = def.duration + (def.recurrence || 0);
                    
                    if (Math.abs(cycle - 1440) < 10) {
                        // DAILY Event (approx 24h cycle)
                        showOnTimeline = true;
                        dayStartMinute = (def.start_minute || 0) % 1440;
                    } else if (Math.abs(cycle - 10080) < 100) {
                        // WEEKLY Event
                        // Does it fall on this day index?
                        // Def start is absolute (e.g. 9120 for Sunday)
                        // Day start is currentDayIndex * 1440.
                        const defDayIndex = Math.floor((def.start_minute || 0) / 1440) % 7;
                        if (defDayIndex === currentDayIndex) {
                            showOnTimeline = true;
                            dayStartMinute = (def.start_minute || 0) % 1440;
                        }
                    }
                } else if (def.type === 'one_off') {
                    // Check if absolute start is within current day range
                    if (def.start_minute >= currentDayStart && def.start_minute < currentDayEnd) {
                        showOnTimeline = true;
                        dayStartMinute = def.start_minute % 1440;
                    }
                }

                if (showOnTimeline) {
                    const duration = def.duration;
                    // Clip duration if it goes past midnight
                    let displayDuration = duration;
                    if (dayStartMinute + duration > 1440) displayDuration = 1440 - dayStartMinute;

                    const leftPct = (dayStartMinute / 1440) * 100;
                    const widthPct = (displayDuration / 1440) * 100;

                    const marker = document.createElement('div');
                    // Gray/Blue style for System Events
                    marker.className = 'absolute top-1 bottom-1 bg-slate-400 opacity-50 rounded-sm border-l border-r border-slate-500 z-0 flex items-center justify-center overflow-hidden cursor-help shadow-sm hover:opacity-80 transition-all';
                    marker.style.left = `${leftPct}%`;
                    marker.style.width = `${widthPct}%`;
                    
                    // Add Label
                    if (widthPct > 5) {
                        marker.innerHTML = `<span class="text-[9px] font-bold text-slate-800 truncate px-1 pointer-events-none">${def.name}</span>`;
                    }

                    marker.onmouseenter = (e) => showEventTooltip(e, def);
                    marker.onmouseleave = hideTooltip;
                    container.appendChild(marker);
                }
            });

            // --- 2. RENDER USER SCHEDULE EVENTS (Yellow) ---
            // Filter schedule for this day
            const todaysEvents = simState.schedule.filter(e => e.day === currentDayIndex);

            todaysEvents.forEach(evt => {
                const start = evt.start_time; 
                let duration = evt.duration;
                if (start + duration > 1440) duration = 1440 - start; 

                const leftPct = (start / 1440) * 100;
                const widthPct = (duration / 1440) * 100;

                const marker = document.createElement('div');
                // Yellow style for User Events (z-index higher to sit on top of system events if overlap)
                marker.className = 'absolute top-0 bottom-0 bg-yellow-400 opacity-80 rounded-sm border-l border-r border-yellow-600 z-10 flex items-center justify-center overflow-hidden cursor-help shadow-sm hover:opacity-100 hover:bg-yellow-300 transition-all'; 
                marker.style.left = `${leftPct}%`;
                marker.style.width = `${widthPct}%`;
                
                if (widthPct > 5) {
                    marker.innerHTML = `<span class="text-[10px] font-bold text-yellow-900 truncate px-1 pointer-events-none">${evt.name}</span>`;
                }

                // Look up template for tooltip details
                const template = eventDefinitions.find(d => d.id === evt.template_id);
                // Merge template data with instance data for tooltip
                const tooltipData = template ? { ...template, ...evt } : evt;

                marker.onmouseenter = (e) => showEventTooltip(e, tooltipData);
                marker.onmouseleave = hideTooltip;

                container.appendChild(marker);
            });
        }

        function showEventTooltip(e, evt) {
            const tooltip = document.getElementById('hover-tooltip');
            const titleEl = document.getElementById('tooltip-title');
            const impactsList = document.getElementById('tooltip-impacts');
            const synergyList = document.getElementById('tooltip-synergy'); // We'll reuse/clear this

            // Set Title
            titleEl.innerText = evt.name;
            titleEl.innerHTML += `<span class="block text-xs font-normal text-gray-500 mt-1">${formatTime(evt.start_time)} - ${formatTime(evt.start_time + evt.duration)} (${evt.duration} min)</span>`;

            // Clear lists
            impactsList.innerHTML = '';
            document.getElementById('tooltip-synergy-section').classList.add('hidden'); // Hide synergy section for events

            // Show Impacts (We need to fetch template impacts or store them in schedule)
            // Currently `simState.schedule` items might not have full impacts if we only saved basic info.
            // But we can look up the template in `eventDefinitions`.
            const template = eventDefinitions.find(def => def.id === evt.template_id);
            
            if (template && template.impacts) {
                template.impacts.forEach(imp => {
                    const colorClass = imp.adjustment > 0 ? 'text-green-600' : 'text-red-500';
                    const sign = imp.adjustment > 0 ? '+' : '';
                    impactsList.innerHTML += `
                    <li class="flex justify-between items-center border-b border-gray-50 pb-1 last:border-0">
                        <span class="text-gray-600">${imp.metric_name}</span>
                        <span class="font-bold ${colorClass} text-xs">${sign}${imp.adjustment}</span>
                    </li>`;
                });
            } else {
                impactsList.innerHTML = '<li class="text-gray-400 italic text-xs">Geen directe impact data beschikbaar.</li>';
            }

            // Position Tooltip
            tooltip.classList.remove('hidden');
            // document.getElementById('tooltip-synergy-section').classList.add('hidden'); // Ensure synergy hidden

            const rect = e.target.getBoundingClientRect();
            let top = rect.bottom + 10; 
            let left = rect.left;

            // Edge detection
            if (left + 250 > window.innerWidth) left = window.innerWidth - 260;
            if (top + 200 > window.innerHeight) top = rect.top - 210;

            tooltip.style.top = `${top}px`;
            tooltip.style.left = `${left}px`;

            requestAnimationFrame(() => {
                tooltip.classList.remove('opacity-0', 'scale-95');
                tooltip.classList.add('opacity-100', 'scale-100');
            });
        }

        function formatTime(minutes) {
            const h = Math.floor((minutes % 1440) / 60);
            const m = (minutes % 1440) % 60;
            return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}`;
        }

        function updateTimelineUI(currentMinute, timeStr) {
            const pct = (currentMinute / 1440) * 100;
            const safePct = Math.min(100, Math.max(0, pct));
            
            document.getElementById('timeline-progress').style.width = `${safePct}%`;
            document.getElementById('timeline-playhead').style.left = `${safePct}%`;
            
            // Tooltip update
            const tooltip = document.getElementById('scrub-time-tooltip');
            if(tooltip) tooltip.innerText = timeStr;
        }

        function startScrub(e) {
            simState.isScrubbing = true;
            handleScrub(e); // Jump immediately
        }

        function handleScrub(e) {
            if (!simState.isScrubbing) return;
            e.preventDefault(); // Stop text selection

            const track = document.getElementById('timeline-track');
            const rect = track.getBoundingClientRect();
            const x = e.clientX - rect.left;
            let pct = x / rect.width;
            pct = Math.min(1, Math.max(0, pct)); // Clamp 0-1

            // Convert pct to minutes
            const newDayMinute = Math.round(pct * 1440);
            
            // Preserve the "Day" count, just change the time of day
            const currentDayCount = Math.floor(simState.tick / 1440);
            simState.tick = (currentDayCount * 1440) + newDayMinute;

            updateClockDisplay(); // Visually update immediately
            checkEvents();        // Trigger events for this new time
        }

        function stopScrub() {
            if (simState.isScrubbing) {
                simState.isScrubbing = false;
                // Resume timer if playing? Yes, handled by tick() check.
            }
        }

        // --- EVENT ENGINE ---
        function checkEvents() {
            let stateChanged = false;
            const currentTick = simState.tick;
            
            // 1. Check Scheduled Events (Planner)
            simState.schedule.forEach(instance => {
                // Calculate absolute start/end in minutes
                // Assuming "Day 0" is the first 24h block, etc.
                const absoluteStart = (instance.day * 1440) + instance.start_time;
                const absoluteEnd = absoluteStart + instance.duration;
                
                // Check overlap
                const isActive = (currentTick >= absoluteStart && currentTick < absoluteEnd);
                
                // Sync State
                const instanceUniqueId = `sched-${instance.instance_id}`;
                const isCurrentlyActive = activeEvents.some(e => e.uniqueId === instanceUniqueId);
                
                if (isActive && !isCurrentlyActive) {
                    // Start
                    const template = eventDefinitions.find(e => e.id === instance.template_id);
                    if(template) {
                        // Create a composite active event object
                        activeEvents.push({
                            uniqueId: instanceUniqueId,
                            id: template.id, // for UI matching
                            name: template.name,
                            impacts: template.impacts,
                            categories: template.categories
                        });
                        toggleEventUI(template.id, true); // Visual feedback
                        stateChanged = true;
                    }
                } else if (!isActive && isCurrentlyActive) {
                    // Stop
                    activeEvents = activeEvents.filter(e => e.uniqueId !== instanceUniqueId);
                    // Only turn off UI if no other instance of this template is active
                    const stillActive = activeEvents.some(e => e.id === instance.template_id);
                    if(!stillActive) toggleEventUI(instance.template_id, false);
                    stateChanged = true;
                }
            });

            // 2. Legacy/Static Events (Optional: Keep recurring events working?)
            // If we want to strictly follow the planner, we disable this. 
            // But if we want "Background" events (like Night time?), we might keep it.
            // Let's keep it for "Recurring" types that aren't manually scheduled.
            eventDefinitions.forEach(def => {
                if(def.type !== 'recurring') return; // One-offs are now expected to be in schedule

                let isActive = false;
                if (def.recurrence > 0) {
                    const cycle = def.duration + def.recurrence; 
                    const offset = currentTick - (def.start_minute || 0);
                    if (offset >= 0) {
                        const pos = offset % cycle;
                        if (pos < def.duration) isActive = true;
                    }
                }

                const uniqueId = `static-${def.id}`;
                const isCurrentlyActive = activeEvents.some(e => e.uniqueId === uniqueId);

                if (isActive && !isCurrentlyActive) {
                    activeEvents.push({
                        uniqueId: uniqueId,
                        id: def.id,
                        name: def.name,
                        impacts: def.impacts,
                        categories: def.categories
                    });
                    toggleEventUI(def.id, true);
                    stateChanged = true;
                } else if (!isActive && isCurrentlyActive) {
                    activeEvents = activeEvents.filter(e => e.uniqueId !== uniqueId);
                    toggleEventUI(def.id, false);
                    stateChanged = true;
                }
            });

            if (stateChanged) calculateMetrics();
        }

        function toggleEventUI(id, active) {
            const btn = document.getElementById(`btn-event-${id}`);
            if(!btn) return;
            
            if (active) {
                btn.classList.add('bg-red-50', 'border-metro-darkred', 'text-metro-darkred', 'ring-1', 'ring-metro-darkred');
                document.getElementById('active-event-display').classList.remove('hidden');
                document.getElementById('event-name').innerText = eventDefinitions.find(e => e.id === id).name;
            } else {
                btn.classList.remove('bg-red-50', 'border-metro-darkred', 'text-metro-darkred', 'ring-1', 'ring-metro-darkred');
                if (activeEvents.length === 0) {
                    document.getElementById('active-event-display').classList.add('hidden');
                } else {
                    document.getElementById('event-name').innerText = activeEvents[activeEvents.length-1].name;
                }
            }
        }

        // --- 1. MAIN CALCULATION ENGINE ---
        function calculateMetrics() {
            let currentScores = {};
            if (!metrics || !Array.isArray(metrics)) return;

            // Start at 100
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
                if (!event.impacts || !Array.isArray(event.impacts)) return;

                const hasCategories = event.categories && Array.isArray(event.categories) && event.categories.length > 0;

                if (!hasCategories) {
                    // GLOBAL EVENT (Apply once)
                    event.impacts.forEach(impact => {
                        const metric = metrics.find(m => m.name === impact.metric_name);
                        if(metric && currentScores[metric.id] !== undefined) {
                            currentScores[metric.id] += (Number(impact.adjustment) || 0);
                        }
                    });
                } else {
                    // CATEGORY SPECIFIC EVENT (Apply per matching instance on grid)
                    gridState.forEach(funcIndex => {
                        if (!funcIndex || funcIndex === 0) return;
                        const func = availableFunctions[funcIndex];
                        if (!func) return;

                        // Check if this function's category is targeted by the event
                        if (event.categories.includes(func.category)) {
                             event.impacts.forEach(impact => {
                                const metric = metrics.find(m => m.name === impact.metric_name);
                                if(metric && currentScores[metric.id] !== undefined) {
                                    currentScores[metric.id] += (Number(impact.adjustment) || 0);
                                }
                            });
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

            // CALCULATE & UPDATE GLOBAL AVERAGE
            let newAverage = totalScore / metrics.length;
            updateGlobalScore(newAverage);
        }

        // --- 2. GLOBAL SCORE & VISUAL FEEDBACK LOGIC ---
        function updateGlobalScore(newScore) {
            const diff = newScore - currentAverageScore;

            const scoreEl = document.getElementById('global-score-val');
            const deltaEl = document.getElementById('score-delta');
            const flashEl = document.getElementById('score-flash');

            // Only trigger effects if there is a real change
            if (Math.abs(diff) > 0.5) {
                const isPositive = diff > 0;
                const arrow = isPositive ? '▲' : '▼';
                const colorClass = isPositive ? 'text-green-100' : 'text-red-100';

                // PLAY SOUND
                playSynthSound(isPositive ? 'success' : 'failure');

                // SHOW DELTA (Arrow & Number)
                deltaEl.innerText = `${arrow} ${Math.abs(diff).toFixed(1)}`;
                deltaEl.className = `text-sm font-bold transition-all duration-500 px-2 rounded backdrop-blur-sm ${colorClass}`;

                requestAnimationFrame(() => {
                    deltaEl.classList.remove('opacity-0', 'translate-y-2');
                });

                // FLASH EFFECT
                flashEl.classList.replace('opacity-0', 'opacity-20');
                setTimeout(() => flashEl.classList.replace('opacity-20', 'opacity-0'), 300);

                // Hide Delta after 3 seconds
                if (deltaTimeout) clearTimeout(deltaTimeout);
                deltaTimeout = setTimeout(() => {
                    deltaEl.classList.add('opacity-0', 'translate-y-2');
                }, 3000);
            }

            // Update Text
            scoreEl.innerText = Math.round(newScore);
            currentAverageScore = newScore;
        }

        // --- 3. DRAG & DROP LOGIC ---
        function drag(ev, dbId) {
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
            const col = i % gridWidth;
            const row = Math.floor(i / gridWidth);
            
            if (row > 0) neighbors.push(i - gridWidth); // Top
            if (row < gridHeight - 1) neighbors.push(i + gridWidth); // Bottom
            if (col > 0) neighbors.push(i - 1); // Left
            if (col < gridWidth - 1) neighbors.push(i + 1); // Right
            
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
                feedbackBar.innerHTML = `
                    <div class="inline-block bg-red-100 text-red-700 border-2 border-red-400 px-6 py-3 rounded-lg shadow-2xl font-bold text-sm animate-bounce pointer-events-auto">
                        ️ ${check.message}
                    </div>
                `;
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
                playSynthSound('failure'); // Buzz on error
                return;
            }

            applyFunctionToCell(cellIndex, funcIndex);
        }

        function handleCellClick(index) {
            if (gridState[index] !== 0) applyFunctionToCell(index, 0);
        }

        function applyFunctionToCell(cellIndex, funcIndex) {
            gridState[cellIndex] = funcIndex;
            updateCellUI(cellIndex, availableFunctions[funcIndex]);
            calculateMetrics(); // Triggers Score Update
        }

        function updateCellUI(index, func) {
            const cell = document.getElementById(`cell-${index}`);
            updateCellContent(cell, func);
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

        // --- AUDIO SYNTHESIS ---
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        function playSynthSound(type) {
            if (audioCtx.state === 'suspended') audioCtx.resume();
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            const now = audioCtx.currentTime;

            if (type === 'success') {
                // High Pitch Ping (Up)
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(600, now);
                oscillator.frequency.exponentialRampToValueAtTime(900, now + 0.1);
                gainNode.gain.setValueAtTime(0.1, now);
                gainNode.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
                oscillator.start(now);
                oscillator.stop(now + 0.5);
            } else {
                // Low Pitch Thud (Down/Error)
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

        // --- TOOLTIP & VISUALISATIE LOGICA (SIM.10) ---

        let hoverTimeout = null; // Voor de 0.5s vertraging

        // Helper: Vind metric naam
        function getMetricName(id) {
            const m = metrics.find(x => x.id == id);
            return m ? m.name : 'Onbekend';
        }

        // Helper: Vind alle 8 omliggende cellen (Noord, Zuid, Oost, West + Diagonalen)
        function getSurroundingIndices(index) {
            const row = Math.floor(index / gridWidth);
            const col = index % gridWidth;
            let indices = [];

            // Loop door grid van 3x3 rondom de cel
            for (let r = row - 1; r <= row + 1; r++) {
                for (let c = col - 1; c <= col + 1; c++) {
                    // Check of we binnen het bord blijven
                    if (r >= 0 && r < gridHeight && c >= 0 && c < gridWidth) {
                        const neighborIndex = r * gridWidth + c;
                        if (neighborIndex !== index) { // Jezelf niet meetellen
                            indices.push(neighborIndex);
                        }
                    }
                }
            }
            return indices;
        }

        function showTooltip(event, cellIndex) {
            // Stap 1: Clear eventuele oude timers zodat we niet flikkeren
            if (hoverTimeout) clearTimeout(hoverTimeout);

            // Stap 2: Start de vertraging van 0.5 seconde (500ms)
            hoverTimeout = setTimeout(() => {
                executeShowTooltip(cellIndex);
            }, 500); // <--- Acceptatie Criterium: >0.5s
        }

        function executeShowTooltip(cellIndex) {
            // 1. Basic Data Validation
            const funcIndex = gridState[cellIndex];
            // if (!funcIndex || funcIndex === 0) return;

            const func = availableFunctions[funcIndex];
            if (!func) return;

            const tooltip = document.getElementById('hover-tooltip');
            const titleEl = document.getElementById('tooltip-title');
            const impactsList = document.getElementById('tooltip-impacts');
            const synergyList = document.getElementById('tooltip-synergy');

            // Set Title
            titleEl.innerText = func.name;

            // --- A. OUTGOING IMPACTS (Effect on Neighbors) ---
            impactsList.innerHTML = '';
            let hasImpacts = false;

            if (func.impacts && typeof func.impacts === 'object') {
                for (const [metricId, value] of Object.entries(func.impacts)) {
                    if(value == 0) continue;
                    hasImpacts = true;
                    const valNum = Number(value);
                    const name = getMetricName(metricId);
                    const colorClass = valNum > 0 ? 'text-green-600' : 'text-red-500'; // Red for negative
                    const sign = valNum > 0 ? '+' : '';

                    impactsList.innerHTML += `
                    <li class="flex justify-between items-center border-b border-gray-50 pb-1 last:border-0">
                        <span class="text-gray-600">${name}</span>
                        <span class="font-bold ${colorClass} text-xs">${sign}${valNum}</span>
                    </li>`;
                }
            }
            if (!hasImpacts) impactsList.innerHTML = '<li class="text-gray-400 italic text-xs">Geen uitgaande effecten</li>';

            // --- B. INCOMING SYNERGY (What neighbors do to ME) ---
            synergyList.innerHTML = '';
            const neighbors = getSurroundingIndices(cellIndex);
            let receivedEffects = {}; // Store aggregates: { 'Air Quality': { val: 10, sources: ['Park'] } }

            neighbors.forEach(nIndex => {
                const nFuncIndex = gridState[nIndex];
                if (nFuncIndex && nFuncIndex !== 0) {
                    const neighborFunc = availableFunctions[nFuncIndex];

                    // If neighbor has impacts, add them to my "received" list
                    if (neighborFunc.impacts) {
                        for (const [metricId, value] of Object.entries(neighborFunc.impacts)) {
                            const valNum = Number(value);
                            if (valNum === 0) continue;

                            const mName = getMetricName(metricId);

                            if (!receivedEffects[mName]) {
                                receivedEffects[mName] = { value: 0, sources: [] };
                            }

                            receivedEffects[mName].value += valNum;
                            // Avoid duplicate source names (e.g. "Park, Park") -> just "Park"
                            if (!receivedEffects[mName].sources.includes(neighborFunc.name)) {
                                receivedEffects[mName].sources.push(neighborFunc.name);
                            }
                        }
                    }
                }
            });

            // Render Received Effects
            let hasSynergy = false;
            for (const [metricName, data] of Object.entries(receivedEffects)) {
                hasSynergy = true;
                const colorClass = data.value > 0 ? 'text-green-600' : 'text-red-500';
                const sign = data.value > 0 ? '+' : '';
                const sourceText = data.sources.join(', '); // e.g. "Park, Factory"

                synergyList.innerHTML += `
                <li class="flex justify-between items-start border-b border-gray-50 pb-1 last:border-0">
                    <div class="flex flex-col leading-tight">
                        <span class="text-gray-600">${metricName}</span>
                        <span class="text-[10px] text-gray-400 italic">van: ${sourceText}</span>
                    </div>
                    <span class="font-bold ${colorClass} text-xs mt-1">${sign}${data.value}</span>
                </li>`;
            }

            if (!hasSynergy) {
                synergyList.innerHTML = '<li class="text-gray-400 italic text-xs">Geen invloed van buren</li>';
            }

            // --- C. HIGHLIGHT NEIGHBORS (Visual Scope) ---
            neighbors.forEach(nIndex => {
                const el = document.getElementById(`cell-${nIndex}`);
                if(el) el.classList.add('neighbor-highlight');
            });

            // --- D. POSITIONING ---
            const cell = document.getElementById(`cell-${cellIndex}`);
            const rect = cell.getBoundingClientRect();

            tooltip.classList.remove('hidden');
            let top = rect.top;
            let left = rect.right + 10;

            // Screen edge detection
            if (left + 250 > window.innerWidth) left = rect.left - 270;
            if (top + 350 > window.innerHeight) top = window.innerHeight - 370; // Adjusted for taller tooltip

            tooltip.style.top = `${top}px`;
            tooltip.style.left = `${left}px`;

            requestAnimationFrame(() => {
                tooltip.classList.remove('opacity-0', 'scale-95');
                tooltip.classList.add('opacity-100', 'scale-100');
            });
        }

        function hideTooltip() {
            // Stop de timer als de muis alweer weg is voordat de 0.5s voorbij is
            if (hoverTimeout) clearTimeout(hoverTimeout);

            const tooltip = document.getElementById('hover-tooltip');

            // Verberg Tooltip
            tooltip.classList.remove('opacity-100', 'scale-100');
            tooltip.classList.add('opacity-0', 'scale-95');

            setTimeout(() => {
                if(tooltip.classList.contains('opacity-0')) {
                    tooltip.classList.add('hidden');
                    // RESET STATE
                    document.getElementById('tooltip-synergy-section').classList.remove('hidden');
                }
            }, 200);

            // --- VERWIJDER HIGHLIGHTS ---
            // We halen simpelweg de class van ALLE cellen af, dat is het veiligst/snelst
            for(let i=0; i<gridState.length; i++) {
                const el = document.getElementById(`cell-${i}`);
                if(el) el.classList.remove('neighbor-highlight');
            }
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
            <h3 id="tooltip-title" class="text-lg font-bold text-gray-800 mb-2 border-b pb-2">Titel</h3>

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

    {{-- PLANNER MODAL --}}
    <div id="planner-modal" class="fixed inset-0 z-[99999] hidden">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="togglePlannerModal(false)"></div>
        
        {{-- Modal Content --}}
        <div class="absolute inset-4 bg-white rounded-lg shadow-2xl flex flex-col overflow-hidden animate-fade-in-up">
            {{-- Header --}}
            <div class="bg-gray-100 border-b border-gray-200 p-4 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-metro-darkred" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Weekplanning & Scenario Opslaan
                </h2>
                <div class="flex space-x-2">
                    <button onclick="toggleLoadModal(true)" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 font-bold flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Laden
                    </button>
                    <button onclick="saveSimulationState()" class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700 font-bold flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        Opslaan
                    </button>
                    <button onclick="togglePlannerModal(false)" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-50 font-bold">
                        Sluiten
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="flex-1 flex overflow-hidden">
                {{-- Sidebar: Event Templates --}}
                <aside class="w-64 bg-gray-50 border-r border-gray-200 p-4 overflow-y-auto">
                    <h3 class="font-bold text-gray-500 uppercase text-xs tracking-wider mb-4">Event Sjablonen</h3>
                    <div id="planner-event-list" class="space-y-2">
                        {{-- JS populates this --}}
                    </div>
                    <p class="text-xs text-gray-400 mt-4 italic">Sleep events naar de kalender om ze in te plannen.</p>
                </aside>

                {{-- Calendar Grid --}}
                <main class="flex-1 overflow-auto bg-gray-100 p-4">
                    <div class="grid grid-cols-7 gap-px bg-gray-300 border border-gray-300 rounded overflow-hidden min-w-[800px]">
                        {{-- Headers --}}
                        @foreach(['Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrijdag', 'Zaterdag', 'Zondag'] as $index => $day)
                            <div class="bg-white p-2 text-center font-bold text-gray-700 border-b border-gray-200">
                                {{ $day }} <span class="text-xs text-gray-400 block font-normal">Dag {{ $index + 1 }}</span>
                            </div>
                        @endforeach

                        {{-- Days Columns --}}
                        @for($d = 0; $d < 7; $d++)
                            <div class="bg-white min-h-[500px] relative group" 
                                 id="day-col-{{ $d }}"
                                 ondragover="allowPlannerDrop(event)"
                                 ondrop="dropOnPlanner(event, {{ $d }})">
                                
                                {{-- Hour Markers (Background) --}}
                                @for($h = 0; $h < 24; $h++)
                                    <div class="absolute w-full border-t border-gray-100 text-[9px] text-gray-300 pl-1 select-none pointer-events-none" 
                                         style="top: {{ ($h / 24) * 100 }}%; height: {{ (1/24)*100 }}%">
                                        {{ $h }}:00
                                    </div>
                                @endfor

                                {{-- Scheduled Events Container --}}
                                <div id="day-events-{{ $d }}" class="absolute inset-0 w-full h-full pointer-events-none">
                                    {{-- JS populates this --}}
                                </div>
                            </div>
                        @endfor
                    </div>
                </main>
            </div>
        </div>
    </div>

    {{-- LOAD MODAL --}}
    <div id="load-modal" class="fixed inset-0 z-[100000] hidden">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="toggleLoadModal(false)"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-2xl w-[500px] overflow-hidden animate-fade-in-up">
            <div class="bg-gray-100 border-b border-gray-200 p-4 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">Scenario Laden</h3>
                <button onclick="toggleLoadModal(false)" class="text-gray-500 hover:text-gray-700">&times;</button>
            </div>
            <div class="p-4 max-h-[400px] overflow-y-auto" id="simulation-list">
                <div class="text-center text-gray-400 py-4">Laden...</div>
            </div>
        </div>
    </div>

</x-app-layout>
