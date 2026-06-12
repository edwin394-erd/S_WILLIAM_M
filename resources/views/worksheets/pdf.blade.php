<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: portrait; margin: 5mm; }
        body { font-family: "Arial Narrow", Arial, sans-serif; font-size: 8.5px; margin: 0; padding: 0; color: #333; }
        
        /* Eliminamos bordes negros fuertes, usamos gris muy claro o ninguno */
        table { width: 100%; border-collapse: collapse; border: none; }
        th, td { border: 1px solid #eeeeee; padding: 3px 5px; vertical-align: middle; }

        /* Cabecera Superior Sin Bordes */
        .top-info-table td { border: none; padding: 0; }
        .logo-container { width: 15%; text-align: left; }
        .title-container { width: 70%; text-align: center; font-size: 14px; font-weight: bold; color: #000; }
        .week-container { width: 15%; text-align: right; font-size: 9px; }

        /* Cabecera Azul Principal */
        .header-main { background-color: #004b8d !important; color: white; font-weight: bold; }
        .header-main td { border: none; height: 28px; font-size: 10px; }

        /* Fila de Fecha Celeste */
        .date-divider { background-color: #cce5ff !important; font-weight: bold; color: #004b8d; border: none; height: 22px; }

        /* Estilos de Bloques de Orden */
        .order-header-row { background-color: #d3d3d3 !important; font-weight: bold; text-align: center; color: #000; border: none; }
        .order-data-row { background-color: #ffff99 !important; font-weight: bold; border: none; }
        .task-row { background-color: #ffffff !important; border-bottom: 1px solid #f0f0f0; }

        /* Lógica de Color para AR */
        .ar-yes { color: #ff0000 !important; font-weight: bold; }
        
        .text-center { text-align: center; }
        img.logo { height: 45px; }
        
        /* Quitar bordes laterales para un look más moderno/SAP */
        .no-border-side { border-left: none !important; border-right: none !important; }
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('es');
        $includeSummary = $includeSummary ?? true;
    @endphp

    <table class="top-info-table">
        <tr>
            <td class="logo-container">
                <img src="{{ public_path('imgs/petroboscan.png') }}" class="logo">
            </td>
            <td class="title-container">SABANA DE PLANIFICACION POR ESPECIALIDAD</td>
            <td class="week-container">
                SEMANA: {{ $worksheet->week_number }}<br>
                {{ \Carbon\Carbon::parse($worksheet->start_date)->translatedFormat('d/m/Y') }} AL {{ \Carbon\Carbon::parse($worksheet->end_date)->translatedFormat('d/m/Y') }}
            </td>
        </tr>
    </table>

    <table style="margin-bottom: 2px;">
        <tr class="header-main">
            <td style="width: 33%; padding-left: 10px;">DEPARTAMENTO: {{ strtoupper($worksheet->department->name) }}</td>
            <td style="width: 34%; text-align: center;">PLANIFICACION OPERATIVA</td>
            <td style="width: 33%; text-align: right; padding-right: 10px;">TOTAL ODM POR DEPARTAMENTO: {{ $worksheet->workOrders->count() }}</td>
        </tr>
    </table>

 <table>
        <colgroup>
            <col style="width: 4%;">
            <col style="width: 10%;">
            <col style="width: 8%;">
            <col style="width: 50%;">
            <col style="width: 12%;">
            <col style="width: 10%;">
            <col style="width: 6%;">
        </colgroup>
        <tbody>
            @foreach($worksheet->dates as $date => $orders)
                <tr class="date-divider">
                    <td colspan="5" style="padding-left: 10px; border: none;">
                        {{ strtoupper(\Carbon\Carbon::parse($date)->translatedFormat('l, d \d\e F \d\e Y')) }}
                    </td>
                    <td colspan="2" style="text-align: right; padding-right: 10px; border: none;">TOTAL ODM POR FECHA: {{ $orders->count() }}</td>
                </tr>

                @foreach($orders as $workOrder)
                <tr class="order-header-row">
                    <td style="width: 4%; border: none;">AR</td>
                    <td style="width: 10%; border: none;">ODM</td>
                    <td style="width: 8%; border: none;">TIPO</td>
                    <td style="width: 50%; border: none; text-align: left; padding-left: 5px;">ACCION REQUERIDA</td>
                    <td style="width: 12%; border: none; text-align: left; padding-left: 5px;">INSTALACION</td>
                    <td style="width: 10%; border: none; text-align: left; padding-left: 5px;">EQUIPO</td>
                    <td style="width: 6%; border: none;">IMPACTO</td>
                </tr>

                <tr class="order-data-row">
                    <td class="text-center {{ $workOrder->is_high_risk ? 'ar-yes' : '' }}" style="width: 4%; border: none;">
                        {{ $workOrder->is_high_risk ? 'SÍ' : 'NO' }}
                    </td>
                    <td class="text-center" style="width: 10%; border: none;">{{ $workOrder->odm_number }}</td>
                    <td class="text-center" style="width: 8%; border: none;">{{ $workOrder->type }}</td>
                    <td style="width: 50%; border: none; text-align: left; padding-left: 5px;">{{ strtoupper($workOrder->accion_requerida) }}</td>
                    <td style="width: 12%; border: none; text-align: left; padding-left: 5px;">{{ $workOrder->installation->name }}</td>
                    <td style="width: 10%; border: none; text-align: left; padding-left: 5px;">{{ $workOrder->equipment->name }}</td>
                    <td class="text-center" style="width: 6%; border: none;">{{ $workOrder->impacto }} Bls</td>
                </tr>
                    @foreach($workOrder->tasks as $task)
                    <tr class="task-row">
                        <td class="text-center" style="border-top: none;">{{ $workOrder->odm_number ? 'A-' . substr($workOrder->odm_number, -6) : '---' }}</td>
                        <td colspan="2" class="text-center" style="font-weight: bold; border-top: none;"> {{ $task->discipline->name }}</td>
                        <td colspan="4" style="border-top: none;">
                            <strong>{{ strtoupper($workOrder->accion_requerida) }}</strong>
                            <span style="display: block; margin-top: 2px; color: #333; font-size: 9px;">Prioridad: {{ $task->priority ?? 'Sin prioridad' }}</span>
                            <span style="float: right; color: #666;">
                               De {{ \Carbon\Carbon::parse($task->time_start)->format('H:i') }} a {{ \Carbon\Carbon::parse($task->time_end)->format('H:i') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                    <tr style="height: 6px;"><td colspan="7" style="border: none;"></td></tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
    <style>
    .page-break { page-break-before: always; }
    .tabla-resumen, .tabla-riesgo { 
        width: 100%; 
        border-collapse: collapse; 
        font-family: "Arial Narrow", Arial, sans-serif; 
        font-size: 8.5px; 
    }
    .tabla-resumen th, .tabla-resumen td { border: 1px solid #000; padding: 3px; text-align: center; }
    .bg-header-resumen { background-color: #f2f2f2; font-weight: bold; }
    
    /* Estilo para la sección de Totales en Azul */
    .bg-total-azul { background-color: #004b8d !important; color: white !important; font-weight: bold; border-color: #000; }
    .bg-total-azul td { border: 1px solid #000; }

/* Estilos específicos para replicar la estructura de la imagen */
 .page-break { page-break-before: always; }
    .tabla-riesgo {
        width: 100%;
        border-collapse: collapse; /* Crucial para que los bordes no se dupliquen */
        font-family: sans-serif;
    }
    .tabla-riesgo td {
        border: 1px solid #000; /* Borde base para todas las celdas */
    }
    .header-lateral-rojo {
        background-color: #ff0000;
        color: #fff;
        text-align: center;
        font-weight: bold;
        width: 100px;
    }
    .cell-total-roja {
        background-color: #ff0000;
        color: #fff;
        text-align: center;
        font-weight: bold;
        width: 10px;
    }
    .cell-dia-gris {
        background-color: #7f7f7f;
        color: #fff;
        text-align: center;
        width: 140px;
    }
    .cell-label-ait {
        background-color: #d9d9d9;
        text-align: left;
        width: 80px;
    }
    .cell-descripcion {
        background-color: #fff;
        padding-left: 5px;
    }
    .fila-separador-gris {
        background-color: #7f7f7f;
        height: 15px;
    }
    .footer-rojo-total {
        background-color: #ff0000;
        color: #fff;
        font-weight: bold;
        padding-left: 10px;
    }
</style>

@if($includeSummary)
    {{-- <div class="page-break"></div> --}}

    <div style="font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; margin-bottom: 5px;">RESUMEN SEMANAL</div>
<table class="tabla-resumen">
    <thead>
        <tr class="bg-header-resumen">
            <th colspan="2" style="border: none; background: white;"></th>
            @foreach($worksheet->dates as $date => $orders)
                <th>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</th>
            @endforeach
            <th class="bg-total-azul">Total</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td rowspan="3" style="font-weight:bold; width: 35px;">AIT</td>
            <td class="bg-header-resumen" style="text-align: left;">ODM</td>
            @foreach($worksheet->dates as $date => $orders)
                <td>{{ $orders->count() }}</td>
            @endforeach
            <td class="bg-total-azul" style="font-weight:bold;">{{ $worksheet->workOrders->count() }}</td>
        </tr>
        <tr>
            <td class="bg-header-resumen" style="text-align: left;">CUADRILLAS</td>
            @foreach($worksheet->dates as $date => $orders)
                <td>{{ $orders->flatMap->tasks->pluck('discipline_id')->unique()->count() }}</td>
            @endforeach
            <td class="bg-total-azul" style="font-weight:bold;">{{ $worksheet->workOrders->flatMap->tasks->pluck('discipline_id')->unique()->count() }}</td>
        </tr>
        <tr>
            <td class="bg-header-resumen" style="text-align: left;">HRS PROG.</td>
            @foreach($worksheet->dates as $date => $orders)
                @php 
                    $horasDia = $orders->flatMap->tasks->sum(function($task) {
                        return \Carbon\Carbon::parse($task->time_start)->diffInMinutes(\Carbon\Carbon::parse($task->time_end)) / 60;
                    });
                @endphp
                <td>{{ number_format($horasDia, 2) }}</td>
            @endforeach
            <td class="bg-total-azul" style="font-weight:bold;">
                {{ number_format($worksheet->workOrders->flatMap->tasks->sum(function($t){ 
                    return \Carbon\Carbon::parse($t->time_start)->diffInMinutes(\Carbon\Carbon::parse($t->time_end)) / 60; 
                }), 2) }}
            </td>
        </tr>
    </tbody>
</table>

    @if($worksheet->workOrders->where('is_high_risk', true)->count() > 0)

    <div style="font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; color: #ff0000; margin-top: 25px; margin-bottom: 5px;">
        RESUMEN SEMANAL - TRABAJOS ALTO RIESGO
    </div>

@php
    $highRiskDays = $worksheet->dates->filter(fn($os) => $os->where('is_high_risk', true)->count() > 0);
    $totalRows = 1; // Fila del header
    foreach($highRiskDays as $os) {
        $totalRows += ($os->where('is_high_risk', true)->count() * 2) + 1; // Tareas + Separador gris
    }
@endphp
<table class="tabla-riesgo">
    <tbody>
        @php
            $highRiskDays = $worksheet->dates->filter(fn($os) => $os->where('is_high_risk', true)->count() > 0);
            
            $rowsForLateral = 0;
            foreach($highRiskDays as $os) {
                $rowsForLateral += ($os->where('is_high_risk', true)->count() * 2) + 1;
            }

            $isFirstRowTotal = true; 
        @endphp

        <tr>
            <td colspan="4" style="background-color: #ffffff; height: 35px; border: none;"></td>
            <td class="cell-total-roja">Total</td>
        </tr>

        @foreach($highRiskDays as $date => $orders)
            @php 
                $highRisk = $orders->where('is_high_risk', true);
                $totalDay = $highRisk->count(); // Total de órdenes para este día específico
            @endphp
            
            @foreach($highRisk as $order)
                <tr>
                    @if($isFirstRowTotal)
                        <td class="header-lateral-rojo" rowspan="{{ $rowsForLateral }}">
                            TRABAJOS<br>ALTO<br>RIEZGO
                        </td>
                        @php $isFirstRowTotal = false; @endphp
                    @endif

                    @if($loop->first)
                        <td rowspan="{{ ($totalDay * 2) + 1 }}" class="cell-dia-gris">
                            {{ strtolower(\Carbon\Carbon::parse($date)->translatedFormat('l')) }}<br>
                            {{ \Carbon\Carbon::parse($date)->format('d/m/y') }}
                        </td>
                    @endif

                    <td class="cell-label-ait">AIT</td>
                    <td class="cell-descripcion">{{ $order->accion_requerida }}</td>
                    <td class="cell-total-roja">1</td>
                </tr>

                <tr>
                    <td class="cell-label-ait" colspan="2">Total</td>
                    {{-- Aquí va el total de la orden individual (normalmente 1) --}}
                    <td class="cell-total-roja">1</td>
                </tr>
            @endforeach

            {{-- Fila separadora: Aquí se muestra la suma total del día --}}
            <tr>
                <td colspan="2" class="fila-separador-gris">Total Dia</td>
                <td class="cell-total-roja">{{ $totalDay }}</td>
            </tr>
        @endforeach

        <tr>
            <td colspan="4" class="footer-rojo-total">Total</td>
            <td class="cell-total-roja" style="font-size: 12px;">
                {{ $worksheet->workOrders->where('is_high_risk', true)->count() }}
            </td>
        </tr>
    </tbody>
</table>
@endif

@endif

<!-- Imagen de prevención añadida al final de la sábana -->
<div style="text-align:center; margin-top:8px;">
    <img src="{{ public_path('imgs/sabana_prevention.png') }}" alt="Prevencion y deteccion de riesgos" style="width:100%; object-fit:contain;" />
</div>
</body>
</html>