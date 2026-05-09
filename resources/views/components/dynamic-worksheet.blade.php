@props([
    'records', 
    'worksheetId',
    'routePrefix' => 'admin.workorders'
])

@php
    $dummyId = 'DUMMY_ID';
    $showUrl = route($routePrefix . '.show', $dummyId);
    $deleteUrl = route($routePrefix . '.destroy', $dummyId);
@endphp

<div class="space-y-4" x-cloak
     x-data="{ 
        search: '', 
        page: 1, 
        perPage: 10,
        records: {{ json_encode($records) }},
        showUrlTemplate: '{{ $showUrl }}',
        deleteUrlTemplate: '{{ $deleteUrl }}',
        
        get filteredRecords() {
            if (this.search === '') return this.records;
            return this.records.filter(r => 
                r.odm_number.toLowerCase().includes(this.search.toLowerCase()) ||
                r.accion_requerida.toLowerCase().includes(this.search.toLowerCase()) ||
                (r.installation && r.installation.name.toLowerCase().includes(this.search.toLowerCase()))
            );
        },
        get pagedRecords() {
            let start = (this.page - 1) * this.perPage;
            let end = start + parseInt(this.perPage);
            return this.filteredRecords.slice(start, end);
        },
        get totalPages() {
            return Math.ceil(this.filteredRecords.length / this.perPage) || 1;
        },
        getShowUrl(id) { return this.showUrlTemplate.replace('{{ $dummyId }}', id); },
        getDeleteUrl(id) { return this.deleteUrlTemplate.replace('{{ $dummyId }}', id); }
     }">

    {{-- Buscador y Botón Agregar --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="relative">
            <input type="text" x-model="search" @input="page = 1"
                placeholder="Buscar por ODM, Acción o Instalación..." 
                class="block w-full md:w-80 px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="flex items-center gap-4">
            <a href="{{ route($routePrefix . '.create', ['worksheet_id' => $worksheetId]) }}" 
               class="inline-flex items-center text-white bg-blue-600 hover:bg-blue-700 font-medium rounded-lg text-sm px-4 py-2">
                <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nueva Asignación
            </a>
        </div>
    </div>

    {{-- Tabla de Estilo Petro Boscán --}}
    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-300 text-xs">
        {{-- Encabezado --}}
        <div class="flex bg-gray-800 text-white font-bold uppercase p-2">
            <div class="w-10 text-center">AR</div>
            <div class="w-32 px-2 border-l border-gray-600">ODM / COD</div>
            <div class="w-28 px-2 border-l border-gray-600">Tipo</div>
            <div class="flex-1 px-2 border-l border-gray-600">Acción Requerida</div>
            <div class="w-32 px-2 border-l border-gray-600">Instalación</div>
            <div class="w-24 text-right px-2 border-l border-gray-600">Impacto</div>
        </div>

        {{-- Cuerpo Dinámico --}}
        <template x-for="order in pagedRecords" :key="order.id">
            <div class="border-b border-gray-300 hover:bg-gray-50 transition">
                {{-- Fila Principal --}}
                <div class="flex items-center font-bold bg-white p-2">
                    <div class="w-10 text-center text-red-600" x-text="order.is_high_risk ? 'ALTO' : ''"></div>
                    <div class="w-32 px-2 border-l border-gray-200" x-text="order.odm_number"></div>
                    <div class="w-28 px-2 border-l border-gray-200" x-text="order.type"></div>
                    <div class="flex-1 px-2 border-l border-gray-200 uppercase" x-text="order.accion_requerida"></div>
                    <div class="w-32 px-2 border-l border-gray-200" x-text="order.installation?.name ?? 'N/A'"></div>
                    <div class="w-24 text-right px-2 border-l border-gray-200" x-text="new Intl.NumberFormat().format(order.impacto) + ' Bls'"></div>
                </div>

                {{-- Filas de Tareas (Disciplinas) --}}
                <template x-for="task in order.tasks" :key="task.id">
                    <div class="flex items-center text-[10px] text-gray-600 bg-gray-50/50 p-1 border-t border-gray-100 italic">
                        <div class="w-10"></div>
                        <div class="w-32 px-2 font-semibold" x-text="'A-' + order.odm_number.slice(-6)"></div>
                        <div class="w-28 px-2" x-text="task.discipline?.name ?? 'Sin Disciplina'"></div>
                        <div class="flex-1 px-2" x-text="order.accion_requerida"></div>
                        <div class="w-auto px-2 text-right font-mono">
                            <span class="font-bold text-gray-800" x-text="task.date"></span> 
                            <span x-text="task.time_start.substring(11, 16) + ' - ' + task.time_end.substring(11, 16)"></span>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        {{-- Estado vacío --}}
        <div x-show="filteredRecords.length === 0" class="p-8 text-center text-gray-500">
            No se encontraron asignaciones.
        </div>
    </div>

    {{-- Paginación (Desktop) --}}
    <div class="flex items-center justify-between" x-show="totalPages > 1">
        <p class="text-xs text-gray-600">
            Página <span x-text="page"></span> de <span x-text="totalPages"></span>
        </p>
        <nav class="inline-flex -space-x-px">
            <button @click="page--" :disabled="page === 1" class="px-3 py-1 border border-gray-300 rounded-l-md disabled:opacity-50">Ant.</button>
            <button @click="page++" :disabled="page === totalPages" class="px-3 py-1 border border-gray-300 rounded-r-md disabled:opacity-50">Sig.</button>
        </nav>
    </div>
</div>