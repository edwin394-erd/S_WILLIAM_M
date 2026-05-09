<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\WorkOrder;
use App\Models\Installation;
use App\Models\Equipment;
use App\Models\WorkSheet;
use App\Models\Discipline;
use App\Models\OrderTask;

class WorkOrderController extends Controller
{
    public function index()
    {
        // Lógica para mostrar la lista de órdenes de trabajo
    }

    public function create(Request $request)
    {
        $installations = Installation::all();
        $equipment = Equipment::all();
        $worksheet = null;

        if ($request->filled('worksheet_id')) {
            $worksheet = WorkSheet::find($request->query('worksheet_id'));
        }

        $disciplines = $worksheet ? $worksheet->department->disciplines : collect();
        $nextOdmNumber = WorkOrder::nextOdmNumber();

        return view('workorders.create')
            ->with('installations', $installations)
            ->with('equipment', $equipment)
            ->with('worksheet', $worksheet)
            ->with('disciplines', $disciplines)
            ->with('nextOdmNumber', $nextOdmNumber);
    }

   public function store(Request $request)
{
    $validated = $request->validate([
        'worksheet_id'     => 'required|exists:work_sheets,id',
        'installation_id'  => 'required|exists:installations,id',
        'discipline_id'    => 'required|array|min:1',
        'discipline_id.*'  => 'required|exists:disciplines,id',
        'equipment_id'     => 'required|exists:equipment,id',
        'type'             => 'required|in:CORRECTIVO,PREVENTIVO,PREDICTIVO,DETECTIVO',
        'impact'           => 'required|numeric',
        'accion_requerida' => 'required|string',
        'date'             => 'required|date',
        'time_start'       => 'required|date_format:H:i',
        'time_end'         => 'required|date_format:H:i',
        'high_risk'        => 'sometimes|boolean',
    ]);

    $disciplineIds = $request->input('discipline_id', []);

    foreach ($disciplineIds as $disciplineId) {
        $count = OrderTask::where('discipline_id', $disciplineId)
            ->whereDate('date', $request->date)
            ->count();

        if ($count >= 5) {
            return back()
                ->withInput()
                ->with('error', "La disciplina seleccionada ya tiene 5 actividades programadas para esa fecha.");
        }
    }

    $installation = Installation::findOrFail($request->installation_id);
    $equipment = Equipment::findOrFail($request->equipment_id);
    $dateTimeStart = Carbon::parse("{$request->date} {$request->time_start}");
    $dateTimeEnd = Carbon::parse("{$request->date} {$request->time_end}");

    return DB::transaction(function () use ($request, $disciplineIds, $installation, $equipment, $dateTimeStart, $dateTimeEnd) {
        $odmNumber = WorkOrder::nextOdmNumber();

        $workOrder = WorkOrder::create([
            'work_sheet_id'   => $request->worksheet_id,
            'odm_number'      => $odmNumber,
            'type'            => $request->type,
            'installation_id' => $installation->id,
            'equipment_id'    => $equipment->id,
            'impacto'         => $request->impact,
            'accion_requerida'=> $request->accion_requerida,
            'is_high_risk'    => $request->boolean('high_risk'),
        ]);

        foreach ($disciplineIds as $disciplineId) {
            $discipline = Discipline::find($disciplineId);

            $workOrder->tasks()->create([
                'discipline_id' => $disciplineId,
                'date'          => $request->date,
                'time_start'    => $dateTimeStart,
                'time_end'      => $dateTimeEnd,
            ]);
        }

        return redirect()->route('admin.worksheets.show', $request->worksheet_id)
            ->with('success', "Orden {$odmNumber} creada exitosamente.");
    });
}

    public function destroy($id)
    {
        $workOrder = WorkOrder::findOrFail($id);
        $worksheet = $workOrder->workSheet;
    
        DB::transaction(function () use ($workOrder) {
            $workOrder->tasks()->delete();
            $workOrder->delete();
        });


        return redirect()->route('worksheets.show', $worksheet->id)
            ->with('success', "Orden {$workOrder->odm_number} eliminada correctamente.");
    }

    public function show($id)
    {
        // Lógica para mostrar los detalles de una orden de trabajo específica
    }
}
