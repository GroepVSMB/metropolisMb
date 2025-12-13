<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Metropolis - Gemeente Portaal</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .scroller::-webkit-scrollbar { width: 6px; }
        .scroller::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 4px; }
        
        .dragging { opacity: 0.5; }
        .drag-over { border-color: #be1e2d !important; border-width: 2px !important; transform: scale(1.02); }
    </style>
</head>
<body class="bg-white text-[#333333]">

@php
    $jsFunctionsData = $functions->map(function($f) {
        return [
            'id' => $f->id,
            'name' => $f->name,
            'category' => $f->category->name ?? 'Onbekend',
            'color_hex' => $f->category->color_hex ?? '#cccccc',
            'livability' => $f->livability_number,
            'image' => $f->image, // Added Image
        ];
    });

    $groupedFunctions = $functions->groupBy(fn($f) => $f->category->name ?? 'Overig');
@endphp

<header class="bg-white p-3 lg:p-5 px-4 lg:px-10 border-b-4 border-[#be1e2d] flex items-center gap-3 lg:gap-4 sticky top-0 z-50 shadow-sm">
    <div class="w-[35px] h-[35px] lg:w-[50px] lg:h-[50px] grid grid-cols-2 gap-1 rotate-45 mr-3 lg:mr-5 ml-1 lg:ml-2 shrink-0">
        <div class="bg-[#be1e2d] w-full h-full"></div>
        <div class="bg-[#e35205] w-full h-full"></div>
        <div class="bg-[#be1e2d] w-full h-full"></div>
        <div class="bg-[#be1e2d] w-full h-full"></div>
    </div>
    <h1 class="text-xl lg:text-[2.5rem] font-extrabold uppercase tracking-wide text-[#be1e2d] leading-none">
        Metropolis
    </h1>
</header>

<main class="flex flex-col lg:flex-row gap-6 lg:gap-10 max-w-[1400px] mx-auto my-4 lg:my-10 px-3 lg:px-5">

    <aside class="w-full lg:w-1/4 min-w-[250px] order-2 lg:order-1 h-[calc(100vh-150px)] overflow-y-auto scroller pr-2">
        <h2 class="text-[#be1e2d] text-xl lg:text-2xl font-bold border-b-2 border-gray-200 pb-2 mb-3 lg:mb-5">Functies</h2>
        
        <div class="space-y-6">
            @foreach($groupedFunctions as $categoryName => $catFunctions)
                <div>
                    <h3 class="text-sm uppercase font-bold text-gray-400 mb-2 tracking-wider">{{ $categoryName }}</h3>
                    <ul class="space-y-1">
                        @foreach($catFunctions as $function)
                            <li draggable="true"
                                ondragstart="drag(event, {{ $function->id }})"
                                class="group flex items-center p-2 bg-gray-50 rounded border border-gray-200 cursor-grab active:cursor-grabbing hover:border-[#be1e2d] hover:shadow-sm transition-all select-none">
                                
                                @if($function->image)
                                    <img src="{{ $function->image }}" class="w-10 h-10 rounded mr-3 object-cover border border-gray-200">
                                @else
                                    <span class="w-10 h-10 rounded mr-3 bg-gray-200 block"></span>
                                @endif
                                
                                <span class="font-medium text-gray-700 group-hover:text-[#be1e2d]">{{ $function->name }}</span>
                                <svg class="w-4 h-4 ml-auto text-gray-300 group-hover:text-[#be1e2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </aside>

    <section class="w-full lg:w-2/4 flex flex-col items-center order-1 lg:order-2">
        <div class="bg-[#eef2f5] p-2 lg:p-5 rounded shadow-inner w-full box-border">
            <div class="grid grid-cols-4 grid-rows-3 gap-1 lg:gap-2 w-full aspect-[4/3]">
                @for($i = 0; $i < 12; $i++)
                    <div 
                         id="cell-{{ $i }}"
                         onclick="handleCellClick({{ $i }})"
                         ondrop="drop(event, {{ $i }})"
                         ondragover="allowDrop(event)"
                         ondragenter="enterDrag({{ $i }})"
                         ondragleave="leaveDrag({{ $i }})"
                         class="bg-white border border-gray-300 flex flex-col items-center justify-center text-center cursor-pointer text-xs lg:text-sm text-gray-400 transition-all hover:border-[#be1e2d] select-none p-0.5 lg:p-1 overflow-hidden active:scale-95 relative">
                        Kavel {{ $i + 1 }}
                    </div>
                @endfor
            </div>
            <p class="text-center text-xs text-gray-400 mt-2">Sleep functies om te plaatsen. Klik op een functie om deze te verwijderen.</p>
        </div>
    </section>

    <aside class="w-full lg:w-1/4 min-w-[250px] flex flex-col gap-3 lg:gap-5 order-3">
        <article class="bg-[#448a28] p-4 text-white font-bold flex justify-between items-center shadow-md rounded">
            <span>Leefbaarheid</span>
            <span id="score-val" class="text-3xl">6.0</span>
        </article>
    </aside>

</main>

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
        // cell.style.backgroundColor = func.color_hex;
        
        // Clear current content
        cell.innerHTML = '';

        if(func.id === 'empty') {
            cell.innerText = `Kavel ${index + 1}`;
            
            cell.classList.add('border-gray-300');
            cell.classList.remove('shadow-sm');
        } else {
            cell.classList.remove('border-gray-300');
            cell.classList.add('shadow-sm');
           
       

            // Image Container
            if (func.image) {
                const img = document.createElement('img');
                img.src = func.image;
                img.className = 'w-full h-full object-cover absolute top-0 left-0';
                img.style.pointerEvents = 'none'; // Ensure clicks pass through to div
                cell.appendChild(img);
            }

            // Name Label (On top of image)
            const span = document.createElement('span');
            span.className = 'font-bold text-[0.7rem] lg:text-sm leading-tight relative z-10 drop-shadow-md bg-white/80 px-1 rounded mt-auto mb-1';
            span.innerText = func.name;
            span.style.backgroundColor = func.color_hex;
            span.style.color = "white"
            cell.appendChild(span);
        }
    }

    function updateScore() {
        let score = 6.0;
        gridState.forEach(funcIndex => {
            const func = availableFunctions[funcIndex];
            if(funcIndex !== 0 && func) {
                // Livability logic: 100 is neutral. 
                // Difference / 100 = grade impact. (e.g. +50 diff = +0.5 grade)
                const difference = func.livability - 100;
                score += (difference * 0.01);
            }
        });
        score = Math.max(1, Math.min(10, score));
        document.getElementById('score-val').innerText = score.toFixed(1);
    }
</script>
</body>
</html>