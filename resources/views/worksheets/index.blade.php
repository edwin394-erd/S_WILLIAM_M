@extends('layouts.app')

@section('title', 'Administrar Sabanas')

@section('content')
<div>
    {{-- <h1 class="text-xl font-bold text-gray-800">Sabanas</h1>
    <br>
    --}}

  @php
    $columnas = [
        'week_number'     => 'Semana',
        'department.name'       => 'Departamento',
        'start_date'      => 'Fecha de Inicio',
        'end_date'        => 'Fecha de Fin', 
        'work_orders_count' => 'Órdenes de Trabajo',
        'enviado' => 'ESTATUS',

    ];
@endphp
    
    <x-gridcards
        :records="$worksheets" 
        :columns="$columnas" 
        :departmentOptions="$departmentOptions"
        :eliminar="true" 
        {{-- :editar="true"  --}}
        :ver="true" 
        :agregar="true"
        :descargar_pdf="true"
        :routePrefix="'admin.worksheets'"
        :enviar_telegram="true"
    />
</div>
@endsection
