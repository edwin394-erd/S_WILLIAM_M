@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Actividades de tu disciplina</h1>

   <x-work-orders-table 
    :records="$workOrders"
    :reportar="true"
    :crear="false" 

    {{-- :worksheetId="$worksheet->id" --}}
    {{-- :editar="true"
    :eliminar="true"
    :ver="true"
    
    routePrefix="admin.workorders" --}}
/>
@endsection