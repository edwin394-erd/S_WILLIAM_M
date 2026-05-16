@extends('layouts.app')

@section('title', 'pre-cierre de actividad: '. $workOrder->odm_number)

@section('content')
    <div class="rounded rounded-lg shadow bg-white p-6 md:w-1/2 w-full">

        <form action="{{ route('tecnico.reportar', $workOrder->id) }}" method="POST" enctype="multipart/form-data" x-data="evidencesDropzone()" class="space-y-6">
            @csrf
            @method('PUT')

            <x-input
                label="Codigo"
                name="codigo"
                type="text"
                required
                value="A -{{ substr($workOrder->odm_number, 6) }}"
                :readonly="true"
            />
            <x-input
                label="Observaciones"
                name="observacion"
                type="textarea"
                placeholder="Escribe aquí tus observaciones sobre la actividad realizada..."
                :required
            />

            <div class="space-y-3">
                <p class="text-sm font-semibold text-gray-700">Evidencias</p>
                <div
                    class="group relative rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-6 text-center transition hover:border-pdvsa-red hover:bg-white"
                    :class="dragActive ? 'border-pdvsa-red bg-white' : ''"
                    @dragover.prevent="dragActive = true"
                    @dragenter.prevent="dragActive = true"
                    @dragleave.prevent="dragActive = false"
                    @drop.prevent="handleDrop($event)"
                    @click="$refs.evidencias.click()"
                >
                    <input
                        x-ref="evidencias"
                        id="evidencias"
                        name="evidencias[]"
                        type="file"
                        multiple
                        class="absolute inset-0 h-full w-full opacity-0 cursor-pointer"
                        @change="addFiles($event)"
                    />
                    <div class="pointer-events-none text-center">
                        {{-- <svg class="mx-auto mb-3 h-10 w-10 text-pdvsa-red" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 8V8m0 0l-4 4m4-4l4 4"></path>
                        </svg> --}}
                        <div class="flex justify-center items-center py-2">
                            <x-svg-upload :pxls="40" class="mx-auto mb-3 text-pdvsa-red"/>
                        </div>
                        <p class="text-sm font-semibold text-gray-700">Arrastra tus archivos o haz clic aquí</p>
                        <p class="text-xs text-gray-500">Selecciona archivos PDF, JPG o PNG. Puedes adjuntar varios archivos.</p>
                    </div>
                </div>

                <template x-if="files.length">
                    <div class="rounded-lg border border-gray-200 bg-white p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800">Archivos seleccionados</h3>
                            <button type="button" class="text-xs font-semibold uppercase text-red-600 hover:text-red-800" @click="clearFiles()">Limpiar todo</button>
                        </div>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <template x-for="(file, index) in files" :key="index">
                                <li class="flex items-center justify-between gap-3 rounded border border-gray-200 px-3 py-2">
                                    <span x-text="file.name"></span>
                                    <button type="button" class="text-xs font-semibold text-red-600 hover:text-red-800" @click="removeFile(index)">Eliminar</button>
                                </li>
                            </template>
                        </ul>
                    </div>
                </template>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('tecnico.actividades', auth()->user()->discipline_id) }}" class="rounded bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Cancelar</a>
                <button type="submit" class="rounded bg-slate-500 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Enviar evidencias</button>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
<script>
    function evidencesDropzone() {
        return {
            files: [],
            dragActive: false,
            addFiles(event) {
                const newFiles = Array.from(event.target.files || []);
                newFiles.forEach(file => {
                    if (!this.files.some(existing => existing.name === file.name && existing.size === file.size)) {
                        this.files.push(file);
                    }
                });
                this.updateInputFiles();
            },
            handleDrop(event) {
                this.dragActive = false;
                const droppedFiles = Array.from(event.dataTransfer.files || []);
                droppedFiles.forEach(file => {
                    if (!this.files.some(existing => existing.name === file.name && existing.size === file.size)) {
                        this.files.push(file);
                    }
                });
                this.updateInputFiles();
            },
            removeFile(index) {
                this.files.splice(index, 1);
                this.updateInputFiles();
            },
            clearFiles() {
                this.files = [];
                this.updateInputFiles();
            },
            updateInputFiles() {
                const dataTransfer = new DataTransfer();
                this.files.forEach(file => dataTransfer.items.add(file));
                this.$refs.evidencias.files = dataTransfer.files;
            }
        }
    }
</script>
@endpush