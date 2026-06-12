@props(['order'])

<div class="border-b border-gray-300 hover:bg-gray-50 transition">
    {{-- Fila Superior: Datos Generales --}}
    <div class="flex items-center text-xs font-bold bg-white p-2">
        <div class="w-10 text-center text-red-600">
            {{ $order->is_high_risk ? 'ALTO' : '' }}
        </div>
        <div class="w-32 px-2 border-l border-gray-200">
            {{ $order->odm_number }}
        </div>
        <div class="w-28 px-2 border-l border-gray-200">
            {{ $order->type }}
        </div>
        <div class="w-28 px-2 border-l border-gray-200">
            {{ $order->tasks->first()->priority ?? 'Sin prioridad' }}
        </div>
        <div class="flex-1 px-2 border-l border-gray-200 uppercase">
            {{ $order->accion_requerida }}
        </div>
        <div class="w-32 px-2 border-l border-gray-200">
            {{ $order->installation->name ?? 'N/A' }}
        </div>
        <div class="w-28 px-2 border-l border-gray-200">
            {{ $order->equipment->name ?? 'N/A' }}
        </div>
        <div class="w-24 text-right px-2 border-l border-gray-200">
            {{ number_format($order->impacto, 0) }} Bls
        </div>
    </div>

    {{-- Fila Inferior: Detalles de Tarea (Disciplinas y Horarios) --}}
    @foreach($order->tasks as $task)
    <div class="flex items-center text-[10px] text-gray-600 bg-gray-50/50 p-1 border-t border-gray-100">
        <div class="w-10"></div> {{-- Espacio para AR --}}
        <div class="w-32 px-2 italic font-semibold">
            A-{{ substr($order->odm_number, -6) }}
        </div>
        <div class="w-28 px-2">
            {{ $task->discipline->name ?? 'Sin Disciplina' }}
        </div>
        <div class="flex-1 px-2">
            {{ $order->accion_requerida }}
        </div>
        <div class="w-auto px-2 text-right font-mono">
            Del <span class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($task->date)->format('d/m/y') }}</span> 
            {{ \Carbon\Carbon::parse($task->time_start)->format('H:i') }} 
            Al {{ \Carbon\Carbon::parse($task->time_end)->format('H:i') }}
            <br>
            <span class="text-xs text-slate-600">Prioridad: {{ $task->priority ?? 'Sin prioridad' }}</span>
        </div>
    </div>
    @endforeach
</div>