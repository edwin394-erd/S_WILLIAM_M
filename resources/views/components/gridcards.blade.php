@props([
    'records', 
    'columns', 
    'eliminar' => false, 
    'editar' => false, 
    'ver' => false,
    'compartir_telegram' => false,
    'descargar_pdf' => false,
    'enviar_telegram' => false,
    'buscable' => true, 
    'agregar' => false,
    'routePrefix' => null, 
    'createParams' => [],
    'departmentOptions' => [],
])

@php
    $dummyId = 'DUMMY_ID';
    $showUrl = ($ver && $routePrefix) ? route($routePrefix . '.show', $dummyId) : '';
    $editUrl = ($editar && $routePrefix) ? route($routePrefix . '.edit', $dummyId) : '';
    $deleteUrl = ($eliminar && $routePrefix) ? route($routePrefix . '.destroy', $dummyId) : '';
    $pdfUrl = ($descargar_pdf && $routePrefix) ? route($routePrefix . '.pdf', $dummyId) : '';
    $telegramUrl = ($enviar_telegram && $routePrefix) ? route($routePrefix . '.send-telegram', $dummyId) : '';

    $departmentOptions = (array) $departmentOptions;
    if (empty($departmentOptions)) {
        $departmentOptions = collect($records)->pluck('department.name')->filter()->unique()->values()->all();
        $departmentOptions = $departmentOptions ? array_combine($departmentOptions, $departmentOptions) : [];
    }
    if (!array_key_exists('', $departmentOptions)) {
        $departmentOptions = ['' => 'Todos los departamentos'] + $departmentOptions;
    }

    $weekOptions = collect($records)
        ->groupBy(fn($record) => data_get($record, 'week_key') ?? data_get($record, 'week_number'))
        ->sortByDesc(fn($group, $weekKey) => data_get($group->first(), 'start_date'))
        ->map(function ($group, $weekKey) {
            $record = $group->first();
            $weekLabel = data_get($record, 'week_label') ?? ('Semana ' . data_get($record, 'week_number'));
            $start = isset($record->start_date) ? \Carbon\Carbon::parse($record->start_date)->format('d/m') : '';
            $end = isset($record->end_date) ? \Carbon\Carbon::parse($record->end_date)->format('d/m') : '';
            return [
                'value' => (string) $weekKey,
                'label' => $weekLabel . ' (' . $start . ' - ' . $end . ')',
            ];
        })
        ->values()
        ->all();
    $today = new \DateTime('now', new \DateTimeZone('America/Caracas'));
    $weekday = (int) $today->format('N');
    if ($weekday >= 4) {
        $currentStart = (new \DateTime('thursday this week', new \DateTimeZone('America/Caracas')))->format('Y-m-d');
    } else {
        $currentStart = (new \DateTime('thursday last week', new \DateTimeZone('America/Caracas')))->format('Y-m-d');
    }

    array_unshift($weekOptions, ['value' => '', 'label' => 'Todas las semanas']);
@endphp

