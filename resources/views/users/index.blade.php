@extends('layouts.app')

@section('content')
<div>
    <h1 class="text-xl font-bold text-gray-800">Usuarios</h1>
    <br>
  @php
    $columnas = [
        'id'              => 'ID',
        'name'            => 'Nombre',
        'email'           => 'Correo',
        'role'            => 'Rol',
        'department.name' => 'Departamento', // Accede a la relación
        'discipline.name'  => 'Disciplina', // Accede a la relación
        'created_at'      => 'Fecha de Creación',
    ];
@endphp
    
    <x-dynamic-table 
        :records="$users" 
        :columns="$columnas" 
        :eliminar="true" 
        :editar="true" 
        :ver="false" 
        :agregar="true"
        :routePrefix="'admin.users'"
    />
</div>
@endsection
