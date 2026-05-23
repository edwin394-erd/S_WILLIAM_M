@extends('layouts.app')

@section('title', 'Crear Sabana')

@section('content')
<div class="bg-white p-6 rounded-lg w-full max-w-md border border-gray-300">
    {{-- <h1 class="text-2xl font-bold text-gray-800 mb-4">Crear Sabana</h1> --}}
    @php
        // Derivar la siguiente semana a partir de las claves de $weekMap provistas por el controlador
        $keys = array_keys($weekMap);
        if (count($keys) > 1) {
            // Elegir la que no sea la actual
            $nextWeek = ($keys[0] == $currentWeek) ? $keys[1] : $keys[0];
        } else {
            $nextWeek = $currentWeek;
        }
        $nextWeekStart = $weekMap[$nextWeek]['start'] ?? $currentStart;
        $nextWeekEnd = $weekMap[$nextWeek]['end'] ?? $currentEnd;
    @endphp

    <form action="{{ route('admin.worksheets.store') }}" method="POST" x-data="{
            selectedWeek: '{{ $nextWeek }}',
            startDate: '{{ $nextWeekStart }}',
            endDate: '{{ $nextWeekEnd }}',
            weekMap: @js($weekMap),
            setWeek(value) {
                this.selectedWeek = value;
                this.startDate = this.weekMap[value].start;
                this.endDate = this.weekMap[value].end;
            }
        }">
        @csrf
        <div class="mb-4">
            {{-- <x-select 
                name="week_number"
                label="Semana"
                :options="$weekOptions"
                :selected="$currentWeek"
                @change="setWeek($event.detail)"
                required
            /> --}}
            <x-input label="Semana" 
                     name="week_number" 
                     type="number" 
                     placeholder="Número de semana (1-52)" 
                     required 
                     min="{{ $nextWeek }}" 
                     max="{{ $nextWeek }}"
                     x-model="selectedWeek"
                     value="{{ $nextWeek }}"
                     readonly/>

                    
            {{-- <br> --}}

            <div class="flex">
                <div class="w-1/2 mr-2">
                    <x-input label="Fecha de Inicio"
                        name="start_date" 
                        type="date" 
                        placeholder="Fecha de inicio" 
                        required 
                        readonly 
                        x-bind:value="startDate"/>
                </div>
                <div class="w-1/2 ml-2">
                    <x-input label="Fecha de Fin"
                        name="end_date" 
                        type="date" 
                        placeholder="Fecha de fin" 
                        required 
                        readonly 
                        x-bind:value="endDate"/>
                </div>
            </div>

            <x-select 
                name="department_id" 
                label="Departamento"
                :options="$departments->pluck('name', 'id')" 
                :buscable="true"
                placeholder="Seleccione un departamento"
                required
            />
           
            
        </div>

        <x-confirm-cancel backUrl="{{ route('admin.worksheets.index') }}"/>
    </form>

    
</div>

@endsection


