@extends('layouts.app')

@section('title', 'Departamentos>>' . ($department->name ?? 'Sin nombre') . '>>Disciplinas')

@section('content')
<div>
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