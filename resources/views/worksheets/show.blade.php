@extends('layouts.app')
@section('title', 'Detalles de la Sábana: ' . ($worksheet->codigo ?? 'Semana ' . $worksheet->week_number) . ' - Departamento ' . ($worksheet->department->name ?? 'Sin departamento'))

@section('content')
<div class="p-0">
     <x-work-orders-table 
    :records="$worksheet->workOrders" 
    :worksheetId="$worksheet->id"
    :editar="true"
    :eliminar="true"
    {{-- :ver="true" --}}
    :reportar="false"
    routePrefix="admin.workorders"
/>
</div>

@endsection