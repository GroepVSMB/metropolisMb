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
        /* Scrollbar styling for the list */
        .scroller::-webkit-scrollbar { width: 6px; }
        .scroller::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 4px; }
    </style>
</head>
<body class="bg-white text-[#333333]">

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

    <aside class="w-full lg:w-1/4 min-w-[250px] order-2 lg:order-1">
        <h2 class="text-[#be1e2d] text-xl lg:text-2xl font-bold border-b-2 border-gray-200 pb-2 mb-3 lg:mb-5">Functies</h2>
        <ul class="space-y-1 lg:space-y-0 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2 lg:gap-0">
            @foreach($functions as $function)
                <li class="group flex items-center py-2 lg:py-3 text-base lg:text-lg font-medium text-[#be1e2d] cursor-pointer hover:text-black hover:bg-gray-50 transition-colors border-b border-gray-100">
                    <span class="font-bold text-[#be1e2d] mr-3 text-lg lg:text-xl hidden lg:inline">&gt;</span>
                    <span class="lg:hidden w-3 h-3 rounded-full mr-2" style="background-color: {{ $function->color_hex }}"></span>
                    {{ $function->name }}
                    <span class="ml-auto text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded uppercase font-normal">{{ $function->category }}</span>
                </li>
            @endforeach
        </ul>
    </aside>

    <section class="w-full lg:w-2/4 flex flex-col items-center order-1 lg:order-2">
        <div class="bg-[#eef2f5] p-2 lg:p-5 rounded shadow-inner w-full box-border">
            <div class="grid grid-cols-4 grid-rows-3 gap-1 lg:gap-2 w-full aspect-[4/3]">
                @for($i = 0; $i < 12; $i++)
                    <div onclick="cycleCell({{ $i }})"
                         id="cell-{{ $i }}"
                         class="bg-white border border-gray-300 flex flex-col items-center justify-center text-center cursor-pointer text-xs lg:text-sm text-gray-400 transition-all hover:border-[#be1e2d] select-none p-0.5 lg:p-1 overflow-hidden active:scale-95">
                        Kavel {{ $i + 1 }}
                    </div>
                @endfor
            </div>
        </div>

        <div class="mt-6 w-full flex gap-2">
            <input type="text" id="save-name" placeholder="Naam van ontwerp..." class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#be1e2d]">
            <button onclick="saveLayout()" class="bg-[#be1e2d] text-white px-4 py-2 rounded shadow hover:bg-[#a71c26] transition font-bold text-sm">
                Opslaan
            </button>
        </div>
    </section>

    <aside class="w-full lg:w-1/4 min-w-[250px] flex flex-col gap-3 lg:gap-5 order-3">

        <article class="bg-[#448a28] p-4 text-white font-bold flex justify-between items-center shadow-md rounded">
            <span>Leefbaarheid</span>
            <span id="score-val" class="text-3xl">6.0</span>
        </article>

        <div class="bg-gray-50 border border-gray-200 rounded p-4 shadow-sm flex-1 flex flex-col max-h-[400px]">
            <h3 class="font-bold text-[#be1e2d] text-lg mb-3 border-b border-gray-200 pb-2">Opgeslagen Plannen</h3>

            <div id="saved-list" class="scroller space-y-2 overflow-y-auto flex-1 pr-1">
                <p class="text-sm text-gray-400 italic">Laden...</p>
            </div>
        </div>
    </aside>

</main>

<script>
    const dbFunctions = @json($functions);

    // Include 'Empty' state
    const availableFunctions = [
        { id: 'empty', name: 'Kavel', color_hex: '#ffffff', text_color: '#9ca3af', category: 'Leeg' },
        ...dbFunctions
    ];

    let gridState = Array(12).fill(0);

    // --- GAME LOGIC ---

    function cycleCell(index) {
        const currentFuncIndex = gridState[index];
        const nextFuncIndex = (currentFuncIndex + 1) % availableFunctions.length;
        gridState[index] = nextFuncIndex;
        updateCellUI(index, availableFunctions[nextFuncIndex]);
        updateScore();
    }

    function updateCellUI(index, func) {
        const cell = document.getElementById(`cell-${index}`);
        cell.style.backgroundColor = func.color_hex;
        cell.style.color = func.text_color;

        if(func.id === 'empty') {
            cell.innerHTML = `Kavel ${index + 1}`;
            cell.classList.add('border-gray-300');
        } else {
            cell.innerHTML = `<span class="font-bold text-[0.7rem] lg:text-sm leading-tight">${func.name}</span>`;
            cell.classList.remove('border-gray-300');
        }
    }

    function updateScore() {
        let score = 6.0;
        gridState.forEach(funcIndex => {
            const func = availableFunctions[funcIndex];
            if(func.category === 'Groen') score += 0.5;
            if(func.category === 'Werken') score -= 0.3;
            if(func.category === 'Dienst') score += 0.2;
            if(func.category === 'Wonen') score += 0.1;
        });
        score = Math.max(1, Math.min(10, score));
        document.getElementById('score-val').innerText = score.toFixed(1);
    }

    // --- CRUD LOGIC ---

    // Helper for CSRF token
    const getCsrf = () => document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // 1. CREATE (Save)
    function saveLayout() {
        const name = document.getElementById('save-name').value;
        if(!name) return alert('Vul een naam in');

        fetch('/simulation/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
            body: JSON.stringify({ gridState, name }),
        })
            .then(r => r.json())
            .then(data => {
                document.getElementById('save-name').value = ''; // Clear input
                fetchList(); // Refresh list
            });
    }

    // 2. READ (List)
    function fetchList() {
        fetch('/simulation/list')
            .then(r => r.json())
            .then(data => {
                const container = document.getElementById('saved-list');
                container.innerHTML = ''; // Clear current

                if(data.length === 0) {
                    container.innerHTML = '<p class="text-sm text-gray-400 text-center py-4">Geen plannen gevonden.</p>';
                    return;
                }

                data.forEach(sim => {
                    const date = new Date(sim.created_at).toLocaleDateString();
                    const item = document.createElement('div');
                    item.className = 'bg-white p-2 rounded border border-gray-100 shadow-sm flex justify-between items-center group';
                    item.innerHTML = `
                            <div onclick="loadLayout(${sim.id})" class="cursor-pointer flex-1">
                                <div class="font-bold text-sm text-gray-700 group-hover:text-[#be1e2d]">${sim.name}</div>
                                <div class="text-xs text-gray-400">${date}</div>
                            </div>
                            <button onclick="deleteLayout(${sim.id})" class="text-gray-300 hover:text-red-600 p-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        `;
                    container.appendChild(item);
                });
            });
    }

    // 3. READ (Load specific)
    function loadLayout(id) {
        fetch(`/simulation/${id}`)
            .then(r => r.json())
            .then(data => {
                gridState = data.gridState;
                // Redraw grid
                gridState.forEach((funcIndex, index) => {
                    updateCellUI(index, availableFunctions[funcIndex]);
                });
                updateScore();
            });
    }

    // 4. DELETE
    function deleteLayout(id) {
        if(!confirm('Zeker weten?')) return;

        fetch(`/simulation/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': getCsrf() }
        })
            .then(r => r.json())
            .then(() => fetchList());
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        // Init empty grid UI
        gridState.forEach((_, i) => updateCellUI(i, availableFunctions[0]));
        fetchList();
    });
</script>
</body>
</html>
