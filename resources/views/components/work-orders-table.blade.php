@props([
    'records', 
    'worksheetId' => null,
    'routePrefix' => 'admin.workorders',
    'eliminar' => false, 
    'editar' => false, 
    'ver' => false,
    'crear' => true,
    'reportar' => false,
    'pdf' => false,
    'disciplineId' => null,
    'departmentOptions' => [],
    'disciplineOptions' => [],
    'dateFrom' => '',
    'dateTo' => '',
    'weekFilter' => '',
    'extraplan' => false,
    'filtroFechas' => false,
    'weekOptions' => [],
])

@php

    $dummyId = 'DUMMY_ID';
    $dummyWorkOrderId = 'DUMMY_W';
    $dummyDisciplineId = 'DUMMY_D';
    $showUrl = ($ver) ? route($routePrefix . '.show', $dummyId) : '';
    $editUrl = ($editar) ? route($routePrefix . '.edit', $dummyId) : '';
    $deleteUrl = ($eliminar) ? route($routePrefix . '.destroy', $dummyId) : '';
    $reportUrl =  ($reportar)
        ? (auth()->user()->role === 'supervisor'
            ? route('supervisor.reportar.formulario', ['id_disciplina' => $dummyDisciplineId, 'work_order' => $dummyWorkOrderId])
            : route('tecnico.reportar.formulario', ['id_disciplina' => $dummyDisciplineId, 'work_order' => $dummyWorkOrderId]))
        : '';
    
    $createParams = [];
    if ($worksheetId) {
        $createParams['worksheet_id'] = $worksheetId;
    }
    if ($extraplan) {
        $createParams['is_extraplan'] = 1;
    }
    $createRoute = route($routePrefix . '.create', $createParams);
@endphp

