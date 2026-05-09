@extends('layouts.app')

@section('content')
<div>
    <h1 class="text-xl font-bold text-gray-800">Departamentos</h1>
    <br>
    @php
        $columnas = [
            'id' => 'id',
            'name'  => 'Nombre',
          
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