@extends('layouts.app')

@section('title', 'Administrar Instalaciones')

@section('content')
<div>

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
        :pdfRoute="'admin.installations.pdf'"
    />
</div>


@endsection
