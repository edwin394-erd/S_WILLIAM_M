<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Reporte Diario</title>
    <style>
        @page { size: portrait; margin: 8mm; }
        body { font-family: "Arial Narrow", Arial, sans-serif; font-size: 9px; color: #333; margin: 0; padding: 0; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #eeeeee; padding: 5px 7px; vertical-align: middle; font-size: 9px; }
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
        .logo { height: 36px; }
        .title { font-size: 12px; font-weight: bold; color: #000; text-align: center; }
        .section-title { font-size: 9px; color: #004b8d; font-weight: bold; margin: 6px 0; }
        .summary { display: table; width: 100%; border-collapse: separate; border-spacing: 4px; margin-bottom: 4px; }
        .summary-row { display: table-row; }
        .summary-card { display: table-cell; width: 33.333%; padding: 5px; border: 1px solid #e2e8f0; background: #f8fafc; vertical-align: top; }
        .charts-container { display: table; width: 100%; margin-top: 5px; margin-bottom: 15px; table-layout: fixed; }
        .chart-box { display: table-cell; width: 50%; text-align: center; padding: 8px; border: 1px solid #eeeeee; background: #ffffff; vertical-align: top; }
        .chart-title { display: block; font-size: 8px; font-weight: bold; color: #004b8d; margin-bottom: 4px; }
        .chart-img { width: 100%; max-width: 240px; height: auto; display: inline-block; }
        .compact-table th, .compact-table td { padding: 4px 6px; font-size: 8.5px; }
        .order-header-row { background-color: #d3d3d3 !important; font-weight: bold; text-align: center; color: #000; border: none; }
        .order-data-row { background-color: #ffff99 !important; font-weight: bold; border: none; }
        .task-row { background-color: #ffffff !important; border-bottom: 1px solid #f0f0f0; }
        .date-divider td { background-color: #a1c8ef; color: #1f2937; font-weight: bold; border: none; }
        .text-center { text-align: center; }
        .ar-yes { color: #ff0000 !important; font-weight: bold; }
        .no-border-side { border-left: none !important; border-right: none !important; }
    </style>
</head>
<body>
    <table style="margin-bottom: 6px;">
        <tr>
            <td style="width: 20%;"><img src="{{ public_path('imgs/petroboscan.png') }}" class="logo"></td>
            <td style="width: 65%;" class="title">REPORTE DIARIO</td>
            <td style="width: 15%; text-align: right;">Generado: {{ $generatedAt ?? now()->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <p class="section-title">Resumen - Día anterior ({{ $dateFrom }})</p>
    <div class="summary">
        <div class="summary-row">
            <div class="summary-card">
                <div style="font-size:8px;color:#475569">Tareas Totales</div>
                <div style="font-weight:bold;font-size:12px">{{ array_sum($tasksByStatus ?? []) }}</div>
            </div>
            <div class="summary-card">
                <div style="font-size:8px;color:#475569">Cumplimiento</div>
                <div style="font-weight:bold;font-size:12px">{{ $generalCompletionPercentage ?? 0 }}%</div>
            </div>
            <div class="summary-card">
                <div style="font-size:8px;color:#475569">Completadas</div>
                <div style="font-weight:bold;font-size:12px">{{ $tasksByStatus['COMPLETADO'] ?? 0 }}</div>
            </div>
            <div class="summary-card">
                <div style="font-size:8px;color:#475569">En revisión</div>
                <div style="font-weight:bold;font-size:12px">{{ $tasksByStatus['POR REVISION'] ?? 0 }}</div>
            </div>
             <div class="summary-card">
                <div style="font-size:8px;color:#475569">Pendientes</div>
                <div style="font-weight:bold;font-size:12px">{{ $tasksByStatus['PENDIENTE'] ?? 0 }}</div>
            </div>
             <div class="summary-card">
                <div style="font-size:8px;color:#475569">No completadas</div>
                <div style="font-weight:bold;font-size:12px">{{ $tasksByStatus['NO COMPLETADO'] ?? 0 }}</div>
            </div>
        </div>
      
    </div>

    @if(!empty($priorityFilter) && $priorityFilter !== 'all')
        <p style="font-size:8px;color:#475569;margin-bottom:6px;">Filtro de prioridad: Prioridad alta y Actividad critica</p>
    @endif

    <table class="charts-container" style="page-break-inside: avoid;">
        <tr>
            <td class="chart-box">
                <span class="chart-title">Distribución por estatus</span>
                @if(!empty($pieBase64))
                    <img class="chart-img" src="{{ $pieBase64 }}" />
                @endif
            </td>
            <td class="chart-box">
                <span class="chart-title">Relación Plan vs Extra Plan</span>
                @if(!empty($barBase64))
                    <img class="chart-img" src="{{ $barBase64 }}" />
                @endif
            </td>
        </tr>
        <tr>
            <td class="chart-box">
                <span class="chart-title">Órdenes por tipo</span>
                @if(!empty($typeBase64))
                    <img class="chart-img" src="{{ $typeBase64 }}" />
                @endif
            </td>
            <td class="chart-box">
                <span class="chart-title">Cumplimiento por departamento</span>
                @if(!empty($deptCompBase64))
                    <img class="chart-img" src="{{ $deptCompBase64 }}" />
                @endif
            </td>
        </tr>
    </table>

    <div style="margin-bottom:10px; page-break-inside: avoid;">
        <p class="section-title">Actividades realizadas ayer ({{ $dateFrom }})</p>
        @php
            $yesterdayGroupsByDept = $yesterdayTasks->groupBy(fn($task) => $task->department?->name ?? $task->discipline?->department?->name ?? 'Sin departamento');
        @endphp
        @if($yesterdayGroupsByDept->isEmpty())
            <div style="text-align:center; font-size:9px; color:#666;">No hay actividades realizadas ayer.</div>
        @else
            <table>
                <tbody>
                    @foreach($yesterdayGroupsByDept as $departmentName => $departmentTasks)
                        <tr class="date-divider">
                            <td colspan="7" style="padding-left: 10px; border: none; font-weight: bold;">{{ strtoupper($departmentName) }}</td>
                        </tr>
                        @foreach($departmentTasks->groupBy('work_order_id') as $orderId => $tasks)
                            @php $workOrder = $tasks->first()->workOrder; @endphp
                            <tr class="order-header-row">
                                <td style="width: 4%; border: none;">AR</td>
                                <td style="width: 10%; border: none;">ODM</td>
                                <td style="width: 8%; border: none;">TIPO</td>
                                <td style="width: 50%; border: none; text-align: left; padding-left: 5px;">ACCIÓN REQUERIDA</td>
                                <td style="width: 12%; border: none; text-align: left; padding-left: 5px;">INSTALACIÓN</td>
                                <td style="width: 10%; border: none; text-align: left; padding-left: 5px;">EQUIPO</td>
                                <td style="width: 6%; border: none;">IMPACTO</td>
                            </tr>
                            <tr class="order-data-row">
                                <td class="text-center {{ $workOrder?->is_high_risk ? 'ar-yes' : '' }}" style="width: 4%; border: none;">{{ $workOrder?->is_high_risk ? 'SÍ' : 'NO' }}</td>
                                <td class="text-center" style="width: 10%; border: none;">{{ $workOrder?->odm_number ?? '-' }}</td>
                                <td class="text-center" style="width: 8%; border: none;">{{ $workOrder?->type ?? '-' }}</td>
                                <td style="width: 50%; border: none; text-align: left; padding-left: 5px;">{{ strtoupper($workOrder?->accion_requerida ?? '-') }}</td>
                                <td style="width: 12%; border: none; text-align: left; padding-left: 5px;">{{ $workOrder?->installation?->name ?? '-' }}</td>
                                <td style="width: 10%; border: none; text-align: left; padding-left: 5px;">{{ $workOrder?->equipment?->name ?? '-' }}</td>
                                <td class="text-center" style="width: 6%; border: none;">{{ $workOrder?->impacto ? $workOrder->impacto . ' Bls' : '-' }}</td>
                            </tr>
                            @foreach($tasks as $task)
                                <tr class="task-row">
                                    <td class="text-center" style="border-top: none;">{{ $workOrder?->odm_number ? 'A-' . substr($workOrder->odm_number, -6) : '---' }}</td>
                                    <td colspan="2" class="text-center" style="font-weight: bold; border-top: none;">{{ $task->discipline?->name ?? '-' }}</td>
                                    <td colspan="4" style="border-top: none;">
                                        <strong>{{ strtoupper($workOrder?->accion_requerida ?? '-') }}</strong>
                                        <span style="display: block; margin-top: 2px; color: #333; font-size: 8px;">Prioridad: {{ $task->priority ?? 'Sin prioridad' }}</span>
                                        <span style="float: right; color: #666; font-size: 8px;">
                                           De {{ $task->time_start ? \Carbon\Carbon::parse($task->time_start)->format('H:i') : '-' }} a {{ $task->time_end ? \Carbon\Carbon::parse($task->time_end)->format('H:i') : '-' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            <tr style="height: 6px;"><td colspan="7" style="border: none;"></td></tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top: 6px; font-size: 8px; font-weight: bold; text-align: right;">Total actividades ayer: {{ $yesterdayTasks->count() }}</div>
        @endif
    </div>

    <div style="margin-top:12px; page-break-inside: avoid;">
        <p class="section-title">Actividades programadas para hoy ({{ $todayDate ?? now()->toDateString() }})</p>
        @php
            $todayGroupsByDept = $todayTasks->groupBy(fn($task) => $task->department?->name ?? $task->discipline?->department?->name ?? 'Sin departamento');
        @endphp
        @if($todayGroupsByDept->isEmpty())
            <div style="text-align:center; font-size:9px; color:#666;">No hay actividades registradas para hoy.</div>
        @else
            <table>
                <tbody>
                    @foreach($todayGroupsByDept as $departmentName => $departmentTasks)
                        <tr class="date-divider">
                            <td colspan="7" style="padding-left: 10px; border: none; font-weight: bold;">{{ strtoupper($departmentName) }}</td>
                        </tr>
                        @foreach($departmentTasks->groupBy('work_order_id') as $orderId => $tasks)
                            @php $workOrder = $tasks->first()->workOrder; @endphp
                            <tr class="order-header-row">
                                <td style="width: 4%; border: none;">AR</td>
                                <td style="width: 10%; border: none;">ODM</td>
                                <td style="width: 8%; border: none;">TIPO</td>
                                <td style="width: 50%; border: none; text-align: left; padding-left: 5px;">ACCIÓN REQUERIDA</td>
                                <td style="width: 12%; border: none; text-align: left; padding-left: 5px;">INSTALACIÓN</td>
                                <td style="width: 10%; border: none; text-align: left; padding-left: 5px;">EQUIPO</td>
                                <td style="width: 6%; border: none;">IMPACTO</td>
                            </tr>
                            <tr class="order-data-row">
                                <td class="text-center {{ $workOrder?->is_high_risk ? 'ar-yes' : '' }}" style="width: 4%; border: none;">{{ $workOrder?->is_high_risk ? 'SÍ' : 'NO' }}</td>
                                <td class="text-center" style="width: 10%; border: none;">{{ $workOrder?->odm_number ?? '-' }}</td>
                                <td class="text-center" style="width: 8%; border: none;">{{ $workOrder?->type ?? '-' }}</td>
                                <td style="width: 50%; border: none; text-align: left; padding-left: 5px;">{{ strtoupper($workOrder?->accion_requerida ?? '-') }}</td>
                                <td style="width: 12%; border: none; text-align: left; padding-left: 5px;">{{ $workOrder?->installation?->name ?? '-' }}</td>
                                <td style="width: 10%; border: none; text-align: left; padding-left: 5px;">{{ $workOrder?->equipment?->name ?? '-' }}</td>
                                <td class="text-center" style="width: 6%; border: none;">{{ $workOrder?->impacto ? $workOrder->impacto . ' Bls' : '-' }}</td>
                            </tr>
                            @foreach($tasks as $task)
                                <tr class="task-row">
                                    <td class="text-center" style="border-top: none;">{{ $workOrder?->odm_number ? 'A-' . substr($workOrder->odm_number, -6) : '---' }}</td>
                                    <td colspan="2" class="text-center" style="font-weight: bold; border-top: none;">{{ $task->discipline?->name ?? '-' }}</td>
                                    <td colspan="4" style="border-top: none;">
                                        <strong>{{ strtoupper($workOrder?->accion_requerida ?? '-') }}</strong>
                                        <span style="display: block; margin-top: 2px; color: #333; font-size: 8px;">Prioridad: {{ $task->priority ?? 'Sin prioridad' }}</span>
                                        <span style="float: right; color: #666; font-size: 8px;">
                                           De {{ $task->time_start ? \Carbon\Carbon::parse($task->time_start)->format('H:i') : '-' }} a {{ $task->time_end ? \Carbon\Carbon::parse($task->time_end)->format('H:i') : '-' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            <tr style="height: 6px;"><td colspan="7" style="border: none;"></td></tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top: 6px; font-size: 8px; font-weight: bold; text-align: right;">Total actividades hoy: {{ $todayTasks->count() }}</div>
        @endif
    </div>
</body>
</html>
