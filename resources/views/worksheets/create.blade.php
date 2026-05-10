@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded-lg w-full max-w-md border border-gray-300">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Crear Sabana</h1>
    <form action="{{ route('admin.worksheets.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <x-input 
                :label="'Numero de Semana'" 
                :name="'week_number'" 
                :type="'text'" 
                pattern="[0-9]*"
                title="Solo se permiten números"
                :placeholder="'Numero de semana'" 
                :required="true"
                :readonly="true"
                :value="$numeroSemana"
            />
            
            <div class="flex">
                <div class="w-1/2 mr-2">
                    <x-input :label="'Fecha de Inicio'"
                        :name="'start_date'" 
                        :type="'date'" 
                        :placeholder="'Fecha de inicio'" 
                        :required="true" 
                        :readonly="true"
                        :value="$fechaInicio"/>
                </div>
                <div class="w-1/2 ml-2">
                    <x-input :label="'Fecha de Fin'"
                        :name="'end_date'" 
                        :type="'date'" 
                        :placeholder="'Fecha de fin'" 
                        :required="true" 
                        :value="$fechaFin"/>
                        
                </div>
            </div>

            <x-select 
                name="department_id" 
                label="Dapartamento"
                :options="$departments->pluck('name', 'id')" 
                :buscable="true"
                placeholder="Seleccione un departamento"
                required
            />
           
            


            
        </div>

        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Crear</button>
    </form>

    
</div>

@endsection


