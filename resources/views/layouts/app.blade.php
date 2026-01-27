<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    {{-- ACCESSIBILITY WIDGET (Zoom & Text-to-Speech) --}}
<div x-data="accessibilityTool()" 
     class="fixed bottom-5 right-5 z-50 flex flex-col items-end space-y-2"
     {{-- Belangrijk: We forceren hier dat de widget zelf NIET meeschaalt met de root font-size --}}
     style="font-size: 16px !important;">

    {{-- Het Menu --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         {{-- We gebruiken hier [300px] ipv w-64, zodat hij fixed blijft --}}
         class="bg-white rounded-lg shadow-xl border border-gray-200 p-[16px] w-[300px] mb-4">
        
        <h3 class="font-bold text-gray-700 mb-3 border-b pb-2 text-[16px]">Toegankelijkheid</h3>

        {{-- 1. ZOOM FUNCTIE --}}
        <div class="mb-4">
            <p class="text-[14px] text-gray-600 mb-2 font-semibold">Tekstgrootte</p>
            <div class="flex items-center justify-between bg-gray-100 rounded p-1">
                <button @click="decreaseFont()" class="p-2 hover:bg-gray-200 rounded text-gray-700 font-bold text-[16px]" aria-label="Tekst verkleinen">A-</button>
                <span class="text-[14px] font-mono" x-text="zoomPercentage + '%'"></span>
                <button @click="increaseFont()" class="p-2 hover:bg-gray-200 rounded text-gray-700 font-bold text-[16px]" aria-label="Tekst vergroten">A+</button>
            </div>
            <button @click="resetFont()" class="text-[12px] text-blue-600 hover:underline mt-1 w-full text-center">Reset</button>
        </div>

        {{-- 2. AUTO-READ (TTS) FUNCTIE --}}
        <div class="mb-2">
            <p class="text-[14px] text-gray-600 mb-2 font-semibold">Voorleeshulp</p>
            <button @click="toggleTTS()" 
                    :class="ttsEnabled ? 'bg-metro-darkred text-white' : 'bg-gray-200 text-gray-700'"
                    class="w-full py-2 px-3 rounded flex items-center justify-center transition-colors shadow-sm font-medium text-[14px]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                </svg>
                <span x-text="ttsEnabled ? 'Voorlezen AAN' : 'Voorlezen UIT'"></span>
            </button>
            <p class="text-[11px] text-gray-400 mt-2 italic leading-tight">
                Beweeg over knoppen, titels of teksten om ze te horen.
            </p>
        </div>
    </div>

    {{-- De Hoofdknop (Floating Action Button) --}}
    <button @click="open = !open" 
            class="bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg transition-transform transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-blue-300 flex items-center justify-center"
            {{-- We gebruiken vaste px maten zodat de knop niet meegroeit --}}
            style="width: 60px; height: 60px;"
            aria-label="Toegankelijkheidsopties openen">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
    </button>
</div>

<script>
    function accessibilityTool() {
        return {
            open: false,
            zoomPercentage: 100,
            ttsEnabled: false,
            speechSynth: window.speechSynthesis,
            currentUtterance: null,
            highlightBox: null, // Voor de oranje rand

            init() {
                const savedZoom = localStorage.getItem('access_zoom');
                if (savedZoom) {
                    this.zoomPercentage = parseInt(savedZoom);
                    this.applyZoom();
                }

                // Global Event Listener voor TTS
                document.body.addEventListener('mouseover', (e) => {
                    if (!this.ttsEnabled) return;

                    // AANGEPAST: Uitgebreidere lijst met tags (ook div, span, headers)
                    // We gebruiken 'closest' om te kijken of we over iets interessants zweven
                    let target = e.target.closest('a, button, h1, h2, h3, h4, h5, h6, p, label, input, li, span, div, td, th');
                    
                    // Filter: negeer divs die alleen als container dienen (geen directe tekst)
                    if (target && this.shouldRead(target)) {
                        
                        // Stop vorige spraak
                        this.speechSynth.cancel();

                        // Bepaal de tekst
                        let textToRead = target.getAttribute('aria-label') || target.innerText || target.placeholder || target.alt;

                        // Lees alleen als er tekst is en het niet te lang is (bijv hele pagina container)
                        if (textToRead && textToRead.trim().length > 0 && textToRead.length < 500) {
                            
                            // Visuele feedback (Oranje Rand)
                            target.style.outline = '3px solid #f59e0b';
                            target.style.outlineOffset = '2px';
                            
                            this.speak(textToRead);

                            // Reset rand als muis weggaat
                            target.addEventListener('mouseleave', () => {
                                target.style.outline = 'none';
                                this.speechSynth.cancel();
                            }, { once: true });
                        }
                    }
                });
            },

            // Check of een element de moeite waard is om te lezen
            shouldRead(element) {
                // Negeer de accessibility widget zelf
                if (element.closest('[x-data="accessibilityTool()"]')) return false;

                // Als het een div of span is, check of hij directe tekst bevat 
                // (zodat we niet de hele main-container voorlezen)
                if (element.tagName === 'DIV' || element.tagName === 'SPAN') {
                    // Simpele check: heeft dit element directe tekst of is het een kaartje?
                    // We laten het toe, maar filteren lege containers in de tekst-check hierboven.
                    // Als de gebruiker over een kaartje hovert (bijv .function-card), willen we die wel lezen.
                    return true;
                }
                return true;
            },

            increaseFont() {
                if (this.zoomPercentage < 200) {
                    this.zoomPercentage += 10;
                    this.applyZoom();
                }
            },
            decreaseFont() {
                if (this.zoomPercentage > 70) {
                    this.zoomPercentage -= 10;
                    this.applyZoom();
                }
            },
            resetFont() {
                this.zoomPercentage = 100;
                this.applyZoom();
            },
            applyZoom() {
                document.documentElement.style.fontSize = this.zoomPercentage + '%';
                localStorage.setItem('access_zoom', this.zoomPercentage);
            },

            toggleTTS() {
                this.ttsEnabled = !this.ttsEnabled;
                if (this.ttsEnabled) {
                    this.speak("Voorleeshulp geactiveerd. Beweeg over elementen om ze te horen.");
                } else {
                    this.speechSynth.cancel();
                    // Verwijder eventuele outlines die zijn blijven hangen
                    document.querySelectorAll('*').forEach(el => el.style.outline = 'none');
                }
            },
            speak(text) {
                if (!text) return;
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'nl-NL';
                utterance.rate = 1.0;
                this.speechSynth.speak(utterance);
            }
        }
    }
</script>
    </body>
</html>
