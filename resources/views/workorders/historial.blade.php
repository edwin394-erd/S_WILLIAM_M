@extends('layouts.app')

@if(auth()->user()->role === 'admin' || auth()->user()->role === 'planificador')
    @section('title', 'Historial de Asignaciones')
@else
    @section('title', 'Historial de Asignaciones - '.($departmentName ?? ''))
    
@endif


@section('content')

 <x-work-orders-table 
    :records="$workOrders"
    :reportar="true"
    :crear="false"
    :filtroFechas="true"
    :pdf="true"
/>

  
@endsection
