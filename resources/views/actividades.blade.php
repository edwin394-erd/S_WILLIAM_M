@extends('layouts.app')

@section('title', 'Actividades de la disciplina: '. $disciplina->name)

@section('content')



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