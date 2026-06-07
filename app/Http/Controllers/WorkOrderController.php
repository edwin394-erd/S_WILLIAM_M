<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\WorkOrder;
use App\Models\Installation;
use App\Models\Equipment;
use App\Models\WorkSheet;
use App\Models\Department;
use App\Models\Discipline;
use App\Models\OrderTask;
use App\Models\OrderTaskEvidence;
use Illuminate\Support\Facades\Storage;


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

            if (!$worksheet) {
                return redirect()->route('admin.worksheets.index')->with('error', 'Hoja de trabajo no encontrada.');
            }
          
            
            if ($worksheet->start_date < Carbon::today('America/Caracas')->toDateString() && ! $request->boolean('is_extraplan')) {
                return redirect()->route('admin.worksheets.show', $worksheet->id)->with('error', 'No se pueden agregar órdenes a una sabana pasada.');
            }
        }

        $disciplines = collect();
        if ($worksheet) {
            $disciplines = $worksheet->department->disciplines;
        } elseif ($request->filled('is_extraplan')) {
            $disciplines = Discipline::all();
        }
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
        'discipline_id'    => 'required|exists:disciplines,id',
        'equipment_id'     => 'required|exists:equipment,id',
        'type'             => 'required|in:CORRECTIVO,PREVENTIVO,PREDICTIVO,DETECTIVO',
        'impact'           => 'required|numeric',
        'accion_requerida' => 'required|string',
        'date'             => 'required|date',
        'time_start'       => 'nullable|date_format:H:i',
        'time_end'         => 'nullable|date_format:H:i',
        'high_risk'        => 'sometimes|boolean',
        'is_extraplan'     => 'sometimes|boolean',
    ]);

    $disciplineId = $request->discipline_id;
    $schedule = $this->computeScheduleTimes($disciplineId, $request->date);

    if ($schedule['count'] >= 5) {
        return back()
            ->withInput()
            ->with('error', "La disciplina seleccionada ya tiene 5 actividades programadas para esa fecha.");
    }

    $installation = Installation::findOrFail($request->installation_id);
    $equipment = Equipment::findOrFail($request->equipment_id);
    $dateTimeStart = Carbon::parse("{$request->date} {$schedule['time_start']}");
    $dateTimeEnd = Carbon::parse("{$request->date} {$schedule['time_end']}");

    $workOrder = DB::transaction(function () use ($request, $disciplineId, $installation, $equipment, $dateTimeStart, $dateTimeEnd) {
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
            'is_extraplan'    => $request->boolean('is_extraplan'),
        ]);

        $workOrder->tasks()->create([
            'discipline_id' => $disciplineId,
            'date'          => $request->date,
            'time_start'    => $dateTimeStart,
            'time_end'      => $dateTimeEnd,
        ]);

        if (! $request->boolean('is_extraplan')) {
            $worksheet = WorkSheet::find($request->worksheet_id);
            WorkSheet::where('id', $worksheet->id)->update(['enviado' => 'POR ENVIAR']);
        }

        return $workOrder;
    });

    $message = "Orden {$workOrder->odm_number} creada exitosamente.";

    if ($workOrder->is_extraplan) {
        $sent = $this->sendExtraplanToTelegram($workOrder);

        if ($sent) {
            $message .= ' Extraplan enviado automáticamente a Telegram.';
        } else {
            $message .= ' No se pudo enviar automáticamente a Telegram al grupo del departamento.';
        }
    }

    return redirect()->route('admin.worksheets.show', $request->worksheet_id)
        ->with('success', $message);
}

    private function sendExtraplanToTelegram(WorkOrder $workOrder): bool
    {
        try {
            $workOrder->load(['workSheet.department', 'installation', 'equipment', 'tasks.discipline']);

            $worksheet = $workOrder->workSheet;
            $department = $worksheet->department;

            if (! $department || ! $department->grupo_telegram_id) {
                return false;
            }

            $worksheet->setRelation('workOrders', collect([$workOrder]));
            $worksheet->dates = $worksheet->workOrders->groupBy(function ($order) {
                return Carbon::parse($order->tasks->first()->date ?? $order->created_at)->format('l, d F, Y');
            });

            $departmentName = str_replace(' ', '-', $department->name);
            $timestamp = now()->format('d-m-Y-H');
            $fileName = "Extraplan-{$workOrder->odm_number}-{$departmentName}-{$timestamp}.pdf";
            $pdfPath = storage_path('app/public/' . $fileName);

            $pdf = Pdf::loadView('worksheets.pdf', ['worksheet' => $worksheet, 'includeSummary' => false])->setPaper('a4', 'portrait');
            $pdf->save($pdfPath);

            $token = '8694198608:AAF02Ce6Kfm1dv2as1HW-gCdqP9jFHU0yg8';
            $chatId = $department->grupo_telegram_id;

            $response = Http::attach(
                'document',
                file_get_contents($pdfPath),
                $fileName
            )->post("https://api.telegram.org/bot{$token}/sendDocument", [
                'chat_id' => $chatId,
                'caption' => "Nuevo extraplan agregado. ODM: {$workOrder->odm_number}",
            ]);

            unlink($pdfPath);

            if (! $response->successful()) {
                Log::error('Fallo envío de extraplan a Telegram', [
                    'work_order_id' => $workOrder->id,
                    'chat_id' => $chatId,
                    'response' => $response->json(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $exception) {
            Log::error('Error al generar/enviar extraplan a Telegram', [
                'work_order_id' => $workOrder->id,
                'exception' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    public function scheduleInfo(Request $request)
    {
        $validated = $request->validate([
            'discipline_id' => 'required|exists:disciplines,id',
            'date'          => 'required|date',
        ]);

        $schedule = $this->computeScheduleTimes($validated['discipline_id'], $validated['date']);

        return response()->json($schedule);
    }

    private function computeScheduleTimes(int $disciplineId, string $date): array
    {
        $count = OrderTask::where('discipline_id', $disciplineId)
            ->whereDate('date', $date)
            ->count();

        if ($count >= 5) {
            return [
                'count'      => $count,
                'time_start' => null,
                'time_end'   => null,
                'message'    => 'Máximo de 5 actividades alcanzado para esta disciplina en esta fecha.',
            ];
        }

        $startHour = 7 + ($count * 2);

        return [
            'count'      => $count,
            'time_start' => sprintf('%02d:00', $startHour),
            'time_end'   => sprintf('%02d:00', $startHour + 2),
        ];
    }

    public function destroy($id)
    {
        $workOrder = WorkOrder::findOrFail($id);
        $worksheet = $workOrder->workSheet;
    
        DB::transaction(function () use ($workOrder) {
            $workOrder->tasks()->delete();
            $workOrder->delete();
        });

        // CORRECCIÓN: El método update requiere un array asociativo
        WorkSheet::where('id', $worksheet->id)->update(['enviado' => 'POR ENVIAR']);

        return redirect()->route('admin.worksheets.show', $worksheet->id)
            ->with('success', "Orden {$workOrder->odm_number} eliminada correctamente.");
    }

    private function getCurrentWeekBoundaries(): array
    {
        $today = Carbon::now('America/Caracas')->startOfDay();
        $weekday = $today->dayOfWeekIso; // 1 = Monday, 7 = Sunday

        if ($weekday >= 4) {
            $weekStart = Carbon::parse('thursday this week', 'America/Caracas')->startOfDay();
        } else {
            $weekStart = Carbon::parse('thursday last week', 'America/Caracas')->startOfDay();
        }

        $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();

        return [
            'start' => $weekStart,
            'end' => $weekEnd,
        ];
    }

    public function actividades($id_discipline)
    {
        OrderTask::markOverduePendingAsNotCompleted();

        Discipline::findOrFail($id_discipline);
        $boundaries = $this->getCurrentWeekBoundaries();

        $workOrders = WorkOrder::whereHas('tasks', function ($query) use ($id_discipline, $boundaries) {
            $query->where('discipline_id', $id_discipline)
                ->whereBetween('date', [$boundaries['start']->toDateString(), $boundaries['end']->toDateString()]);
        })
        ->with([
            'tasks' => function ($query) use ($id_discipline, $boundaries) {
                $query->where('discipline_id', $id_discipline)
                    ->whereBetween('date', [$boundaries['start']->toDateString(), $boundaries['end']->toDateString()]);
            },
            'tasks.evidences',
            'installation',
            'equipment',
        ])
        ->get();



        $disciplina = Discipline::find($id_discipline);
        // dd($workOrders);

        if(auth()->user()->discipline_id !== (int)$id_discipline) {
            abort(403, 'No tienes permiso para ver estas actividades.');
        }

        return view('actividades')
            ->with('workOrders', $workOrders)
            ->with('disciplina', $disciplina)
            ->with('weekStart', $boundaries['start'])
            ->with('weekEnd', $boundaries['end']);
    }

    public function formulario($id_discipline, $work_order_id)
    {
        OrderTask::markOverduePendingAsNotCompleted();

        $user = auth()->user();

        if ($user->role === 'tecnico') {
            if ($user->discipline_id !== (int) $id_discipline) {
                abort(403, 'No tienes permiso para ver este formulario.');
            }

            if (! $user->discipline_id) {
                abort(403, 'No tienes una disciplina asignada. Contacta al administrador.');
            }
        }

        if ($user->role === 'supervisor') {
            $supervisorDisciplineIds = $user->disciplines->pluck('id')->map(fn($id) => (int) $id)->all();
            if (! in_array((int) $id_discipline, $supervisorDisciplineIds, true)) {
                abort(403, 'No tienes permiso para ver este formulario.');
            }
        }

        $tasks_uncompleted = OrderTask::where('work_order_id', $work_order_id)
            ->where('discipline_id', $id_discipline)
            ->where('status', 'PENDIENTE')
            ->count();

        if ($tasks_uncompleted < 1) {
            if ($user->role === 'supervisor') {
                return redirect()->route('supervisor.workorders.index')->with('error', 'No puedes reportar esta orden porque no tienes actividades pendientes en esa disciplina.');
            }

            return redirect()->route('tecnico.actividades', $id_discipline)->with('error', 'No puedes reportar esta orden porque no tienes actividades pendientes.');
        }

        $workOrder = WorkOrder::with(['tasks' => function ($query) use ($id_discipline) {
            $query->where('discipline_id', $id_discipline);
        }, 'installation', 'equipment'])->findOrFail($work_order_id);

        return view('workorders.reportar')->with('workOrder', $workOrder);
    }

public function reportar(Request $request, $id)
    {
        if ($request->hasFile('file')) {
            $request->validate([
                'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:20240',
                'order_task_id' => 'required|integer|exists:order_tasks,id',
            ]);

            $task = OrderTask::findOrFail($request->order_task_id);

            Storage::makeDirectory('public/evidences');

            $file = $request->file('file');
            $path = $file->store('evidences', 'public');

            $evidence = OrderTaskEvidence::create([
                'order_task_id' => $task->id,
                'path'          => $path,
            ]);

            return response()->json([
                'success' => true,
                'evidence_id' => $evidence->id,
                'path' => Storage::url($path),
            ]);
        }

        $user_id = auth()->id();
        $user = auth()->user();

        $request->validate([
            'codigo' => 'required|string',
            'observacion' => 'required|string',
            'order_task_id' => 'required|integer|exists:order_tasks,id',
        ]);



        $task = OrderTask::findOrFail($request->order_task_id);

        if ($user->role === 'tecnico' && $user->discipline_id !== $task->discipline_id) {
            abort(403, 'No tienes permiso para reportar esta actividad.');
        }

        if ($user->role === 'supervisor') {
            $supervisorDisciplineIds = $user->disciplines->pluck('id')->map(fn($id) => (int) $id)->all();
            if (! in_array($task->discipline_id, $supervisorDisciplineIds, true)) {
                abort(403, 'No tienes permiso para reportar esta actividad.');
            }
        }

        $newStatus = 'POR REVISION';
        if (in_array($user->role, ['admin', 'supervisor'])) {
            if (in_array($task->status, ['PENDIENTE','POR REVISION', 'COMPLETADO'])) {
                $newStatus = 'COMPLETADO';
            }
        }
        $workorder = $task->workOrder;
         $worksheet = $workorder->workSheet;

      

        $task->update([
            'observation' => $request->observacion,
            'status' => $newStatus,
            'user_report_id' => $user_id,
        ]);

        if ($newStatus === 'POR REVISION') {
            $this->notifyTaskReportToTelegram($task);
        }

       

        if (in_array(auth()->user()->role, ['admin', 'supervisor'])) {
            $routeName = auth()->user()->role === 'supervisor'
                ? 'supervisor.worksheets.show'
                : 'admin.worksheets.show';

            return redirect()->route($routeName, $worksheet->id)
                ->with('success', 'Actividad reportada con éxito.');
        }

        return redirect()->route('tecnico.actividades', auth()->user()->discipline_id)
                         ->with('success', 'Actividad reportada y pre-cerrada con éxito.');
    }

    private function notifyTaskReportToTelegram(OrderTask $task): bool
    {
        try {
            $task->load('workOrder.workSheet.department');

            $department = optional($task->workOrder)->workSheet->department;
            if (! $department || ! $department->grupo_telegram_id) {
                return false;
            }

            $worksheetLink = route('admin.worksheets.show', $task->workOrder->work_sheet_id);
            $text = "La actividad de la orden {$task->workOrder->odm_number} ha sido reportada y ahora está POR REVISION.\n" .
                    "Ver sabana: {$worksheetLink}";

            $token = '8694198608:AAF02Ce6Kfm1dv2as1HW-gCdqP9jFHU0yg8';
            $chatId = $department->grupo_telegram_id;

            $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
            ]);

            if (! $response->successful()) {
                Log::error('Fallo envío de notificación de reporte a Telegram', [
                    'order_task_id' => $task->id,
                    'chat_id' => $chatId,
                    'response' => $response->json(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $exception) {
            Log::error('Error al notificar reporte de tarea a Telegram', [
                'order_task_id' => $task->id,
                'exception' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    public function completeClosure(Request $request, $workOrderId)
    {
        if (! in_array(auth()->user()->role, ['admin', 'supervisor'], true)) {
            abort(403, 'No tienes permiso para completar el cierre.');
        }

        $request->validate([
            'codigo' => 'required|string',
            'observacion' => 'required|string',
            'order_task_id' => 'required|integer|exists:order_tasks,id',
        ]);

        $task = OrderTask::findOrFail($request->order_task_id);
        $workOrder = WorkOrder::findOrFail($workOrderId);

        if ($task->work_order_id !== $workOrder->id) {
            abort(404, 'La tarea no pertenece a esta orden.');
        }

        if (! in_array($task->status, ['POR REVISION', 'COMPLETADO'], true)) {
            abort(403, 'No se puede completar el cierre de una actividad no reportada.');
        }

        $task->update([
            'observation' => $request->observacion,
            'status' => 'COMPLETADO',
            'user_report_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Observación guardada y orden completada.');
    }

    public function reassign(Request $request, $workOrderId)
    {
        if (! in_array(auth()->user()->role, ['admin', 'planificador', 'supervisor'], true)) {
            abort(403, 'No tienes permiso para reasignar esta orden.');
        }

        $request->validate([
            'target_week' => 'required|in:current,next',
        ]);

        $workOrder = WorkOrder::with('workSheet.department')->findOrFail($workOrderId);

        if (! $workOrder->workSheet) {
            abort(404, 'La orden no está asociada a una sabana válida.');
        }

        if (auth()->user()->role === 'supervisor' && auth()->user()->department_id !== $workOrder->workSheet->department_id) {
            abort(403, 'No tienes permiso para reasignar esta orden fuera de tu departamento.');
        }

        $boundaries = $this->getCurrentWeekBoundaries();
        $weekStart = $boundaries['start']->copy();

        if ($request->target_week === 'next') {
            $weekStart->addDays(7);
        }

        $targetWorksheet = WorkSheet::where('department_id', $workOrder->workSheet->department_id)
            ->where('start_date', $weekStart->toDateString())
            ->first();

        if (! $targetWorksheet) {
            return redirect()->back()->with('error', 'Debes crear la sabana para esa semana.');
        }

        $previousWorksheetId = $workOrder->work_sheet_id;

        DB::transaction(function () use ($workOrder, $targetWorksheet, $request, $previousWorksheetId) {
            $workOrder->tasks()
                ->where('status', 'NO COMPLETADO')
                ->update(['status' => 'PENDIENTE']);

            $workOrder->update([
                'work_sheet_id' => $targetWorksheet->id,
                'is_extraplan' => $request->target_week === 'current',
            ]);

            if ($previousWorksheetId !== $targetWorksheet->id) {
                WorkSheet::where('id', $previousWorksheetId)->update(['enviado' => 'POR ENVIAR']);
                $targetWorksheet->update(['enviado' => 'POR ENVIAR']);
            }
        });

        return redirect()->back()->with('success', 'Orden reasignada correctamente.');
    }

    private function resolveWeekNumber(string $weekStartDate): int
    {
        $weekStart = Carbon::parse($weekStartDate, 'America/Caracas')->startOfDay();
        $year = $weekStart->year;
        $firstThursday = Carbon::parse("first thursday of january {$year}", 'America/Caracas')->startOfDay();

        $weeksElapsed = (int) floor($weekStart->diffInDays($firstThursday) / 7);

        return max(1, $weeksElapsed + 1);
    }

    public function extraPlans()
    {
        $extraplans = WorkOrder::where('is_extraplan', true)->get();
        $worksheet = WorkSheet::whereDate('start_date', '<=', Carbon::today('America/Caracas')->toDateString())
            ->whereDate('end_date', '>=', Carbon::today('America/Caracas')->toDateString())
            ->first();

        if (!$worksheet) {
            $worksheet = WorkSheet::latest('start_date')->first();
        }

        return view('extraplans.index')
            ->with('extraplans', $extraplans)
            ->with('worksheetId', optional($worksheet)->id);
    }

    public function historial(Request $request)
    {
        OrderTask::markOverduePendingAsNotCompleted();

        $status = $request->query('status');
        $dateFrom = $request->query('dateFrom');
        $dateTo = $request->query('dateTo');
        $weekStartQuery = $request->query('week_start');
        $weekFilter = $weekStartQuery;

        if ($weekStartQuery && (!$dateFrom || !$dateTo)) {
            try {
                $weekStart = Carbon::parse($weekStartQuery, 'America/Caracas')->startOfDay();
                $dayOfWeek = (int) $weekStart->format('N');
                if ($dayOfWeek >= 4) {
                    $weekStart = $weekStart->copy()->subDays($dayOfWeek - 4);
                } else {
                    $weekStart = $weekStart->copy()->subDays($dayOfWeek + 3);
                }
                $weekEnd = $weekStart->copy()->addDays(6);
                $dateFrom = $dateFrom ?: $weekStart->toDateString();
                $dateTo = $dateTo ?: $weekEnd->toDateString();
            } catch (\Exception $e) {
                $weekFilter = null;
            }
        }

        $query = WorkOrder::with([
                'tasks' => function ($taskQuery) {
                    $taskQuery->orderBy('date', 'asc')->orderBy('time_start', 'asc');
                },
                'installation',
                'equipment',
                'workSheet',
                'tasks.discipline',
                'tasks.evidences',
            ])
            ->orderByRaw('(select max(date) from order_tasks where order_tasks.work_order_id = work_orders.id) desc');

        // If the current user is a supervisor, restrict to orders in their worksheet department
        if (auth()->user() && auth()->user()->role === 'supervisor') {
            $deptId = auth()->user()->department_id;
            if ($deptId) {
                $query->whereHas('workSheet', function ($q) use ($deptId) {
                    $q->where('department_id', $deptId);
                });
            }
        }

        if ($request->filled('department_id')) {
            $query->whereHas('workSheet', function ($sheetQuery) use ($request) {
                $sheetQuery->where('department_id', $request->query('department_id'));
            });
        }

        if ($request->filled('discipline_id')) {
            $query->whereHas('tasks', function ($taskQuery) use ($request) {
                $taskQuery->where('discipline_id', $request->query('discipline_id'));
            });
        }

        if ($dateFrom || $dateTo) {
            $query->whereHas('tasks', function ($taskQuery) use ($dateFrom, $dateTo) {
                if ($dateFrom) {
                    $taskQuery->whereDate('date', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $taskQuery->whereDate('date', '<=', $dateTo);
                }
            });
        }

        $departments = Department::orderBy('name')->pluck('name', 'id')->toArray();
        $disciplines = Discipline::orderBy('name')->pluck('name', 'id')->toArray();

        $workOrders = $query->get();

        $weekOptions = collect($workOrders)
            ->flatMap(fn($order) => $order->tasks->pluck('date')->filter())
            ->map(function ($date) {
                $taskDate = Carbon::parse($date, 'America/Caracas');
                $dayOfWeek = (int) $taskDate->format('N');
                if ($dayOfWeek >= 4) {
                    $weekStart = $taskDate->copy()->subDays($dayOfWeek - 4);
                } else {
                    $weekStart = $taskDate->copy()->subDays($dayOfWeek + 3);
                }
                $weekEnd = $weekStart->copy()->addDays(6);
                $weekNumber = (int) $weekStart->format('W');
                return [
                    'value' => $weekStart->toDateString(),
                    'start' => $weekStart->toDateString(),
                    'end' => $weekEnd->toDateString(),
                    'label' => 'Semana ' . $weekNumber . ' — ' . $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m'),
                ];
            })
            ->unique('value')
            ->sortByDesc('value')
            ->values()
            ->all();

        $departmentID = auth()->user()->department_id ?? null;
        $dapartmentName = $departmentID ? DB::table('departments')->where('id', $departmentID)->value('name') : 'N/A';
       
        return view('workorders.historial')
            ->with('workOrders', $workOrders)
            ->with('departmentName', $dapartmentName)
            ->with('weekOptions', $weekOptions)
            ->with('departmentOptions', $departments)
            ->with('disciplineOptions', $disciplines)
            ->with('dateFrom', $dateFrom)
            ->with('dateTo', $dateTo)
            ->with('weekFilter', $weekFilter);
    }

    public function supervisorWorkOrders(Request $request)
    {
        return $this->historial($request);
    }

    public function historialPdf(Request $request)
    {
        $status = $request->query('status');
        $dateFrom = $request->query('dateFrom');
        $dateTo = $request->query('dateTo');
        $search = $request->query('search');

        $query = WorkOrder::with([
                'tasks' => function ($taskQuery) {
                    $taskQuery->orderBy('date', 'asc')->orderBy('time_start', 'asc');
                },
                'installation',
                'equipment',
                'workSheet',
                'tasks.discipline',
                'tasks.evidences',
            ])
            ->orderByRaw('(select max(date) from order_tasks where order_tasks.work_order_id = work_orders.id) desc');

        if (auth()->user() && auth()->user()->role === 'supervisor') {
            $deptId = auth()->user()->department_id;
            if ($deptId) {
                $query->whereHas('workSheet', function ($q) use ($deptId) {
                    $q->where('department_id', $deptId);
                });
            }
        }

        if ($request->filled('department_id')) {
            $query->whereHas('workSheet', function ($sheetQuery) use ($request) {
                $sheetQuery->where('department_id', $request->query('department_id'));
            });
        }

        if ($request->filled('discipline_id')) {
            $query->whereHas('tasks', function ($taskQuery) use ($request) {
                $taskQuery->where('discipline_id', $request->query('discipline_id'));
            });
        }

        if ($status && $status !== 'ALL') {
            $query->whereHas('tasks', function ($taskQuery) use ($status) {
                $taskQuery->where('order_tasks.status', $status);
            });
        }

        if ($dateFrom || $dateTo) {
            $query->whereHas('tasks', function ($taskQuery) use ($dateFrom, $dateTo) {
                if ($dateFrom) {
                    $taskQuery->whereDate('date', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $taskQuery->whereDate('date', '<=', $dateTo);
                }
            });
        }

        if ($search) {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('odm_number', 'like', "%{$search}%")
                    ->orWhere('accion_requerida', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhereHas('installation', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('equipment', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $workOrders = $query->get();

        $departmentNameFilter = null;
        $disciplineNameFilter = null;

        if ($request->filled('department_id')) {
            $departmentNameFilter = Department::where('id', $request->query('department_id'))->value('name');
        }

        if ($request->filled('discipline_id')) {
            $disciplineNameFilter = Discipline::where('id', $request->query('discipline_id'))->value('name');
        }

        $pdf = Pdf::loadView('workorders.pdf', compact('workOrders', 'status', 'dateFrom', 'dateTo', 'search', 'departmentNameFilter', 'disciplineNameFilter'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('historial.pdf');
    }

    public function show($id)
    {
        // Lógica para mostrar los detalles de una orden de trabajo específica
    }


}
