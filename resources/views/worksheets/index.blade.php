@extends('layouts.app')

@section('content')
<div>
    <h1 class="text-xl font-bold text-gray-800">Sabanas</h1>
    <br>
  @php
    $columnas = [
        'week_number'     => 'Semana',
        'department.name'       => 'Departamento',
        'start_date'      => 'Fecha de Inicio',
        'end_date'        => 'Fecha de Fin', 

    ];
@endphp
    
    <x-dynamic-table 
        :records="$worksheets" 
        :columns="$columnas" 
        :eliminar="true" 
        :editar="true" 
        :ver="true" 
        :agregar="true"
        :routePrefix="'admin.worksheets'"
    />
</div>
@endsection
