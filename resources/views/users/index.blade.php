@extends('layouts.app')
@section('title', 'Administrar Usuarios')
@section('content')
<div>
  
  @php
    $columnas = [
        'id'                => 'ID',
        'name'              => 'Nombre',
        'email'             => 'Correo',
        'role'              => 'Rol',
        'department.name'   => 'Departamento',
        'discipline_names'  => 'Disciplinas',
        'created_at'        => 'Fecha de Creación',
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
        :pdfRoute="'admin.users.pdf'"
    />
</div>
@endsection
