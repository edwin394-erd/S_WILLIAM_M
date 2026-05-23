@extends('layouts.app')

@section('title', 'Crear Departamento')

@section('content')
<div class="bg-white p-6 rounded-lg w-full max-w-md border border-gray-300">
    {{-- <h1 class="text-2xl font-bold text-gray-800 mb-4">Crear Departamento</h1> --}}
    <form action="{{ route('admin.departments.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <x-input :label="'Nombre del Departamento'" 
                     :name="'name'" 
                     :type="'text'" 
                     :placeholder="'Ingrese el nombre del departamento'" 
                     :required="true" />
        </div>

        <div class="mb-4">
            <x-input :label="'Id Telegram'" 
                     :name="'grupo_telegram_id'" 
                     :type="'text'" 
                     :placeholder="'Ingrese el ID de Telegram para notificaciones'" 
                     :required="true" />
        </div>

         <x-confirm-cancel backUrl="{{ route('admin.departments.index') }}"/>
    </form>
</div>
@endsection