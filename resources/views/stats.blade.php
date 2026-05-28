@extends('layouts.app')
@if (auth()->user()->role === 'admin')
    @section('title', 'Estadísticas de Órdenes')
@else
    @section('title', 'Estadísticas del Departamento - '.($departmentName ?? ''))
@endif

@section('content')
<div class="space-y-6">

    @php
        $completedOrders = $tasksByStatus['COMPLETADO'] ?? 0;
        $pendingOrders = $tasksByStatus['PENDIENTE'] ?? 0;
        $reviewOrders = $tasksByStatus['POR REVISION'] ?? 0;
        $notCompletedOrders = $tasksByStatus['NO COMPLETADO'] ?? 0;
    @endphp

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
       

        <form id="week-filter-form"
              x-data="{
                  selectedDepartmentId: @js($selectedDepartmentId ?? ''),
                  selectedDisciplineId: @js($selectedDisciplineId ?? ''),
                  weekStart: @js($selectedWeekStart ?? ''),
                  disciplineOptions: @js($disciplineOptions ?? []),
                  allDisciplineOptions: @js($allDisciplineOptions ?? $disciplineOptions ?? []),
                  disciplinesByDepartment: @js($disciplinesByDepartment ?? []),
                  findDepartmentForDiscipline() {
                      if (!this.selectedDisciplineId) {
                          return '';
                      }

                      for (const [departmentId, disciplines] of Object.entries(this.disciplinesByDepartment)) {
                          if (Object.prototype.hasOwnProperty.call(disciplines, this.selectedDisciplineId.toString())) {
                              return departmentId;
                          }
                      }

                      return '';
                  },

                  updateDisciplineOptions() {
                      if (!this.selectedDepartmentId) {
                          this.disciplineOptions = this.allDisciplineOptions;
                      } else {
                          this.disciplineOptions = this.disciplinesByDepartment[this.selectedDepartmentId] ?? this.allDisciplineOptions;
                      }

                      if (this.selectedDisciplineId && !Object.keys(this.disciplineOptions).includes(this.selectedDisciplineId.toString())) {
                          this.selectedDisciplineId = '';
                      }
                  }
              }"
              x-effect="if (!selectedDepartmentId && selectedDisciplineId) { const departmentId = findDepartmentForDiscipline(); if (departmentId) { selectedDepartmentId = departmentId; updateDisciplineOptions(); } }"
              x-init="updateDisciplineOptions()"
              x-on:selected-discipline.window="if (!selectedDepartmentId && $event.detail) { selectedDisciplineId = $event.detail; const departmentId = findDepartmentForDiscipline(); if (departmentId) { selectedDepartmentId = departmentId; updateDisciplineOptions(); $nextTick(() => $el.closest('form').requestSubmit()); } }"
              method="GET"
              action="{{ auth()->user()->role === 'supervisor' ? route('supervisor.stats') : route('admin.stats') }}"
              class="flex flex-wrap gap-2 items-center">

            <x-select
                name="week_start"
                placeholder="Todas las semanas"
                :options="$weekOptions"
                selected="{{ $selectedWeekStart ?? '' }}"
                buscable
                nullable
                nullable-label="Todas las semanas"
                class="w-full sm:w-56"
                x-model="weekStart"
                @change="weekStart = $event.detail; $nextTick(() => $el.closest('form').requestSubmit())"
            />

            @if(auth()->user()->role === 'admin')
                <x-select
                    name="department_id"
                    placeholder="Todos los departamentos"
                    :options="$departmentOptions"
                    selected="{{ $selectedDepartmentId ?? '' }}"
                    buscable
                    nullable
                    nullable-label="Todos los departamentos"
                    class="w-full sm:w-56"
                    x-model="selectedDepartmentId"
                    @change="selectedDepartmentId = $event.detail; updateDisciplineOptions(); $nextTick(() => $el.closest('form').requestSubmit())"
                />
            @endif

            <x-select
                id="select-disciplinas-component"
                name="discipline_id"
                placeholder="Todas las disciplinas"
                :options="$disciplineOptions"
                selected="{{ $selectedDisciplineId ?? '' }}"
                buscable
                nullable
                nullable-label="Todas las disciplinas"
                class="w-full sm:w-56"
                x-model="selectedDisciplineId"
                @change="$dispatch('selected-discipline', $event.detail); $nextTick(() => $el.closest('form').requestSubmit())"
            />

            @if(!empty($selectedWeekStart) || !empty($selectedDisciplineId) || (isset($selectedDepartmentId) && !empty($selectedDepartmentId)))
                <a href="{{ auth()->user()->role === 'supervisor' ? route('supervisor.stats') : route('admin.stats') }}" class="rounded-2xl border border-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-gray-100">Limpiar</a>
            @endif
        </form>
    </div>

    {{-- BLOQUE DE TARJETAS DE MÉTRICAS --}}
    <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4 mb-2">
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-5 relative">
            <a href="{{ auth()->user()->role === 'supervisor' ? route('supervisor.workorders.historial', ['status' => 'COMPLETADO']) : route('admin.workorders.historial', ['status' => 'COMPLETADO']) }}" class="absolute top-3 right-3 inline-flex items-center justify-center rounded-md px-2 py-1 text-xs font-semibold text-green-600 bg-green-100 hover:bg-green-200">Ver mas</a>
            <div>
                <p class="text-xs uppercase text-gray-500 tracking-widest">Órdenes completadas</p>
                <p class="mt-4 text-4xl font-bold text-green-600"><span id="completed-orders-count">{{ $completedOrders }}</span></p>
                <p class="mt-2 text-sm text-gray-600">Cantidad de órdenes marcadas como completadas.</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-5 relative">
            <a href="{{ auth()->user()->role === 'supervisor' ? route('supervisor.workorders.historial', ['status' => 'PENDIENTE']) : route('admin.workorders.historial', ['status' => 'PENDIENTE']) }}" class="absolute top-3 right-3 inline-flex items-center justify-center rounded-md px-2 py-1 text-xs font-semibold text-yellow-600 bg-yellow-100 hover:bg-yellow-200">Ver mas</a>
            <div>
                <p class="text-xs uppercase text-gray-500 tracking-widest">Órdenes pendientes</p>
                <p class="mt-4 text-4xl font-bold text-yellow-600"><span id="pending-orders-count">{{ $pendingOrders }}</span></p>
                <p class="mt-2 text-sm text-gray-600">Cantidad de órdenes aún pendientes de ejecución.</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-5 relative">
            <a href="{{ auth()->user()->role === 'supervisor' ? route('supervisor.workorders.historial', ['status' => 'POR REVISION']) : route('admin.workorders.historial', ['status' => 'POR REVISION']) }}" class="absolute top-3 right-3 inline-flex items-center justify-center rounded-md px-2 py-1 text-xs font-semibold text-blue-600 bg-blue-100 hover:bg-blue-200">Ver mas</a>
            <div>
                <p class="text-xs uppercase text-gray-500 tracking-widest">Órdenes por revisión</p>
                <p class="mt-4 text-4xl font-bold text-blue-600"><span id="review-orders-count">{{ $reviewOrders }}</span></p>
                <p class="mt-2 text-sm text-gray-600">Cantidad de órdenes que están en revisión.</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-5 relative">
            <a href="{{ auth()->user()->role === 'supervisor' ? route('supervisor.workorders.historial', ['status' => 'NO COMPLETADO']) : route('admin.workorders.historial', ['status' => 'NO COMPLETADO']) }}" class="absolute top-3 right-3 inline-flex items-center justify-center rounded-md px-2 py-1 text-xs font-semibold text-red-600 bg-red-100 hover:bg-red-200">Ver mas</a>
            <div>
                <p class="text-xs uppercase text-gray-500 tracking-widest">Órdenes no completadas</p>
                <p class="mt-4 text-4xl font-bold text-red-600"><span id="not-completed-orders-count">{{ $notCompletedOrders }}</span></p>
                <p class="mt-2 text-sm text-gray-600">Cantidad de órdenes no se completaron durante la semana establecida.</p>
            </div>
        </div>
    </div>

    {{-- SECCIÓN DE GRÁFICOS Y DETALLES --}}
    <div id="chart-stats" class="grid gap-2 lg:grid-cols-[1.4fr_1fr] items-stretch">
        <div class="w-full h-full">
            <x-leads-chart
                chart-id="leads-stats-chart"
                value="{{ $completedOrders }}"
                percentage="{{ $generalCompletionPercentage ?? $completedPercentage }}"
                percentage-label="Cumplimiento general"
                label="Órdenes completadas"
                money-spent="{{ auth()->user()->role === 'supervisor' ? 'Departamento: '.$departmentName : 'Departamentos: '.$departmentCount }}"
                conversion="{{ $conversionLabel }}"
                :series="$chartSeries"
                :categories="$chartCategories"
                show-labels
                show-legend
                chart-type="bar"
                :horizontal="false"
                chart-height="250"
            />
        </div>
      
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-4 max-h-[360px] min-h-0 overflow-auto custom-scrollbar">
            <div class="space-y-3">
                <div class="rounded-3xl bg-gray-50 p-3">
                    <p class="text-xs uppercase tracking-widest text-gray-500">Tipos de órdenes (Completadas)</p>
                    <ul id="orders-by-type-list" class="mt-3 space-y-2 text-sm text-gray-700">
                        @foreach($ordersByType as $type => $count)
                            <li class="flex justify-between gap-3">
                                <span>{{ $type }}</span>
                                <span class="font-semibold">{{ $count }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                
                <div class="rounded-3xl bg-gray-50 p-3">
                    @php
                        $isAdmin = auth()->user()->role !== 'supervisor';
                        $showDisciplineBreakdown = $isAdmin && !empty($selectedDepartmentId);
                        $ordersByNameLabel = $showDisciplineBreakdown
                            ? 'Cantidad de órdenes por disciplina (Completadas)'
                            : ($isAdmin ? 'Cantidad de órdenes por departamento (Completadas)' : 'Cantidad de órdenes por disciplina (Completadas)');
                    @endphp
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xs uppercase tracking-widest text-gray-500">
                            {{ $ordersByNameLabel }}
                        </p>
                        @if($showDisciplineBreakdown)
                            <a href="{{ route('admin.stats', array_filter(['week_start' => $selectedWeekStart])) }}" class="text-xs font-semibold text-slate-700 hover:text-slate-900">Volver a departamentos</a>
                        @endif
                    </div>
                    <ul id="orders-by-name-list" class="mt-3 space-y-2 text-sm text-gray-700">
                        @if($isAdmin && empty($selectedDepartmentId))
                            @foreach($ordersByDepartmentLinks as $department)
                                <li class="group rounded-lg hover:bg-slate-100 transition-colors">
                                    <a href="{{ route('admin.stats', array_filter(['week_start' => $selectedWeekStart, 'department_id' => $department['id']])) }}" class="flex items-center justify-between gap-3 px-3 py-2 w-full text-left text-slate-700 hover:text-slate-900">
                                        <span>{{ $department['name'] }}</span>
                                        <span class="font-semibold">{{ $department['count'] }} ({{ $ordersByDepartmentCompletion[$department['name']] ?? 0 }}% cumpl.)</span>
                                    </a>
                                </li>
                            @endforeach
                        @else
                            @foreach($ordersByDiscipline as $name => $count)
                                <li class="flex justify-between gap-3">
                                    <span>{{ $name }}</span>
                                    <span class="font-semibold">{{ $count }} ({{ $completionByDiscipline[$name] ?? 0 }}% cumpl.)</span>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('week-filter-form');
        if (!form) return;

        const chartId = 'leads-stats-chart';
        const chartObject = () => window.leadsCharts && window.leadsCharts[chartId];

        const completedOrdersCount = document.getElementById('completed-orders-count');
        const pendingOrdersCount = document.getElementById('pending-orders-count');
        const reviewOrdersCount = document.getElementById('review-orders-count');
        const notCompletedOrdersCount = document.getElementById('not-completed-orders-count');
        
        // CORREGIDO: Únicas declaraciones limpias de los contenedores de listas
        const ordersByTypeList = document.getElementById('orders-by-type-list');
        const ordersByNameList = document.getElementById('orders-by-name-list');

        // === NUEVO PUENTE DE REACTIVIDAD ===
        const departmentSelect = form.querySelector('[name="department_id"]');
        const disciplineSelectNode = document.getElementById('select-disciplinas-component');

        if (departmentSelect) {
            departmentSelect.closest('[x-data]').addEventListener('change', function () {
                setTimeout(() => {
                    const padreData = Alpine.$data(form);
                    padreData.updateDisciplineOptions();
                    
                    if (disciplineSelectNode) {
                        Alpine.$data(disciplineSelectNode).options = padreData.disciplineOptions;
                    }
                }, 60); 
            });
        }

        // Inicializa el select de disciplinas con las opciones cargadas desde el servidor
        if (disciplineSelectNode) {
            const padreData = Alpine.$data(form);
            if (padreData) {
                padreData.updateDisciplineOptions();
                Alpine.$data(disciplineSelectNode).options = padreData.disciplineOptions;
            }
        }

        const buildListHtml = (items, completions) => {
            return Object.entries(items || {}).map(([name, count]) => {
                const percent = completions?.[name];
                const suffix = percent !== undefined && percent !== null ? ` (${percent}% cumpl.)` : '';
                return `<li class="flex justify-between gap-3"><span>${name}</span><span class="font-semibold">${count}${suffix}</span></li>`;
            }).join('');
        };

        const buildLinkListHtml = (items, completions) => {
            return (items || []).map((item) => {
                const percent = completions?.[item.name];
                const suffix = percent !== undefined && percent !== null ? ` (${percent}% cumpl.)` : '';
                return `<li class="group rounded-lg hover:bg-slate-100 transition-colors">
                    <a href="${item.url}" class="flex items-center justify-between gap-3 px-3 py-2 w-full text-left text-slate-700 hover:text-slate-900">
                        <span>${item.name}</span>
                        <span class="font-semibold">${item.count}${suffix}</span>
                    </a>
                </li>`;
            }).join('');
        };

        const progressBarsHtml = (items) => {
            return Object.entries(items || {}).map(([name, percent]) => {
                const width = Math.min(Math.max(Number(percent) || 0, 0), 100);
                return `<div class="space-y-2">
                    <div class="flex items-center justify-between text-sm text-slate-700">
                        <span>${name}</span>
                        <span class="font-semibold">${percent}%</span>
                    </div>
                    <div class="h-2 rounded-full bg-slate-200 overflow-hidden">
                        <div class="h-2 rounded-full bg-emerald-500" style="width: ${width}%"></div>
                    </div>
                </div>`;
            }).join('');
        };

        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const url = new URL(form.action, window.location.origin);
            const formData = new FormData(form);
            for (const [key, value] of formData.entries()) {
                if (value) {
                    url.searchParams.set(key, value);
                }
            }

            try {
                const response = await fetch(url.toString(), {
                    headers: { 'Accept': 'application/json' },
                });

                if (!response.ok) return;

                const data = await response.json();

                const chart = chartObject();
                if (chart) {
                    chart.updateOptions({ xaxis: { categories: data.chartCategories || [] } }, false, false);
                    chart.updateSeries(data.chartSeries || [], true);
                }

                if (completedOrdersCount) completedOrdersCount.textContent = data.completedOrders ?? completedOrdersCount.textContent;
                if (pendingOrdersCount) pendingOrdersCount.textContent = data.pendingOrders ?? pendingOrdersCount.textContent;
                if (reviewOrdersCount) reviewOrdersCount.textContent = data.reviewOrders ?? reviewOrdersCount.textContent;
                if (notCompletedOrdersCount) notCompletedOrdersCount.textContent = data.notCompletedOrders ?? notCompletedOrdersCount.textContent;
                if (ordersByTypeList) ordersByTypeList.innerHTML = buildListHtml(data.ordersByType || {});
                if (ordersByNameList) {
                    if (Array.isArray(data.ordersByNameLinks) && data.ordersByNameLinks.length) {
                        ordersByNameList.innerHTML = buildLinkListHtml(data.ordersByNameLinks, data.ordersByNameCompletion || {});
                    } else {
                        ordersByNameList.innerHTML = buildListHtml(data.ordersByName || {}, data.ordersByNameCompletion || {});
                    }
                }

                const completionByDepartmentNode = document.getElementById('completion-by-department');
                const completionByDisciplineNode = document.getElementById('completion-by-discipline');
                if (completionByDepartmentNode) {
                    completionByDepartmentNode.innerHTML = data.completionByDepartment ? progressBarsHtml(data.completionByDepartment) : '<p class="text-xs text-gray-500">No hay datos de cumplimiento para este filtro.</p>';
                }
                if (completionByDisciplineNode) {
                    completionByDisciplineNode.innerHTML = data.completionByDiscipline ? progressBarsHtml(data.completionByDiscipline) : '<p class="text-xs text-gray-500">No hay datos de cumplimiento para este filtro.</p>';
                }
            } catch (error) {
                console.error('Error al actualizar el gráfico:', error);
            }
        });
    });
</script>
@endpush
@endsection