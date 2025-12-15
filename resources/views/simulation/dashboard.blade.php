<x-app-layout>

    <style>
        .simulation-container { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        /* Custom scrollbar voor de functielijst */
        .scroller::-webkit-scrollbar { width: 6px; }
        .scroller::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 4px; }
        .scroller::-webkit-scrollbar-track { background-color: #f1f5f9; }

        .dragging { opacity: 0.5; }
        .drag-over { border-color: #be1e2d !important; border-width: 2px !important; transform: scale(1.02); }
    </style>

    {{-- Header Slot (Vervangt de custom header) --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-metro-darkred leading-tight uppercase tracking-wide">
            {{ __('Simulation Dashboard') }}
        </h2>
    </x-slot>

    {{-- 3. Main Content --}}
    <div class="py-12 simulation-container">
        <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8">

            <div class="flex flex-col lg:flex-row gap-6 items-start">

                {{-- KOLOM 1: Sidebar Links (Nu in een mooie witte kaart) --}}
                <aside class="w-full lg:w-1/4 min-w-[250px] bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 h-[calc(100vh-200px)] flex flex-col">
                    <h2 class="text-metro-darkred text-lg font-bold border-b-2 border-gray-100 pb-2 mb-4">
                        Beschikbare Functies
                    </h2>

                    {{-- De Scroller zit nu IN de kaart --}}
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
                                                <svg class="w-4 h-4 ml-auto text-gray-300 group-hover:text-metro-darkred" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </aside>

                {{-- KOLOM 2: Midden Grid (Al netjes in styling) --}}
                <section class="w-full lg:w-2/4 flex flex-col items-center bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="bg-[#eef2f5] p-2 lg:p-5 rounded-lg shadow-inner w-full box-border border border-gray-200">
                        <div class="grid grid-cols-4 grid-rows-3 gap-2 w-full aspect-[4/3]">
                            @for($i = 0; $i < 12; $i++)
                                <div
                                     id="cell-{{ $i }}"
                                     onclick="handleCellClick({{ $i }})"
                                     ondrop="drop(event, {{ $i }})"
                                     ondragover="allowDrop(event)"
                                     ondragenter="enterDrag({{ $i }})"
                                     ondragleave="leaveDrag({{ $i }})"
                                     class="bg-white border border-gray-300 flex flex-col items-center justify-center text-center cursor-pointer text-xs lg:text-sm text-gray-400 transition-all hover:border-metro-darkred select-none p-1 overflow-hidden active:scale-95 relative rounded-sm shadow-sm">
                                    Kavel {{ $i + 1 }}
                                </div>
                            @endfor
                        </div>
                    </div>
                    <p class="text-center text-xs text-gray-500 mt-4 italic">
                        Sleep functies naar de kavels. Klik op een kavel om deze leeg te maken.
                    </p>
                </section>

                {{-- KOLOM 3: Sidebar Rechts (Score) --}}
                <aside class="w-full lg:w-1/4 min-w-[250px] flex flex-col gap-5">
                    <article class="bg-[#448a28] p-5 text-white font-bold flex justify-between items-center shadow-lg rounded-lg transform hover:scale-105 transition-transform">
                        <div class="flex flex-col">
                            <span class="uppercase text-xs opacity-80 tracking-wider">Huidige Score</span>
                            <span class="text-xl">Leefbaarheid</span>
                        </div>
                        <span id="score-val" class="text-4xl font-black">6.0</span>
                    </article>

                    {{-- Extra info blok (optioneel, voor opvulling) --}}
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 text-sm text-gray-600">
                        <p>Tip: Probeer een balans te vinden tussen wonen, groen en voorzieningen voor de hoogste score.</p>
                    </div>
                </aside>

            </div>
        </div>
    </div>

    {{-- 4. JavaScript --}}
    <script>
        const dbFunctions = @json($jsFunctionsData);

        const availableFunctions = [
            { id: 'empty', name: 'Kavel', color_hex: '#ffffff', category: 'Leeg', livability: 0, image: null },
            ...dbFunctions
        ];

        let gridState = Array(12).fill(0);

        function allowDrop(ev) { ev.preventDefault(); }

        function drag(ev, dbId) {
            const indexInArray = availableFunctions.findIndex(f => f.id === dbId);
            ev.dataTransfer.setData("funcIndex", indexInArray);
            ev.dataTransfer.effectAllowed = "copy";
        }

        function drop(ev, cellIndex) {
            ev.preventDefault();
            leaveDrag(cellIndex);
            const funcIndex = ev.dataTransfer.getData("funcIndex");
            if (funcIndex !== "") applyFunctionToCell(cellIndex, parseInt(funcIndex));
        }

        function enterDrag(index) { document.getElementById(`cell-${index}`).classList.add('drag-over'); }
        function leaveDrag(index) { document.getElementById(`cell-${index}`).classList.remove('drag-over'); }

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
                // Verwijder eventuele inline styling als die er was
                cell.style.borderWidth = '';
                cell.style.borderColor = '';
            } else {
                cell.classList.remove('border-gray-300');
                cell.classList.add('shadow-sm');

                // Image Container
                if (func.image) {
                    const img = document.createElement('img');
                    img.src = func.image;
                    img.className = 'w-full h-full object-cover absolute top-0 left-0';
                    img.style.pointerEvents = 'none';
                    cell.appendChild(img);
                }

                // Name Label
                const span = document.createElement('span');
                span.className = 'font-bold text-[0.7rem] lg:text-sm leading-tight relative z-10 drop-shadow-md bg-white/90 px-2 py-0.5 rounded mt-auto mb-1';
                span.innerText = func.name;

                // Kleine stijl aanpassing voor leesbaarheid
                span.style.borderBottom = `3px solid ${func.color_hex}`;
                span.style.color = "#333";

                cell.appendChild(span);
            }
        }

        function updateScore() {
            let score = 6.0;
            gridState.forEach(funcIndex => {
                const func = availableFunctions[funcIndex];
                if(funcIndex !== 0 && func) {
                    const difference = func.livability - 100;
                    score += (difference * 0.01);
                }
            });
            score = Math.max(1, Math.min(10, score));
            document.getElementById('score-val').innerText = score.toFixed(1);
        }
    </script>
</x-app-layout>
