@extends('layouts.app')

@section('title', 'Historial de Asignaciones')

@section('content')

 <x-work-orders-table 
    :records="$workOrders"
    :reportar="true"
    :crear="false"
    :filtroFechas="true"
/>

  
@endsection
