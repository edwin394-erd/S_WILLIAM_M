@extends('layouts.app')

@section('title', 'Bitácora de Movimientos')

@section('content')
<div x-data="{ 
        showFilters: false, 
        resourceFilter: '{{ $selectedResource ?? '' }}', 
        actionFilter: '{{ $selectedAction ?? '' }}', 
        userFilter: '{{ $selectedUserId ?? '' }}', 
        dateFromFilter: '{{ $selectedDateFrom ?? '' }}', 
        dateToFilter: '{{ $selectedDateTo ?? '' }}' 
    }">
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        {{-- <div>
            <h1 class="text-xl font-semibold text-heading">Bitácora de Movimientos</h1>
            <p class="text-sm text-slate-500">Filtra los registros sin ocupar espacio permanente en la página.</p>
        </div> --}}
        <button type="button" @click="showFilters = true" class="inline-flex items-center justify-center rounded-base border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-gray-100">
            Filtrar resultados
        </button>
    </div>

    <div x-show="showFilters" x-cloak @keydown.escape.window="showFilters = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div @click.stop class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-heading">Filtros de auditoría</h2>
                    <p class="text-sm text-slate-500">Selecciona recurso, tipo de acción, usuario y rango de fechas.</p>
                </div>
                <button type="button" @click="showFilters = false" class="text-slate-500 hover:text-slate-800">Cerrar</button>
            </div>

            <div class="px-6 py-6">
                <form method="GET" action="{{ route('admin.audit-logs.index') }}" @submit.prevent class="grid gap-4 sm:grid-cols-2 lg:grid-cols-2">
                    <div>
                        <x-select
                            name="resource"
                            label="Recurso"
                            :options="$resourceOptions"
                            selected="{{ $selectedResource ?? '' }}"
                            x-model="resourceFilter"
                            placeholder="Todos los recursos"
                            buscable
                            nullable
                            nullable-label="Todos los recursos"
                            class="w-full"
                            @change="$dispatch('audit-log-filter-update', { name: 'resourceFilter', value: $event.detail })"
                        />
                    </div>

                    <div>
                        <x-select
                            name="action"
                            label="Tipo de acción"
                            :options="$actionOptions"
                            selected="{{ $selectedAction ?? '' }}"
                            x-model="actionFilter"
                            placeholder="Todas las acciones"
                            buscable
                            nullable
                            nullable-label="Todas las acciones"
                            class="w-full"
                            @change="$dispatch('audit-log-filter-update', { name: 'actionFilter', value: $event.detail })"
                        />
                    </div>

                    <div>
                        <x-select
                            name="user_id"
                            label="Usuario"
                            :options="$userOptions"
                            selected="{{ $selectedUserId ?? '' }}"
                            x-model="userFilter"
                            placeholder="Todos los usuarios"
                            buscable
                            nullable
                            nullable-label="Todos los usuarios"
                            class="w-full"
                            @change="$dispatch('audit-log-filter-update', { name: 'userFilter', value: $event.detail })"
                        />
                    </div>

                    <div class="grid gap-3">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-heading">Desde</label>
                            <input type="date" name="date_from" x-model="dateFromFilter" @input.debounce.250="$dispatch('audit-log-filter-update', { name: 'dateFromFilter', value: $event.target.value })" class="w-full rounded-base border border-default-medium px-3 py-2 text-sm text-heading" />
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-heading">Hasta</label>
                            <input type="date" name="date_to" x-model="dateToFilter" @input.debounce.250="$dispatch('audit-log-filter-update', { name: 'dateToFilter', value: $event.target.value })" class="w-full rounded-base border border-default-medium px-3 py-2 text-sm text-heading" />
                        </div>
                    </div>

                    <div class="sm:col-span-2 flex flex-wrap items-center gap-2 pt-2">
                        <button type="button" @click="showFilters = false; $dispatch('audit-log-filter-refresh')" class="inline-flex items-center justify-center rounded-base bg-pdvsa-red px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Aplicar</button>
                        <button type="button" @click="resourceFilter = ''; actionFilter = ''; userFilter = ''; dateFromFilter = ''; dateToFilter = ''; $dispatch('audit-log-filter-update', { name: 'resourceFilter', value: '' }); $dispatch('audit-log-filter-update', { name: 'actionFilter', value: '' }); $dispatch('audit-log-filter-update', { name: 'userFilter', value: '' }); $dispatch('audit-log-filter-update', { name: 'dateFromFilter', value: '' }); $dispatch('audit-log-filter-update', { name: 'dateToFilter', value: '' });" class="inline-flex items-center justify-center rounded-base border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-gray-100">Limpiar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @php
        $columnas = [
            'created_at' => 'Fecha / Hora',
            'user.name' => 'Usuario',
            'action' => 'Acción',
            'resource_name' => 'Recurso',
            'subject_id' => 'ID Recurso',
            'old_values_text' => 'Antes',
            'new_values_text' => 'Después',
        ];
    @endphp

    <x-dynamic-table
        :records="$auditLogs"
        :columns="$columnas"
        :buscable="true"
        :eliminar="false"
        :editar="false"
        :ver="false"
    />
</div>
@endsection
