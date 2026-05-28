@props([
    'value' => '0',
    'label' => 'Leads generated per week',
    'percentage' => '0',
    'percentageLabel' => 'Incremento Mensual',
    'moneySpent' => '$0',
    'conversion' => '0%',
    'series',
    'categories',
    'showLabels' => false,
    'showLegend' => false,
    'colors' => [],
    'chartType' => 'bar',
    'chartHeight' => 220,
    'horizontal' => false,
    'chartId' => null,
])

@php 
    $chartId = $chartId ?? 'chart-' . Str::random(8); 
@endphp

<div {{ $attributes->merge(['class' => 'w-full bg-neutral-primary-soft border border-default rounded-3xl shadow-xs p-4 md:p-6']) }}>
  <div class="flex justify-between border-light border-">
    <dl>
      <dt class="text-sm">{{ $label }}</dt>
      <dd class="text-xl font-semibold text-heading">{{ $value }}</dd>
    </dl>
    @php
      $percentageValue = (float) str_replace('%', '', $percentage);
      $isPositive = $percentageValue >= 0;
      $badgeClasses = $isPositive
          ? 'inline-flex items-center bg-success-soft  text-fg-success-strong text-xs font-medium px-1.5 py-0.5 rounded'
          : 'inline-flex items-center bg-danger-soft text-fg-danger-strong text-xs font-medium px-1.5 py-0.5 rounded';
      $arrowPath = $isPositive
          ? 'M12 6v13m0-13 4 4m-4-4-4 4'
          : 'M12 18V5m0 13 4-4m-4 4-4-4';
  @endphp
  <div>
      <span class="{{ $badgeClasses }}">
        <svg class="w-4 h-4 me-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $arrowPath }}"/></svg>
        {{ $percentageLabel }}: {{ $percentage }}%
      </span>
    </div>
  </div>

  {{-- <div class="grid grid-cols-2 py-3 gap-4">
    <dl>
      <dt class="text-body">Departamentos</dt>
      <dd class="text-lg font-semibold text-heading">{{ $moneySpent }}</dd>
    </dl>
    <dl>
      <dt class="text-body">Meses</dt>
      <dd class="text-lg font-semibold text-heading">{{ $conversion }}</dd>
    </dl>
  </div> --}}

  <div id="{{ $chartId }}" style="min-height: {{ $chartHeight }}px;"></div>

  {{-- <div class="grid grid-cols-1 items-center border-light border-t justify-between">
    <div class="flex justify-between items-center pt-4 md:pt-6">
      <button id="btn-{{ $chartId }}" data-dropdown-toggle="drop-{{ $chartId }}" class="text-sm font-medium text-body hover:text-heading text-center inline-flex items-center" type="button">
          Last 7 days
          <svg class="w-4 h-4 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
      </button>
      <div id="drop-{{ $chartId }}" class="z-10 hidden bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44">
          <ul class="p-2 text-sm text-body font-medium">
            <li><a href="#" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">Yesterday</a></li>
            <li><a href="#" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">Today</a></li>
            <li><a href="#" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">Last 7 days</a></li>
            <li><a href="#" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">Last 30 days</a></li>
            <li><a href="#" class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded">Last 90 days</a></li>
          </ul>
      </div>
      <a href="#" class="inline-flex items-center text-fg-brand bg-transparent box-border border border-transparent hover:bg-neutral-secondary-medium focus:ring-4 focus:ring-neutral-tertiary font-medium leading-5 rounded-base text-sm px-3 py-2 focus:outline-none">
        Revenue Report
        <svg class="w-4 h-4 ms-1.5 -me-0.5 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"/></svg>
      </a>
    </div>
  </div> --}}
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

                const colors = @json($colors);
                const categories = @json($categories) || [];
                const horizontal = @json($horizontal) === true || @json($horizontal) === 'true';
                const options = {
                    series: @json($series),
                    chart: {
                        type: @json($chartType),
                        height: @json($chartHeight),
                        fontFamily: "Inter, sans-serif",
                        toolbar: { show: false },
                    },
                    plotOptions: {
                        bar: {
                            horizontal,
                            columnWidth: horizontal ? '40%' : '50%',
                            barHeight: horizontal ? '60%' : null,
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
                    legend: {
                        show: @json($showLegend),
                        position: 'top',
                        markers: { radius: 4 },
                        itemMargin: { horizontal: 8, vertical: 4 },
                    },
                    xaxis: {
                        floating: false,
                        categories,
                        tickPlacement: 'on',
                        labels: {
                            show: @json($showLabels),
                            style: { colors: '#6B7280', fontSize: '12px' },
                        },
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                    },
                    yaxis: {
                        show: true,
                        tickAmount: horizontal ? categories.length : undefined,
                        labels: {
                            show: true,
                            formatter: horizontal ? function (value, index) {
                                return categories[index] ?? value;
                            } : undefined,
                            style: { colors: '#6B7280', fontSize: '12px' },
                        },
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                    },
                    fill: { opacity: 1 },
                };

                if (Array.isArray(colors) && colors.length > 0) {
                    options.colors = colors;
                }

                const chart = new ApexCharts(container, options);
                chart.render();

                window.leadsCharts = window.leadsCharts || {};
                window.leadsCharts["{{ $chartId }}"] = chart;
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