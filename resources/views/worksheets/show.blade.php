@extends('layouts.app')
@section('title', 'Detalles de la Sábana: ' . ($worksheet->codigo ?? 'Semana ' . $worksheet->week_number) . ' - Departamento ' . ($worksheet->department->name ?? 'Sin departamento'))

@section('content')
<div class="p-0">
     <x-work-orders-table 
    :records="$worksheet->workOrders" 
    :worksheetId="$worksheet->id"
    :eliminar="true"
    :reportar="true"
    :extraplan="$extraplan"
    routePrefix="admin.workorders"
/>
</div>

@endsection