@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded-lg w-full max-w-md border border-gray-300">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Crear Departamento</h1>
    <form action="{{ route('admin.departments.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <x-input :label="'Nombre del Departamento'" 
                     :name="'name'" 
                     :type="'text'" 
                     :placeholder="'Ingrese el nombre del departamento'" 
                     :required="true" />
        </div>

        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Crear</button>
    </form>
</div>
@endsection