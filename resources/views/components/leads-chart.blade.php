@props([
    'value' => '0',
    'label' => 'Leads generated per week',
    'percentage' => '0',
    'moneySpent' => '$0',
    'conversion' => '0%',
    'series',
    'categories'
])

@php 
    $chartId = 'chart-' . Str::random(8); 
@endphp

<div {{ $attributes->merge(['class' => 'max-w-sm w-full bg-neutral-primary-soft border border-default rounded-base shadow-xs p-4 md:p-6']) }}>
  <div class="flex justify-between pb-4 mb-4 border-b border-light">
    <div class="flex items-center">
      <div class="w-12 h-12 bg-neutral-primary-medium border border-default-medium flex items-center justify-center rounded-full me-3">
        <svg class="w-7 h-7 text-body" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M4.5 17H4a1 1 0 0 1-1-1 3 3 0 0 1 3-3h1m0-3.05A2.5 2.5 0 1 1 9 5.5M19.5 17h.5a1 1 0 0 0 1-1 3 3 0 0 0-3-3h-1m0-3.05a2.5 2.5 0 1 0-2-4.45m.5 13.5h-7a1 1 0 0 1-1-1 3 3 0 0 1 3-3h3a3 3 0 0 1 3 3 1 1 0 0 1-1 1Zm-1-9.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z"/></svg>
      </div>
      <div>
        <h5 class="text-2xl font-semibold text-heading">{{ $value }}</h5>
        <p class="text-sm text-body">{{ $label }}</p>
      </div>
    </div>
    <div>
      <span class="inline-flex items-center bg-success-soft border border-success-subtle text-fg-success-strong text-xs font-medium px-1.5 py-0.5 rounded">
        <svg class="w-4 h-4 me-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v13m0-13 4 4m-4-4-4 4"/></svg>
        {{ $percentage }}%
      </span>
    </div>
  </div>

  <div class="grid grid-cols-2">
    <dl class="flex items-center">
        <dt class="text-body text-sm font-normal me-1">Money spent:</dt>
        <dd class="text-heading text-sm font-semibold">{{ $moneySpent }}</dd>
    </dl>
    <dl class="flex items-center justify-end">
        <dt class="text-body text-sm font-normal me-1">Conversion:</dt>
        <dd class="text-heading text-sm font-semibold">{{ $conversion }}</dd>
    </dl>
  </div>

  <!-- Contenedor del gráfico de columnas -->
  <div id="{{ $chartId }}"></div>

  <div class="grid grid-cols-1 items-center border-light border-t justify-between">
    <div class="flex justify-between items-center pt-4 md:pt-6">
      <button id="btn-{{ $chartId }}" data-dropdown-toggle="drop-{{ $chartId }}" class="text-sm font-medium text-body hover:text-heading text-center inline-flex items-center" type="button">
          Last 7 days
          <svg class="w-4 h-4 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
      </button>
      
      <div id="drop-{{ $chartId }}" class="z-10 hidden bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44">
          <ul class="p-2 text-sm text-body font-medium">
            <li><a href="#" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium rounded">Yesterday</a></li>
            <li><a href="#" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium rounded">Today</a></li>
            <li><a href="#" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium rounded">Last 7 days</a></li>
          </ul>
      </div>
      
      <a href="#" class="inline-flex items-center text-fg-brand font-medium leading-5 rounded-base text-sm px-3 py-2">
        Leads Report
        <svg class="w-4 h-4 ms-1.5 -me-0.5 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/></svg>
      </a>
    </div>
  </div>
</div>

@push('scripts')
<script>
    (function() {
        const initChart = () => {
            const container = document.getElementById("{{ $chartId }}");
            if (container && typeof ApexCharts !== 'undefined') {
                const style = getComputedStyle(document.documentElement);
                const brandColor = style.getPropertyValue('--color-fg-brand').trim() || "#1447E6";
                const brandSubtle = style.getPropertyValue('--color-fg-brand-subtle').trim() || "#93C5FD";

                const options = {
                    colors: [brandColor, brandSubtle],
                    series: @json($series),
                    chart: {
                        type: "bar", // Cambiado a columnas según tu nuevo ID
                        height: "220px",
                        fontFamily: "Inter, sans-serif",
                        toolbar: { show: false },
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: "50%",
                            borderRadiusApplication: "end",
                            borderRadius: 4,
                        },
                    },
                    tooltip: {
                        shared: true,
                        intersect: false,
                    },
                    states: {
                        hover: { filter: { type: "darken", value: 1 } },
                    },
                    stroke: {
                        show: true,
                        width: 0,
                        colors: ["transparent"],
                    },
                    grid: {
                        show: false,
                        padding: { left: 2, right: 2, top: -14 }
                    },
                    dataLabels: { enabled: false },
                    legend: { show: false },
                    xaxis: {
                        floating: false,
                        categories: @json($categories),
                        labels: { show: false },
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                    },
                    yaxis: { show: false },
                    fill: { opacity: 1 },
                };

                const chart = new ApexCharts(container, options);
                chart.render();
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initChart);
        } else {
            initChart();
        }
    })();
</script>
@endpush