<div class="space-y-6" 
     x-cloak
     x-data="{ 
        search: '', 
        departmentFilter: '',
        weekFilter: '',
        page: 1, 
        perPage: 9,
        records: {{ json_encode($records) }},
        departments: {{ json_encode($departmentOptions) }},
        weeks: {{ json_encode($weekOptions) }},
        currentStart: '{{ $currentStart }}',
        showUrlTemplate: '{{ $showUrl }}',
        editUrlTemplate: '{{ $editUrl }}',
        pdfUrlTemplate: '{{ $pdfUrl }}',
        deleteUrlTemplate: '{{ $deleteUrl }}',
        telegramUrlTemplate: '{{ $telegramUrl }}',
        
        get filteredRecords() {
            return this.records.filter(r => {
                const matchesDepartment = this.departmentFilter === '' || String(r.department?.name || '').toLowerCase() === this.departmentFilter.toLowerCase();
                if (!matchesDepartment) return false;

                const matchesWeek = this.weekFilter === '' || String(r.week_key ?? r.week_number) === this.weekFilter;
                if (!matchesWeek) return false;

                if (this.search === '') return true;
                const searchTerm = this.search.toLowerCase();

                return Object.values(r).some(v => {
                    if (v === null || v === undefined) return false;
                    if (typeof v === 'object') return JSON.stringify(v).toLowerCase().includes(searchTerm);
                    return String(v).toLowerCase().includes(searchTerm);
                });
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
        getPdfUrl(id) { return this.pdfUrlTemplate.replace('{{ $dummyId }}', id); },
        getTelegramUrl(id) { return this.telegramUrlTemplate.replace('{{ $dummyId }}', id); },
        getDeleteUrl(id) { return this.deleteUrlTemplate.replace('{{ $dummyId }}', id); },
        
        formatValue(value) {
            if (typeof value !== 'string') return value;
            const isoDateTime = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/;
            if (isoDateTime.test(value)) {
                const [date, time] = value.split('T');
                return `${date.split('-').reverse().join('/')} ${time.slice(0, 5)}`;
            }
            const isoDate = /^\d{4}-\d{2}-\d{2}$/;
            if (isoDate.test(value)) {
                return value.split('-').reverse().join('/');
            }
            return value;
        }
     }">

    {{-- Controles Superiores --}}
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap items-center justify-between gap-4 w-full">
            @if($buscable)
                <div class="relative flex-1 min-w-[220px] w-50 sm:w-auto">
                    <input 
                        type="text" 
                        x-model="search"
                        @input="page = 1"
                        placeholder="Buscar registros..." 
                        class="block w-full md:w-100 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500"
                    >
                </div>
            @endif

            <div class="flex flex-wrap items-center gap-2 justify-end">
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600">Ver:</label>
                    <select x-model="perPage" @change="page = 1" class="text-sm border-gray-300 rounded-lg py-1.5">
                        <option value="6">6</option>
                        <option value="9">9</option>
                        <option value="18">18</option>
                    </select>
                </div>

                @if($agregar && $routePrefix)
                    <a href="{{ route($routePrefix . '.create', $createParams) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-300 p-1 transition-colors">
                        <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Nuevo
                    </a>
                @endif
            </div>
        </div>

        <div class="gap-2 w-full flex">
            <div class=" w-1/2 min-w-[150px]">
                <x-select 
                    class="mb-0 w-full"
                    name="departmentFilter"
                    :options="$departmentOptions" 
                    selected="" 
                    @change="departmentFilter = $event.detail; page = 1" 
                    buscable="true" 
                    placeholder="Seleccione departamento..." 
                />
            </div>
            <div class="w-1/2 min-w-[150px]">
                <x-select
                    class="mb-0 w-full"
                    name="weekFilter"
                    :options="$weekOptions"
                    selected=""
                    @change="weekFilter = $event.detail; page = 1"
                    buscable="true"
                    placeholder="Filtrar por semana..."
                />
            </div>
        </div>
    </div>

 {{-- Grid de Cards --}}
<div class="overflow-y-auto max-h-[40vh] md:max-h-[50vh]">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="record in pagedRecords" :key="record.id">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-shadow flex flex-col">
                
                {{-- Header de la Card --}}
                <div class="px-5 py-2 bg-slate-50 border-b border-gray-100 rounded-t-xl flex justify-between items-center">
                    <div>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Semana</span>
                        <span class="text-lg font-bold text-slate-800" x-text="record.week_label ?? ('N° ' + (record.week_number ?? 'N/A'))"></span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Departamento</span>
                        <span class="text-sm font-semibold text-slate-700 bg-white px-2.5 py-1 rounded-md border border-gray-200 inline-block" x-text="record.department?.name ?? 'N/A'"></span>
                    </div>
                </div>

                {{-- Cuerpo de la Card --}}
                {{-- Cuerpo de la Card --}}
<div class="p-5 flex-grow space-y-3">
    @php
        // Marcador para evitar duplicar el bloque de fechas
        $datesRendered = false;
        $taskStatusFields = ['pending_tasks_count', 'review_tasks_count', 'completed_tasks_count', 'not_completed_tasks_count'];
    @endphp

    <div x-show="record.pending_tasks_count !== undefined || record.review_tasks_count !== undefined || record.completed_tasks_count !== undefined || record.not_completed_tasks_count !== undefined"
         class="grid grid-cols-2 gap-2 pb-3 mb-3 border-b border-gray-100">
        <div class="inline-flex items-center justify-between gap-2 rounded-full bg-yellow-50 text-yellow-800 px-3 py-2 text-[11px] font-semibold">
            <span>Pendientes</span>
            <span x-text="record.pending_tasks_count ?? 0"></span>
        </div>
        <div class="inline-flex items-center justify-between gap-2 rounded-full bg-sky-50 text-sky-800 px-3 py-2 text-[11px] font-semibold">
            <span>Revisión</span>
            <span x-text="record.review_tasks_count ?? 0"></span>
        </div>
        <div class="inline-flex items-center justify-between gap-2 rounded-full bg-emerald-50 text-emerald-800 px-3 py-2 text-[11px] font-semibold">
            <span>Completadas</span>
            <span x-text="record.completed_tasks_count ?? 0"></span>
        </div>
        <div class="inline-flex items-center justify-between gap-2 rounded-full bg-rose-50 text-rose-800 px-3 py-2 text-[11px] font-semibold">
            <span>No completadas</span>
            <span x-text="record.not_completed_tasks_count ?? 0"></span>
        </div>
    </div>

    @foreach($columns as $field => $label)
        @continue(in_array($field, $taskStatusFields))
        {{-- Condición para omitir los campos que ya están en el header --}}
        @if($field !== 'week_number' && $field !== 'department.name')
            
            {{-- Si detectamos una de las fechas, renderizamos ambas juntas en la misma línea --}}
            @if(($field === 'start_date' || $field === 'end_date'))
                @if(!$datesRendered)
                    <div class="grid grid-cols-2 gap-4">
                        {{-- Fecha de Inicio --}}
                        <div class="flex flex-col items-start">
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $columns['start_date'] ?? 'Inicio' }}</span>
                            <span class="text-sm font-medium text-gray-800" x-text="formatValue(record.start_date) ?? 'N/A'"></span>
                        </div>
                        {{-- Fecha de Fin --}}
                        <div class="flex flex-col items-end">
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $columns['end_date'] ?? 'Fin' }}</span>
                            <span class="text-sm font-medium text-gray-800" x-text="formatValue(record.end_date) ?? 'N/A'"></span>
                        </div>
                    </div>
                    @php $datesRendered = true; @endphp
                @endif
            @else
                {{-- Resto de las columnas normales --}}
                <div class="flex flex-col">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $label }}</span>
                    <span
                        class="text-sm font-medium text-gray-800"
                        x-bind:class="{
                            'bg-green-50 text-green-700 px-2 py-1 rounded-full text-center block mt-1': '{{ $field }}' === 'enviado' && record.{{ $field }} === 'ENVIADO',
                            'bg-yellow-50 text-yellow-700 px-2 py-1 rounded-full text-center block mt-1': '{{ $field }}' === 'enviado' && record.{{ $field }} !== 'ENVIADO'
                        }"
                        x-text="formatValue({{ str_replace('.', '?.', "record.$field") }}) ?? 'N/A'"
                    ></span>
                </div>
            @endif

        @endif
    @endforeach
