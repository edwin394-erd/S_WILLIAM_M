@extends('layouts.app')

@section('title', 'Crear Usuario')
@section('content')
<div class="bg-white p-6 rounded-lg w-full max-w-md border border-gray-300">
    {{-- <h1 class="text-2xl font-bold text-gray-800 mb-4">Crear Usuario</h1> --}}
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
            <br>
            <div class="flex">
         <div class="w-1/2 p-1">
    <!-- Añadimos un id al contenedor o directamente al componente para identificarlo -->
    <x-select 
        id="select-department"
        name="department_id" 
        label="Departamento" 
        :options="$departments_with_disciplines->pluck('name', 'id')->toArray()" 
        placeholder="Seleccione"
        required
        :nullable="true"
    />
</div>
<div class="w-1/2 p-1">
    <x-select 
        id="select-discipline"
        name="discipline_id" 
        label="Disciplina" 
        :options="$departments_with_disciplines->flatMap->disciplines->pluck('name','id')->toArray()" 
        placeholder="Seleccione"
        buscable="true"
        :nullable="true"
    />
</div>
                
                
                            
                            
                
                           
            </div>
            <br>

            

            
        </div>

        <x-confirm-cancel backUrl="{{ route('admin.users.index') }}"/>
        
        
    </form>

    
</div>

@endsection


@section("scripts")
<script>
    // Convertimos la colección de PHP a un objeto JSON de JS
    const departments = @json($departments_with_disciplines);

    document.getElementById('select-department').addEventListener('change', function(e) {
        const departmentId = e.detail; 
        const disciplineSelect = document.getElementById('select-discipline');
        
        if (!disciplineSelect) return;

        // Buscamos el departamento seleccionado
        const selectedDept = departments.find(d => d.id == departmentId);
        
        // Construimos un objeto puro { id: name } idéntico a cómo lo genera Laravel .toArray()
        const newOptions = {};
        if (selectedDept && selectedDept.disciplines) {
            selectedDept.disciplines.forEach(d => {
                newOptions[d.id] = d.name;
            });
        }

        // Obtenemos el proxy reactivo de Alpine (funciona en v3 y v2 como fallback)
        const alpineData = window.Alpine ? Alpine.$data(disciplineSelect) : disciplineSelect.__x$data;

        if (alpineData) {
            // Actualizamos el estado interno de forma reactiva
            alpineData.options = newOptions;
            alpineData.selected = null;
            alpineData.search = '';
        }
    });
</script>
@endsection