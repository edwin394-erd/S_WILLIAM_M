@extends('layouts.app')
@section('title', 'Administrar Equipos')
@section('content')
<div>

    @php
        $columnas = [
            'id' => 'id',
            'name'  => 'Nombre',
          
        ];
    @endphp
    
    <x-dynamic-table 
        :records="$equipment" 
        :columns="$columnas" 
        :eliminar="true" 
        :editar="true" 
        {{-- :ver="true"  --}}
        :agregar="true"
        :routePrefix="'admin.equipment'"
        :pdfRoute="'admin.equipment.pdf'"
    />
</div>


@endsection
