@extends('layouts.app')

@section('title', 'Editar Departamento')

@section('content')
<div class="bg-white p-6 rounded-lg w-full max-w-md border border-gray-300">
    <form action="{{ route('admin.departments.update', $department->id) }}" method="POST">
        @csrf
        @method('PUT')

        <x-input name="name" label="Nombre" value="{{ $department->name }}" required />
        <x-input name="grupo_telegram_id" label="Grupo Telegram (opcional)" value="{{ $department->grupo_telegram_id }}" />

        <x-confirm-cancel backUrl="{{ route('admin.departments.index') }}" />
    </form>
</div>
@endsection
