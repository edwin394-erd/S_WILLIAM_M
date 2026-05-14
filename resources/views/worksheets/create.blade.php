@extends('layouts.app')

@section('title', 'Crear Sabana')

@section('content')
<div class="bg-white p-6 rounded-lg w-full max-w-md border border-gray-300">
    {{-- <h1 class="text-2xl font-bold text-gray-800 mb-4">Crear Sabana</h1> --}}
    <form action="{{ route('admin.worksheets.store') }}" method="POST" x-data="{
            selectedWeek: '{{ $currentWeek }}',
            startDate: '{{ $currentStart }}',
            endDate: '{{ $currentEnd }}',
            weekMap: @js($weekMap),
            setWeek(value) {
                this.selectedWeek = value;
                this.startDate = this.weekMap[value].start;
                this.endDate = this.weekMap[value].end;
            }
        }">
        @csrf
        <div class="mb-4">
            <x-select 
                name="week_number"
                label="Semana"
                :options="$weekOptions"
                :selected="$currentWeek"
                @change="setWeek($event.detail)"
                required
            />
            <br>

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

        <button type="submit" class="bg-slate-500 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded">Crear</button>
    </form>

    
</div>

@endsection


