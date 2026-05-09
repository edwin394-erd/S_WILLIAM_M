@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded-lg w-full border border-gray-300"
     x-data="{ 
        installations: {{ $installations->toJson() }},
        impactValue: 0,
        disciplines: @js($disciplines->pluck('name','id')),
        selectedDisciplines: @js(old('discipline_id', [])),
        syncImpact(selectedId) {
            const inst = this.installations.find(i => i.id == selectedId);
            this.impactValue = inst ? inst.impact : 0;
        },
        addDiscipline() {
            this.selectedDisciplines.push('');
        },
        removeDiscipline(index) {
            this.selectedDisciplines.splice(index, 1);
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
                   
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
                            <label class="block text-sm font-medium text-heading">Disciplinas</label>
                            <button type="button" @click="addDiscipline()" class="inline-flex items-center text-sm text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-lg transition">
                                + Agregar
                            </button>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <template x-for="(disc, index) in selectedDisciplines" :key="index">
                                <div class="flex flex-col sm:flex-row items-end gap-2 w-full sm:w-[calc(50%-0.75rem)]">
                                    <div class="flex-1">
                                        <label class="sr-only">Disciplina</label>
                                        <select name="discipline_id[]" x-model="selectedDisciplines[index]" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs">
                                            <option value="">Seleccione una disciplina</option>
                                            <template x-for="(label, value) in disciplines" :key="value">
                                                <option :value="value" x-text="label"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <button type="button" @click="removeDiscipline(index)" class="shrink-0 rounded-4xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-100 transition">
                                        X
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div x-show="selectedDisciplines.length === 0" class="text-sm text-gray-500">
                            No hay disciplinas seleccionadas.
                        </div>
                    </div>
                </div>

            </div>      
            

            
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Crear</button>
        </div>
    </form>

    
</div>

@endsection


