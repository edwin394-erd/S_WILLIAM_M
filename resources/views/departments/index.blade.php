@extends('layouts.app')

@section('title', 'Administrar DEPARTAMENTOS')

@section('content')
<div>
    {{-- <h1 class="text-xl font-bold text-gray-800">Departamentos</h1>
    <br> --}}
    @php
        $columnas = [
            'id' => 'id',
            'name'  => 'Nombre',
            'grupo_telegram_id' => 'Grupo Telegram (ID)',
          
        ];
    @endphp
    
    <x-dynamic-table 
        :records="$departments" 
        :columns="$columnas" 
        :eliminar="true" 
        :editar="true" 
        :ver="true" 
        :agregar="true"
        :routePrefix="'admin.departments'"
    />
</div>


@endsection