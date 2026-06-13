@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="bg-white p-6 rounded-lg w-full max-w-md border border-gray-300">
    @php
        $selectedDepartmentId = old('department_id', $user->department_id);
        $initialDisciplineOptions = $departments_with_disciplines->firstWhere('id', $selectedDepartmentId)?->disciplines->pluck('name', 'id')->toArray() ?? [];
    @endphp

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <x-input name="name" label="Nombre" value="{{ $user->name }}" required />
        <x-input name="email" label="Email" type="email" value="{{ $user->email }}" required />

        <x-select 
            id="select-role"
            name="role" 
            label="Rol del Usuario" 
            :options="['admin' => 'Administrador', 'supervisor' => 'Supervisor', 'tecnico' => 'Técnico', 'planificador' => 'Planificador']" 
            selected="{{ old('role', $user->role) }}"
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
                    selected="{{ old('department_id', $user->department_id) }}"
                    placeholder="Seleccione"
                    :nullable="true"
                />
            </div>
            <div class="w-1/2">
                <div id="discipline-single-wrapper" class="{{ old('role', $user->role) === 'supervisor' ? 'hidden' : '' }}">
                    <x-select 
                        id="select-discipline-single"
                        name="discipline_id" 
                        label="Disciplina" 
                        :options="$initialDisciplineOptions" 
                        selected="{{ old('discipline_id', $user->discipline_id) }}"
                        placeholder="Seleccione"
                        buscable="true"
                        :nullable="true"
                    />
                </div>

                <div id="discipline-multi-wrapper" class="{{ old('role', $user->role) === 'supervisor' ? '' : 'hidden' }}">
                    <label for="select-discipline-multi" class="block mb-2.5 text-sm font-medium text-heading">Disciplinas</label>
                    <select id="select-discipline-multi" name="discipline_select"
                        class="block w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-900 focus:border-slate-500 focus:ring-slate-500">
                        <option value="">Seleccione una disciplina...</option>
                    </select>
                    <div id="discipline-tags" class="flex flex-nowrap gap-2 mt-3 overflow-x-auto whitespace-nowrap max-h-14 py-1"></div>
                    <div id="discipline-hidden-inputs"></div>
                    @error('discipline_ids')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Selecciona las disciplinas una por una y verás las etiquetas abajo.</p>
                </div>
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
    const allDisciplines = @json($departments_with_disciplines->flatMap->disciplines->pluck('name', 'id')->toArray());
    const roleSelect = document.getElementById('select-role');
    const departmentSelect = document.getElementById('select-department');
    const disciplineSingleWrapper = document.getElementById('discipline-single-wrapper');
    const disciplineMultiWrapper = document.getElementById('discipline-multi-wrapper');
    const disciplineSingleComponent = document.getElementById('select-discipline-single');
    const disciplineMultiSelect = document.getElementById('select-discipline-multi');
    const disciplineTags = document.getElementById('discipline-tags');
    const disciplineHiddenInputs = document.getElementById('discipline-hidden-inputs');

    let selectedDisciplineIds = @json(old('discipline_ids', $user->disciplines->pluck('id')->toArray()));
    selectedDisciplineIds = selectedDisciplineIds.map(String);
    const initialRole = '{{ old('role', $user->role) }}';
    const initialDepartmentId = '{{ old('department_id', $user->department_id) }}';

    function setRoleUI(role) {
        if (role === 'supervisor') {
            disciplineSingleWrapper.classList.add('hidden');
            disciplineMultiWrapper.classList.remove('hidden');
        } else {
            disciplineSingleWrapper.classList.remove('hidden');
            disciplineMultiWrapper.classList.add('hidden');
        }
    }

    function buildHiddenInputs() {
        if (!disciplineHiddenInputs) return;
        disciplineHiddenInputs.innerHTML = '';
        selectedDisciplineIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'discipline_ids[]';
            input.value = id;
            disciplineHiddenInputs.appendChild(input);
        });
    }

    function setSingleOptions(disciplines) {
        if (!disciplineSingleComponent) return;
        const alpineData = window.Alpine ? Alpine.$data(disciplineSingleComponent) : disciplineSingleComponent.__x$data;
        if (!alpineData) return;

        const options = {};
        disciplines.forEach(discipline => {
            options[discipline.id] = discipline.name;
        });
        alpineData.options = options;
        if (!Object.keys(options).includes(alpineData.selected)) {
            alpineData.selected = '';
        }
    }

    function setMultiOptions(disciplines) {
        if (!disciplineMultiSelect) return;
        disciplineMultiSelect.innerHTML = '<option value="">Seleccione una disciplina...</option>';
        disciplines.forEach(discipline => {
            const option = document.createElement('option');
            option.value = discipline.id;
            option.textContent = discipline.name;
            disciplineMultiSelect.appendChild(option);
        });
    }

    function renderDisciplineTags() {
        if (!disciplineTags) return;
        disciplineTags.innerHTML = '';
        selectedDisciplineIds.forEach(id => {
            const tag = document.createElement('span');
            tag.className = 'inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700';
            tag.innerHTML = `<span>${allDisciplines[id] ?? 'Disciplina'}</span><button type="button" data-value="${id}" class="text-slate-500 hover:text-slate-700">✕</button>`;
            disciplineTags.appendChild(tag);
        });
    }

    function addDisciplineId(id) {
        if (!id || selectedDisciplineIds.includes(id)) return;
        selectedDisciplineIds.push(id);
        buildHiddenInputs();
        renderDisciplineTags();
    }

    function removeDisciplineId(id) {
        selectedDisciplineIds = selectedDisciplineIds.filter(value => value !== String(id));
        buildHiddenInputs();
        renderDisciplineTags();
    }

    function updateDisciplineOptions(departmentId) {
        const selectedDept = departments.find(d => d.id == departmentId);
        const options = selectedDept ? selectedDept.disciplines : [];
        setSingleOptions(options);
        setMultiOptions(options);

        const validIds = options.map(discipline => discipline.id.toString());
        selectedDisciplineIds = selectedDisciplineIds.filter(id => validIds.includes(id.toString()));
        buildHiddenInputs();
        renderDisciplineTags();
    }

    function resetSupervisorDisciplines() {
        selectedDisciplineIds = [];
        buildHiddenInputs();
        renderDisciplineTags();
    }

    function getSelectValue(e, element) {
        return e?.detail ?? element?.value ?? '';
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', function(e) {
            const role = getSelectValue(e, roleSelect);
            setRoleUI(role);
            resetSupervisorDisciplines();
        });
    }

    if (departmentSelect) {
        departmentSelect.addEventListener('change', function(e) {
            const departmentId = getSelectValue(e, departmentSelect);
            updateDisciplineOptions(departmentId);
            if (getSelectValue(null, roleSelect) === 'supervisor') {
                resetSupervisorDisciplines();
            }
        });
    }

    if (disciplineMultiSelect) {
        disciplineMultiSelect.addEventListener('change', function() {
            const selectedId = this.value;
            if (!selectedId) return;
            addDisciplineId(selectedId);
            this.value = '';
        });
    }

    if (disciplineTags) {
        disciplineTags.addEventListener('click', function(event) {
            const button = event.target.closest('button[data-value]');
            if (!button) return;
            const value = button.dataset.value;
            removeDisciplineId(value);
        });
    }

    setRoleUI(initialRole);
    if (initialDepartmentId) {
        updateDisciplineOptions(initialDepartmentId);
    } else {
        updateDisciplineOptions('');
    }
    buildHiddenInputs();
    renderDisciplineTags();
</script>
@endsection
