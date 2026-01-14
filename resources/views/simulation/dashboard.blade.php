<x-app-layout>

    <style>
        .simulation-container { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .scroller::-webkit-scrollbar { width: 6px; }
        .scroller::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 4px; }
        .scroller::-webkit-scrollbar-track { background-color: #f1f5f9; }

        /* Dragging Styles */
        .dragging { opacity: 0.5; }

        .drag-over-valid {
            border-color: #22c55e !important;
            background-color: #f0fdf4 !important;
            border-width: 2px !important;
            transform: scale(1.02);
        }

        .drag-over-invalid {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
            border-width: 2px !important;
            transform: scale(1.02);
            cursor: not-allowed;
        }


        .function-item {
            position: relative; /* Needed for absolute positioning of badge */
        }

        .new-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ff4757; /* Red/Pink color */
            color: white;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            z-index: 10;
            pointer-events: none; /* Let clicks pass through to the item */
        }

        .function-item img
        {
            width: min(160px, 20vw);
            height: min(160px, 20vw);
        }
    </style>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-metro-darkred leading-tight uppercase tracking-wide">
            {{ __('Simulation Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 simulation-container">
        <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8">

            {{-- 1. ERROR TOAST (Appears on failed drop) --}}
            <div id="error-toast" class="hidden fixed top-20 right-5 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg z-50 transition-opacity duration-300 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <strong class="font-bold">Niet toegestaan!</strong>
                    <span class="block text-sm" id="error-text">Reden onbekend.</span>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-6 items-start">

                {{-- COLUMN 1: Sidebar (Draggables) --}}
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
                                                ondragstart="drag(event, {{ $function->id }})"
                                                {{-- NEW: Trigger acknowledgement on mouse down (click or start of drag) --}}
                                                onmousedown="acknowledgeFunction({{ $function->id }})"
                                                class="function-item group flex items-center p-2 bg-gray-50 rounded border border-gray-200 cursor-grab active:cursor-grabbing hover:border-metro-darkred hover:shadow-sm transition-all select-none relative">

                                                {{-- NEW: The Badge Logic --}}
                                                {{-- Note: This relies on the Controller update from the previous step --}}
                                                @if(!$function->acknowledged_by_users_exists)
                                                    <span id="badge-{{ $function->id }}" class="new-badge">NIEUW</span>
                                                @endif

                                                @if($function->image)
                                                    <img src="{{ $function->image }}" class="w-10 h-10 rounded mr-3 object-cover border border-gray-200">
                                                @else
                                                    <span class="w-10 h-10 rounded mr-3 bg-gray-200 block"></span>
                                                @endif

                                                <span class="font-medium text-gray-700 text-sm group-hover:text-metro-darkred">{{ $function->name }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </aside>

                {{-- COLUMN 2: The Grid --}}
                <section class="w-full lg:w-2/4 flex flex-col items-center bg-white shadow-sm sm:rounded-lg p-6 relative">

                    <div class="bg-[#eef2f5] p-2 lg:p-5 rounded-lg shadow-inner w-full box-border border border-gray-200">
                        <div class="grid grid-cols-4 grid-rows-3 gap-2 w-full aspect-[4/3]">
                            @for($i = 0; $i < 12; $i++)
                                <div
                                    id="cell-{{ $i }}"
                                    onclick="handleCellClick({{ $i }})"
                                    ondrop="drop(event, {{ $i }})"
                                    ondragover="allowDrop(event, {{ $i }})"
                                    ondragleave="leaveDrag({{ $i }})"
                                    class="bg-white border border-gray-300 flex flex-col items-center justify-center text-center cursor-pointer text-xs lg:text-sm text-gray-400 transition-all select-none p-1 overflow-hidden active:scale-95 relative rounded-sm shadow-sm hover:border-metro-darkred">
                                    Kavel {{ $i + 1 }}
                                </div>
                            @endfor
                        </div>
                    </div>

                    {{-- 2. LIVE FEEDBACK BAR (New feature) --}}
                    <div id="live-feedback" class="mt-4 w-full p-3 rounded text-sm font-bold text-center hidden">
                    </div>

                    <p class="text-center text-xs text-gray-500 mt-2 italic">
                        Sleep functies naar de kavels. Let op de regels!
                    </p>
                </section>

                {{-- COLUMN 3: Score & Rules --}}
                <aside class="w-full lg:w-1/4 min-w-[250px] flex flex-col gap-5">
                    <article class="bg-[#448a28] p-5 text-white font-bold flex justify-between items-center shadow-lg rounded-lg transform hover:scale-105 transition-transform">
                        <div class="flex flex-col">
                            <span class="uppercase text-xs opacity-80 tracking-wider">Huidige Score</span>
                            <span class="text-xl">Leefbaarheid</span>
                        </div>
                        <span id="score-val" class="text-4xl font-black">6.0</span>
                    </article>
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                        <h4 class="font-bold text-gray-800 mb-2 border-b pb-1">Event Simulatie</h4>
                        
                        {{-- Active Indicator --}}
                        <div id="active-event-display" class="hidden bg-blue-50 text-blue-800 p-2 rounded text-sm mb-3 border border-blue-200 text-center font-bold animate-pulse">
                            <span id="event-name">Geen Event</span> Actief!
                        </div>
                    
                        {{-- Event Buttons --}}
                        <div class="space-y-2">
                            @foreach($jsEventsData as $event)
                                <button 
                                    id="btn-event-{{ $event['id'] }}"
                                    onclick="triggerEvent({{ $event['id'] }})" 
                                    class="w-full text-left px-3 py-2 text-xs font-medium bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded flex justify-between items-center transition-all">
                                    <span class="font-bold text-gray-700">{{ $event['name'] }}</span>
                                    <span class="text-xs text-gray-400 bg-white px-1 rounded border">{{ $event['duration'] }} min</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 text-sm text-gray-600">
                        <h4 class="font-bold text-gray-800 mb-2 border-b pb-1">Actieve Regels:</h4>
                        @if(!empty($incompatibilityRules))
                            <ul class="list-disc pl-4 space-y-2 text-xs">
                                @foreach($incompatibilityRules as $category => $enemies)
                                    <li>
                                        <span class="font-bold text-gray-700">{{ $category }}</span> botst met: <br>
                                        <span class="text-red-500 font-semibold">{{ implode(', ', $enemies) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-400 italic text-xs">Geen regels gevonden.</p>
                        @endif
                    </div>
                </aside>

            </div>
        </div>
    </div>

    {{-- JAVASCRIPT LOGIC --}}
    <script>
        const dbFunctions = @json($jsFunctionsData);
        const incompatibilityRules = @json($incompatibilityRules ?? []);
        // --- NEW: Voeg deze twee regels toe ---
        const eventDefinitions = @json($jsEventsData); 
        let activeEvents = [];
        const availableFunctions = [
            { id: 'empty', name: 'Kavel', color_hex: '#ffffff', category: 'Leeg', livability: 0, image: null },
            ...dbFunctions
        ];

        let gridState = Array(12).fill(0);
        let currentDragIndex = null;

        function drag(ev, dbId) {
            // 1. Get Data
            const func = availableFunctions.find(f => f.id === dbId);
            const indexInArray = availableFunctions.indexOf(func);
            currentDragIndex = indexInArray;
            ev.dataTransfer.setData("funcIndex", indexInArray);
            ev.dataTransfer.effectAllowed = "copy";

            // 2. Configure the Ghost Element to match Grid Cell Size
            const ghost = document.getElementById('drag-ghost');
            const ghostImg = document.getElementById('ghost-img');
            const ghostBlock = document.getElementById('ghost-color-block');
            const ghostLabel = document.getElementById('ghost-label');

            // GET REFERENCE SIZE FROM THE GRID
            // We grab the size of the first cell so the drag image matches the grid exactly
            const referenceCell = document.getElementById('cell-0');
            const width = referenceCell.offsetWidth;
            const height = referenceCell.offsetHeight;

            // Apply dimensions to ghost
            ghost.style.width = `${width}px`;
            ghost.style.height = `${height}px`;

            if (func) {
                // Update Label to look like the Grid version (larger text, positioned at bottom)
                ghostLabel.innerText = func.name;
                ghostLabel.style.borderBottom = `3px solid ${func.color_hex}`;
                
                // Set Image vs Color
                if (func.image) {
                    ghostImg.src = func.image;
                    ghostImg.classList.remove('hidden');
                    ghostBlock.classList.add('hidden');
                } else {
                    ghostImg.classList.add('hidden');
                    ghostBlock.classList.remove('hidden');
                    ghostBlock.style.backgroundColor = func.color_hex || '#ccc';
                }

                // 3. Set Drag Image
                // We set the offset to half width/height so the cursor is in the center of the big card
                ev.dataTransfer.setDragImage(ghost, width / 2, height / 2);
            }
        }

    
        // 2. Logic Check
        function checkAdjacency(targetCellIndex, functionIndex) {
            const incomingFunc = availableFunctions[functionIndex];
            const incomingCat = incomingFunc.category;
            const neighbors = getNeighbors(targetCellIndex);

            for (let neighborIndex of neighbors) {
                // Get the neighbor on the grid
                const neighborFuncIndex = gridState[neighborIndex];
                if (neighborFuncIndex === 0) continue; // Skip empty neighbors

                const neighborFunc = availableFunctions[neighborFuncIndex];
                const neighborCat = neighborFunc.category;

                // CHECK 1: Does the INCOMING function hate the NEIGHBOR?
                // (This is what you already had)
                if (incompatibilityRules[incomingCat] && incompatibilityRules[incomingCat].includes(neighborCat)) {
                    return {
                        valid: false,
                        message: `CONFLICT: ${incomingCat} mag niet naast ${neighborCat}!`
                    };
                }

                // CHECK 2: Does the NEIGHBOR hate the INCOMING function?
                // (This is the missing part that fixes your issue)
                if (incompatibilityRules[neighborCat] && incompatibilityRules[neighborCat].includes(incomingCat)) {
                    return {
                        valid: false,
                        message: `CONFLICT: ${neighborCat} staat geen ${incomingCat} toe!`
                    };
                }
            }
            return { valid: true };
        }

        function getNeighbors(i) {
            let neighbors = [];
            const col = i % 4;
            if (i >= 4) neighbors.push(i - 4); // North
            if (i < 8)  neighbors.push(i + 4); // South
            if (col > 0) neighbors.push(i - 1); // West
            if (col < 3) neighbors.push(i + 1); // East
            return neighbors;
        }

        // 3. Hover Feedback (Updated for Live Message)
        function allowDrop(ev, cellIndex) {
            ev.preventDefault();
            const cell = document.getElementById(`cell-${cellIndex}`);
            const feedbackBar = document.getElementById('live-feedback');

            const check = checkAdjacency(cellIndex, currentDragIndex);

            if (check.valid) {
                cell.classList.add('drag-over-valid');
                cell.classList.remove('drag-over-invalid');
                ev.dataTransfer.dropEffect = "copy";

                // Hide feedback if valid
                feedbackBar.classList.add('hidden');
                feedbackBar.innerHTML = '';
            } else {
                cell.classList.add('drag-over-invalid');
                cell.classList.remove('drag-over-valid');
                ev.dataTransfer.dropEffect = "none";

                // SHOW FEEDBACK MESSAGE IMMEDIATELY
                feedbackBar.innerHTML = `
                    <div class="bg-red-100 text-red-700 border border-red-400 px-4 py-2 rounded flex items-center justify-center animate-pulse">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        ${check.message}
                    </div>
                `;
                feedbackBar.classList.remove('hidden');
            }
        }

        function leaveDrag(index) {
            const cell = document.getElementById(`cell-${index}`);
            cell.classList.remove('drag-over-valid', 'drag-over-invalid');

            // Clear feedback when leaving the cell
            const feedbackBar = document.getElementById('live-feedback');
            feedbackBar.classList.add('hidden');
        }

        // 4. Drop (Commit)
        function drop(ev, cellIndex) {
            ev.preventDefault();
            leaveDrag(cellIndex); // Clears the styling

            const funcIndex = parseInt(ev.dataTransfer.getData("funcIndex"));
            const check = checkAdjacency(cellIndex, funcIndex);

            if (!check.valid) {
                showError(check.message); // Show the sticky Toast if they drop it anyway
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
            updateScore();
        }

        function updateCellUI(index, func) {
            const cell = document.getElementById(`cell-${index}`);
            cell.innerHTML = '';

            if(func.id === 'empty') {
                cell.innerText = `Kavel ${index + 1}`;
                cell.classList.add('border-gray-300');
                cell.classList.remove('shadow-sm');
            } else {
                cell.classList.remove('border-gray-300');
                cell.classList.add('shadow-sm');

                if (func.image) {
                    const img = document.createElement('img');
                    img.src = func.image;
                    img.className = 'w-full h-full object-cover absolute top-0 left-0';
                    img.style.pointerEvents = 'none';
                    cell.appendChild(img);
                }

                const span = document.createElement('span');
                span.className = 'font-bold text-[0.7rem] lg:text-sm leading-tight relative z-10 drop-shadow-md bg-white/90 px-2 py-0.5 rounded mt-auto mb-1';
                span.innerText = func.name;
                span.style.borderBottom = `3px solid ${func.color_hex}`;
                cell.appendChild(span);
            }
        }

    

        function showError(msg) {
            const toast = document.getElementById('error-toast');
            document.getElementById('error-text').innerText = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 5000);
        }


        function acknowledgeFunction(id) {
            // 1. Select the badge element
            const badge = document.getElementById(`badge-${id}`);
            
            // 2. Only proceed if the badge actually exists (it hasn't been clicked yet)
            if (badge) {
                // Optimistic UI: Remove it immediately
                badge.remove();

                // 3. Send the API request to backend
                fetch(`/simulation/acknowledge/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}', // Laravel Blade helper for CSRF
                        'Content-Type': 'application/json'
                    }
                }).then(response => {
                    if (!response.ok) console.error("Acknowledgement failed");
                }).catch(err => console.error(err));
            }
        }

       // --- NIEUWE VERSIE VAN EVENT LOGICA ---

   // --- JAVASCRIPT LOGICA ---

    function triggerEvent(eventId) {
        const eventDef = eventDefinitions.find(e => e.id === eventId);
        if(!eventDef) return;

        // STAP 1: Stop alle HUIDIGE events (reset knoppen en maak lijst leeg)
        [...activeEvents].forEach(existingEvent => {
            endEvent(existingEvent.id);
        });

        // STAP 2: Voeg het NIEUWE event toe
        activeEvents.push(eventDef);
        
        // STAP 3: UI Update (Banner)
        const display = document.getElementById('active-event-display');
        document.getElementById('event-name').innerText = eventDef.name;
        // Gebruik hier ook de metro-kleur voor de tekst
        display.className = "bg-red-50 text-metro-darkred p-2 rounded text-sm mb-3 border border-metro-darkred text-center font-bold animate-pulse";
        display.classList.remove('hidden');

        // STAP 4: UI Update (Knop Highlighten in Metro Stijl)
        const btn = document.getElementById(`btn-event-${eventId}`);
        if(btn) {
            // Verwijder standaard grijze styling
            btn.classList.remove('bg-gray-50', 'border-gray-200');
            
            // Voeg Metro styling toe: Lichte rode achtergrond, Metro-rode rand en tekst
            btn.classList.add('bg-red-50', 'border-metro-darkred', 'text-metro-darkred', 'ring-1', 'ring-metro-darkred');
        }

        // STAP 5: Herbereken score
        updateScore();

        // STAP 6: Auto-stop na 5 seconden
        setTimeout(() => {
            endEvent(eventId);
        }, 5000); 
    }

    function endEvent(eventId) {
        // Verwijder uit lijst
        activeEvents = activeEvents.filter(e => e.id !== eventId);
        
        // UI: Verberg banner als er niets meer actief is
        if(activeEvents.length === 0) {
            document.getElementById('active-event-display').classList.add('hidden');
        }

        // UI: Reset de knop stijl naar standaard
        const btn = document.getElementById(`btn-event-${eventId}`);
        if(btn) {
            // Verwijder de Metro styling
            btn.classList.remove('bg-red-50', 'border-metro-darkred', 'text-metro-darkred', 'ring-1', 'ring-metro-darkred');
            
            // Voeg de standaard grijze styling weer toe
            btn.classList.add('bg-gray-50', 'border-gray-200');
        }

        // Herbereken score (terug naar normaal)
        updateScore();
    }

    function updateScore() {
        let baseScore = 6.0;
        let totalScore = baseScore;

        gridState.forEach(funcIndex => {
            // funcIndex 0 betekent 'leeg', dus die slaan we over
            if(funcIndex !== 0) {
                const func = availableFunctions[funcIndex];
                
                if(func) {
                    // A. Basis Leefbaarheid (Zorg dat het een nummer is!)
                    let itemLivability = Number(func.livability);

                    // B. Check actieve events
                    activeEvents.forEach(event => {
                        // Zoek of dit event impact heeft op de categorie van deze functie
                        const impact = event.impacts.find(i => i.category_name === func.category);
                        if(impact) {
                            // Tel de adjustment erbij op (bijv. +20 of -10)
                            itemLivability += Number(impact.adjustment);
                        }
                    });

                    // C. Formule: Elke 100 punten is neutraal. 
                    // 110 punten = +0.1 op score. 90 punten = -0.1 op score.
                    let effect = (itemLivability - 100) * 0.01;
                    totalScore += effect;
                }
            }
        });

        // Begrens de score tussen 1.0 en 10.0
        totalScore = Math.max(1, Math.min(10, totalScore));
        
        // Update de tekst op het scherm
        const scoreEl = document.getElementById('score-val');
        scoreEl.innerText = totalScore.toFixed(1);

        // Visuele feedback: Maak de tekst blauw als er een event bezig is
        if(activeEvents.length > 0) {
            scoreEl.classList.add('text-blue-600');
        } else {
            scoreEl.classList.remove('text-blue-600');
        }
    }
    </script>

    {{-- DRAG GHOST TEMPLATE (Dynamic Size) --}}
    <div id="drag-ghost" class="fixed top-[-9999px] left-[-9999px] bg-white border border-gray-300 flex flex-col items-center shadow-lg rounded-sm overflow-hidden z-50">
        
        {{-- Image: Covers the whole background --}}
        <img id="ghost-img" src="" class="absolute top-0 left-0 w-full h-full object-cover hidden">
        
        {{-- Color Block: Covers background if no image --}}
        <div id="ghost-color-block" class="absolute top-0 left-0 w-full h-full hidden"></div>
        
        {{-- Label: Styled exactly like the grid items (Bottom centered, white background) --}}
        <span id="ghost-label" 
            class="font-bold text-sm leading-tight relative z-10 drop-shadow-md bg-white/90 px-2 py-0.5 rounded mt-auto mb-2 max-w-[90%] truncate text-center">
            Label
        </span>
    </div>
    
</x-app-layout>
