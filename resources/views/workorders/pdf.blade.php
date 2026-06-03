<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: portrait; margin: 5mm; }
        body { font-family: "Arial Narrow", Arial, sans-serif; font-size: 8.5px; margin: 0; padding: 0; color: #333; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; border: none; }
        th, td { border: 1px solid #eeeeee; padding: 3px 5px; vertical-align: middle; }
        .top-info-table td { border: none; padding: 0; }
        .logo-container { width: 15%; text-align: left; }
        .title-container { width: 70%; text-align: center; font-size: 14px; font-weight: bold; color: #000; }
        .week-container { width: 15%; text-align: right; font-size: 9px; }
        .header-main { background-color: #004b8d !important; color: white; font-weight: bold; }
        .header-main td { border: none; height: 28px; font-size: 10px; }
        .date-divider { background-color: #cce5ff !important; font-weight: bold; color: #004b8d; border: none; height: 22px; }
        .order-header-row { background-color: #d3d3d3 !important; font-weight: bold; text-align: center; color: #000; border: none; }
        .order-data-row { background-color: #ffff99 !important; font-weight: bold; border: none; }
        .task-row { background-color: #ffffff !important; border-bottom: 1px solid #f0f0f0; }
        .ar-yes { color: #ff0000 !important; font-weight: bold; }
        .text-center { text-align: center; }
        img.logo { height: 45px; }
        .no-border-side { border-left: none !important; border-right: none !important; }
    </style>
    <title>Historial PDF</title>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('es');
        $generatedAt = now()->format('d/m/Y H:i');

        // Group orders by the date of their first task (fallback 'Sin fecha')
        $grouped = $workOrders->groupBy(function($order) {
            $d = optional($order->tasks->first())->date;
            return $d ? \Carbon\Carbon::parse($d)->format('Y-m-d') : 'Sin fecha';
        });

        // department name if all same
        $departments = $workOrders->map(fn($o) => optional($o->workSheet->department)->name)->unique()->filter();
        $departmentName = $departments->count() === 1 ? $departments->first() : 'VARIOS';
    @endphp

    <table class="top-info-table">
        <tr>
            <td class="logo-container">
                <img src="{{ public_path('imgs/petroboscan.png') }}" class="logo">
            </td>
            <td class="title-container">HISTORIAL DE ÓRDENES</td>
            <td class="week-container">
                Generado: {{ $generatedAt }}
            </td>
        </tr>
    </table>

    <table style="margin-bottom: 2px;">
        <tr class="header-main">
            <td style="width: 33%; padding-left: 10px;">DEPARTAMENTO: {{ strtoupper($departmentName) }}</td>
            <td style="width: 34%; text-align: center;">HISTORIAL FILTRADO</td>
            <td style="width: 33%; text-align: right; padding-right: 10px;">TOTAL ODM: {{ $workOrders->count() }}</td>
        </tr>
    </table>

    @php
        $statusFilter = request()->query('status');
        $dateFrom = request()->query('dateFrom');
        $dateTo = request()->query('dateTo');
        $search = request()->query('search');
        $departmentNameFilter = $departmentNameFilter ?? null;
        $disciplineNameFilter = $disciplineNameFilter ?? null;
        $parts = [];
        if ($statusFilter) $parts[] = 'Estado: ' . $statusFilter;
        if ($departmentNameFilter) $parts[] = 'Departamento: ' . strtoupper($departmentNameFilter);
        if ($disciplineNameFilter) $parts[] = 'Disciplina: ' . strtoupper($disciplineNameFilter);
        if ($dateFrom || $dateTo) {
            $df = $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : '...';
            $dt = $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : '...';
            $parts[] = 'Fechas: ' . $df . ' → ' . $dt;
        }
        if ($search) $parts[] = 'Búsqueda: "' . $search . '"';
        $filtersLabel = count($parts) ? implode(' | ', $parts) : 'Sin filtros';
    @endphp

    <table style="margin-bottom: 6px; font-size: 9px;">
        <tr>
            <td style="padding: 6px; border: none; color: #004b8d; font-weight: bold;">Filtros aplicados: {{ $filtersLabel }}</td>
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
            @foreach($grouped as $date => $orders)
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
                            <td colspan="2" class="text-center" style="font-weight: bold; border-top: none;">DISCIPLINA: {{ optional($task->discipline)->name }}</td>
                            <td colspan="4" style="border-top: none;">
                                <strong>{{ strtoupper($workOrder->accion_requerida) }}</strong>
                                <span style="float: right; color: #666;">De {{ \Carbon\Carbon::parse($task->time_start)->format('H:i') }} a {{ \Carbon\Carbon::parse($task->time_end)->format('H:i') }}</span>
                            </td>
                        </tr>
                    @endforeach

                    <tr style="height: 6px;"><td colspan="7" style="border: none;"></td></tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>