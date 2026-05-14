@props([
    'records', 
    'columns', 
    'eliminar' => false, 
    'editar' => false, 
    'ver' => false, 
    'buscable' => true, 
    'agregar' => false,
    'routePrefix' => null, 
    'createParams' => []
])

@php
    // Generamos las plantillas de rutas usando un ID temporal para reemplazar en Alpine
    $dummyId = 'DUMMY_ID';
    $showUrl = ($ver && $routePrefix) ? route($routePrefix . '.show', $dummyId) : '';
    $editUrl = ($editar && $routePrefix) ? route($routePrefix . '.edit', $dummyId) : '';
    $deleteUrl = ($eliminar && $routePrefix) ? route($routePrefix . '.destroy', $dummyId) : '';
@endphp

<div class="space-y-4" 
x-cloak
     x-data="{ 
        search: '', 
        page: 1, 
        perPage: 8,
        records: {{ json_encode($records) }},
        showUrlTemplate: '{{ $showUrl }}',
        editUrlTemplate: '{{ $editUrl }}',
        deleteUrlTemplate: '{{ $deleteUrl }}',
        
        get filteredRecords() {
            if (this.search === '') return this.records;
            return this.records.filter(r => 
                Object.values(r).some(v => String(v).toLowerCase().includes(this.search.toLowerCase()))
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

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        {{-- Buscador con Alpine --}}
        @if($buscable)
            <div class="relative">
                <input 
                    type="text" 
                    x-model="search"
                    @input="page = 1"
                    placeholder="Buscar en la tabla..." 
                    class="block w-full md:w-80 px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg focus:ring-slate-500 focus:border-slate-500"
                >
            </div>
        @endif

        {{-- Controles superiores: Paginación y Agregar --}}
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2" x-show="records.length > 0" style="display: none;">
                <label for="perPage" class="text-sm text-gray-600">Mostrar:</label>
                <select id="perPage" x-model="perPage" @change="page = 1" class="text-sm border-gray-300 rounded-lg py-1.5 focus:ring-slate-500 focus:border-slate-500">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
            
            @if($agregar && $routePrefix)
                <a href="{{ route($routePrefix . '.create', $createParams) }}" class="inline-flex items-center text-slate-600 bg-slate-100 hover:bg-slate-200 focus:ring-4 focus:outline-none focus:ring-slate-300 font-medium rounded-lg text-sm px-4 py-2">
                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Agregar Nuevo 
                </a>
            @endif
        </div>
    </div>

    <div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-base border border-default">
        <table class="w-full text-sm text-left text-body">
            <thead class="text-sm text-body bg-neutral-secondary-soft border-b border-default">
                <tr>
                    @foreach($columns as $field => $label)
                        <th scope="col" class="px-6 py-3 font-medium">{{ $label }}</th>
                    @endforeach
                    @if($eliminar || $editar || $ver)
                        <th scope="col" class="px-6 py-3 font-medium text-center">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                {{-- Loop dinámico con Alpine --}}
                <template x-for="record in pagedRecords" :key="record.id">
                    <tr class="bg-neutral-primary border-b border-default hover:bg-gray-50 transition-colors">
                        @foreach($columns as $field => $label)
                            <td class="px-6 py-2" x-text="formatValue(record.{{ $field }}) ?? 'N/A'"></td>
                        @endforeach
                        
                        @if($eliminar || $editar || $ver)
                            <td class="px-6 py-2 whitespace-nowrap text-center">
                                <div class="inline-flex items-center justify-center gap-2">
                                    @if($ver && $routePrefix)
                                        <a :href="getShowUrl(record.id)" class="text-green-600 hover:underline">                                                <x-svg-show/>
</a>
                                    @endif

                                    @if($editar && $routePrefix)
                                        <a :href="getEditUrl(record.id)" class="text-slate-600 hover:underline"><x-svg-edit/></a>
                                    @endif

                                    @if($eliminar && $routePrefix)
                                        <form :action="getDeleteUrl(record.id)" method="POST" class="inline-flex items-center" @submit="if(!confirm('¿Estás seguro de eliminar este registro?')) $event.preventDefault()">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline cursor-pointer">
                                                <x-svg-delete/>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                </template>
                
                {{-- Estado vacío --}}
                <tr x-show="filteredRecords.length === 0" style="display: none;">
                    <td colspan="{{ count($columns) + ($eliminar || $editar || $ver ? 1 : 0) }}" class="px-6 py-10 text-center text-gray-500">
                        No se encontraron registros.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Controles de Paginación --}}
   <div class="flex items-center justify-between px-4 py-1 bg-white border border-gray-200 rounded-b-lg" x-show="totalPages > 1" style="display: none;">
    <!-- Vista Móvil (Botones grandes) -->
    <div class="flex flex-1 justify-between sm:hidden">
        <button @click="page--" :disabled="page === 1" 
            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            Anterior
        </button>
        <button @click="page++" :disabled="page === totalPages" 
            class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            Siguiente
        </button>
    </div>

    <!-- Vista Desktop -->
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-600">
                Mostrando página <span class="font-semibold text-gray-900" x-text="page"></span> de <span class="font-semibold text-gray-900" x-text="totalPages"></span>
            </p>
        </div>
        <div>
            <nav class="relative z-0 inline-flex -space-x-px rounded-md shadow-xs" aria-label="Pagination">
                <!-- Botón Anterior -->
                <button @click="page--" :disabled="page === 1" 
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-slate-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <span class="sr-only">Anterior</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>

                <!-- Indicador de Páginas Central (Opcional, pero ayuda al diseño) -->
                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-gray-50 text-sm font-medium text-gray-700 select-none">
                    <span x-text="page"></span> / <span x-text="totalPages"></span>
                </span>

                <!-- Botón Siguiente -->
                <button @click="page++" :disabled="page === totalPages" 
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-slate-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <span class="sr-only">Siguiente</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
            </nav>
        </div>
    </div>
</div>
</div>