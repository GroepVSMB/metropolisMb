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
                    <div id="live-feedback" class="fixed top-28 left-1/2 transform -translate-x-1/2 z-[100] w-auto min-w-[300px] text-center hidden pointer-events-none transition-all duration-200"></div>
                    <div class="bg-[#eef2f5] p-2 lg:p-5 rounded-lg shadow-inner w-full box-border border border-gray-200">
                        <div class="bg-[#eef2f5] p-2 lg:p-5 rounded-lg shadow-inner w-full box-border border border-gray-200 relative">
                            <div id="three-container"
                                 class="w-full aspect-[4/3] cursor-grab active:cursor-grabbing rounded overflow-hidden relative shadow-sm bg-sky-100"
                                 ondrop="handle3DDrop(event)"
                                 ondragover="handle3DDragOver(event)">
                            </div>

                            <div id="loading-overlay" class="absolute inset-0 flex items-center justify-center bg-white/80 z-10 pointer-events-none transition-opacity duration-500">
                                <span class="text-metro-darkred font-bold animate-pulse">3D Wereld laden...</span>
                            </div>
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

        let gridState = Array(36).fill(0);
        let currentDragIndex = null;
        let activeEvents = [];

        // GLOBAL SCORE TRACKING
        let currentAverageScore = 100;
        let deltaTimeout = null;

        // --- 3D ENGINE VARIABLES ---
        let scene, camera, renderer, raycaster, mouse;
        let gridMeshes = []; // Stores the 36 ground tiles
        let buildingMeshes = []; // Stores the placed buildings
        const container = document.getElementById('three-container');
        const loadingOverlay = document.getElementById('loading-overlay');

        // --- CAMERA CONTROLS ---
        let isDragging = false;
        let mouseDidMove = false;
        let previousMousePosition = { x: 0, y: 0 };
        const cameraTarget = new THREE.Vector3(0, 0, 5);


        // Grid Config
        const COLS = 6;
        const ROWS = 6;
        const TILE_SIZE = 10;
        const GAP = 1;

        // --- INITIALIZATION ---
        function init3D() {
            // 1. Scene & Camera
            scene = new THREE.Scene();
            scene.background = new THREE.Color(0xeef2f5); // Match dashboard bg
            scene.fog = new THREE.Fog(0xeef2f5, 50, 150);

            const aspect = container.clientWidth / container.clientHeight;
            camera = new THREE.PerspectiveCamera(45, aspect, 0.1, 1000);

            // New position for 6x6 grid
            camera.position.set(0, 70, 60);
            camera.lookAt(cameraTarget);

            // 2. Renderer
            renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setSize(container.clientWidth, container.clientHeight);
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            container.appendChild(renderer.domElement);

            // 3. Lighting
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.7);
            scene.add(ambientLight);

            const dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
            dirLight.position.set(30, 60, 30);
            dirLight.castShadow = true;
            dirLight.shadow.mapSize.width = 2048;
            dirLight.shadow.mapSize.height = 2048;
            scene.add(dirLight);

            // 4. Setup Grid (Ground)
            createGroundGrid();

            // 5. Tools
            raycaster = new THREE.Raycaster();
            mouse = new THREE.Vector2();

            // 6. Animation Loop
            animate();

            // Hide loader
            setTimeout(() => loadingOverlay.classList.add('opacity-0'), 500);

            // 7. Event Listeners
            container.addEventListener('mousedown', onMouseDown, false);
            container.addEventListener('mousemove', onCameraMove, false);
            container.addEventListener('mouseup', onMouseUp, false);
            container.addEventListener('mouseleave', onMouseUp, false); // Stop drag if mouse leaves
            window.addEventListener('resize', onWindowResize, false);
        }

        function onMouseDown(event) {
            isDragging = true;
            mouseDidMove = false;
            container.style.cursor = 'grabbing';
            previousMousePosition.x = event.clientX;
            previousMousePosition.y = event.clientY;
        }

        function onCameraMove(event) {
            if (isDragging) {
                mouseDidMove = true;
                const deltaX = event.clientX - previousMousePosition.x;
                const deltaY = event.clientY - previousMousePosition.y;

                // Adjust the sensitivity of the pan
                const panSpeed = 0.1;

                const panDelta = new THREE.Vector3(-deltaX * panSpeed, 0, -deltaY * panSpeed);
                
                // Apply the pan to both camera and its target
                camera.position.add(panDelta);
                cameraTarget.add(panDelta);
                camera.lookAt(cameraTarget);

                previousMousePosition.x = event.clientX;
                previousMousePosition.y = event.clientY;
            } else {
                 // If not dragging, handle the hover/tooltip logic
                handle3DMouseMove(event);
            }
        }

        function onMouseUp(event) {
            if (isDragging) {
                isDragging = false;
                container.style.cursor = 'grab';
            }
            // If the mouse didn't move between down and up, it's a click
            if (!mouseDidMove) {
                handle3DClick(event);
            }
        }

        function createGroundGrid() {
            const geometry = new THREE.BoxGeometry(TILE_SIZE, 1, TILE_SIZE);

            // Calculate offset to center the grid
            const startX = -((COLS * (TILE_SIZE + GAP)) / 2) + TILE_SIZE/2;
            const startZ = -((ROWS * (TILE_SIZE + GAP)) / 2) + TILE_SIZE/2;

            for (let i = 0; i < (COLS * ROWS); i++) {
                const material = new THREE.MeshStandardMaterial({
                    color: 0xffffff,
                    roughness: 0.8
                });
                const tile = new THREE.Mesh(geometry, material);

                // Grid Logic to 3D Position
                const col = i % COLS;
                const row = Math.floor(i / COLS);

                tile.position.x = startX + (col * (TILE_SIZE + GAP));
                tile.position.z = startZ + (row * (TILE_SIZE + GAP));
                tile.position.y = -0.5; // Slightly below 0

                tile.receiveShadow = true;
                tile.userData = { id: i, type: 'ground' }; // Store index for logic

                scene.add(tile);
                gridMeshes.push(tile);
            }
        }

        // --- RENDERING STATE ---
        function update3DScene() {
            buildingMeshes.forEach(mesh => scene.remove(mesh));
            buildingMeshes = [];

            gridState.forEach((funcIndex, index) => {
                if (funcIndex !== 0) {
                    const func = availableFunctions[funcIndex];
                    const groundTile = gridMeshes[index];
                    // CHANGED: Passed 'index' as 3rd argument
                    createBuilding(func, groundTile.position, index);
                }
            });
        }

        function createBuilding(func, position, index) {
            let group = new THREE.Group();

            // --- MATERIAL & GEOMETRY HELPERS ---
            const createWedge = (width, height, depth) => {
                const shape = new THREE.Shape();
                shape.moveTo(0, 0);
                shape.lineTo(width / 2, height);
                shape.lineTo(width, 0);
                shape.lineTo(0, 0);
                const extrudeSettings = { depth, bevelEnabled: false };
                return new THREE.ExtrudeGeometry(shape, extrudeSettings);
            };

            // 1. PARK (Groen) - More detail, varied trees, bench
            if (func.category === 'Groen') {
                // Base
                const baseGeo = new THREE.BoxGeometry(9, 0.5, 9);
                const baseMat = new THREE.MeshStandardMaterial({ color: 0x5ea664 }); // Grass Green
                const base = new THREE.Mesh(baseGeo, baseMat);
                base.position.y = 0.25;
                group.add(base);

                // Pond
                const pondGeo = new THREE.CylinderGeometry(2.5, 2.5, 0.4, 16);
                const pondMat = new THREE.MeshStandardMaterial({ color: 0x4fc3f7, roughness: 0.1, metalness: 0.2 });
                const pond = new THREE.Mesh(pondGeo, pondMat);
                pond.position.set(2, 0.5, 2);
                group.add(pond);

                // Trees (varied)
                for (let i = 0; i < 5; i++) {
                    const treeGroup = new THREE.Group();
                    const trunkMat = new THREE.MeshStandardMaterial({ color: 0x8d6e63 });
                    const trunk = new THREE.Mesh(new THREE.CylinderGeometry(0.2, 0.2, 1.5, 6), trunkMat);
                    trunk.position.y = 0.75;
                    treeGroup.add(trunk);

                    // Randomly choose tree type
                    if (Math.random() > 0.4) {
                        // Conical Tree
                        const leaves = new THREE.Mesh(new THREE.ConeGeometry(1.2, 2.5, 8), new THREE.MeshStandardMaterial({ color: 0x2e7d32 }));
                        leaves.position.y = 2.5;
                        treeGroup.add(leaves);
                    } else {
                        // Spherical Tree
                        const leaves = new THREE.Mesh(new THREE.SphereGeometry(1.1, 8, 6), new THREE.MeshStandardMaterial({ color: 0x4caf50 }));
                        leaves.position.y = 2.2;
                        treeGroup.add(leaves);
                    }

                    treeGroup.position.set((Math.random() * 8) - 4, 0.5, (Math.random() * 8) - 4);
                    if (treeGroup.position.distanceTo(pond.position) > 3) {
                        group.add(treeGroup);
                    }
                }
                
                // Park Bench
                const benchGroup = new THREE.Group();
                const benchMat = new THREE.MeshStandardMaterial({color: 0x6d4c41});
                const seatGeo = new THREE.BoxGeometry(2, 0.2, 0.5);
                const backGeo = new THREE.BoxGeometry(2, 0.8, 0.15);
                
                const seat = new THREE.Mesh(seatGeo, benchMat);
                seat.position.y = 0.8;
                
                const back = new THREE.Mesh(backGeo, benchMat);
                back.position.set(0, 1.3, -0.175);
                
                benchGroup.add(seat, back);
                benchGroup.position.set(-3, 0.5, -3); // Place it somewhere
                group.add(benchGroup);
            }
            // 2. INDUSTRY (Industrie) - More rooftop detail and pipes
            else if (func.category === 'Industrie') {
                const mainMat = new THREE.MeshStandardMaterial({ color: func.color_hex || 0x78909c, metalness: 0.1, roughness: 0.8 });
                
                // Main Factory Hall (larger base)
                const mainGeo = new THREE.BoxGeometry(8, 4, 9);
                const main = new THREE.Mesh(mainGeo, mainMat);
                main.position.y = 2;
                group.add(main);

                // Second story/office
                const officeGeo = new THREE.BoxGeometry(4, 2, 8);
                const office = new THREE.Mesh(officeGeo, new THREE.MeshStandardMaterial({ color: 0xb0bec5 }));
                office.position.set(-2, 5, 0); // On top of main hall
                group.add(office);

                // Smokestacks
                const stackMat = new THREE.MeshStandardMaterial({ color: 0x546e7a });
                const stack1 = new THREE.Mesh(new THREE.CylinderGeometry(0.7, 0.9, 8, 12), stackMat);
                stack1.position.set(3, 4, 3);
                group.add(stack1);

                const stack2 = new THREE.Mesh(new THREE.CylinderGeometry(0.7, 0.9, 7, 12), stackMat);
                stack2.position.set(3, 3.5, -3);
                group.add(stack2);

                // Connecting Pipe
                const pipeGeo = new THREE.CylinderGeometry(0.2, 0.2, 6, 8);
                const pipe = new THREE.Mesh(pipeGeo, new THREE.MeshStandardMaterial({color: 0x9e9e9e}));
                pipe.rotation.x = Math.PI / 2;
                pipe.position.set(3, 5, 0);
                group.add(pipe);

                // Rooftop machinery
                 for(let i = 0; i < 3; i++) {
                    const vent = new THREE.Mesh(
                        new THREE.BoxGeometry(0.5, 0.8, 0.5), 
                        new THREE.MeshStandardMaterial({color: 0x607d8b})
                    );
                    vent.position.set(-3 + i * 1.5, 6.4, -2);
                    group.add(vent);
                }
            }
            // 3. LIVING (Wonen) - Gabled roof, chimney, windows
            else if (func.category === 'Wonen') {
                // Main House Body
                const bodyMat = new THREE.MeshStandardMaterial({ color: 0xf5f5dc }); // Cream color
                const bodyGeo = new THREE.BoxGeometry(6, 4, 7);
                const body = new THREE.Mesh(bodyGeo, bodyMat);
                body.position.y = 2;
                group.add(body);

                // Gabled Roof
                const roofMat = new THREE.MeshStandardMaterial({ color: 0x8d6e63 }); // Brown Roof
                const roofGeo = createWedge(6, 2.5, 7.2);
                const roof = new THREE.Mesh(roofGeo, roofMat);
                roof.position.set(-3, 4, -3.6); // Align with box
                group.add(roof);

                // Door
                const door = new THREE.Mesh(new THREE.BoxGeometry(1.2, 2.2, 0.2), new THREE.MeshStandardMaterial({ color: func.color_hex || '#795548' }));
                door.position.set(0, 1.1, 3.6);
                group.add(door);
                
                // Chimney
                const chimney = new THREE.Mesh(new THREE.BoxGeometry(0.8, 2, 0.8), new THREE.MeshStandardMaterial({color: '#a1887f'}));
                chimney.position.set(2, 5.5, -2);
                group.add(chimney);

                // Windows
                const windowMat = new THREE.MeshStandardMaterial({ color: '#81d4fa', roughness: 0.2 });
                const windowGeo = new THREE.BoxGeometry(1.2, 1.2, 0.1);
                
                const frontWindow = new THREE.Mesh(windowGeo, windowMat);
                frontWindow.position.set(-1.8, 2.5, 3.55);
                group.add(frontWindow);

                const sideWindow1 = new THREE.Mesh(windowGeo, windowMat);
                sideWindow1.position.set(3.05, 2.5, 0);
                sideWindow1.rotation.y = Math.PI / 2;
                group.add(sideWindow1);
                
                const sideWindow2 = new THREE.Mesh(windowGeo, windowMat);
                sideWindow2.position.set(-3.05, 2.5, 0);
                sideWindow2.rotation.y = -Math.PI / 2;
                group.add(sideWindow2);

            }
            // 4. SERVICES / DEFAULT - Modern multi-story block
            else {
                const height = 6 + Math.random() * 4; // Randomized height
                const mainMat = new THREE.MeshStandardMaterial({ color: func.color_hex || 0xcccccc, metalness: 0, roughness: 0.5 });
                const glassMat = new THREE.MeshStandardMaterial({ color: 0x81d4fa, transparent: true, opacity: 0.6, roughness: 0.1 });
                
                // Ground floor (mostly glass)
                const base = new THREE.Mesh(new THREE.BoxGeometry(7, 3, 7), mainMat);
                base.position.y = 1.5;
                group.add(base);
                
                const baseGlass = new THREE.Mesh(new THREE.BoxGeometry(7.1, 2.5, 7.1), glassMat);
                baseGlass.position.y = 1.5;
                group.add(baseGlass);
                
                // Upper floors
                const upperFloors = new THREE.Mesh(new THREE.BoxGeometry(6.5, height - 3, 6.5), mainMat);
                upperFloors.position.y = 3 + (height-3)/2;
                group.add(upperFloors);
                
                // Inset Windows
                const windowInsetMat = new THREE.MeshStandardMaterial({ color: '#263238' });
                for(let y = 4; y < height - 1; y += 1.5) {
                     const inset = new THREE.Mesh(new THREE.BoxGeometry(6.6, 1, 1), windowInsetMat);
                     inset.position.set(0, y, 3);
                     group.add(inset);

                     const inset2 = inset.clone();
                     inset2.position.set(0, y, -3);
                     group.add(inset2);

                     const inset3 = inset.clone();
                     inset3.rotation.y = Math.PI / 2;
                     inset3.position.set(3, y, 0);
                     group.add(inset3);
                     
                     const inset4 = inset3.clone();
                     inset4.position.set(-3, y, 0);
                     group.add(inset4);
                }
            }

            // --- GLOBAL ADJUSTMENTS ---
            group.position.set(position.x, position.y, position.z);

            // IMPORTANT: Attach ID to the whole group so Raycaster finds it
            group.userData = { id: index, type: 'building' };

            // Traverse group to ensure all parts cast shadows and inherit ID
            group.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                    // If child has no user data, give it the parent's ID so clicking a tree works
                    if(!child.userData.id) child.userData = { id: index, type: 'building_part' };
                }
            });

            // Animation
            group.scale.set(0,0,0);
            animatePopIn(group);

            scene.add(group);
            buildingMeshes.push(group);
        }

        function animatePopIn(mesh) {
            let scale = 0;
            const target = 1;
            function step() {
                scale += 0.1;
                if(scale < target) {
                    mesh.scale.set(scale, scale, scale);
                    requestAnimationFrame(step);
                } else {
                    mesh.scale.set(1,1,1);
                }
            }
            step();
        }

        function animate() {
            requestAnimationFrame(animate);
            // Slowly rotate generic buildings? (Optional)
            renderer.render(scene, camera);
        }

        function onWindowResize() {
            const width = container.clientWidth;
            const height = container.clientHeight;
            renderer.setSize(width, height);
            camera.aspect = width / height;
            camera.updateProjectionMatrix();
        }

        // --- INTERACTION LOGIC (Raycasting) ---

        function getIntersectedTile(event) {
            const rect = renderer.domElement.getBoundingClientRect();
            mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
            mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

            raycaster.setFromCamera(mouse, camera);

            // Check Ground AND Buildings
            const interactables = [...gridMeshes, ...buildingMeshes];

            // recursive: true is needed for Groups (Trees, Houses)
            const intersects = raycaster.intersectObjects(interactables, true);

            if (intersects.length > 0) {
                // Find the first object that has a valid ID
                // We might hit a tree leaf, we need to climb up to find the ID
                let object = intersects[0].object;

                // Traverse up until we find the userData.id (max 5 levels to be safe)
                let depth = 0;
                while(object && object.userData.id === undefined && depth < 5) {
                    object = object.parent;
                    depth++;
                }

                if(object && object.userData.id !== undefined) {
                    // If we hit a building, return the corresponding Ground Tile
                    // (so the logic remains consistent)
                    return gridMeshes[object.userData.id];
                }
            }
            return null;
        }

        function handle3DDragOver(event) {
            event.preventDefault();
            const tile = getIntersectedTile(event);

            // Reset all colors
            gridMeshes.forEach(m => m.material.emissive.setHex(0x000000));

            if (tile) {
                // Highlight current hover
                tile.material.emissive.setHex(0x555555); // Gray glow
                event.dataTransfer.dropEffect = "copy";
            }
        }

        function handle3DDrop(event) {
            event.preventDefault();
            const tile = getIntersectedTile(event);

            if (tile) {
                const index = tile.userData.id;

                // Re-use your existing logic!
                const check = checkAdjacency(index, currentDragIndex);
                if(check.valid) {
                    playSynthSound('success');
                    applyFunctionToCell(index, currentDragIndex);
                } else {
                    playSynthSound('failure');
                    showError(check.message);
                }
            }
            // Reset colors
            gridMeshes.forEach(m => m.material.emissive.setHex(0x000000));
        }

        function handle3DClick(event) {
            const tile = getIntersectedTile(event);
            if(tile) {
                const index = tile.userData.id;
                if(gridState[index] !== 0) {
                    // Clear cell
                    applyFunctionToCell(index, 0);
                }
            }
        }

        // Tooltip logic for 3D
        function handle3DMouseMove(event) {
            const tile = getIntersectedTile(event);

            if (tile) {
                const index = tile.userData.id;

                // Only show tooltip if the tile has a building on it
                if (gridState[index] !== 0) {
                    // 1. Populate the tooltip content (Title, Impacts, Synergy)
                    executeShowTooltip(index);

                    // 2. Position Tooltip at Mouse Cursor (2D Screen Coordinates)
                    const tooltip = document.getElementById('hover-tooltip');

                    // Add slight offset (15px) so the cursor doesn't cover the text
                    let x = event.clientX + 15;
                    let y = event.clientY + 15;

                    // Prevent tooltip from going off-screen
                    if (x + 270 > window.innerWidth) x -= 280; // Flip to left
                    if (y + 350 > window.innerHeight) y = window.innerHeight - 360; // Flip up

                    tooltip.style.left = `${x}px`;
                    tooltip.style.top = `${y}px`;
                } else {
                    handleMouseLeave(); // Hide if over empty tile
                }
            } else {
                handleMouseLeave(); // Hide if not over any tile
            }
        }

        // Initialize 3D on load
        document.addEventListener('DOMContentLoaded', () => {
            init3D();
        });

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

            // FIXED: Use fixed size instead of looking for #cell-0
            const width = 100;
            const height = 100;

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

        function handleMouseLeave() {
            const tooltip = document.getElementById('hover-tooltip');
            tooltip.classList.remove('opacity-100', 'scale-100');
            tooltip.classList.add('opacity-0', 'scale-95');

            // Hide completely after transition
            setTimeout(() => {
                if(tooltip.classList.contains('opacity-0')) {
                    tooltip.classList.add('hidden');
                }
            }, 200);

            // Reset 3D Colors
            if(typeof gridMeshes !== 'undefined') {
                gridMeshes.forEach(m => {
                    m.material.color.setHex(0xffffff);
                    m.material.emissive.setHex(0x000000);
                });
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
            const col = i % COLS;
            const row = Math.floor(i / COLS);

            if (row > 0) neighbors.push(i - COLS); // Top
            if (row < ROWS - 1) neighbors.push(i + COLS); // Bottom
            if (col > 0) neighbors.push(i - 1); // Left
            if (col < COLS - 1) neighbors.push(i + 1); // Right
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
            // 1. Update State
            gridState[cellIndex] = funcIndex;

            // 2. Update Metrics (Bars/Score)
            calculateMetrics();

            // 3. Update 3D World (Instead of 2D UI)
            update3DScene();
        }

        function updateCellUI(index, func) {
            // Do nothing - 2D grid is gone.
            return;
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
            const row = Math.floor(index / COLS);
            const col = index % COLS;
            let indices = [];

            // Loop through a 3x3 grid centered on the cell
            for (let r = row - 1; r <= row + 1; r++) {
                for (let c = col - 1; c <= col + 1; c++) {
                    // Check if the neighbor is within the grid boundaries
                    if (r >= 0 && r < ROWS && c >= 0 && c < COLS) {
                        const neighborIndex = r * COLS + c;
                        if (neighborIndex !== index) { // Don't include the cell itself
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
            // 1. DATA VALIDATION
            const funcIndex = gridState[cellIndex];
            if (!funcIndex || funcIndex === 0) return;

            const func = availableFunctions[funcIndex];
            if (!func) return;

            const tooltip = document.getElementById('hover-tooltip');
            const titleEl = document.getElementById('tooltip-title');
            const impactsList = document.getElementById('tooltip-impacts');
            const synergyList = document.getElementById('tooltip-synergy');

            // 2. POPULATE HTML CONTENT
            titleEl.innerText = func.name;

            // --- A. OUTGOING IMPACTS ---
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

                    impactsList.innerHTML += `
                    <li class="flex justify-between items-center border-b border-gray-50 pb-1 last:border-0">
                        <span class="text-gray-600">${name}</span>
                        <span class="font-bold ${colorClass} text-xs">${sign}${valNum}</span>
                    </li>`;
                }
            }
            if (!hasImpacts) impactsList.innerHTML = '<li class="text-gray-400 italic text-xs">Geen uitgaande effecten</li>';

            // --- B. INCOMING SYNERGY ---
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

                synergyList.innerHTML += `
                <li class="flex justify-between items-start border-b border-gray-50 pb-1 last:border-0">
                    <div class="flex flex-col leading-tight">
                        <span class="text-gray-600">${metricName}</span>
                        <span class="text-[10px] text-gray-400 italic">van: ${data.sources.join(', ')}</span>
                    </div>
                    <span class="font-bold ${colorClass} text-xs mt-1">${sign}${data.value}</span>
                </li>`;
            }
            if (!hasSynergy) synergyList.innerHTML = '<li class="text-gray-400 italic text-xs">Geen invloed van buren</li>';

            // 3. 3D VISUAL SCOPE (Highlighting)
            // Ensure meshes exist before trying to highlight them
            if (typeof gridMeshes !== 'undefined') {
                // Reset all tiles
                gridMeshes.forEach(m => {
                    m.material.emissive.setHex(0x000000);
                    m.material.color.setHex(0xffffff);
                });

                // Highlight Neighbors (Yellow)
                neighbors.forEach(nIndex => {
                    if(gridMeshes[nIndex]) {
                        gridMeshes[nIndex].material.emissive.setHex(0x555500);
                    }
                });

                // Highlight Self (Gray)
                if(gridMeshes[cellIndex]) {
                    gridMeshes[cellIndex].material.emissive.setHex(0x555555);
                }
            }

            // 4. SHOW TOOLTIP
            tooltip.classList.remove('hidden');
            requestAnimationFrame(() => {
                tooltip.classList.remove('opacity-0', 'scale-95');
                tooltip.classList.add('opacity-100', 'scale-100');
            });
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
