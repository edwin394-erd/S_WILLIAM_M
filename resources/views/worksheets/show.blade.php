@extends('layouts.app')
@section('title', 'Detalles de la Sábana: ' . ($worksheet->codigo ?? 'Semana ' . $worksheet->week_number) . ' - Departamento ' . ($worksheet->department->name ?? 'Sin departamento'))

@section('content')
<div class="p-0">
     <x-work-orders-table 
    :records="$worksheet->workOrders" 
    :worksheetId="$worksheet->id"
    :eliminar="auth()->user()->role === 'admin' || auth()->user()->role === 'planificador'"
    :reportar="true"
    :extraplan="$extraplan"
    routePrefix="admin.workorders"
    :crear="auth()->user()->role === 'admin' || auth()->user()->role === 'planificador'"
/>
</div>

@endsection