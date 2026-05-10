@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded-lg w-full border border-gray-300"
     x-data="{ 
        installations: {{ $installations->toJson() }},
        impactValue: 0,
        disciplines: @js($disciplines->pluck('name','id')),
        selectedDiscipline: @js(old('discipline_id')),
        syncImpact(selectedId) {
            const inst = this.installations.find(i => i.id == selectedId);
            this.impactValue = inst ? inst.impact : 0;
        }
     }">
    
    <h1 class="text-2xl font-bold text-gray-800 mb-4">
        Crear Asignación para Sabana - {{ 'Semana ' . $worksheet->week_number  }}
    </h1>
    <form action="{{ route('admin.workorders.store') }}" method="POST">
        @csrf
        @if(isset($worksheet))
            <input type="hidden" name="worksheet_id" value="{{ $worksheet->id }}">
        @elseif(request('worksheet_id'))
            <input type="hidden" name="worksheet_id" value="{{ request('worksheet_id') }}">
        @endif
        <div class="mb-4">
            <div class="md:flex">
                <div class="w-full md:w-1/2 mr-2">
                     <x-input :label="'ODM'" 
                     :name="'odm_number'" 
                     :type="'text'" 
                     :value="$nextOdmNumber"
                     readonly
                     :placeholder="'ODM automático'" />
    

                    <x-select 
                        name="type" 
                        label="Tipo de Mantenimiento"
                        :options="['PREVENTIVO' => 'Preventivo', 'CORRECTIVO' => 'Correctivo', 'PREDICTIVO' => 'Predictivo', 'DETECTIVO' => 'Detectivo']" 
                        placeholder="Seleccione un tipo de matenimiento"
                        required
                        
                    />

                    <x-input :label="'Accion requerida'" 
                            :name="'accion_requerida'" 
                            :type="'text'" 
                            :placeholder="'Ingrese una descripción del trabajo a realizar'" 
                            :required="true" />

                    <div class="flex">
                        <div class="w-1/2 mr-2">
                            {{-- Usamos @change.stop para capturar el cambio del input oculto interno --}}
                            <x-select 
                                name="installation_id"
                                label="Instalación"
                                :options="$installations->pluck('name', 'id')"
                                placeholder="Seleccione una instalación"
                                :buscable="true"
                                @change="syncImpact($event.detail)"
                            />     
                        </div>

                        <div class="w-1/2 mr-2">
                            <x-input
                                name="impact"
                                label="Impacto (bls)"
                                x-model="impactValue"
                                readonly
                            />       
                        </div>
                    </div>
                    
                    <x-input 
                        label="Alto Riesgo"
                        :name="'high_risk'"
                        :type="'checkbox'"
                        :placeholder="'Indique si es de alto riesgo'"
                    />
                </div>
                <div class="w-full md:w-1/2 ml-2">
                    <x-select 
                        name="equipment_id"
                        label="Equipo"
                        :options="$equipment->pluck('name', 'id')"
                        placeholder="Seleccione un equipo"
                        :buscable="true"
                    /> 

                    <div class="flex">
                        <div class="w-1/2 mr-2">
                           <x-input
                                name="date"
                                label="Fecha"
                                type="date"
                                min="{{ $worksheet->start_date ?? '' }}"
                                max="{{ $worksheet->end_date ?? '' }}"
                            />
                        </div>

                            

                        <div class="w-1/2 mr-2 ">
                            <div class="flex">
                                <div class="w-1/2 mr-2">
                                    <x-input
                                        name="time_start"
                                        label="Hora Inicio"
                                        type="time"
                                    /> 
                                </div>
                                <div class="w-1/2">
                                    <x-input
                                        name="time_end"
                                        label="Hora Fin"
                                        type="time"
                                    /> 
                                </div>
                            </div>
                           
                        </div>

                    </div>
                   
                    <x-select 
                        name="discipline_id"
                        label="Disciplina"
                        :options="$disciplines->pluck('name', 'id')"
                        placeholder="Seleccione una disciplina"
                        :buscable="true"
                        x-model="selectedDiscipline"
                        required
                    />
                </div>

            </div>      
            

            
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Crear</button>
        </div>
    </form>

    
</div>

@endsection


