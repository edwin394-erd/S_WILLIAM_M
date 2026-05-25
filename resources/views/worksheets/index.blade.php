@extends('layouts.app')

@section('title', 'Administrar Sabanas')

@section('content')
<div>
    {{-- <h1 class="text-xl font-bold text-gray-800">Sabanas</h1>
    <br>
    --}}

  @php
    $columnas = [
        'week_number' => 'Semana',
        'department.name' => 'Departamento',
        'start_date' => 'Fecha de Inicio',
        'end_date' => 'Fecha de Fin',
        'work_orders_count' => 'Órdenes de Trabajo',
        'pending_tasks_count' => 'Pendientes',
        'review_tasks_count' => 'Por revisión',
        'completed_tasks_count' => 'Completadas',
        'not_completed_tasks_count' => 'No completadas',
        'enviado' => 'ESTATUS',
    ];

    $routePrefix = auth()->user()->role === 'supervisor' ? 'supervisor.worksheets' : 'admin.worksheets';
    $allowPdf = auth()->user()->role !== 'tecnico';
    $enviarTelegram = auth()->user()->role === 'admin' || auth()->user()->role === 'planificador';
    $canAdd = auth()->user()->role !== 'supervisor';
@endphp
    
    <x-gridcards
        :records="$worksheets"
        :columns="$columnas"
        :eliminar="auth()->user()->role === 'admin' || auth()->user()->role === 'planificador'"
        :agregar="$canAdd"
        :ver="true"
        :descargar_pdf="$allowPdf"
        :enviar_telegram="$enviarTelegram"
        :routePrefix="$routePrefix"
    />
</div>
@endsection
