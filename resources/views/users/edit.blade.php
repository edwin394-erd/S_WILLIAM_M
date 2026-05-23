@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="bg-white p-6 rounded-lg w-full max-w-md border border-gray-300">
    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <x-input name="name" label="Nombre" value="{{ $user->name }}" required />
        <x-input name="email" label="Email" type="email" value="{{ $user->email }}" required />

        <x-select 
            name="role" 
            label="Rol del Usuario" 
            :options="['admin' => 'Administrador', 'supervisor' => 'Supervisor', 'tecnico' => 'Técnico', 'planificador' => 'Planificador']" 
            selected="{{ $user->role }}"
            placeholder="Seleccione un rol"
            required
        />
        <br>

        <div class="flex gap-2">
            <div class="w-1/2">
                <x-select 
                    name="department_id" 
                    label="Departamento" 
                    :options="$departments_with_disciplines->pluck('name', 'id')->toArray()" 
                    selected="{{ $user->department_id }}"
                    placeholder="Seleccione un departamento"
                />
            </div>
            <div class="w-1/2">
                <x-select 
                    name="discipline_id" 
                    label="Disciplina" 
                    :options="$departments_with_disciplines->flatMap->disciplines->pluck('name','id')->toArray()" 
                    selected="{{ $user->discipline_id }}"
                    placeholder="Seleccione una disciplina"
                    buscable="true"
                />
            </div>
        </div>
        <br>

        <x-confirm-cancel backUrl="{{ route('admin.users.index') }}" />
    </form>
</div>
@endsection
