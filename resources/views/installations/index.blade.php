@extends('layouts.app')

@section('content')
<div>
    <h1 class="text-xl font-bold text-gray-800">Instalaciones</h1>
    <br>
    @php
        $columnas = [
            'id' => 'id',
            'name'  => 'Nombre',
            'impact'  => 'Impacto (bls)',
        ];
    @endphp
    
    <x-dynamic-table 
        :records="$installations" 
        :columns="$columnas" 
        :eliminar="true" 
        :editar="true" 
        :agregar="true"
        :routePrefix="'admin.installations'"
    />
</div>


@endsection
