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
            {{ __('Simulation Dashboard') }}
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
                    <div class="bg-[#eef2f5] p-2 lg:p-5 rounded-lg shadow-inner w-full box-border border border-gray-200">
                        <div class="grid grid-cols-4 grid-rows-3 gap-2 w-full aspect-[4/3]">
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
                    <div id="live-feedback" class="mt-4 w-full p-3 rounded text-sm font-bold text-center hidden"></div>
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
                feedbackBar.innerHTML = `<div class="bg-red-100 text-red-700 border border-red-400 px-4 py-2 rounded animate-pulse">${check.message}</div>`;
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
            cell.innerHTML = ''; // Clear current content

            if(func.id === 'empty') {
                cell.innerText = `Kavel ${index + 1}`;
                cell.classList.add('border-gray-300');
                cell.classList.remove('shadow-sm', 'border-metro-darkred');
                // Remove highlight if it was stuck
                cell.classList.remove('neighbor-highlight');
            } else {
                cell.classList.remove('border-gray-300');
                cell.classList.add('shadow-sm');

                // 1. The Image (Background)
                if (func.image) {
                    const img = document.createElement('img');
                    img.src = func.image;
                    // CRITICAL: pointer-events-none ensures the mouse "sees" the DIV, not the IMG
                    img.className = 'w-full h-full object-cover absolute top-0 left-0 pointer-events-none';
                    cell.appendChild(img);
                }

                // 2. The Label (Text)
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
            const row = Math.floor(index / 4); // Grid is 4 breed
            const col = index % 4;
            let indices = [];

            // Loop door grid van 3x3 rondom de cel
            for (let r = row - 1; r <= row + 1; r++) {
                for (let c = col - 1; c <= col + 1; c++) {
                    // Check of we binnen het bord blijven (3 rijen hoog, 4 kolommen breed)
                    if (r >= 0 && r < 3 && c >= 0 && c < 4) {
                        const neighborIndex = r * 4 + c;
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
            if (!funcIndex || funcIndex === 0) return;

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
                }
            }, 200);

            // --- VERWIJDER HIGHLIGHTS ---
            // We halen simpelweg de class van ALLE cellen af, dat is het veiligst/snelst
            for(let i=0; i<12; i++) {
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
