@extends('layouts.app')

@section('content')

<div>
    <h1 class="text-xl font-bold text-gray-800">Disciplinas</h1>
    <br>
    @php
        $columnas = [
            'id' => 'id',
            'name'  => 'Nombre',
            'department.name' => 'Departamento', // Accede a la relación
            'created_at'   => 'Fecha de Creación',

            // 'updated_at'   => 'Fecha de Actualización'
        ];
    @endphp
    
    <x-dynamic-table 
        :records="$disciplines_department" 
        :columns="$columnas" 
        :eliminar="true" 
        :editar="true" 
        :ver="false" 
        :agregar="true"
        :routePrefix="'admin.disciplines'"
    />

@endsection