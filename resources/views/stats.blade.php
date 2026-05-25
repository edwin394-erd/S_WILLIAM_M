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

    <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4 mb-2">
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-5 relative">
          
                <a href="{{ auth()->user()->role === 'supervisor' ? route('supervisor.workorders.historial', ['status' => 'COMPLETADO']) : route('admin.workorders.historial', ['status' => 'COMPLETADO']) }}" class="absolute top-3 right-3 inline-flex items-center justify-center rounded-md px-2 py-1 text-xs font-semibold text-green-600 bg-green-100 hover:bg-green-200">Ver mas</a>
         
            <div>
                <p class="text-xs uppercase text-gray-500 tracking-widest">Órdenes completadas</p>
                <p class="mt-4 text-4xl font-bold text-green-600">{{ $completedOrders }}</p>
                <p class="mt-2 text-sm text-gray-600">Cantidad de órdenes marcadas como completadas.</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-5 relative">
            
                <a href="{{ auth()->user()->role === 'supervisor' ? route('supervisor.workorders.historial', ['status' => 'PENDIENTE']) : route('admin.workorders.historial', ['status' => 'PENDIENTE']) }}" class="absolute top-3 right-3 inline-flex items-center justify-center rounded-md px-2 py-1 text-xs font-semibold text-yellow-600 bg-yellow-100 hover:bg-yellow-200">Ver mas</a>
        
            <div>
                <p class="text-xs uppercase text-gray-500 tracking-widest">Órdenes pendientes</p>
                <p class="mt-4 text-4xl font-bold text-yellow-600">{{ $pendingOrders }}</p>
                <p class="mt-2 text-sm text-gray-600">Cantidad de órdenes aún pendientes de ejecución.</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-5 relative">
          
                <a href="{{ auth()->user()->role === 'supervisor' ? route('supervisor.workorders.historial', ['status' => 'POR REVISION']) : route('admin.workorders.historial', ['status' => 'POR REVISION']) }}" class="absolute top-3 right-3 inline-flex items-center justify-center rounded-md px-2 py-1 text-xs font-semibold text-blue-600 bg-blue-100 hover:bg-blue-200">Ver mas</a>
         
            <div>
                <p class="text-xs uppercase text-gray-500 tracking-widest">Órdenes por revisión</p>
                <p class="mt-4 text-4xl font-bold text-blue-600">{{ $reviewOrders }}</p>
                <p class="mt-2 text-sm text-gray-600">Cantidad de órdenes que están en revisión.</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-5 relative">
        
                <a href="{{ auth()->user()->role === 'supervisor' ? route('supervisor.workorders.historial', ['status' => 'NO COMPLETADO']) : route('admin.workorders.historial', ['status' => 'NO COMPLETADO']) }}" class="absolute top-3 right-3 inline-flex items-center justify-center rounded-md px-2 py-1 text-xs font-semibold text-red-600 bg-red-100 hover:bg-red-200">Ver mas</a>
         
            <div>
                <p class="text-xs uppercase text-gray-500 tracking-widest">Órdenes no completadas</p>
                <p class="mt-4 text-4xl font-bold text-red-600">{{ $notCompletedOrders }}</p>
                <p class="mt-2 text-sm text-gray-600">Cantidad de órdenes no se completaron durante la semana establecida.</p>
            </div>
        </div>
    </div>

    {{-- CORRECCIÓN AQUÍ: Quitamos items-stretch, max-h y overflow del contenedor padre global --}}
    <div id="chart-stats" class="grid gap-2 lg:grid-cols-[1.4fr_1fr] items-stretch">
      
        {{-- Columna 1: La gráfica mantiene su tamaño controlado de forma natural --}}
        <div class="w-full h-full">
            <x-leads-chart
                value="{{ $completedCount }}"
                percentage="{{ $completedPercentage }}"
                label="Órdenes completadas"
                money-spent="{{ auth()->user()->role === 'supervisor' ? 'Departamento: '.$departmentName : 'Departamentos: '.$departmentCount }}"
                conversion="Meses: {{ count($chartCategories) }}"
                :series="$chartSeries"
                :categories="$chartCategories"
                show-labels
                show-legend
                chart-type="bar"
                :horizontal="false"
                chart-height="250"
            />
        </div>
      
        {{-- Columna 2: Aquí es donde limitamos la altura del detalle y añadimos el scroll interno --}}
        <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-4 h-full overflow-y-auto custom-scrollbar">
            
            <div class="h-full  gap-3 sm:grid-cols-2">
                <div class="rounded-3xl bg-gray-50 p-3">
                    <p class="text-xs uppercase tracking-widest text-gray-500">Tipos de órdenes (Completadas)</p>
                    <ul class="mt-3 space-y-2 text-sm text-gray-700">
                        @foreach($ordersByType as $type => $count)
                            <li class="flex justify-between gap-3">
                                <span>{{ $type }}</span>
                                <span class="font-semibold">{{ $count }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                
                <div class="rounded-3xl bg-gray-50 p-3 overflow-y-auto">
                    <p class="text-xs uppercase tracking-widest text-gray-500">
                        {{ auth()->user()->role === 'supervisor' ? 'Cantidad de órdenes por disciplina (Completadas)' : 'Cantidad de órdenes por departamento (Completadas)' }}
                    </p>
                    <ul class="mt-3 space-y-2 text-sm text-gray-700">
                        @foreach(auth()->user()->role === 'supervisor' ? $ordersByDiscipline : $ordersByDepartment as $name => $count)
                            <li class="flex justify-between gap-3">
                                <span>{{ $name }}</span>
                                <span class="font-semibold">{{ $count }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection