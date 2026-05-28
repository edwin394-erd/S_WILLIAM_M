@extends('layouts.app')

@section('title', 'Crear Disciplina')

@section('content')
<div class="bg-white p-6 rounded-lg w-full max-w-md border border-gray-300">
    <form action="{{ route('admin.disciplines.store') }}" method="POST">
        @csrf

        <x-input name="name" label="Nombre" placeholder="Nombre de la disciplina" required />

        <x-select name="department_id" label="Departamento" :options="$departments" placeholder="Seleccione un departamento" required />
        <br>
        <x-confirm-cancel backUrl="{{ route('admin.disciplines.index') }}" />
    </form>
</div>
@endsection
