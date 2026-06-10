@extends('layouts.app')

@if(auth()->user()->role === 'admin' || auth()->user()->role === 'planificador')
    @section('title', 'Historial de Asignaciones')
@elseif(auth()->user()->role === 'supervisor')
    @section('title', 'Historial de Asignaciones - '.($departmentName ?? ''))
@elseif(auth()->user()->role === 'tecnico')
    @section('title', 'Historial de mi Disciplina')
@else
    @section('title', 'Historial de Asignaciones')
@endif


@section('content')


 <x-work-orders-table 
    :records="$workOrders"
    :reportar="true"
    :crear="false"
    :filtroFechas="true"
    :weekOptions="$weekOptions"
    :departmentOptions="$departmentOptions"
    :disciplineOptions="$disciplineOptions"
    :dateFrom="$dateFrom ?? ''"
    :dateTo="$dateTo ?? ''"
    :weekFilter="$weekFilter ?? ''"
    :pdf="true"
/>

  
@endsection