<div class="space-y-2" x-cloak
     x-data="{ 
        search: '', 
        page: 1, 
        perPage: 10,
        records: {{ json_encode($records) }},
        showUrlTemplate: '{{ $showUrl }}',
        editUrlTemplate: '{{ $editUrl }}',
        deleteUrlTemplate: '{{ $deleteUrl }}',
        reportUrlTemplate: '{{ $reportUrl }}',
        reassignUrlTemplate: '{{ route('workorders.reassign', ['work_order' => $dummyWorkOrderId]) }}',
        createRoute: '{{ $createRoute }}',
        disciplineId: {{ $disciplineId ?? 'null' }},
        authRole: '{{ auth()->user()->role }}',
        showReportModal: false,
        showReassignModal: false,
        showExtraplanWarning: false,
        reportOrder: null,
        reassignOrder: null,
        reportTask: null,
        reassignTarget: 'current',
        observationInput: '',
        supervisorDisciplineIds: @js(auth()->user()->role === 'supervisor' ? auth()->user()->disciplines->pluck('id')->toArray() : []),
        statusFilter: @js(request()->query('status', 'ALL')),
        departmentFilter: @js(request()->query('department_id', '')),
        disciplineFilter: @js(request()->query('discipline_id', '')),
        departments: @js($departmentOptions),
        disciplines: @js($disciplineOptions),
        weekFilter: '{{ $weekFilter ?? '' }}',
        weeks: @js($weekOptions),
        dateFrom: '{{ $dateFrom ?? '' }}',
        dateTo: '{{ $dateTo ?? '' }}',
        setWeek(value) {
            this.weekFilter = value;
            if (!value) {
                this.dateFrom = '';
                this.dateTo = '';
            } else {
                const week = this.weeks.find(w => w.value === value);
                if (week) {
                    this.dateFrom = week.start;
                    this.dateTo = week.end;
                }
            }
            this.page = 1;
        },
        getPdfUrl() {
            const params = new URLSearchParams();
            if (this.statusFilter && this.statusFilter !== 'ALL') params.set('status', this.statusFilter);
            if (this.departmentFilter) params.set('department_id', this.departmentFilter);
            if (this.disciplineFilter) params.set('discipline_id', this.disciplineFilter);
            if (this.dateFrom) params.set('dateFrom', this.dateFrom);
            if (this.dateTo) params.set('dateTo', this.dateTo);
            if (this.search) params.set('search', this.search);
            const base = this.authRole === 'supervisor' ? '{{ route("supervisor.workorders.historial.pdf") }}' : '{{ route("admin.workorders.historial.pdf") }}';
            return base + (params.toString() ? ('?' + params.toString()) : '');
        },
        
        get filteredRecords() {
            return this.records.filter(r => {
                const matchesSearch = this.search === '' ||
                    r.odm_number.toLowerCase().includes(this.search.toLowerCase()) ||
                    r.accion_requerida.toLowerCase().includes(this.search.toLowerCase()) ||
                    r.type?.toLowerCase().includes(this.search.toLowerCase()) ||
                    (r.installation && r.installation.name.toLowerCase().includes(this.search.toLowerCase()));

                const status = r.tasks?.[0]?.status ?? '';
                const matchesStatus = this.statusFilter === 'ALL' || this.statusFilter === status;
                const matchesDepartment = !this.departmentFilter || String(r.workSheet?.department_id ?? r.work_sheet?.department_id ?? '') === String(this.departmentFilter);
                const matchesDiscipline = !this.disciplineFilter || r.tasks?.some(task => String(task.discipline_id) === String(this.disciplineFilter));

                let matchesDateRange = true;
                if (this.dateFrom && this.dateTo) {
                    const from = new Date(this.dateFrom);
                    const to = new Date(this.dateTo);
                    matchesDateRange = r.tasks?.some(task => {
                        if (!task.date) {
                            return false;
                        }
                        const taskDateValue = task.date.includes('T') ? task.date.split('T')[0] : task.date;
                        const taskDate = new Date(taskDateValue);
                        return taskDate >= from && taskDate <= to;
                    }) ?? false;
                }

                return matchesSearch && matchesStatus && matchesDepartment && matchesDiscipline && matchesDateRange;
            });
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
        openReportModal(order) {
            this.reportOrder = order;
            this.reportTask = order.tasks?.[0] ?? null;
            this.observationInput = this.reportTask?.observation || '';
            this.showReportModal = true;
        },
        getSupervisorPendingTask(order) {
            return order.tasks?.find(task => task.status === 'PENDIENTE' && this.supervisorDisciplineIds.includes(task.discipline_id)) ?? null;
        },
        getReportUrl(workOrderId, disciplineId) {
            const selectedDisciplineId = disciplineId || this.disciplineId;
            if (!selectedDisciplineId) {
                return '#';
            }
            return this.reportUrlTemplate
                .replace('{{ $dummyDisciplineId }}', selectedDisciplineId)
                .replace('{{ $dummyWorkOrderId }}', workOrderId);
        },
        getReassignUrl(workOrderId) {
            return this.reassignUrlTemplate.replace('{{ $dummyWorkOrderId }}', workOrderId);
        },
        openReassignModal(order) {
            this.reassignOrder = order;
            this.reassignTarget = 'current';
            this.showReassignModal = true;
        },
        closeReassignModal() {
            this.showReassignModal = false;
        }
     }">
    

    {{-- Buscador y Cabecera fija --}}
    <div class="sticky top-0 z-20 bg-white py-3">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 px-1">
            <div class="grid gap-3 w-full sm:grid-cols-2 xl:grid-cols-6">
                <div class="sm:col-span-2 xl:col-span-2">
                    <input type="text" x-model="search" @input="page = 1"
                        placeholder="Buscar por ODM, Acción o Instalación..." 
                        class="block w-full px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500 shadow-sm">
                </div>
                <div class="sm:col-span-1 xl:col-span-1">
                    <x-select
                        name="statusFilter"
                        :options="[
                            'ALL' => 'Todos los estados',
                            'PENDIENTE' => 'Pendiente',
                            'POR REVISION' => 'Por revisión',
                            'COMPLETADO' => 'Completado',
                            'NO COMPLETADO' => 'No completado',
                        ]"
                        selected="{{ request()->query('status', 'ALL') }}"
                        placeholder="Filtrar estado"
                        class="w-full"
                        @change="statusFilter = $event.detail; page = 1"
                    />
                </div>
                @if(!empty($departmentOptions))
                <div class="sm:col-span-1 xl:col-span-1">
                    <x-select
                        name="department_id"
                        :options="$departmentOptions"
                        selected="{{ request()->query('department_id', '') }}"
                        placeholder="Filtrar departamento"
                        class="w-full"
                        :nullable="true"
                        :buscable="true"
                        nullableLabel="Todos los departamentos"
                        @change="departmentFilter = $event.detail; page = 1"
                    />
                </div>
                @endif
                @if(!empty($disciplineOptions))
                <div class="sm:col-span-1 xl:col-span-1">
                    <x-select
                        name="discipline_id"
                        :options="$disciplineOptions"
                        selected="{{ request()->query('discipline_id', '') }}"
                        placeholder="Filtrar disciplina"
                        class="w-full"
                        :nullable="true"
                        :buscable="true"
                        nullableLabel="Todas las disciplinas"
                        @change="disciplineFilter = $event.detail; page = 1"
                    />
                </div>
                @endif
                @if($filtroFechas)
                <div class="sm:col-span-1 xl:col-span-1">
                    <x-select
                        name="weekFilter"
                        :options="$weekOptions"
                        selected="{{ $weekFilter ?? '' }}"
                        placeholder="Filtrar por semana"
                        class="w-full"
                        :nullable="true"
                        :buscable="true"
                        nullableLabel="Todas las semanas"
                        @change="setWeek($event.detail)"
                    />
                </div>
                <div class="flex w-full gap-2 col-span-2">
                    <div class="sm:col-span-1 md:w-1/2 me-2">
                        <input type="date" id="dateFrom" x-model="dateFrom" @input="weekFilter = ''; page = 1"
                            class="block w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500 shadow-sm">
                    </div>
                    <div class="sm:col-span-1  md:w-1/2 me-2">
                        <input type="date" id="dateTo" x-model="dateTo" @input="weekFilter = ''; page = 1"
                            class="block w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500 shadow-sm">
                    </div>
                </div>
                @endif
            </div>
            @if ($crear)
                <div class="flex items-center w-full sm:w-auto">
                    @if ($extraplan)
                        <button type="button" @click="showExtraplanWarning = true"
                            class="inline-flex items-center justify-center w-full sm:w-auto text-slate-600 bg-slate-100 hover:bg-slate-200 font-medium rounded-lg text-sm px-4 py-2 transition-colors shadow-sm">
                            <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Nueva Asignación (extraplan)
                        </button>
                    @else
                        <a href="{{ $worksheetId ? route($routePrefix . '.create', ['worksheet_id' => $worksheetId]) : route($routePrefix . '.create') }}" 
                           class="inline-flex items-center justify-center w-full sm:w-auto text-slate-600 bg-slate-100 hover:bg-slate-200 font-medium rounded-lg text-sm px-4 py-2 transition-colors shadow-sm">
                            <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Nueva Asignación
                        </a>
                    @endif
                </div>
            @endif
            @if($pdf)
                <div class="ml-2">
                    <a :href="getPdfUrl()" target="_blank" class="w-full cursor-pointer inline-flex items-center justify-center text-xs px-3 py-1 rounded-md bg-slate-100 text-slate-700 hover:bg-slate-200 border border-gray-200">PDF</a>
                </div>
            @endif
           
        </div>
    </div>

    <div x-show="showExtraplanWarning" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6">
        <div @click.stop class="bg-white rounded-lg w-full max-w-2xl p-4 shadow-xl">
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900 mb-2">Advertencia de Extraplan</h2>
                    <p class="mt-1 text-sm text-slate-600">Esta sabana ya está en curso, por lo que la nueva orden debe agregarse como un <strong>Extraplan</strong>.</p>
                </div>
                <button @click="showExtraplanWarning = false" class="rounded-full bg-slate-100 p-2 text-slate-600 hover:bg-slate-200 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="mt-5 text-sm text-slate-700 space-y-3">
                <p>Al aceptar, se abrirá el formulario de creación con el tipo extra-plan activado.</p>
                <p>Si aún no desea continuar, puede cancelar y revisar las órdenes existentes.</p>
            </div>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                <button type="button" @click="showExtraplanWarning = false" class="inline-flex items-center justify-center rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Cancelar</button>
                <a :href="createRoute" class="inline-flex items-center justify-center rounded-md bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-500 transition">Aceptar</a>
            </div>
        </div>
    </div>

    <div x-show="showReassignModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click="closeReassignModal()">
        <div class="bg-white rounded-lg w-full max-w-md p-4" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold">Reasignar Orden</h3>
                    <p class="text-sm text-slate-600">Elige si reasignar a la sabana actual o a la siguiente.</p>
                </div>
                <button @click="closeReassignModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form :action="getReassignUrl(reassignOrder?.id)" method="POST">
                @csrf
                <div class="space-y-4">
                    <label class="block text-sm font-medium text-slate-700">Destino de la reassignación</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 rounded-lg border border-slate-200 p-3 cursor-pointer">
                            <input type="radio" name="target_week" value="current" class="form-radio text-brand" x-model="reassignTarget">
                            <span>
                                <span class="font-semibold">Sabana actual</span>
                                <span class="block text-sm text-slate-500">Mantener la orden en la semana vigente.</span>
                            </span>
                        </label>
                        <label class="flex items-center gap-2 rounded-lg border border-slate-200 p-3 cursor-pointer">
                            <input type="radio" name="target_week" value="next" class="form-radio text-brand" x-model="reassignTarget">
                            <span>
                                <span class="font-semibold">Sabana siguiente</span>
                                <span class="block text-sm text-slate-500">Mover la orden a la próxima semana.</span>
                            </span>
                        </label>
                    </div>
                </div>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <button type="button" @click="closeReassignModal()" class="inline-flex items-center justify-center rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Cancelar</button>
                    <button type="submit" class="inline-flex items-center justify-center rounded-md bg-amber-100 text-amber-700 px-4 py-2 text-sm font-semibold hover:bg-amber-200 transition">Confirmar reasignar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Contenedor de Órdenes --}}
   <div class="@if(auth()->user()->role ==="tecnico") max-h-[30vh] md:max-h-[50vh]  @else  max-h-[40vh] md:max-h-[55vh] @endif overflow-y-auto space-y-4">
        <template x-for="order in pagedRecords" :key="order.id">
            <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-300 text-xs">
                
                {{-- Encabezado de Tabla (Oculto en Móvil, visible en Desktop) --}}
                <div class="hidden md:grid grid-cols-12 bg-slate-600 text-white font-bold uppercase p-2 items-center text-center">
                    <div class="col-span-1">AR</div>
                    <div class="col-span-2 border-l border-gray-500">ODM / COD</div>
                    <div class="col-span-1 border-l border-gray-500 text-left px-2">Tipo</div>
                    <div class="col-span-3 border-l border-gray-500 text-left px-2">Acción Requerida</div>
                    <div class="col-span-2 border-l border-gray-500 text-left px-2">Instalación</div>
                    <div class="col-span-1 border-l border-gray-500 text-right px-2">Impacto</div>
                    <div class="col-span-1 border-l border-gray-500 text-right px-2">Equipo</div>
                    <div class="col-span-1 border-l border-gray-500 text-right px-2">Acciones</div>
                </div>

                {{-- Fila Principal (Estilo Tarjeta en Móvil, Fila en Desktop) --}}
                <div class="flex flex-col md:grid md:grid-cols-12 md:items-center font-bold bg-white p-4 md:p-2 gap-2 md:gap-0">
                    <div class="col-span-1 text-left md:text-center text-red-600" x-text="order.is_high_risk ? 'ALTO RIESGO' : ''"></div>
                    
                    <div class="col-span-2 md:px-2 md:border-l md:border-gray-200 md:text-center flex items-center justify-between md:justify-center gap-2">
                        <div class="text-slate-700 text-sm md:text-xs" x-text="order.odm_number"></div>
                        <div class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase"
                             :class="order.is_extraplan ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-slate-100 text-slate-600 border border-slate-200'"
                             x-text="order.is_extraplan ? 'EXTRAPLAN' : 'PLAN'">
                        </div>
                    </div>
                    
                    <hr class="md:hidden border-gray-100">
                    
                    <div class="col-span-1 md:px-2 md:border-l md:border-gray-200 text-gray-500 md:text-slate-900 text-[10px] md:text-xs">
                        <span class="md:hidden font-normal block text-gray-400">Tipo:</span> 
                        <span x-text="order.type"></span>
                    </div>
                    
                    <div class="col-span-3 md:px-2 md:border-l md:border-gray-200 uppercase text-slate-800 md:truncate">
                        <span class="md:hidden font-normal block text-gray-400 normal-case">Acción Requerida:</span>
                        <span x-text="order.accion_requerida"></span>
                    </div>

                    <div class="col-span-2 md:px-2 md:border-l md:border-gray-200 text-left md:truncate">
                        <span class="md:hidden font-normal block text-gray-400">Instalación:</span> 
                        <span x-text="order.installation?.name ?? 'N/A'"></span>
                    </div>
                    
                    <div class="col-span-1 md:text-right md:px-2 md:border-l md:border-gray-200 font-mono text-gray-700">
                        <span class="md:hidden font-normal block text-gray-400 text-xs">Impacto:</span> 
                        <span x-text="new Intl.NumberFormat().format(order.impacto) + ' Bls'"></span>
                    </div>

                    <div class="col-span-1 md:text-right md:px-2 md:border-l md:border-gray-200 text-right md:truncate">
                        <span class="md:hidden font-normal block text-gray-400 text-xs">Equipo:</span> 
                        <span x-text="order.equipment?.name ?? 'N/A'"></span>
                    </div>

                    <div class="col-span-1 md:px-2 md:border-l md:border-gray-200 text-right md:truncate">
                        <span class="md:hidden font-normal block text-gray-400 text-xs">Acciones:</span>
                        <div class="flex flex-wrap justify-end gap-1">
                            @if ($ver)
                                <a :href="getShowUrl(order.id)"
                                   class="inline-flex items-center justify-center rounded bg-slate-100 text-slate-700 px-2 py-1 text-[10px] font-semibold hover:bg-slate-200 transition">Ver</a>
                            @endif
                            <template x-if="order.tasks?.[0]?.status === 'PENDIENTE'">
                                @if ($editar)
                                    <a :href="getEditUrl(order.id)"
                                       class="inline-flex items-center justify-center rounded bg-slate-100 text-slate-700 px-2 py-1 text-[10px] font-semibold hover:bg-slate-200 transition">Editar</a>
                                @endif
                            </template>
                            @if ($reportar)
                                <template x-if="order.tasks?.[0]?.status === 'PENDIENTE' && authRole === 'tecnico' && !order.tasks?.some(task => task.status === 'NO COMPLETADO')">
                                    <a :href="getReportUrl(order.id, order.tasks?.[0]?.discipline_id)"
                                       class="inline-flex items-center justify-center rounded bg-slate-100 text-slate-700 px-2 py-1 text-[10px] font-semibold hover:bg-slate-200 transition">
                                        Reportar
                                    </a>
                                </template>
                                <template x-if="authRole === 'supervisor' && getSupervisorPendingTask(order) && !order.tasks?.some(task => task.status === 'NO COMPLETADO')">
                                    <a :href="getReportUrl(order.id, getSupervisorPendingTask(order)?.discipline_id)"
                                       class="inline-flex items-center justify-center rounded bg-slate-100 text-slate-700 px-2 py-1 text-[10px] font-semibold hover:bg-slate-200 transition">
                                        Reportar
                                    </a>
                                </template>
                                <template x-if="order.tasks?.[0]?.status !== 'PENDIENTE' && !order.tasks?.some(task => task.status === 'NO COMPLETADO')">
                                    <button type="button"
                                            @click.prevent="openReportModal(order)"
                                            class="inline-flex items-center justify-center rounded bg-slate-100 text-slate-700 px-2 py-1 text-[10px] font-semibold hover:bg-slate-200 transition">
                                        Ver reporte
                                    </button>
                                </template>
                            @endif
                            <template x-if="order.tasks?.[0]?.status === 'NO COMPLETADO' && ['admin','planificador'].includes(authRole)">
                                <button type="button"
                                        @click.prevent="openReassignModal(order)"
                                        class="inline-flex items-center justify-center rounded bg-amber-100 text-amber-700 px-2 py-1 text-[10px] font-semibold hover:bg-amber-200 transition">
                                    Reasignar
                                </button>
                            </template>
                            <template x-if="order.tasks?.[0]?.status === 'PENDIENTE'">
                                @if ($eliminar)
                                    <form :action="getDeleteUrl(order.id)" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('¿Eliminar esta orden?')"
                                                class="inline-flex items-center justify-center rounded bg-red-100 text-red-700 px-2 py-1 text-[10px] font-semibold hover:bg-red-200 transition">
                                            Eliminar
                                        </button>
                                    </form>
                                @endif
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Sub-filas (Disciplinas / Tareas Responsivas) --}}
                <template x-for="task in order.tasks" :key="task.id">
                    <div class="flex flex-col md:grid md:grid-cols-12 md:items-center text-[10px] text-gray-500 bg-slate-50 p-3 md:p-1.5 border-t border-gray-200 md:border-gray-100 italic gap-1 md:gap-0">
                        <div class="col-span-1 hidden md:block"></div>
                        
                        <div class="col-span-2 md:px-2 font-bold text-gray-700 text-left md:text-center" x-text="'A-' + order.odm_number.toString().slice(-6)"></div>
                        
                        <div class="col-span-1 md:px-2 font-semibold text-slate-900 not-italic text-xs md:text-[10px] bg-slate-200 md:bg-transparent px-2 py-0.5 md:p-0 rounded w-max md:w-auto" x-text="task.discipline?.name ?? ''"></div>
                        
                        <div class="col-span-4 md:px-2 md:border-l md:border-gray-200 my-1 md:my-0 md:truncate" x-text="order.accion_requerida"></div>
                        
                        <div class="col-span-3 md:px-2 md:border-l md:border-gray-200 flex items-center justify-between md:justify-start font-mono font-medium text-xs md:text-[10px] bg-white md:bg-transparent p-1.5 md:p-0 rounded border md:border-0 border-gray-100 gap-1">
                            <span class="md:hidden font-sans not-italic text-gray-400">Programación:</span>
                            <div>
                                <span class="text-gray-900" x-text="task.date.includes('T') ? task.date.split('T')[0].split('-').reverse().join('/') : task.date"></span> | 
                                <span class="text-slate-800" x-text="(task.time_start.includes('T') ? task.time_start.split('T')[1].slice(0,5) : task.time_start.slice(0,5)) + ' - ' + (task.time_end.includes('T') ? task.time_end.split('T')[1].slice(0,5) : task.time_end.slice(0,5))"></span>
                            </div>
                        </div>
                        
                        {{-- Acciones y Gestión --}}
                        <div class="col-span-1 md:px-2 md:border-l md:border-gray-200 flex justify-end items-center gap-2 pt-1 md:pt-0">
                            <span class="inline-flex items-center justify-center rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase not-italic"
                                  :class="task.status === 'PENDIENTE' ? 'bg-yellow-100 text-yellow-700' : task.status === 'COMPLETADO' ? 'bg-green-100 text-green-700' : task.status === 'POR REVISION' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700'"
                                  x-text="task.status ?? ''"></span>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        {{-- Modal de Reporte --}}
        <div x-show="showReportModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click="showReportModal = false">
            <div class="bg-white rounded-lg w-full max-w-2xl p-4" @click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold">Reporte de Actividad - <span x-text="reportOrder?.odm_number ?? ''"></span></h3>
                    <hr>
                    <button @click="showReportModal = false" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form x-bind:action="authRole !== 'tecnico' ? '/workorders/' + reportOrder.id + '/complete-closure' : '#'" method="POST">
                    @csrf
                    <input type="hidden" name="order_task_id" :value="reportTask?.id">
                    <div class="mb-5">
                        <label for="codigo" class="block mb-2.5 text-sm font-medium text-heading">Codigo</label>
                        <input
                            id="codigo"
                            name="codigo"
                            type="text"
                            readonly
                            x-bind:value="reportOrder ? 'A -' + reportOrder.odm_number.toString().slice(-6) : ''"
                            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body"
                        />
                    </div>
                    <div class="mb-5">
                        <label for="observation" class="block mb-2.5 text-sm font-medium text-heading">Observacion</label>
                        <textarea
                            id="observation"
                            name="observacion"
                            placeholder="Escribe tus observaciones aquí..."
                            required
                            x-model="observationInput"
                            class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body"
                            @if(auth()->user()->role === 'tecnico' || auth()->user()->role === 'planificador') readonly @endif
                        ></textarea>
                    </div>

                    <div class="mt-4">
                        <h4 class="font-semibold mb-2">Evidencias</h4>
                        <template x-if="reportTask?.evidences?.length > 0 || reportTask?.images?.length > 0">
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <template x-for="(image, index) in reportTask.evidences?.length ? reportTask.evidences : reportTask.images" :key="index">
                                    <div class="rounded overflow-hidden border border-gray-200 bg-slate-50 p-1">
                                        <img :src="image.url || image.path || image" :alt="'Evidencia ' + (index + 1)" class="w-full h-32 object-cover" />
                                        {{-- <img :src="image.url || image.path || image" :alt="'Evidencia ' + (index + 1)" class="w-full h-32 object-cover cursor-zoom-in transition-transform duration-200 hover:scale-110" @click="window.open(image.url || image.path || image, '_blank')" /> --}}

                                    </div>
                                </template>
                            </div>
                        </template>
                        <div x-show="!(reportTask?.evidences?.length > 0 || reportTask?.images?.length > 0)" class="text-sm text-gray-500">
                            Sin evidencias disponibles.
                        </div>
                    </div>

                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'supervisor')
                        <div class="mt-4 text-right" x-show="reportTask?.status === 'POR REVISION'">
                            <button type="submit" class="inline-flex items-center rounded bg-slate-600 text-white px-4 py-2 text-sm font-semibold hover:bg-slate-700 transition">Completar Cierre</button>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        {{-- No hay registros --}}
        <div x-show="filteredRecords.length === 0" class="p-10 text-center bg-white rounded-lg border border-gray-300">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            <p class="mt-2 text-sm text-gray-500 font-medium">No se encontraron asignaciones con ese criterio.</p>
        </div>
    </div>

    {{-- Paginación Responsiva --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-2 pt-2" x-show="totalPages > 1">
        <span class="text-xs text-gray-500 font-medium italic order-2 sm:order-1">
            Mostrando <span x-text="pagedRecords.length"></span> de <span x-text="filteredRecords.length"></span> resultados
        </span>
        <div class="inline-flex shadow-sm rounded-md w-full sm:w-auto order-1 sm:order-2">
            <button @click="page--" :disabled="page === 1" class="flex-1 sm:flex-none px-4 py-2 text-xs font-bold text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 disabled:opacity-40 transition">ANTERIOR</button>
            <div class="px-4 py-2 text-xs font-bold text-slate-700 bg-slate-50 border-t border-b border-gray-300 text-center min-w-[80px]" x-text="page + ' / ' + totalPages"></div>
            <button @click="page++" :disabled="page === totalPages" class="flex-1 sm:flex-none px-4 py-2 text-xs font-bold text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 disabled:opacity-40 transition">SIGUIENTE</button>
        </div>
    </div>
</div>