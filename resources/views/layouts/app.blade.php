<!doctype html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sistema de Gestion de Ordenes </title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
  </head>

  <style>
    [x-cloak] { display: none !important; }
</style>
  <body @guest
    class="bg-cover bg-center bg-blur" 
  style="background-image: url('https://transparenciave.org/wp-content/uploads/2024/05/GettyImages-1663461500-e1713838278416.webp')"
  {{-- style="background-image: url('https://i0.wp.com/efe.com/wp-content/uploads/2023/03/rss-efeda4fbf693c305bda3d5b724c501ff398189eb342w.jpg?fit=1920%2C1280&ssl=1')" --}}
  @endguest @auth
       class="bg-gray-100"   
  @endauth>
   <!-- En layouts/app.blade.php -->

@if (session()->has('success'))
    <x-alert type="success" :message="session('success')" />
@endif

@if (session()->has('error'))
    <x-alert type="error" :message="session('error')" />
@endif

<header class="w-full">

   
    
    @auth
    
<nav class="bg-neutral-primary">
    <div class="flex flex-wrap justify-between items-center mx-auto max-w-screen-xl p-4">
        <a href="#" class="flex items-center space-x-3 rtl:space-x-reverse">
            <x-pdvs-logo class="h-8"/>
            
        </a>

        @guest
            
        <div class="flex items-center space-x-6 rtl:space-x-reverse">
         
            <a href="{{ route('login') }}" class="text-sm font-medium text-fg-brand hover:underline">Iniciar Sesion</a>
        </div>
        @endguest

        @auth
          <div class="flex items-center space-x-6 rtl:space-x-reverse">
           
            <button data-modal-target="popup-modal" data-modal-toggle="popup-modal" class="text-red-600  box-border border border-transparent hover:text-red-900 focus:ring-4 focus:ring-brand-medium font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none" type="button">
              Cerrar Sesion
          </button>
        </div>
        
        @endauth
    </div>

    
</nav>
    <nav class="bg-neutral-secondary-soft border-y border-default border-default">
        
    <div class="max-w-screen-xl px-4 py-3 mx-auto">
        <div class="flex items-center">
            <ul class="flex flex-row font-medium mt-0 space-x-8 rtl:space-x-reverse text-sm">

                 <li>
                    @if (auth()->user()->role == 'admin')
                         <a href="{{ route('admin.stats') }}" class="text-heading hover:underline">Estadisticas</a>
                    @elseif(auth()->user()->role == 'supervisor')
                         <a href="{{ route('supervisor.stats') }}" class="text-heading hover:underline">Estadisticas</a>
                    @endif
                   
                </li>
                <li>
                    <a href="{{ route('admin.worksheets.index') }}" class="text-heading hover:underline" aria-current="page">Sabanas</a>
                </li>
               @if (auth()->user()->role == 'admin')
                <li>
                    <a href="{{ route('admin.departments.index') }}" class="text-heading hover:underline">Departamentos</a>
                </li>
                <li>
                    <a href="{{ route('admin.disciplines.index') }}" class="text-heading hover:underline">Disciplinas</a>
                </li>
                 <li>
                    <a href="{{ route('admin.installations.index') }}" class="text-heading hover:underline">Instalaciones</a>
                </li>
                 <li>
                    <a href="{{ route('admin.equipment.index') }}" class="text-heading hover:underline">Equipos</a>
                </li>
                <li>
                    <a href="{{ route('admin.users.index') }}" class="text-heading hover:underline">Usuarios</a>
                </li>
                @endif
            </ul>
        </div>
    </div>
    @endauth
  </nav>
</header>

<div class="w-full px-4 py-1 mx-auto max-w-screen-xl @auth py-5 @endauth">
    
    @yield('content')
</div>

{{-- modal cerrar sesion --}}
  <div id="popup-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <div class="relative bg-neutral-primary-soft border border-default rounded-base shadow-sm p-4 md:p-6">
                <button type="button" class="absolute top-3 end-2.5 text-body bg-transparent hover:bg-neutral-tertiary hover:text-heading rounded-base text-sm w-9 h-9 ms-auto inline-flex justify-center items-center" data-modal-hide="popup-modal">
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/></svg>
                    <span class="sr-only">Close modal</span>
                </button>
            <div class="p-4 md:p-5 text-center">
                <svg class="mx-auto mb-4 text-fg-disabled w-12 h-12" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <h3 class="mb-6 text-body">¿Estas seguro de que quieres cerrar sesión?</h3>
                <div class="flex items-center space-x-4 justify-center">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-white bg-danger box-border border border-transparent hover:bg-danger-strong focus:ring-4 focus:ring-danger-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none">
                            Si, estoy seguro
                        </button>
                    </form>
                    <button data-modal-hide="popup-modal" type="button" class="text-body bg-neutral-secondary-medium box-border border border-default-medium hover:bg-neutral-tertiary-medium hover:text-heading focus:ring-4 focus:ring-neutral-tertiary shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none">No, cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>

@stack('scripts')

@yield("scripts")

  </body>
</html>