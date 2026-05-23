@extends('layouts.app')

@section('title', 'pre-cierre de actividad: '. $workOrder->odm_number)

@section('content')
    <div class="rounded rounded-lg shadow bg-white p-6 md:w-1/2 w-full">

        <form id="report-form" action="{{ route('tecnico.reportar', $workOrder->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="mb-5">
                <label for="codigo" class="block mb-2.5 text-sm font-medium text-heading">Codigo</label>
                <input
                    id="codigo"
                    name="codigo"
                    type="text"
                    required
                    readonly
                    value="A -{{ substr($workOrder->odm_number, 6) }}"
                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body"
                />
                @error('codigo')
                    <span class="text-xs text-red-500 mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-5">
                <label for="observacion" class="block mb-2.5 text-sm font-medium text-heading">Observaciones</label>
                <textarea
                    id="observacion"
                    name="observacion"
                    required
                    placeholder="Escribe aquí tus observaciones sobre la actividad realizada..."
                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body"
                ></textarea>
                @error('observacion')
                    <span class="text-xs text-red-500 mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="space-y-3">
                <p class="text-sm font-semibold text-gray-700">Evidencias</p>
                <div class="rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-4">
                    <input type="hidden" id="order_task_id" name="order_task_id" value="{{ optional($workOrder->tasks->first())->id }}">
                    <div id="my-dropzone" class="dropzone bg-white p-6 rounded text-center">
                        <div class="dz-message flex flex-col justify-center items-center py-2">
                            <x-svg-upload :pxls="40" class="mx-auto mb-3 text-pdvsa-red"/>
                            <p class="text-sm font-semibold text-gray-700">Arrastra tus archivos o haz clic en el cuadro</p>
                            <p class="text-xs text-gray-500">Selecciona archivos PDF, JPG o PNG. Puedes adjuntar varios archivos.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('tecnico.actividades', auth()->user()->discipline_id) }}" class="rounded bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Cancelar</a>
                <button id="submit-button" type="submit" class="rounded bg-slate-500 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Enviar evidencias</button>
            </div>
        </form>
    </div>

@endsection


@push('scripts')
<script type="module">
    Dropzone.autoDiscover = false;

    document.addEventListener("DOMContentLoaded", function() {
        if (typeof Dropzone === 'undefined') {
            console.error('Dropzone no está definido. Verifica que el bundle de Vite cargue correctamente.');
            return;
        }

        const myForm = document.getElementById('report-form');
        const submitButton = document.getElementById('submit-button');
        const taskId = document.getElementById('order_task_id').value;
        const uploadUrl = myForm.action;

        const myDropzone = new Dropzone("#my-dropzone", {
            url: uploadUrl,
            autoProcessQueue: false,
            uploadMultiple: false,
            parallelUploads: 1,
            paramName: "file",
            maxFilesize: 10,
            acceptedFiles: ".jpeg,.jpg,.png,.pdf",
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            dictRemoveFile: "Quitar",
            addRemoveLinks: true,
            init: function() {
                const dz = this;
                let submitAfterQueue = false;
                let uploadError = false;

                myForm.addEventListener('submit', function(e) {
                    if (dz.getQueuedFiles().length > 0) {
                        e.preventDefault();
                        submitAfterQueue = true;
                        uploadError = false;
                        submitButton.disabled = true;
                        submitButton.innerText = 'Subiendo evidencias...';
                        submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                        dz.processQueue();
                    }
                });

                dz.on('sending', function(file, xhr, formData) {
                    formData.append('order_task_id', taskId);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                });

                dz.on('success', function(file, response) {
                    console.log('Subido con éxito:', response);
                });

                dz.on('error', function(file, errorMessage, xhr) {
                    uploadError = true;
                    console.error('Error en subida:', errorMessage, xhr);
                    alert('No se pudo subir el archivo: ' + (errorMessage.message || errorMessage));
                });

                dz.on('queuecomplete', function() {
                    if (!submitAfterQueue) {
                        return;
                    }

                    if (uploadError) {
                        submitButton.disabled = false;
                        submitButton.innerText = 'Enviar evidencias';
                        submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                        submitAfterQueue = false;
                        return;
                    }

                    myForm.submit();
                });
            }
        });
    });
</script>
@endpush