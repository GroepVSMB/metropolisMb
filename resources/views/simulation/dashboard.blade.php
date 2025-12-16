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
                                                class="group flex items-center p-2 bg-gray-50 rounded border border-gray-200 cursor-grab active:cursor-grabbing hover:border-metro-darkred hover:shadow-sm transition-all select-none">

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

        const availableFunctions = [
            { id: 'empty', name: 'Kavel', color_hex: '#ffffff', category: 'Leeg', livability: 0, image: null },
            ...dbFunctions
        ];

        let gridState = Array(12).fill(0);
        let currentDragIndex = null;

        // 1. Start Drag
        function drag(ev, dbId) {
            const indexInArray = availableFunctions.findIndex(f => f.id === dbId);
            currentDragIndex = indexInArray;
            ev.dataTransfer.setData("funcIndex", indexInArray);
            ev.dataTransfer.effectAllowed = "copy";
        }

        // 2. Logic Check
        function checkAdjacency(targetCellIndex, functionIndex) {
            const incomingFunc = availableFunctions[functionIndex];
            const incomingCat = incomingFunc.category;

            if (!incompatibilityRules[incomingCat]) return { valid: true };

            const enemies = incompatibilityRules[incomingCat];
            const neighbors = getNeighbors(targetCellIndex);

            for (let neighborIndex of neighbors) {
                const neighborFuncIndex = gridState[neighborIndex];
                if (neighborFuncIndex === 0) continue;

                const neighborFunc = availableFunctions[neighborFuncIndex];
                const neighborCat = neighborFunc.category;

                if (enemies.includes(neighborCat)) {
                    return {
                        valid: false,
                        // The message needed for the prompt:
                        message: `CONFLICT: ${incomingCat} kan niet naast ${neighborCat}!`
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

        function updateScore() {
            let score = 6.0;
            gridState.forEach(funcIndex => {
                const func = availableFunctions[funcIndex];
                if(funcIndex !== 0 && func) {
                    score += (func.livability - 100) * 0.01;
                }
            });
            score = Math.max(1, Math.min(10, score));
            document.getElementById('score-val').innerText = score.toFixed(1);
        }

        function showError(msg) {
            const toast = document.getElementById('error-toast');
            document.getElementById('error-text').innerText = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 5000);
        }
    </script>
</x-app-layout>
