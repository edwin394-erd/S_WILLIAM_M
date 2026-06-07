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
        /* Evitar que tablas y gráficos se partan entre páginas en DomPDF/Dompdf */
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
        table, thead, tbody, tr, td, th { page-break-inside: avoid; break-inside: avoid; }
        .chart-box, .chart-img, img { page-break-inside: avoid; break-inside: avoid; }
        .section { page-break-inside: avoid; break-inside: avoid; }
        .top-info-table td { border: none; padding: 0; }
        .logo-container { width: 15%; text-align: left; }
        .title-container { width: 70%; text-align: center; font-size: 14px; font-weight: bold; color: #000; }
        .generated { width: 15%; text-align: right; font-size: 9px; }
        .header-main { background-color: #004b8d !important; color: white; font-weight: bold; }
        .header-main td { border: none; height: 28px; font-size: 10px; }
        .section { margin-bottom: 12px; }
        .section-title { font-size: 9px; letter-spacing: 0.12em; text-transform: uppercase; color: #004b8d; margin: 0 0 6px; }
        .summary-grid { display: table; width: 100%; border-collapse: collapse; margin-top: 4px; }
        .summary-card { display: table-cell; width: 16.6%; padding: 6px; border: 1px solid #e2e8f0; background: #f8fafc; vertical-align: top; }
        .summary-label { display: block; font-size: 7.5px; color: #475569; margin-bottom: 4px; text-transform: uppercase; }
        .summary-value { font-size: 11px; font-weight: bold; color: #0f172a; }
        .filters-list { list-style: none; padding: 0; margin: 0; }
        .filters-list li { margin-bottom: 3px; font-size: 9px; }
        .filters-line { font-size: 9px; white-space: nowrap; }
        .filters-line span { display: inline-block; margin-right: 10px; }
        .stats-table { margin-top: 6px; }
        th { background: #f1f5f9; text-align: left; font-weight: bold; color: #0f172a; }
        .logo { height: 40px; }
        
        .charts-container { display: table; width: 100%; margin-top: 5px; margin-bottom: 15px; table-layout: fixed; }
        .chart-box { display: table-cell; width: 50%; text-align: center; padding: 8px; border: 1px solid #eeeeee; background: #ffffff; vertical-align: top; }
        .chart-img { width: 100%; max-width: 240px; height: auto; display: inline-block; }
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
        $totalOrders = array_sum($tasksByStatus);

        // Detectar filtros activos (por id o por nombre)
        $isDepartmentFilter = false;
        $isDisciplineFilter = false;
        if (!empty($selectedDepartmentId) || (!empty($selectedDepartmentName) && !in_array(strtolower(trim($selectedDepartmentName)), ['todos los departamentos', 'todos', '']))) {
            $isDepartmentFilter = true;
        }
        if (!empty($selectedDisciplineId) || (!empty($selectedDisciplineName) && !in_array(strtolower(trim($selectedDisciplineName)), ['todas las disciplinas', 'todas', '']))) {
            $isDisciplineFilter = true;
        }

        // CONFIGURACIÓN: Estatus de Ejecución
        $pieConfig = [
            'type' => 'pie',
            'data' => [
                'labels' => ['Completado', 'Pendiente', 'Por Revision', 'No Completado'],
                'datasets' => [[
                    'data' => [
                        intval($tasksByStatus['COMPLETADO'] ?? 0),
                        intval($tasksByStatus['PENDIENTE'] ?? 0),
                        intval($tasksByStatus['POR REVISION'] ?? 0),
                        intval($tasksByStatus['NO COMPLETADO'] ?? 0)
                    ],
                    'backgroundColor' => ['#00bfa5', '#ff9100', '#2979ff', '#ff1744']
                ]]
            ],
            'options' => [
                'legend' => ['position' => 'right', 'labels' => ['fontSize' => 9]],
                'plugins' => [
                    'datalabels' => ['color' => '#fff', 'font' => ['weight' => 'bold', 'size' => 9]]
                ]
            ]
        ];

        // CONFIGURACIÓN: Plan vs Extra Plan
        $barConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => ['Plan', 'Extra Plan'],
                'datasets' => [[
                    'data' => [
                        intval($planVsExtra['plan'] ?? 0),
                        intval($planVsExtra['extra'] ?? 0)
                    ],
                    'backgroundColor' => ['#004b8d', '#00bfa5']
                ]]
            ],
            'options' => [
                'legend' => ['display' => false],
                'scales' => ['yAxes' => [['ticks' => ['beginAtZero' => true, 'fontSize' => 8]]], 'xAxes' => [['ticks' => ['fontSize' => 8]]]],
                'plugins' => [
                    'datalabels' => [
                        'color' => '#ffffff',
                        'font' => ['weight' => 'bold', 'size' => 10],
                        'anchor' => 'end',
                        'align' => 'start'
                    ]
                ]
            ]
        ];

        // CONFIGURACIÓN: Órdenes por Tipo (Dona)
        $typeLabels = array_keys($ordersByType ?? []);
        $typeValues = array_map('intval', array_values($ordersByType ?? []));
        $typeConfig = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $typeLabels,
                'datasets' => [[
                    'data' => $typeValues,
                    'backgroundColor' => ['#5c6bc0', '#26a69a', '#ffca28', '#ef5350', '#8d6e63', '#78909c']
                ]]
            ],
            'options' => [
                'legend' => ['position' => 'right', 'labels' => ['fontSize' => 8]],
                'plugins' => [
                    'datalabels' => ['color' => '#fff', 'font' => ['weight' => 'bold', 'size' => 8]]
                ]
            ]
        ];

        // CONFIGURACIÓN: Tipos de Mantenimiento (Barras Horizontales)
        $maintLabels = [];
        $maintValues = [];
        foreach($maintenanceTypeReport ?? [] as $maint) {
            $maintLabels[] = $maint['label'];
            $maintValues[] = intval($maint['total']);
        }
        $maintConfig = [
            'type' => 'horizontalBar',
            'data' => [
                'labels' => $maintLabels,
                'datasets' => [[
                    'data' => $maintValues,
                    'backgroundColor' => '#004b8d'
                ]]
            ],
            'options' => [
                'legend' => ['display' => false],
                'scales' => ['xAxes' => [['ticks' => ['beginAtZero' => true, 'fontSize' => 8]]], 'yAxes' => [['ticks' => ['fontSize' => 8]]]]
            ]
        ];

        // NUEVA CONFIGURACIÓN: Cumplimiento General (Torta)
        $generalCompValue = floatval($generalCompletionPercentage ?? 0);
        $generalCompConfig = [
            'type' => 'pie',
            'data' => [
                'labels' => ['Cumplido', 'Restante'],
                'datasets' => [[
                    'data' => [$generalCompValue, max(0, 100 - $generalCompValue)],
                    'backgroundColor' => ['#00bfa5', '#ff1744']
                ]]
            ],
            'options' => [
                'legend' => ['position' => 'right', 'labels' => ['fontSize' => 8]],
                'plugins' => [
                    'datalabels' => ['color' => '#333', 'font' => ['weight' => 'bold', 'size' => 8]]
                ]
            ]
        ];

        // NUEVA CONFIGURACIÓN: Cumplimiento por Departamento (Torta)
        $deptLabels = [];
        $deptValues = [];
        foreach($ordersByDepartmentStatus ?? [] as $name => $stats) {
            $deptLabels[] = $name;
            $deptValues[] = floatval($stats['completion'] ?? 0);
        }
        $deptCompConfig = [
            'type' => 'pie',
            'data' => [
                'labels' => $deptLabels,
                'datasets' => [[
                    'data' => $deptValues,
                    'backgroundColor' => ['#004b8d', '#5c6bc0', '#26a69a', '#ffca28', '#ef5350', '#78909c']
                ]]
            ],
            'options' => [
                'legend' => ['position' => 'right', 'labels' => ['fontSize' => 8]],
                'plugins' => [
                    'datalabels' => ['color' => '#fff', 'font' => ['weight' => 'bold', 'size' => 8]]
                ]
            ]
        ];

        // NUEVA CONFIGURACIÓN: Gráficos de torta individuales por Departamento
        $deptIndividualCharts = [];
        foreach($ordersByDepartmentStatus ?? [] as $name => $stats) {
            $compValue = floatval($stats['completion'] ?? 0);
            
            $individualConfig = [
                'type' => 'pie',
                'data' => [
                    'labels' => ['Cumplido', 'Restante'],
                    'datasets' => [[
                                'data' => [$compValue, max(0, 100 - $compValue)],
                                'backgroundColor' => ['#26a69a', '#ff1744']
                            ]]
                ],
                'options' => [
                    'legend' => ['position' => 'bottom', 'labels' => ['fontSize' => 7]],
                    'plugins' => [
                        'datalabels' => ['color' => '#333', 'font' => ['weight' => 'bold', 'size' => 8]]
                    ]
                ]
            ];

            $url = "https://quickchart.io/chart?width=140&height=140&c=" . urlencode(json_encode($individualConfig));
            $data = @file_get_contents($url, false, stream_context_create($arrContextOptions));
            
            $deptIndividualCharts[$name] = $data ? 'data:image/png;base64,' . base64_encode($data) : '';
        }

        $arrContextOptions = ["ssl" => ["verify_peer" => false, "verify_peer_name" => false]];

        // Peticiones y conversión a Base64
        $pieUrl = "https://quickchart.io/chart?width=250&height=140&c=" . urlencode(json_encode($pieConfig));
        $pieData = @file_get_contents($pieUrl, false, stream_context_create($arrContextOptions));
        $pieBase64 = $pieData ? 'data:image/png;base64,' . base64_encode($pieData) : '';

        $barUrl = "https://quickchart.io/chart?width=250&height=140&c=" . urlencode(json_encode($barConfig));
        $barData = @file_get_contents($barUrl, false, stream_context_create($arrContextOptions));
        $barBase64 = $barData ? 'data:image/png;base64,' . base64_encode($barData) : '';

        $typeUrl = "https://quickchart.io/chart?width=250&height=140&c=" . urlencode(json_encode($typeConfig));
        $typeData = @file_get_contents($typeUrl, false, stream_context_create($arrContextOptions));
        $typeBase64 = $typeData ? 'data:image/png;base64,' . base64_encode($typeData) : '';

        $maintUrl = "https://quickchart.io/chart?width=250&height=140&c=" . urlencode(json_encode($maintConfig));
        $maintData = @file_get_contents($maintUrl, false, stream_context_create($arrContextOptions));
        $maintBase64 = $maintData ? 'data:image/png;base64,' . base64_encode($maintData) : '';

        $genCompUrl = "https://quickchart.io/chart?width=250&height=140&c=" . urlencode(json_encode($generalCompConfig));
        $genCompData = @file_get_contents($genCompUrl, false, stream_context_create($arrContextOptions));
        $genCompBase64 = $genCompData ? 'data:image/png;base64,' . base64_encode($genCompData) : '';

        $deptCompUrl = "https://quickchart.io/chart?width=250&height=140&c=" . urlencode(json_encode($deptCompConfig));
        $deptCompData = @file_get_contents($deptCompUrl, false, stream_context_create($arrContextOptions));
        $deptCompBase64 = $deptCompData ? 'data:image/png;base64,' . base64_encode($deptCompData) : '';

        // Porcentajes para Órdenes por Tipo
        $ordersByTypeTotal = array_sum($ordersByType ?? []);
        $ordersByTypePercent = [];
        if ($ordersByTypeTotal > 0) {
            foreach ($ordersByType as $key => $val) {
                $ordersByTypePercent[$key] = round((intval($val) / $ordersByTypeTotal) * 100, 1);
            }
        } else {
            foreach ($ordersByType as $key => $val) {
                $ordersByTypePercent[$key] = 0;
            }
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
            <p class="filters-line">
                <span><strong>Semana:</strong> {{ $selectedWeekLabel ?? 'Todas las semanas' }}</span>
                <span><strong>Departamento:</strong> {{ $selectedDepartmentName ?? 'Todos los departamentos' }}</span>
                <span><strong>Disciplina:</strong> {{ $selectedDisciplineName ?? 'Todas las disciplinas' }}</span>
                <span><strong>Desde:</strong> {{ $dateFrom ?? 'No aplica' }}</span>
                <span><strong>Hasta:</strong> {{ $dateTo ?? 'No aplica' }}</span>
            </p>
        </div>

        <div class="section">
            <p class="section-title">Resumen de métricas</p>
            <div class="summary-grid">
                <div class="summary-card">
                    <span class="summary-label">Total órdenes</span>
                    <span class="summary-value">{{ $totalOrders }}</span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Cumplimiento</span>
                    <span class="summary-value">{{ $generalCompletionPercentage ?? 0 }}%</span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Completadas</span>
                    <span class="summary-value">{{ $tasksByStatus['COMPLETADO'] ?? 0 }}</span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Pendientes</span>
                    <span class="summary-value">{{ $tasksByStatus['PENDIENTE'] ?? 0 }}</span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">Por revisión</span>
                    <span class="summary-value">{{ $tasksByStatus['POR REVISION'] ?? 0 }}</span>
                </div>
                <div class="summary-card">
                    <span class="summary-label">No completadas</span>
                    <span class="summary-value">{{ $tasksByStatus['NO COMPLETADO'] ?? 0 }}</span>
                </div>
            </div>
        </div>

      
        <div class="section">
            <div class="charts-container">
                <div class="chart-box" style="padding-right: 5px;">
                    <span style="font-weight: bold; display: block; margin-bottom: 8px; font-size: 8.5px; color: #004b8d;">Distribución por Estatus de Ejecución</span>
                    @if($pieBase64)
                        <img src="{{ $pieBase64 }}" class="chart-img">
                    @endif
                </div>
              
                @unless ($isDepartmentFilter || $isDisciplineFilter)
                <div class="chart-box" style="padding-right: 5px;">
                    <span style="font-weight: bold; display: block; margin-bottom: 8px; font-size: 8.5px; color: #004b8d;">Porcentaje de Cumplimiento General</span>
                    @if($genCompBase64)
                        <img src="{{ $genCompBase64 }}" class="chart-img">
                    @endif
                </div>
                    
                @endunless
                
               
            </div>
        </div>
      

       
        
         
         

        <div class="section"> 
            <p class="section-title">Órdenes por tipo de mantenimiento</p>
            <table>
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Total</th>
                        <th>% del total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ordersByType as $type => $count)
                        <tr>
                            <td>{{ $type }}</td>
                            <td>{{ $count }}</td>
                            <td>{{ $ordersByTypePercent[$type] ?? 0 }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
         <div class="section">
            {{-- <p class="section-title">Visualización Gráfica - Bloque 3</p> --}}
            <div class="charts-container">
                 <div class="chart-box" style="padding-right: 5px;">
                    <span style="font-weight: bold; display: block; margin-bottom: 8px; font-size: 8.5px; color: #004b8d;">Órdenes por Tipo</span>
                    @if($typeBase64)
                        <img src="{{ $typeBase64 }}" class="chart-img">
                    @endif
                </div>
               
            </div>
        </div>


        <div class="section">
            <p class="section-title">Plan vs Extra Plan</p>
            <table>
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Total</th>
                        <th>%</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Plan</td>
                        <td>{{ $planVsExtra['plan'] }}</td>
                        <td>{{ $planVsExtra['plan_percent'] }}%</td>
                    </tr>
                    <tr>
                        <td>Extra Plan</td>
                        <td>{{ $planVsExtra['extra'] }}</td>
                        <td>{{ $planVsExtra['extra_percent'] }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="section">
            {{-- <p class="section-title">Visualización Gráfica - Bloque 3</p> --}}
            <div class="charts-container">
                 <div class="chart-box" style="padding-left: 5px;">
                    <span style="font-weight: bold; display: block; margin-bottom: 8px; font-size: 8.5px; color: #004b8d;">Relación Plan vs Extra Plan</span>
                    @if($barBase64)
                        <img src="{{ $barBase64 }}" class="chart-img">
                    @endif
                </div>
               
            </div>
        </div>

        <div class="section">
            <p class="section-title">Cumplimiento por Departamento</p>
            <table>
                <thead>
                    <tr>
                        <th>Departamento</th>
                        <th>Total</th>
                        <th>Completado</th>
                        <th>Pendiente</th>
                        <th>Por revisión</th>
                        <th>No completado</th>
                        <th>% Cumplimiento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ordersByDepartmentStatus as $name => $stats)
                        <tr>
                            <td>{{ $name }}</td>
                            <td>{{ $stats['total'] }}</td>
                            <td>{{ $stats['completed'] }}</td>
                            <td>{{ $stats['pending'] }}</td>
                            <td>{{ $stats['review'] }}</td>
                            <td>{{ $stats['not_completed'] }}</td>
                            <td>{{ $stats['completion'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
         @unless($isDepartmentFilter || $isDisciplineFilter)
        <div class="section">
            <div class="charts-container">
                 
                
                 <div class="chart-box" style="padding-left: 5px;">
                    <span style="font-weight: bold; display: block; margin-bottom: 8px; font-size: 8.5px; color: #004b8d;">Distribución de Cumplimiento por Departamento</span>
                    @if($deptCompBase64)
                        <img src="{{ $deptCompBase64 }}" class="chart-img">
                    @endif
                </div>
                    
            
               
            </div>
        </div>
        @endunless

        @if($isDepartmentFilter || (!empty($deptIndividualCharts) && count($deptIndividualCharts) > 0))
        <div class="section">
            <p class="section-title">Cumplimiento Individual por Departamento</p>
            <div class="charts-container">
                @php $count = 0; @endphp
                @foreach($deptIndividualCharts as $deptName => $base64Image)
                    @if($count > 0 && $count % 2 == 0)
                        </div><div class="charts-container" style="margin-top: 10px;">
                    @endif
                    <div class="chart-box" style="{{ $count % 2 == 0 ? 'padding-right: 5px;' : 'padding-left: 5px;' }}">
                        <span style="font-weight: bold; display: block; margin-bottom: 6px; font-size: 8.5px; color: #004b8d;">
                            {{ strtoupper($deptName) }}
                        </span>
                        @if($base64Image)
                            <img src="{{ $base64Image }}" class="chart-img" style="max-width: 120px;">
                        @endif
                    </div>
                    @php $count++; @endphp
                @endforeach
                {{-- Relleno si el número de departamentos es impar --}}
                @if($count % 2 != 0)
                    <div class="chart-box" style="border: none; background: transparent;"></div>
                @endif
            </div>
        </div>
        @endif
        <div class="section">
            <p class="section-title">Órdenes por Disciplina</p>
            <table>
                <thead>
                    <tr>
                        <th>Disciplina</th>
                        <th>Total</th>
                        <th>Pendientes</th>
                        <th>Por revisión</th>
                        <th>No completadas</th>
                        <th>Completadas</th>
                        <th>Cumplimiento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departmentDisciplineReport as $departmentName => $disciplines)
                        <tr class="bg-slate-100">
                            <td colspan="7" class="font-semibold" style="background-color: #f0f0f0;">
                                {{ $departmentName }}
                            </td>
                        </tr>
                        @foreach($disciplines as $discipline)
                            <tr>
                                <td class="pl-4">{{ $discipline['discipline_name'] }}</td>
                                <td>{{ $discipline['total'] }}</td>
                                <td>{{ $discipline['pending'] }}</td>
                                <td>{{ $discipline['review'] }}</td>
                                <td>{{ $discipline['not_completed'] }}</td>
                                <td>{{ $discipline['completed'] }}</td>
                                <td>{{ $discipline['completion'] }}%</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
</body>
</html>