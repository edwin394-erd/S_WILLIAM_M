@props(['total' => '$0', 'percentage' => '0', 'series', 'categories'])

@php 
    // Generamos un ID único por si usas el componente varias veces en la misma vista
    $chartId = 'chart-' . Str::random(8); 
@endphp

<div {{ $attributes->merge(['class' => 'max-w-sm w-full bg-neutral-primary-soft border border-default rounded-base shadow-xs p-4 md:p-6']) }}>
    <div class="flex justify-between">
        <div>
            <h5 class="text-2xl font-bold text-heading">{{ $total }}</h5>
            <p class="text-body">Sales this week</p>
        </div>
        <div class="flex items-center px-2.5 py-0.5 font-medium text-fg-success text-center">
            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v13m0-13 4 4m-4-4-4 4"/>
            </svg>
            {{ $percentage }}%
        </div>
    </div>

    <!-- Contenedor del gráfico -->
    <div id="{{ $chartId }}" class="py-4 md:py-6" style="min-height: 200px;"></div>

    <div class="grid grid-cols-1 items-center border-light border-t justify-between">
        <div class="flex justify-between items-center pt-4 md:pt-6">
            <button id="dropdown-{{ $chartId }}" data-dropdown-toggle="drop-{{ $chartId }}" class="text-sm font-medium text-body hover:text-heading text-center inline-flex items-center" type="button">
                Last 7 days
                <svg class="w-4 h-4 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                </svg>
            </button>
            
            <div id="drop-{{ $chartId }}" class="z-10 hidden bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44">
                <ul class="p-2 text-sm text-body font-medium">
                    <li><a href="#" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium rounded">Last 7 days</a></li>
                    <li><a href="#" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium rounded">Last 30 days</a></li>
                </ul>
            </div>
            
            <a href="#" class="inline-flex items-center text-fg-brand font-medium text-sm px-3 py-2">
                Progress report
                <svg class="w-4 h-4 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/>
                </svg>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function() {
        const renderChart = () => {
            const el = document.getElementById("{{ $chartId }}");
            if (el && typeof ApexCharts !== 'undefined') {
                const style = getComputedStyle(document.documentElement);
                const brand = style.getPropertyValue('--color-fg-brand').trim() || "#1447E6";
                const brandSubtle = style.getPropertyValue('--color-fg-brand-subtle').trim() || "#93C5FD";

                const options = {
                    series: @json($series),
                    chart: {
                        type: "area",
                        height: "100%",
                        fontFamily: "Inter, sans-serif",
                        toolbar: { show: false }
                    },
                    fill: {
                        type: "gradient",
                        gradient: { opacityFrom: 0.45, opacityTo: 0.05 }
                    },
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 3 },
                    grid: { show: false },
                    xaxis: {
                        categories: @json($categories),
                        labels: { show: false },
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: { show: false },
                    legend: { show: true, position: 'bottom' },
                    colors: [brand, brandSubtle]
                };

                new ApexCharts(el, options).render();
            }
        };

        // Ejecutar al cargar o en eventos de navegación (como Livewire)
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', renderChart);
        } else {
            renderChart();
        }
    })();
</script>
@endpush