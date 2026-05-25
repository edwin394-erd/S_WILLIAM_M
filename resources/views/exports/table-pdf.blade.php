<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: portrait; margin: 8mm; }
        body { font-family: "Arial Narrow", Arial, sans-serif; font-size: 9px; margin: 0; padding: 0; color: #333; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #eeeeee; padding: 6px 8px; vertical-align: middle; font-size: 9px; }
        .top-info-table td { border: none; padding: 0; }
        .logo-container { width: 15%; text-align: left; }
        .title-container { width: 70%; text-align: center; font-size: 14px; font-weight: bold; color: #000; }
        .generated { width: 15%; text-align: right; font-size: 9px; }
        .header-main { background-color: #004b8d; color: white; font-weight: bold; }
        .text-center { text-align: center; }
        img.logo { height: 45px; }
    </style>
    <title>{{ $title ?? 'Listado' }}</title>
</head>
<body>
    @php \Carbon\Carbon::setLocale('es'); @endphp

    <table class="top-info-table">
        <tr>
            <td class="logo-container">
                <img src="{{ public_path('imgs/petroboscan.png') }}" class="logo">
            </td>
            <td class="title-container">{{ $title ?? 'LISTADO' }}</td>
            <td class="generated">Generado: {{ $generatedAt ?? now()->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <table style="margin-top:8px;">
        <thead>
            <tr class="header-main">
                @foreach($columns as $field => $label)
                    <th>{{ strtoupper($label) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($records as $rec)
                <tr>
                    @foreach($columns as $field => $label)
                        <td>{{ data_get($rec, $field) ?? '' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}" class="text-center">No hay registros</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>