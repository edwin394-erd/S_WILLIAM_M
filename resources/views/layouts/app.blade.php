<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sistema de Gestión de Órdenes - PDVSA</title>
    <link rel="icon" type="image/png" href="{{ asset('imgs/pdvsaicon.png') }}" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        /* Colores institucionales */
        .bg-pdvsa-red { background-color: #C10000; }
        .border-pdvsa-red { border-color: #C10000; }
        .text-pdvsa-red { color: #C10000; }
        
        /* Scrollbar suave para el área de contenido */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #C10000; }
    </style>
</head>

<body x-data="{ sidebarOpen: false }" class="bg-gray-200 font-sans leading-normal tracking-normal h-screen flex flex-col overflow-hidden">

    @auth
    <!-- Header Institucional -->
    <header class="bg-white border-b-4 border-pdvsa-red shadow-sm shrink-0 z-50">
        <div class="flex justify-between items-center px-4 md:px-6 py-2">
            <div class="flex items-center gap-3">
                <!-- Botón Menú Móvil -->
                <button @click="sidebarOpen = !sidebarOpen" class="md:hidden p-2 text-gray-600 hover:text-pdvsa-red transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <x-pdvs-logo class="h-8 md:h-9"/>
            </div>
            <div class="hidden sm:block text-right">
                <p class="text-[10px] md:text-xs text-gray-600 font-bold uppercase">ROL: {{ auth()->user()->role }}</p>
                <p class="text-[11px] md:text-[12px] text-gray-500">{{ now('America/Caracas')->translatedFormat('l d \d\e F \d\e Y H:i') }}</p>
            </div>
        </div>
        <!-- Barra de Bienvenida -->
        <div class="bg-gray-100 border-t border-gray-300 px-4 md:px-6 py-1 flex justify-between text-[11px] md:text-[12px] text-gray-600">
            <span>Usuario: <span class="font-bold">{{ auth()->user()->name ?? 'Usuario' }}</span></span>
            <button data-modal-target="popup-modal" data-modal-toggle="popup-modal" class="cursor-pointer hover:text-pdvsa-red font-bold uppercase transition-colors">
                Cerrar Sesión
            </button>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden relative">
        <!-- Sidebar Desplegable -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-40 w-64 bg-gray-50 border-r border-gray-300 transition-transform duration-300 transform md:relative md:translate-x-0 overflow-y-auto shrink-0 shadow-inner">
            <nav class="mt-4 px-4 text-[14px]">
                <ul class="space-y-1">
                    @if (auth()->user()->role != 'tecnico')
                        
                    <li class="font-bold text-gray-700 border-b border-dotted border-gray-400 pb-1 mb-2 uppercase">Indicadores y Control</li>
                    @endif
                    <li class="pl-4">
                        @if (auth()->user()->role == 'admin' || auth()->user()->role == 'planificador')
                            <a href="{{ route('admin.stats') }}" class="text-gray-600 hover:text-pdvsa-red py-1 block">Estadísticas Administrativas</a>
                        @elseif(auth()->user()->role == 'supervisor')
                            <a href="{{ route('supervisor.stats') }}" class="text-gray-600 hover:text-pdvsa-red py-1 block">Estadísticas Supervisor</a>
                        @endif
                    </li>

                    @if (auth()->user()->role == 'admin' || auth()->user()->role =='planificador')
                    <li class="font-bold text-gray-700 border-b border-dotted border-gray-400 pb-1 mt-4 mb-2 uppercase">Operaciones</li>
                    <li class="pl-4">
                        <a href="{{ route('admin.worksheets.index') }}" class="text-gray-600 hover:text-pdvsa-red py-1 block">Sabanas de Ordenes</a>
                    </li>
                    @endif

                    @if (auth()->user()->role == 'admin')
                    <li class="font-bold text-gray-700 border-b border-dotted border-gray-400 pb-1 mt-4 mb-2 uppercase">Configuración</li>
                    <li class="pl-4"><a href="{{ route('admin.departments.index') }}" class="text-gray-600 hover:text-pdvsa-red py-1 block">Departamentos</a></li>
                    <li class="pl-4"><a href="{{ route('admin.disciplines.index') }}" class="text-gray-600 hover:text-pdvsa-red py-1 block">Disciplinas</a></li>
                    <li class="pl-4"><a href="{{ route('admin.installations.index') }}" class="text-gray-600 hover:text-pdvsa-red py-1 block">Instalaciones</a></li>
                    <li class="pl-4"><a href="{{ route('admin.equipment.index') }}" class="text-gray-600 hover:text-pdvsa-red py-1 block">Equipos</a></li>
                    <li class="pl-4"><a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-pdvsa-red py-1 block font-semibold">Gestión de Usuarios</a></li>
                    @endif

                    @if (auth()->user()->role === "tecnico")
                    <li class="font-bold text-gray-700 border-b border-dotted border-gray-400 pb-1 mt-4 mb-2 uppercase">Ejecución</li>
                    <li class="pl-4">
                        <a href="{{ route('tecnico.actividades', auth()->user()->discipline_id) }}" class="text-gray-600 hover:text-pdvsa-red py-1 block font-bold">Mis Actividades</a>
                    </li>
                    @endif
                </ul>
            </nav>
        </aside>

        <!-- Overlay Fondo (Cierra el menú al tocar fuera en móvil) -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak class="fixed inset-0 bg-black/50 z-30 md:hidden transition-opacity"></div>

        <!-- Área de Contenido Principal -->
        <main class="flex-1 bg-white p-4 md:p-6 overflow-y-auto custom-scrollbar">
            @if (session()->has('success'))
                <x-alert type="success" :message="session('success')" class="mb-4" />
            @endif

            @if (session()->has('error'))
                <x-alert type="error" :message="session('error')" class="mb-4" />
            @endif

            <div class="mb-6 border-b border-gray-300 pb-2">
                <h2 class="text-pdvsa-red font-bold uppercase text-xs md:text-sm tracking-tight italic">
                    @yield('title', 'Gestión de Sistemas')
                </h2>
            </div>

            <div class="w-full">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- Modal de Cerrar Sesión --}}
    <div id="popup-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-[60] justify-center items-center w-full md:inset-0 h-full max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white border-t-4 border-pdvsa-red rounded shadow-xl p-6">
                <div class="text-center">
                    <svg class="mx-auto mb-4 text-gray-400 w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <h3 class="mb-5 text-sm font-bold text-gray-700 uppercase">¿Confirmar cierre de sesión?</h3>
                    <div class="flex justify-center gap-3">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-pdvsa-red text-white text-xs font-bold px-6 py-2 rounded hover:bg-red-800 transition-colors uppercase">Sí, Salir</button>
                        </form>
                        <button data-modal-hide="popup-modal" class="bg-gray-200 text-gray-700 text-xs font-bold px-6 py-2 rounded hover:bg-gray-300 transition-colors uppercase">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endauth

    @guest
    <div class="flex flex-col bg-gray-100 h-screen overflow-y-auto">
        <header class="bg-white border-b-4 border-pdvsa-red shadow-sm shrink-0">
            <div class="flex justify-between items-center px-6 py-2">
                <x-pdvs-logo class="h-10"/>
                <p class="hidden md:block text-[14px] text-gray-500">{{ now('America/Caracas')->translatedFormat('l d \d\e F \d\e Y H:i') }}</p>
            </div>
        </header>
        <main class="flex-1 flex flex-col items-center justify-center p-4">
            @yield('content')
        </main>
    </div>
    @if (session()->has('success'))
                <x-alert type="success" :message="session('success')" class="mb-4" />
            @endif

            @if (session()->has('error'))
                <x-alert type="error" :message="session('error')" class="mb-4" />
            @endif
    @endguest

    @stack('scripts')
    @yield("scripts")
</body>
</html>