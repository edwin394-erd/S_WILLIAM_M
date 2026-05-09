@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded-lg w-full max-w-md border border-gray-300">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Crear Usuario</h1>
    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <x-input :label="'Nombre'" 
                     :name="'name'" 
                     :type="'text'" 
                     :placeholder="'Ingrese el nombre del usuario'" 
                     :required="true" />
            
            <x-input :label="'Email'"
                        :name="'email'" 
                        :type="'email'" 
                        :placeholder="'Ingrese el email del usuario'" 
                        :required="true" />

            <x-select 
                name="role" 
                label="Rol del Usuario" 
                :options="['admin' => 'Administrador', 'supervisor' => 'Supervisor', 'tecnico' => 'Técnico', 'planificador' => 'Planificador']" 
                placeholder="Seleccione un rol"
                required
            />

            <x-select 
                name="department_id" 
                label="Departamento" 
                :options="$departments_with_disciplines->pluck('name', 'id')->toArray()" 
                placeholder="Seleccione un departamento"
                required
            />

            <x-select 
                name="discipline_id" 
                label="Disciplina" 
                :options="[]" 
                placeholder="Seleccione una disciplina"
                required
            />

            

            
        </div>

        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Crear</button>
    </form>

    
</div>

@endsection


@section("scripts")


<!-- Tu HTML anterior se mantiene igual -->

<script>
    // Convertimos la colección de PHP a un objeto JSON de JS
    const departments = @json($departments_with_disciplines);

    document.getElementById('department_id').addEventListener('change', function() {
        const departmentId = this.value;
        const disciplineSelect = document.getElementById('discipline_id');
        
        // Limpiar opciones actuales (dejando solo el placeholder)
        disciplineSelect.innerHTML = '<option value="" disabled selected>Seleccione una disciplina</option>';

        // Buscar el departamento seleccionado en nuestro objeto JSON
        const selectedDept = departments.find(d => d.id == departmentId);

        if (selectedDept && selectedDept.disciplines) {
            selectedDept.disciplines.forEach(discipline => {
                const option = document.createElement('option');
                option.value = discipline.id;
                option.text = discipline.name;
                disciplineSelect.appendChild(option);
            });
        }
    });
</script>
@endsection