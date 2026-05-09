@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Detalles de la Sábana: {{ $worksheet->codigo ?? 'Semana ' . $worksheet->week_number }}</h1>

   <x-work-orders-table 
    :records="$worksheet->workOrders" 
    :worksheetId="$worksheet->id"
    :editar="true"
    :eliminar="true"
    :ver="true"
    routePrefix="admin.workorders"
/>
</div>

@endsection