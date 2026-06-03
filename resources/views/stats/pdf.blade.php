<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Estadísticas</title>
    <style>
        @page { size: portrait; margin: 8mm; }
        body { font-family: "Arial Narrow", Arial, sans-serif; font-size: 9px; color: #333; margin: 0; padding: 0; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #eeeeee; padding: 6px 8px; vertical-align: middle; font-size: 9px; }
        .top-info-table td { border: none; padding: 0; }
        .logo-container { width: 15%; text-align: left; }
        .title-container { width: 70%; text-align: center; font-size: 14px; font-weight: bold; color: #000; }
        .generated { width: 15%; text-align: right; font-size: 9px; }
        .header-main { background-color: #004b8d !important; color: white; font-weight: bold; }
        .header-main td { border: none; height: 28px; font-size: 10px; }
        .section { margin-bottom: 12px; }
        .section-title { font-size: 9px; letter-spacing: 0.12em; text-transform: uppercase; color: #004b8d; margin: 0 0 6px; }
        .summary-grid { display: table; width: 100%; border-collapse: collapse; margin-top: 4px; }
        .summary-card { display: table-cell; width: 33%; padding: 8px; border: 1px solid #e2e8f0; background: #f8fafc; vertical-align: top; }
        .summary-label { display: block; font-size: 8px; color: #475569; margin-bottom: 4px; text-transform: uppercase; }
        .summary-value { font-size: 12px; font-weight: bold; color: #0f172a; }
        .filters-list { list-style: none; padding: 0; margin: 0; }
        .filters-list li { margin-bottom: 3px; font-size: 9px; }
        .stats-table { margin-top: 6px; }
        th { background: #f1f5f9; text-align: left; font-weight: bold; color: #0f172a; }
        .logo { height: 40px; }
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('es');
        $generatedAt = $generatedAt ?? now()->format('d/m/Y H:i');
        $selectedDepartmentName = $selectedDepartmentName ?? auth()->user()->department?->name ?? 'Todos los departamentos';
        if (empty($selectedDepartmentName) || $selectedDepartmentName === 'Sin departamento') {
            $selectedDepartmentName = auth()->user()->department?->name ?? $selectedDepartmentName;
        }
    @endphp

    <div class="page">
        <table class="top-info-table">
            <tr>
                <td class="logo-container">
                    <img src="{{ public_path('imgs/petroboscan.png') }}" class="logo">
                </td>
                <td class="title-container">ESTADÍSTICAS DE ÓRDENES</td>
                <td class="generated">Generado: {{ $generatedAt }}</td>
            </tr>
        </table>

        <table style="margin-top: 6px; margin-bottom: 10px;">
            <tr class="header-main">
                <td style="width: 33%; padding-left: 10px;">DEPARTAMENTO: {{ strtoupper($selectedDepartmentName ?? 'TODOS') }}</td>
                <td style="width: 34%; text-align: center;">REPORTE ESTADÍSTICO</td>
                <td style="width: 33%; text-align: right; padding-right: 10px;">SEMANA: {{ $selectedWeekLabel ?? 'TODAS' }}</td>
            </tr>
        </table>

        <div class="section">
            <p class="section-title">Filtros aplicados</p>
            <ul class="filters-list">
                <li><strong>Semana:</strong> {{ $selectedWeekLabel ?? 'Todas las semanas' }}</li>
                <li><strong>Departamento:</strong> {{ $selectedDepartmentName ?? 'Todos los departamentos' }}</li>
                <li><strong>Disciplina:</strong> {{ $selectedDisciplineName ?? 'Todas las disciplinas' }}</li>
                <li><strong>Desde:</strong> {{ $dateFrom ?? 'No aplica' }}</li>
                <li><strong>Hasta:</strong> {{ $dateTo ?? 'No aplica' }}</li>
            </ul>
        </div>

        <div class="section">
            <p class="section-title">Resumen de métricas</p>
            @php $totalOrders = array_sum($tasksByStatus); @endphp
            <div class="summary-grid">
                <div class="summary-card">
                    <span class="summary-label">Total de órdenes</span>
                    <span class="summary-value">{{ $totalOrders }}</span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Cumplimiento general</span>
                    <span class="summary-value">{{ $generalCompletionPercentage ?? 0 }}%</span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Órdenes completadas</span>
                    <span class="summary-value">{{ $tasksByStatus['COMPLETADO'] ?? 0 }}</span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Órdenes pendientes</span>
                    <span class="summary-value">{{ $tasksByStatus['PENDIENTE'] ?? 0 }}</span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Órdenes por revisión</span>
                    <span class="summary-value">{{ $tasksByStatus['POR REVISION'] ?? 0 }}</span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Órdenes no completadas</span>
                    <span class="summary-value">{{ $tasksByStatus['NO COMPLETADO'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <div class="section">
            <p class="section-title">Órdenes por tipo</p>
            <table>
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ordersByType as $type => $count)
                        <tr>
                            <td>{{ $type }}</td>
                            <td>{{ $count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section">
            <p class="section-title">Órdenes por Departamento</p>
            <table>
                <thead>
                    <tr>
                        <th>Departamento</th>
                        <th>Total</th>
                        <th>Cumplimiento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ordersByDepartment as $name => $count)
                        <tr>
                            <td>{{ $name }}</td>
                            <td>{{ $count }}</td>
                            <td>{{ $ordersByDepartmentCompletion[$name] ?? '0' }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section">
            <p class="section-title">Órdenes por Disciplina</p>
            <table>
                <thead>
                    <tr>
                        <th>Departamento / Disciplina</th>
                        <th>Total</th>
                        <th>Cumplimiento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departmentDisciplineReport as $departmentName => $disciplines)
                        <tr class="bg-slate-100">
                            <td colspan="3" class="font-semibold">{{ $departmentName }}</td>
                        </tr>
                        @foreach($disciplines as $discipline)
                            <tr>
                                <td class="pl-4">{{ $discipline['discipline_name'] }}</td>
                                <td>{{ $discipline['completed'] }}</td>
                                <td>{{ $discipline['completion'] }}%</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
