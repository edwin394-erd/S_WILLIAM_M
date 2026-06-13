<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
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
            class="-translate-x-full fixed inset-y-0 left-0 z-40 w-64 bg-gray-50 border-r border-gray-300 transition-transform duration-300 transform md:relative md:translate-x-0 overflow-y-auto shrink-0 shadow-inner">
            <nav class="mt-30 md:mt-4 px-4 text-[14px]">
                @php
                    $role = auth()->user()->role;
                    $isAdminPlan = in_array($role, ['admin', 'planificador']);
                    $isSupervisor = $role === 'supervisor';
                @endphp

                <ul class="space-y-2">
                    @if (! in_array($role, ['tecnico']))
                        <li class="font-bold text-gray-700 border-b border-dotted border-gray-400 pb-1 mb-2 uppercase">Indicadores</li>

                        @if ($isAdminPlan)
                            <li class="pl-4">
                                <a href="{{ route('admin.stats') }}" class="{{ request()->routeIs('admin.stats*') ? 'text-pdvsa-red bg-pdvsa-red/10 font-semibold rounded-l-full' : 'text-gray-600 hover:text-pdvsa-red' }} py-1 block">Estadísticas</a>
                            </li>
                            <li class="pl-4">
                                <a href="{{ route('admin.workorders.historial') }}" class="{{ request()->routeIs('admin.workorders.historial*') ? 'text-pdvsa-red bg-pdvsa-red/10 font-semibold rounded-l-full' : 'text-gray-600 hover:text-pdvsa-red' }} py-1 block">Historial</a>
                            </li>
                        @elseif ($isSupervisor)
                            <li class="pl-4">
                                <a href="{{ route('supervisor.stats') }}" class="{{ request()->routeIs('supervisor.stats*') ? 'text-pdvsa-red bg-pdvsa-red/10 font-semibold rounded-l-full' : 'text-gray-600 hover:text-pdvsa-red' }} py-1 block">Estadísticas</a>
                            </li>
                             <li class="pl-4">
                                <a href="{{ route('supervisor.workorders.historial') }}" class="{{ request()->routeIs('supervisor.workorders.historial*') ? 'text-pdvsa-red bg-pdvsa-red/10 font-semibold rounded-l-full' : 'text-gray-600 hover:text-pdvsa-red' }} py-1 block">Historial</a>
                            </li>
                        @endif
                    @endif

                    @if ($isAdminPlan || $isSupervisor)
                        <li class="font-bold text-gray-700 border-b border-dotted border-gray-400 pb-1 mt-4 mb-2 uppercase">Operaciones</li>
                        <li class="pl-4">
                            <a href="{{ $isSupervisor ? route('supervisor.worksheets') : route('admin.worksheets.index') }}" class="{{ request()->routeIs($isSupervisor ? 'supervisor.worksheets*' : 'admin.worksheets*') ? 'text-pdvsa-red bg-pdvsa-red/10 font-semibold rounded-l-full' : 'text-gray-600 hover:text-pdvsa-red' }} py-1 block">Sábanas de Órdenes</a>
                        </li>
                    @endif

                    @if ($role === 'admin')
                        <li class="font-bold text-gray-700 border-b border-dotted border-gray-400 pb-1 mt-4 mb-2 uppercase">Configuración</li>
                        <li class="pl-4"><a href="{{ route('admin.departments.index') }}" class="{{ request()->routeIs('admin.departments*') ? 'text-pdvsa-red bg-pdvsa-red/10 font-semibold rounded-l-full' : 'text-gray-600 hover:text-pdvsa-red' }} py-1 block">Departamentos</a></li>
                        <li class="pl-4"><a href="{{ route('admin.disciplines.index') }}" class="{{ request()->routeIs('admin.disciplines*') ? 'text-pdvsa-red bg-pdvsa-red/10 font-semibold rounded-l-full' : 'text-gray-600 hover:text-pdvsa-red' }} py-1 block">Disciplinas</a></li>
                        <li class="pl-4"><a href="{{ route('admin.installations.index') }}" class="{{ request()->routeIs('admin.installations*') ? 'text-pdvsa-red bg-pdvsa-red/10 font-semibold rounded-l-full' : 'text-gray-600 hover:text-pdvsa-red' }} py-1 block">Instalaciones</a></li>
                        <li class="pl-4"><a href="{{ route('admin.equipment.index') }}" class="{{ request()->routeIs('admin.equipment*') ? 'text-pdvsa-red bg-pdvsa-red/10 font-semibold rounded-l-full' : 'text-gray-600 hover:text-pdvsa-red' }} py-1 block">Equipos</a></li>
                        <li class="pl-4"><a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users*') ? 'text-pdvsa-red bg-pdvsa-red/10 font-semibold rounded-l-full' : 'text-gray-600 hover:text-pdvsa-red' }} py-1 block">Gestión de Usuarios</a></li>
                    @endif

                    @if ($role === 'tecnico')
                        <li class="font-bold text-gray-700 border-b border-dotted border-gray-400 pb-1 mt-4 mb-2 uppercase">Ejecución</li>
                        <li class="pl-4">
                            <a href="{{ route('tecnico.actividades', auth()->user()->discipline_id) }}" class="{{ request()->routeIs('tecnico.actividades*') ? 'text-pdvsa-red bg-pdvsa-red/10 font-semibold rounded-l-full' : 'text-gray-600 hover:text-pdvsa-red' }} py-1 block">Mis Actividades</a>
                        </li>
                        <li class="pl-4">
                            <a href="{{ route('tecnico.workorders.historial') }}" class="{{ request()->routeIs('tecnico.workorders.historial*') ? 'text-pdvsa-red bg-pdvsa-red/10 font-semibold rounded-l-full' : 'text-gray-600 hover:text-pdvsa-red' }} py-1 block">Historial</a>
                        </li>
                    @endif
                    {{-- <li class="pl-4 mt-4">
                        <a href="{{ route('probaralert') }}" class="text-gray-600 hover:text-pdvsa-red py-1 block">PRUEBA ALERT</a>
                     </li>
                    --}}
                     {{-- <li class="pl-4">
                        <button data-modal-target="popup-modal" data-modal-toggle="popup-modal" class="text-gray-600 hover:text-pdvsa-red py-1 block text-left w-full">Cerrar Sesión</button>
                    </li> --}}
                </ul>
            </nav>
        </aside>

        <!-- Overlay Fondo (Cierra el menú al tocar fuera en móvil) -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak class="fixed inset-0 bg-black/50 z-30 md:hidden transition-opacity"></div>

        <!-- Área de Contenido Principal -->
        <main class="flex-1 bg-white p-4 md:px-6 md:py-4 overflow-y-auto custom-scrollbar">
            @if (session()->has('success'))
                <x-alert type="success" :message="session('success')" class="mb-4" />
            @endif

            @if (session()->has('error'))
                <x-alert type="error" :message="session('error')" class="mb-4" />
            @endif

            <div class="mb-3 border-b border-gray-300 pb-1">
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
                <p class=" md:block text-[14px] text-gray-500">{{ now('America/Caracas')->translatedFormat('l d \d\e F \d\e Y H:i') }}</p>
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