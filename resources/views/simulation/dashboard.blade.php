<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Metropolis - Gemeente Portaal</title>
    
    <!-- Tailwind CSS (Using stable v3.4 CDN for maximum compatibility) -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <!-- Custom Font (Segoe UI fallback) -->
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    </style>
</head>
<body class="bg-white text-[#333333]">

    <!-- HEADER -->
    <header class="bg-white p-3 lg:p-5 px-4 lg:px-10 border-b-4 border-[#be1e2d] flex items-center gap-3 lg:gap-4 sticky top-0 z-50 shadow-sm lg:shadow-none" role="banner">
        <!-- Logo Icon (Decorative) -->
        <div class="w-[35px] h-[35px] lg:w-[50px] lg:h-[50px] grid grid-cols-2 gap-1 rotate-45 mr-3 lg:mr-5 ml-1 lg:ml-2 shrink-0" aria-hidden="true">
            <div class="bg-[#be1e2d] w-full h-full"></div>
            <div class="bg-[#e35205] w-full h-full"></div>
            <div class="bg-[#be1e2d] w-full h-full"></div>
            <div class="bg-[#be1e2d] w-full h-full"></div>
        </div>
        <!-- Title -->
        <h1 class="text-xl lg:text-[2.5rem] font-extrabold uppercase tracking-wide text-[#be1e2d] leading-none">
            Metropolis
        </h1>
    </header>

    <!-- MAIN CONTENT -->
    <main class="flex flex-col lg:flex-row gap-6 lg:gap-10 max-w-[1400px] mx-auto my-4 lg:my-10 px-3 lg:px-5">
        
        <!-- 1. LIBRARY (Left Sidebar) -->
        <aside class="w-full lg:w-1/4 min-w-[250px] order-2 lg:order-1" aria-labelledby="library-heading">
            <h2 id="library-heading" class="text-[#be1e2d] text-xl lg:text-2xl font-bold border-b-2 border-gray-200 pb-2 mb-3 lg:mb-5">
                Functies
            </h2>
            <ul class="space-y-1 lg:space-y-0 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2 lg:gap-0" role="list" aria-label="Beschikbare stadsfuncties">
                @foreach($functions as $function)
                    <li class="group flex items-center py-2 lg:py-3 text-base lg:text-lg font-medium text-[#be1e2d] cursor-grab hover:text-black hover:underline transition-colors border border-gray-100 lg:border-none p-2 lg:p-0 rounded lg:rounded-none bg-gray-50 lg:bg-transparent" 
                        draggable="true" 
                        aria-label="{{ $function->name }} in categorie {{ $function->category }}">
                        
                        <span class="font-bold text-[#be1e2d] mr-3 text-lg lg:text-xl hidden lg:inline" aria-hidden="true">&gt;</span>
                        
                        <!-- Mobile Circle Indicator (Hidden from screen readers as text conveys meaning) -->
                        <span class="lg:hidden w-3 h-3 rounded-full mr-2" style="background-color: {{ $function->color_hex }}" aria-hidden="true"></span>
                        
                        {{ $function->name }}
                        
                        <span class="ml-auto text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded uppercase font-normal">
                            {{ $function->category }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </aside>

        <!-- 2. GRID (Center) -->
        <section class="w-full lg:w-2/4 flex flex-col items-center order-1 lg:order-2" aria-label="Simulatie Grid">
            <!-- Container with tighter padding on mobile -->
            <div class="bg-[#eef2f5] p-2 lg:p-5 rounded shadow-inner w-full box-border">
                <div class="grid grid-cols-4 grid-rows-3 gap-1 lg:gap-2 w-full aspect-[4/3]" role="grid" aria-label="Stadsgebied van 3 bij 4 vakken">
                    @for($i = 0; $i < 12; $i++)
                        <!-- 
                            Accessibility Features Added:
                            - role="button": Tells screen reader this is clickable
                            - tabindex="0": Makes it focusable via keyboard (Tab key)
                            - aria-label: Dynamic description (updated via JS)
                            - onkeydown: Allows activating via Enter/Space key
                        -->
                        <div role="button"
                             tabindex="0"
                             onclick="cycleCell({{ $i }})" 
                             onkeydown="handleKey(event, {{ $i }})"
                             id="cell-{{ $i }}" 
                             aria-label="Kavel {{ $i + 1 }}, Huidige status: Leeg. Klik om te wijzigen."
                             class="bg-white border border-gray-300 flex flex-col items-center justify-center text-center cursor-pointer text-xs lg:text-sm text-gray-400 transition-all hover:border-[#be1e2d] hover:shadow-md focus:ring-2 focus:ring-[#be1e2d] focus:outline-none select-none p-0.5 lg:p-1 min-h-0 relative overflow-hidden active:scale-95">
                            Kavel {{ $i + 1 }}
                        </div>
                    @endfor
                </div>
            </div>
            <p class="mt-3 text-gray-500 italic text-xs lg:text-sm text-center">
                Klik op een vakje of gebruik Enter om functies te wisselen.
            </p>
        </section>

        <!-- 3. EFFECTS (Right Sidebar) -->
        <aside class="w-full lg:w-1/4 min-w-[250px] flex flex-col gap-3 lg:gap-5 order-3" aria-labelledby="effects-heading">
            <h2 id="effects-heading" class="sr-only">Simulatie Effecten</h2>
            
            <!-- Tile: Leefbaarheid -->
            <article class="bg-[#448a28] p-4 lg:p-6 text-white font-bold text-lg min-h-[100px] lg:min-h-[120px] flex flex-col justify-between shadow-md transition-transform hover:-translate-y-0.5 rounded lg:rounded-none">
                <span>Leefbaarheid</span>
                <span id="score-val" class="text-4xl lg:text-5xl self-end leading-none" aria-live="polite">7.5</span>
            </article>

            <!-- Tile: Meldingen -->
            <article class="bg-[#be1e2d] p-4 lg:p-6 text-white font-bold text-lg min-h-[100px] lg:min-h-[120px] flex flex-col justify-between shadow-md transition-transform hover:-translate-y-0.5 rounded lg:rounded-none">
                <span>Meldingen</span>
                <span class="text-sm lg:text-base font-normal mt-2">
                    Geen actieve meldingen.
                </span>
            </article>

            <!-- Tile: Status -->
            <article class="bg-[#1f4e79] p-4 lg:p-6 text-white font-bold text-lg min-h-[100px] lg:min-h-[120px] flex flex-col justify-between shadow-md transition-transform hover:-translate-y-0.5 rounded lg:rounded-none">
                <span>Status</span>
                <span class="text-sm lg:text-base font-normal mt-2">
                    Sprint: 1<br>
                    Fase: Concept
                </span>
            </article>

        </aside>

    </main>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        // Pass PHP variable to JavaScript
        const dbFunctions = @json($functions);
        
        // Add "Empty" state to the beginning of the list for cycling
        const availableFunctions = [
            { id: 'empty', name: 'Kavel', color_hex: '#ffffff', text_color: '#9ca3af', category: 'Leeg' }, 
            ...dbFunctions
        ];

        // Track state of 12 cells (default to index 0: empty)
        let gridState = Array(12).fill(0); 

        function cycleCell(index) {
            // Calculate next function index
            const currentFuncIndex = gridState[index];
            const nextFuncIndex = (currentFuncIndex + 1) % availableFunctions.length;
            
            // Update state
            gridState[index] = nextFuncIndex;
            
            // Update UI
            updateCellUI(index, availableFunctions[nextFuncIndex]);
            updateScore();
        }

        // Accessibility: Allow keyboard interaction (Enter/Space)
        function handleKey(event, index) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault(); // Prevent scrolling on Space
                cycleCell(index);
            }
        }

        function updateCellUI(index, func) {
            const cell = document.getElementById(`cell-${index}`);
            
            // Update Styling
            cell.style.backgroundColor = func.color_hex;
            cell.style.color = func.text_color;
            
            // Update Text
            if(func.id === 'empty') {
                cell.innerHTML = `Kavel ${index + 1}`;
                cell.classList.add('border-gray-300');
                cell.style.fontWeight = 'normal';
                // Accessiblity Update
                cell.setAttribute('aria-label', `Kavel ${index + 1}, Huidige status: Leeg. Klik om te wijzigen.`);
            } else {
                // Simplified text for mobile to fit better
                cell.innerHTML = `<span class="font-bold text-[0.7rem] lg:text-sm leading-tight">${func.name}</span>`;
                cell.classList.remove('border-gray-300');
                // Accessiblity Update
                cell.setAttribute('aria-label', `Kavel ${index + 1}, Huidige status: ${func.name} (${func.category}). Klik om te wijzigen.`);
            }
        }

        function updateScore() {
            // Simple simulation logic
            let score = 6.0;
            gridState.forEach(funcIndex => {
                const func = availableFunctions[funcIndex];
                if(func.category === 'Groen') score += 0.5;
                if(func.category === 'Werken') score -= 0.3;
                if(func.category === 'Dienst') score += 0.2;
                if(func.category === 'Wonen') score += 0.1;
            });
            
            // Clamp between 1 and 10
            score = Math.max(1, Math.min(10, score));
            document.getElementById('score-val').innerText = score.toFixed(1);
        }
    </script>
</body>
</html>
