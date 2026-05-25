@extends('layouts.app')

@section('content')
<section class="dark:bg-gray-900 flex items-center justify-center py-10 x-cloak">
    <div class="flex flex-col md:flex-row bg-white rounded-lg shadow-md overflow-hidden w-full max-w-4xl justify-center x-cloak">

        {{-- Formulario de Login --}}
        <div class="p-6 space-y-4 md:space-y-6 sm:p-8 w-full md:w-1/2 x-cloak">
            <div class="flex justify-center mb-6">
                <x-pdvs-logo class="h-10"/>
            </div>
            <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white text-center">
                Acceso de Usuarios
            </h1>
            
            <form class="space-y-4 md:space-y-6" action="{{ route('login.submit') }}" method="POST">
                @csrf
                <div>
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Correo</label>
                    <input type="email" name="email" id="email" 
                        class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-slate-600 focus:border-slate-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-slate-500 dark:focus:border-slate-500" 
                        placeholder="nombre@pdvsa.com" required>
                </div>

                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Contraseña</label>
                    <input type="password" name="password" id="password" 
                        placeholder="••••••••" 
                        class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-slate-600 focus:border-slate-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-slate-500 dark:focus:border-slate-500" 
                        required>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="remember" name="remember" type="checkbox" 
                                class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-slate-300 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-slate-600 dark:ring-offset-gray-800">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="remember" class="text-gray-500 dark:text-gray-300">Recuérdame</label>
                        </div>
                    </div>
                    {{-- <a href="#" class="text-sm font-medium text-slate-600 hover:underline dark:text-slate-500">¿Olvidaste tu contraseña?</a> --}}
                </div>

                <button type="submit" 
                    class="w-full text-white bg-slate-600 hover:bg-slate-700 focus:ring-4 focus:outline-none focus:ring-slate-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-slate-600 dark:hover:bg-slate-700 dark:focus:ring-slate-800">
                    Entrar
                </button>
            </form>
        </div>

         {{-- Imagen Lateral --}}
        <div class="hidden md:block md:w-1/2 min-h-[520px] overflow-hidden">
            <img src="{{ asset('imgs/pdvsalogin.jpeg') }}" 
                 alt="PDVSA" width="800" height="600"
                 class="object-cover w-full h-full">
        </div>
    </div>
</section>
@endsection