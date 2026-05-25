@extends('layouts.app')

@section('title', 'Actividades de la disciplina: '. $disciplina->name)

@section('content')

    <div class="mb-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2">
        <p class="text-sm text-slate-500">Semana actual (jueves a miércoles).</p>
        <p class="text-lg font-semibold text-slate-800">{{ $weekStart->format('d/m/Y') }} — {{ $weekEnd->format('d/m/Y') }}</p>
    </div>

   <x-work-orders-table 
    :records="$workOrders"
    :reportar="true"
    :disciplineId="$disciplina->id"
    :crear="false"

    {{-- :worksheetId="$worksheet->id" --}}
    {{-- :editar="true"
    :eliminar="true"
    :ver="true"
    
    routePrefix="admin.workorders" --}}
/>
@endsection