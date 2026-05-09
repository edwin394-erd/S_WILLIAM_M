@extends('layouts.app')

@section('content')
<div>
    <h1 class="text-xl font-bold text-gray-800">Departamentos {{ $department->name }}>>Disciplinas</h1>
    <br>
    @php
        $columnas = [
            'id' => 'id',
            'name'  => 'Nombre',
            'created_at'   => 'Fecha de Creación',
            // 'updated_at'   => 'Fecha de Actualización'
        ];
    @endphp

    <x-dynamic-table 
        :records="$disciplines_department" 
        :columns="$columnas" 
        {{-- :eliminar="true" 
        :editar="true" --}}
    />
</div>


@endsection