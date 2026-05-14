<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\WorkSheet;
use App\Models\Department;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;

class WorkSheetController extends Controller
{
    public function index()
    {
        $worksheets = WorkSheet::with('department')->withCount('workOrders')->get();
        $departmentOptions = ['' => 'Todos los departamentos'] + Department::pluck('name', 'name')->toArray();

        return view('worksheets.index')->with('worksheets', $worksheets)->with('departmentOptions', $departmentOptions);
    }


    private function getWeekStartWednesday(
        ?string $currentDate = null
    ): string {
        $today = $currentDate ? strtotime($currentDate) : strtotime('today');
        $weekday = (int) date('N', $today);

        if ($weekday >= 3) {
            return date('Y-m-d', strtotime('wednesday this week', $today));
        }

        return date('Y-m-d', strtotime('wednesday last week', $today));
    }

    private function resolveWeekNumber(string $weekStartDate): int
    {
        $year = date('Y', strtotime($weekStartDate));
        $firstWednesday = date('Y-m-d', strtotime("first wednesday of january $year"));

        return (int) floor((strtotime($weekStartDate) - strtotime($firstWednesday)) / (7 * 24 * 3600)) + 1;
    }

    public function create()
    {
        $departments = Department::all();

        date_default_timezone_set('America/Caracas');

        $currentStart = $this->getWeekStartWednesday();
        $nextStart = date('Y-m-d', strtotime('+7 days', strtotime($currentStart)));

        $currentWeek = $this->resolveWeekNumber($currentStart);
        $nextWeek = $this->resolveWeekNumber($nextStart);

        $currentEnd = date('Y-m-d', strtotime('+6 days', strtotime($currentStart)));
        $nextEnd = date('Y-m-d', strtotime('+6 days', strtotime($nextStart)));

        $weekOptions = [
            $currentWeek => 'Semana '.$currentWeek.' ('.date('d/m', strtotime($currentStart)).' - '.date('d/m', strtotime($currentEnd)).')',
            $nextWeek => 'Semana '.$nextWeek.' ('.date('d/m', strtotime($nextStart)).' - '.date('d/m', strtotime($nextEnd)).')',
        ];

        $weekMap = [
            $currentWeek => ['start' => $currentStart, 'end' => $currentEnd],
            $nextWeek => ['start' => $nextStart, 'end' => $nextEnd],
        ];

        return view('worksheets.create')->with([
            'departments' => $departments,
            'weekOptions' => $weekOptions,
            'weekMap' => $weekMap,
            'currentWeek' => $currentWeek,
            'currentStart' => $currentStart,
            'currentEnd' => $currentEnd,
        ]);
    }

    public function store(Request $request)
    {
        date_default_timezone_set('America/Caracas');

        $currentStart = $this->getWeekStartWednesday();
        $nextStart = date('Y-m-d', strtotime('+7 days', strtotime($currentStart)));

        $currentWeek = $this->resolveWeekNumber($currentStart);
        $nextWeek = $this->resolveWeekNumber($nextStart);

        $request->validate([
            'week_number' => ['required', 'integer', Rule::in([$currentWeek, $nextWeek])],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'department_id' => 'required|exists:departments,id',
        ]);

        $departamento_semana_existe = WorkSheet::where('department_id', $request->department_id)
            ->where('week_number', $request->week_number)
            ->exists();
        if ($departamento_semana_existe) {
            return back()->withErrors(['department_id' => 'Ya existe una hoja de trabajo para este departamento en esta semana.'])->withInput();
        }

        WorkSheet::create([
            'week_number' => $request->week_number,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'department_id' => $request->department_id,
        ]);

        return redirect()->route('admin.worksheets.index')->with('success', 'Hoja de trabajo creada exitosamente.');
    }

   public function show($id)
{
    // Cargamos workOrders y, dentro de ellas, sus tareas Y las disciplinas de esas tareas
    $worksheet = WorkSheet::with([
        'workOrders.equipment', 
        'workOrders.installation',
        'workOrders.tasks.discipline' // <-- Agregamos .discipline aquí
        ])->with('department')->findOrFail($id);

  

    return view('worksheets.show')->with('worksheet', $worksheet);
}