</div>

                {{-- Acciones (Footer) --}}
                @if($eliminar || $editar || $ver || $compartir_telegram || $descargar_pdf || $enviar_telegram)
                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 rounded-b-xl flex items-center justify-end gap-1 text-sm">
                        @if($ver && $routePrefix)
                            <a :href="getShowUrl(record.id)" class="text-green-600 hover:text-green-800 font-semibold transition-colors hover:bg-slate-300 p-1 rounded" title="Asignaciones"><x-svg-orders/></a>
                        @endif

                        @if($editar && $routePrefix)
                            <a :href="getEditUrl(record.id)" class="text-slate-600 hover:text-slate-800 font-semibold transition-colors hover:bg-slate-300 p-1 rounded">Editar</a>
                        @endif

                        @if($eliminar && $routePrefix)
                            <form :action="getDeleteUrl(record.id)" method="POST" @submit="if(!confirm('¿Eliminar este registro?')) $event.preventDefault()" x-show="record.start_date && record.start_date > currentStart">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 font-semibold transition-colors cursor-pointer hover:bg-slate-300 p-1 rounded" title="Eliminar Sabana">
                                    <x-svg-delete/>
                                </button>
                            </form>
                        @endif

                        @if($descargar_pdf && $routePrefix)
                            <a :href="getPdfUrl(record.id)" target="_blank" class="text-purple-600 hover:text-purple-800 font-semibold transition-colors hover:bg-slate-300 p-1 rounded" title="Generar PDF"><x-svg-pdf/></a>
                        @endif

                        @if($enviar_telegram && $routePrefix)
                            <form :action="getTelegramUrl(record.id)" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="chat_id" :value="record.department?.grupo_telegram_id || ''">
                                <button type="submit" class="text-cyan-600 hover:text-cyan-800 font-semibold transition-colors cursor-pointer hover:bg-slate-300 p-1 rounded" title="Enviar a Telegram">
                                    <x-svg-telegram/>
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </template>
    </div>
</div>

    {{-- Estado Vacío --}}
    <div x-show="filteredRecords.length === 0" class="py-20 text-center bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
        <p class="text-gray-500">No se encontraron resultados para tu búsqueda.</p>
    </div>

    {{-- Paginación --}}
    <div class="flex items-center justify-between" x-show="totalPages > 1">
        <p class="text-sm text-gray-600">
            Página <span class="font-bold text-gray-900" x-text="page"></span> de <span class="font-bold text-gray-900" x-text="totalPages"></span>
        </p>
        <div class="inline-flex shadow-sm rounded-md">
            <button @click="page--" :disabled="page === 1" 
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-50 disabled:opacity-50">
                Anterior
            </button>
            <button @click="page++" :disabled="page === totalPages" 
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-50 disabled:opacity-50">
                Siguiente
            </button>
        </div>
    </div>
</div>