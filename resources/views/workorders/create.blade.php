@extends('layouts.app')
@section('title', 'Crear Asignación para Sabana - ' . ($worksheet->codigo ?? 'Semana ' . $worksheet->week_number))

@section('content')
<div class="bg-white p-6 rounded-lg w-full border border-gray-300"
     x-data="{ 
        installations: {{ $installations->toJson() }},
        impactValue: 0,
        disciplines: @js($disciplines->pluck('name','id')),
        selectedDiscipline: @js(old('discipline_id')),
        selectedDate: @js(old('date')),
        timeStart: @js(old('time_start', '07:00')),
        timeEnd: @js(old('time_end', '09:00')),
        scheduleMessage: '',
        syncImpact(selectedId) {
            const inst = this.installations.find(i => i.id == selectedId);
            this.impactValue = inst ? inst.impact : 0;
        },
        async updateSchedule() {
            if (!this.selectedDiscipline || !this.selectedDate) {
                return;
            }

            try {
                const url = '{{ route('admin.workorders.schedule-info') }}?discipline_id=' + encodeURIComponent(this.selectedDiscipline) + '&date=' + encodeURIComponent(this.selectedDate);
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });

                if (!response.ok) {
                    const error = await response.json().catch(() => ({}));
                    this.scheduleMessage = error.message || 'No se pudo obtener el horario automático.';
                    return;
                }

                const schedule = await response.json();
                this.timeStart = schedule.time_start || this.timeStart;
                this.timeEnd = schedule.time_end || this.timeEnd;
                this.scheduleMessage = schedule.message || `Actividad ${schedule.count + 1} asignada: ${this.timeStart} - ${this.timeEnd}`;
            } catch (error) {
                this.scheduleMessage = 'Error obteniendo horario automático.';
            }
        }
     }"
     x-init="updateSchedule()">
    
    {{-- <h1 class="text-2xl font-bold text-gray-800 mb-4">
        Crear Asignación para Sabana - {{ 'Semana ' . $worksheet->week_number  }}
    </h1> --}}
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
                    <br>

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
                    <br>
                      <x-select 
                        name="discipline_id"
                        label="Disciplina"
                        :options="$disciplines->pluck('name', 'id')"
                        selected="{{ old('discipline_id') }}"
                        placeholder="Seleccione una disciplina"
                        :buscable="true"
                        @change="selectedDiscipline = $event.detail; updateSchedule()"
                        required
                    />
                    <br>

                    <div class="flex">
                        <div class="w-1/2 mr-2">
                           <x-input
                                name="date"
                                label="Fecha"
                                type="date"
                                min="{{ $worksheet->start_date ?? '' }}"
                                max="{{ $worksheet->end_date ?? '' }}"
                                x-model="selectedDate"
                                @change="updateSchedule()"
                            />
                        </div>

                        <div class="w-1/2 mr-2 ">
                            <div class="flex">
                                <div class="w-1/2 mr-2">
                                    <x-input
                                        name="time_start"
                                        label="Hora Inicio"
                                        type="time"
                                        x-model="timeStart"
                                        readonly
                                    /> 
                                </div>
                                <div class="w-1/2">
                                    <x-input
                                        name="time_end"
                                        label="Hora Fin"
                                        type="time"
                                        x-model="timeEnd"
                                        readonly
                                    /> 
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 mt-1" x-text="scheduleMessage"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-confirm-cancel/>
    </form>

    
</div>

@endsection


