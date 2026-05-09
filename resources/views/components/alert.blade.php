@props([
    'type' => 'success',
    'message' => '',
    'timeout' => 3000
])

@php
    $colors = [
        'success' => 'bg-green-50 border-green-500 text-green-800',
        'error'   => 'bg-red-50 border-red-500 text-red-800',
        'info'    => 'bg-blue-50 border-blue-500 text-blue-800',
        'warning' => 'bg-yellow-50 border-yellow-500 text-yellow-800',
    ][$type];
@endphp

<div 
    x-data="{ 
        show: true, 
        width: '100%',
        init() {
            // Iniciamos la animación CSS de la barra un instante después de montar
            setTimeout(() => {
                this.width = '0%';
            }, 50);

            // Cerramos la alerta cuando se cumple el tiempo
            setTimeout(() => {
                this.show = false;
            }, {{ $timeout }});
        }
    }"
    x-show="show"
    x-transition:enter="transform ease-out duration-300 transition"
    x-transition:enter-start="translate-y-4 opacity-0 sm:translate-y-0 sm:translate-x-4"
    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 sm:translate-x-0"
    x-transition:leave-end="opacity-0 sm:translate-x-4"
    class="fixed top-5 right-5 z-50 max-w-sm w-full shadow-lg rounded-lg pointer-events-auto overflow-hidden border-l-4 {{ $colors }}"
>
    <div class="p-4 flex items-start">
        <div class="flex-1 text-sm font-medium">
            {{ $message }}
        </div>
        <!-- Se cambió el color del botón para que herede el color del tema actual (usando opacity) -->
        <button @click="show = false" class="ml-4 inline-flex opacity-50 hover:opacity-100 focus:outline-none transition-opacity">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
    
    <!-- Barra de progreso inferior -->
    <div class="h-1 bg-black/10 w-full">
        <div 
            class="h-full bg-current opacity-50" 
            :style="`width: ${width}; transition: width {{ $timeout }}ms linear;`"
        ></div>
    </div>
</div>