    public function edit($id)
    {
        // Lógica para mostrar el formulario de edición de una hoja de trabajo
    }

    public function update(Request $request, $id)
    {
        // Lógica para actualizar una hoja de trabajo existente
    }

    public function destroy($id)
    {
        $workorders = WorkSheet::findOrFail($id)->workOrders;
        foreach ($workorders as $order) {
            $order->tasks()->delete();
            $order->delete();
        }


        $worksheet = WorkSheet::findOrFail($id);
        $worksheet->delete();

        return redirect()->route('admin.worksheets.index')->with('success', 'Hoja de trabajo eliminada exitosamente.');
    }

   public function generatePdf($id)
{
    $worksheet = WorkSheet::with([
        'department',
        'workOrders.tasks.discipline',
        'workOrders.installation',
        'workOrders.equipment'
    ])->findOrFail($id);

    // Agrupamos las órdenes de trabajo por fecha para que la vista las itere correctamente
    // Usamos 'created_at' o la fecha de la primera tarea como referencia
    $worksheet->dates = $worksheet->workOrders->groupBy(function($order) {
        // Si tienes una fecha específica en la orden úsala, 
        // de lo contrario usamos la fecha de su primera tarea
        return \Carbon\Carbon::parse($order->tasks->first()->date ?? $order->created_at)->format('l, d F, Y');
    });

    $pdf = Pdf::loadView('worksheets.pdf', compact('worksheet'));

    // Configuramos el papel en horizontal (landscape) para que quepan todas las columnas
    $pdf->setPaper('a4', 'portrait'); // Cambia a 'landscape' si quieres horizontal

     $departmentName = str_replace(' ', '-', $worksheet->department->name);
     $timestamp = now()->format('d-m-Y-H');
     $fileName = 'Sabana' . $worksheet->week_number . '-' . $departmentName . '-' . $timestamp . '.pdf';

    return $pdf->stream('sabana_' . $worksheet->week_number . '.pdf');
}

public function sendToTelegram(Request $request, $id)
{
    $worksheet = WorkSheet::with([
        'department',
        'workOrders.tasks.discipline',
        'workOrders.installation',
        'workOrders.equipment'
    ])->findOrFail($id);

    // IMPORTANTE: También debemos agrupar aquí porque Telegram usa la misma vista del PDF
    $worksheet->dates = $worksheet->workOrders->groupBy(function($order) {
        return \Carbon\Carbon::parse($order->tasks->first()->date ?? $order->created_at)->format('l, d F, Y');
    });

    // Generar el PDF con orientación horizontal
    $pdf = Pdf::loadView('worksheets.pdf', compact('worksheet'))->setPaper('a4', 'portrait');

    $departmentName = str_replace(' ', '-', $worksheet->department->name);
    $timestamp = now()->format('d-m-Y-H');
    $fileName = 'Sabana' . $worksheet->week_number . '-' . $departmentName . '-' . $timestamp . '.pdf';

    $pdfPath = storage_path('app/public/' . $fileName);
    $pdf->save($pdfPath);

    $token = '8694198608:AAF02Ce6Kfm1dv2as1HW-gCdqP9jFHU0yg8';
    $chat_id = $request->chat_id;

    $response = Http::attach(
        'document',
        file_get_contents($pdfPath),
        $fileName
    )->post("https://api.telegram.org/bot{$token}/sendDocument", [
        'chat_id' => $chat_id,
        'caption' => "Sábana Semana {$worksheet->week_number} - {$worksheet->department->name}"
    ]);

    unlink($pdfPath);

    WorkSheet::where('id', $id)->update(['enviado' => 'ENVIADO']);

    if ($response->successful()) {
        return back()->with('success', 'PDF enviado correctamente a Telegram.');
    }

    return back()->with('error', 'Fallo de Telegram: ' . $response->json('description'));
}
}
