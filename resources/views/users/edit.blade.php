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
                    id="select-department"
                    name="department_id" 
                    label="Departamento" 
                    :options="$departments_with_disciplines->pluck('name', 'id')->toArray()" 
                    selected="{{ $user->department_id }}"
                    placeholder="Seleccione"
                    :nullable="true"
                />
            </div>
            <div class="w-1/2">
                <x-select 
                    id="select-discipline"
                    name="discipline_id" 
                    label="Disciplina" 
                    :options="$departments_with_disciplines->flatMap->disciplines->pluck('name','id')->toArray()" 
                    selected="{{ $user->discipline_id }}"
                    placeholder="Seleccione"
                    buscable="true"
                    :nullable="true"
                />
            </div>
        </div>
        <br>

        <x-confirm-cancel backUrl="{{ route('admin.users.index') }}" />
    </form>
</div>
@endsection

@section('scripts')
<script>
    const departments = @json($departments_with_disciplines);

    document.getElementById('select-department').addEventListener('change', function(e) {
        const departmentId = e.detail;
        const disciplineSelect = document.getElementById('select-discipline');
        if (!disciplineSelect) return;

        const selectedDept = departments.find(d => d.id == departmentId);
        const newOptions = {};

        if (selectedDept && selectedDept.disciplines) {
            selectedDept.disciplines.forEach(d => {
                newOptions[d.id] = d.name;
            });
        }

        const alpineData = window.Alpine ? Alpine.$data(disciplineSelect) : disciplineSelect.__x$data;
        if (alpineData) {
            alpineData.options = newOptions;
            alpineData.selected = null;
            alpineData.search = '';
        }
    });
</script>
@endsection
