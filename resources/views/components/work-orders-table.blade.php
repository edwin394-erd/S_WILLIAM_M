@props([
    'records', 
    'worksheetId',
    'routePrefix' => 'admin.workorders',
    'eliminar' => false, 
    'editar' => false, 
    'ver' => false,
    'crear' => true,
    'reportar' => false
])

@php
    $dummyId = 'DUMMY_ID';
    $dummyWorkOrderId = 'DUMMY_W';
    $dummyDisciplineId = 'DUMMY_D';
    $showUrl = ($ver) ? route($routePrefix . '.show', $dummyId) : '';
    $editUrl = ($editar) ? route($routePrefix . '.edit', $dummyId) : '';
    $deleteUrl = ($eliminar) ? route($routePrefix . '.destroy', $dummyId) : '';
    $reportUrl =  ($reportar) ? route('tecnico.reportar.formulario', ['id_disciplina' => $dummyDisciplineId, 'work_order' => $dummyWorkOrderId]) : '';
@endphp

<div class="space-y-4" x-cloak
     x-data="{ 
        search: '', 
        page: 1, 
        perPage: 10,
        records: {{ json_encode($records) }},
        showUrlTemplate: '{{ $showUrl }}',
        editUrlTemplate: '{{ $editUrl }}',
        deleteUrlTemplate: '{{ $deleteUrl }}',
        reportUrlTemplate: '{{ $reportUrl }}',
        
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
        getEditUrl(id) { return this.editUrlTemplate.replace('{{ $dummyId }}', id); },
        getDeleteUrl(id) { return this.deleteUrlTemplate.replace('{{ $dummyId }}', id); },
        getReportUrl(workOrderId, disciplineId) {
            return this.reportUrlTemplate
                .replace('{{ $dummyDisciplineId }}', disciplineId)
                .replace('{{ $dummyWorkOrderId }}', workOrderId);
        }
     }">

    {{-- Buscador y Cabecera --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 px-1">
        <div class="relative">
            <input type="text" x-model="search" @input="page = 1"
                placeholder="Buscar por ODM, Acción o Instalación..." 
                class="block w-full md:w-80 px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500 shadow-sm">
        </div>
        @if ($crear)
            
        <div class="flex items-center gap-3">
            <a href="{{ route($routePrefix . '.create', ['worksheet_id' => $worksheetId]) }}" 
               class="inline-flex items-center text-slate-600 bg-slate-100 hover:bg-slate-200 font-medium rounded-lg text-sm px-4 py-2 transition-colors shadow-sm">
                <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nueva Asignación
            </a>
        </div>
        @endif
    </div>

    {{-- Tabla Estilo Petro Boscán con Acciones --}}
    <div class="space-y-4">

        {{-- Listado de Órdenes --}}
        <template x-for="order in pagedRecords" :key="order.id">
            
            <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-300 text-xs ">
                 {{-- Encabezado --}}
        <div class="flex bg-slate-600 text-white font-bold uppercase p-2 items-center">
            <div class="w-10 text-center">AR</div>
            <div class="w-32 px-2 border-l border-gray-600 text-center">ODM / COD</div>
            <div class="w-24 px-2 border-l border-gray-600">Tipo</div>
            <div class="flex-1 px-2 border-l border-gray-600">Acción Requerida</div>
            <div class="w-32 px-2 border-l border-gray-600">Instalación</div>
            <div class="w-24 text-right px-2 border-l border-gray-600">Impacto</div>
            <div class="w-24 text-right px-2 border-l border-gray-600">Equipo</div>
            @if($editar || $eliminar || $ver || $reportar)
                <div class="w-32 text-center px-2 border-l border-gray-600">Gestión</div>
            @endif
        </div>
                {{-- Fila Principal (ODM) --}}
                <div class="flex items-center font-bold bg-white p-2">
                    <div class="w-10 text-center text-red-600" x-text="order.is_high_risk ? 'ALTO' : ''"></div>
                    <div class="w-32 px-2 border-l border-gray-200 text-center text-slate-700" x-text="order.odm_number"></div>
                    <div class="w-24 px-2 border-l border-gray-200 text-[10px]" x-text="order.type"></div>
                    <div class="flex-1 px-2 border-l border-gray-200 uppercase truncate" x-text="order.accion_requerida"></div>
                    <div class="w-32 px-2 border-l border-gray-200 truncate" x-text="order.installation?.name ?? 'N/A'"></div>
                    <div class="w-24 text-right px-2 border-l border-gray-200 font-mono text-gray-700" x-text="new Intl.NumberFormat().format(order.impacto) + ' Bls'"></div>
                    <div class="w-24 text-right px-2 border-l border-gray-200 truncate" x-text="order.equipment?.name ?? 'N/A'"></div>
                    
                    {{-- Acciones dinámicas --}}
                    @if($editar || $eliminar || $ver || $reportar)
                    <div class="w-32 px-2 border-l border-gray-200 flex justify-center gap-3">
                        @if($ver)
                            <a :href="getShowUrl(order.id)" class="text-green-600 hover:text-green-800" title="Ver Detalles">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        @endif
                        @if($editar)
                            <a :href="getEditUrl(order.id)" class="text-slate-600 hover:text-slate-800" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                        @endif
                        @if($eliminar)
                            <form :action="getDeleteUrl(order.id)" method="POST" @submit.prevent="if(confirm('¿Eliminar ODM ' + order.odm_number + '?')) $el.submit()">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800" title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        @endif
                        @if($reportar)
                            <a :href="getReportUrl(order.id, order.tasks[0]?.discipline_id ?? 'N/A')"
                               title="Reportar Actividad"
                               class="inline-flex items-center rounded-full p-1 transition"
                               >
                                <x-svg-check :pxls="20" class="w-5 h-5" />

                            </a>
                        @endif
                    </div>
                    @endif
                </div>
            

                {{-- Sub-filas (Disciplinas / Tareas) --}}
                <template x-for="task in order.tasks" :key="task.id">
                    <div class="flex items-center text-[10px] text-gray-500 bg-slate-50 p-1.5 border-t border-gray-100 italic">
                        <div class="w-10"></div>
                        <div class="w-32 px-2 font-bold text-gray-700" x-text="'A-' + order.odm_number.toString().slice(-6)"></div>
                        <div class="w-24 px-2 font-semibold text-slate-900" x-text="task.discipline?.name ?? 'S/D'"></div>
                        <div class="flex-1 px-2 border-l border-gray-200" x-text="order.accion_requerida"></div>
                        <div class="w-auto px-2 text-right font-mono font-medium">
                            <span class="text-gray-900" x-text="task.date.includes('T') ? task.date.split('T')[0].split('-').reverse().join('/') : task.date"></span> | 
                            <span class="text-slate-800" x-text="(task.time_start.includes('T') ? task.time_start.split('T')[1].slice(0,5) : task.time_start.slice(0,5)) + ' - ' + (task.time_end.includes('T') ? task.time_end.split('T')[1].slice(0,5) : task.time_end.slice(0,5))"></span>
                        </div>
                        @if($editar || $eliminar || $ver || $reportar)
                            <div class="w-32 px-2 border-l border-gray-200">
                                 <span class="inline-flex items-center justify-center ml-3 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase"
                                  :class="task.status === 'PENDIENTE' ? 'bg-yellow-100 text-yellow-700' : task.status === 'completado' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-700'"
                                  x-text="task.status ?? 'S/D'"></span></div> {{-- Espacio para alinear con acciones de arriba --}}
                        @endif
                    </div>
                    
                </template>
            </div>
        </template>

        {{-- No hay registros --}}
        <div x-show="filteredRecords.length === 0" class="p-10 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            <p class="mt-2 text-sm text-gray-500 font-medium">No se encontraron asignaciones con ese criterio.</p>
        </div>
    </div>

    {{-- Paginación Desktop --}}
    <div class="flex items-center justify-between px-2 pt-2" x-show="totalPages > 1">
        <span class="text-xs text-gray-500 font-medium italic">
            Mostrando <span x-text="pagedRecords.length"></span> de <span x-text="filteredRecords.length"></span> resultados
        </span>
        <div class="inline-flex shadow-sm rounded-md">
            <button @click="page--" :disabled="page === 1" class="px-4 py-2 text-xs font-bold text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 disabled:opacity-40 transition">ANTERIOR</button>
            <div class="px-4 py-2 text-xs font-bold text-slate-700 bg-slate-50 border-t border-b border-gray-300" x-text="page + ' / ' + totalPages"></div>
            <button @click="page++" :disabled="page === totalPages" class="px-4 py-2 text-xs font-bold text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 disabled:opacity-40 transition">SIGUIENTE</button>
        </div>
    </div>
</div>