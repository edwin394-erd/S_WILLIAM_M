@extends('layouts.app')

@section('title', 'Editar Disciplina')

@section('content')
<div class="bg-white p-6 rounded-lg w-full max-w-md border border-gray-300">
    <form action="{{ route('admin.disciplines.update', $discipline->id) }}" method="POST">
        @csrf
        @method('PUT')

        <x-input name="name" label="Nombre" value="{{ $discipline->name }}" required />

        <x-select name="department_id" label="Departamento" :options="$departments" selected="{{ $discipline->department_id }}" placeholder="Seleccione departamento..." />
        
        <br>
        <x-confirm-cancel backUrl="{{ route('admin.disciplines.index') }}" />
    </form>
</div>
@endsection
