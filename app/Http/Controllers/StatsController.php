<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Discipline;
use App\Models\OrderTask;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class StatsController extends Controller
{
    public function supervisorStats(Request $request)
    {
        $departmentId = $request->query('department_id') ?: Auth::user()->department_id;
        $departmentName = Department::find($departmentId)->name ?? 'Sin departamento';
        $selectedDepartmentId = $departmentId;

        $weekOptions = $this->weekOptions();
        $selectedWeekStart = $request->query('week_start');
        $selectedDisciplineId = $request->query('discipline_id');
        $weekStart = null;
        $weekEnd = null;

        $disciplineOptions = Discipline::where('department_id', $departmentId)
            ->pluck('name', 'id')
            ->toArray();
        $allDisciplineOptions = $disciplineOptions;

        if ($selectedWeekStart) {
            try {
                $weekStart = Carbon::parse($selectedWeekStart)->startOfDay();
                $weekStart = $this->getWeekStart($weekStart);
                $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();
                $selectedWeekStart = $weekStart->toDateString();
            } catch (\Exception $e) {
                $selectedWeekStart = null;
            }
        }

        $weekLabel = $weekStart ? 'Semana ' . $weekStart->format('W') . ' — ' . $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m') : null;
        $conversionLabel = $weekStart ? 'Semana ' . ($weekStart->format('W')) : 'Meses: ' . ($weekStart ? 7 : count($this->chartMonths(Carbon::now()->subMonths(5)->startOfMonth())));

        $applyDateFilter = function ($query) use ($weekStart, $weekEnd) {
            if ($weekStart) {
                $query->whereBetween('order_tasks.date', [$weekStart->toDateString(), $weekEnd->toDateString()]);
            }
        };

        $totalOrdersQuery = WorkOrder::join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('disciplines.department_id', $departmentId)
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId));
        $applyDateFilter($totalOrdersQuery);
        $totalOrders = $totalOrdersQuery->distinct('work_orders.id')->count('work_orders.id');

        $ordersByTypeQuery = WorkOrder::selectRaw('work_orders.type, count(distinct work_orders.id) as total')
            ->join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('disciplines.department_id', $departmentId)
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
            ->where('order_tasks.status', 'COMPLETADO');
        $applyDateFilter($ordersByTypeQuery);
        $ordersByType = $ordersByTypeQuery
            ->groupBy('work_orders.type')
            ->orderBy('work_orders.type')
            ->pluck('total', 'type')
            ->toArray();

        $highRiskOrdersQuery = WorkOrder::join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('work_orders.is_high_risk', true)
            ->where('disciplines.department_id', $departmentId)
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId));
        $applyDateFilter($highRiskOrdersQuery);
        $highRiskOrders = $highRiskOrdersQuery->distinct('work_orders.id')->count('work_orders.id');

        $extraPlanOrdersQuery = WorkOrder::join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('work_orders.is_extraplan', true)
            ->where('disciplines.department_id', $departmentId)
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId));
        $applyDateFilter($extraPlanOrdersQuery);
        $extraPlanOrders = $extraPlanOrdersQuery->distinct('work_orders.id')->count('work_orders.id');

        $tasksByStatusQuery = OrderTask::selectRaw('status, count(*) as total')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('disciplines.department_id', $departmentId)
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId));
        $applyDateFilter($tasksByStatusQuery);
        $tasksByStatus = $tasksByStatusQuery->groupBy('status')->pluck('total', 'status')->toArray();

        $totalTasks = array_sum($tasksByStatus);
        $generalCompletionPercentage = $totalTasks > 0 ? round((($tasksByStatus['COMPLETADO'] ?? 0) / $totalTasks) * 100, 1) : 0;

        $useWeeklyChart = $weekStart !== null;

        if ($useWeeklyChart) {
            $completedTasks = OrderTask::selectRaw('disciplines.id as discipline_id, disciplines.name as discipline_name, DATE(order_tasks.date) as task_date, count(*) as total')
                ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
                ->where('disciplines.department_id', $departmentId)
                ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
                ->where('order_tasks.status', 'COMPLETADO')
                ->whereBetween('order_tasks.date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->groupBy('disciplines.id', 'disciplines.name', 'task_date')
                ->orderBy('task_date')
                ->get();

            $chartDays = $this->chartWeekDays($weekStart);
            $chartCategories = $this->chartWeekCategories($weekStart);
        } else {
            $endDate = Carbon::now()->endOfMonth();
            $startDate = Carbon::now()->subMonths(5)->startOfMonth();

            $completedTasks = OrderTask::selectRaw('disciplines.id as discipline_id, disciplines.name as discipline_name, MONTH(order_tasks.date) as month_number, count(*) as total')
                ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
                ->where('disciplines.department_id', $departmentId)
                ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
                ->where('order_tasks.status', 'COMPLETADO')
                ->whereBetween('order_tasks.date', [$startDate->toDateString(), $endDate->toDateString()])
                ->groupBy('disciplines.id', 'disciplines.name', 'month_number')
                ->orderBy('month_number')
                ->get();

            $monthLabels = $this->monthLabels();
            $chartDays = $this->chartMonths($startDate);
            $chartCategories = $this->chartCategories($startDate, $monthLabels);
        }

        $disciplineData = [];
        foreach ($completedTasks as $task) {
            $key = $useWeeklyChart ? $task->task_date : $task->month_number;
            $disciplineData[$task->discipline_name][$key] = $task->total;
        }

        $chartSeries = [];
        foreach ($disciplineData as $disciplineName => $periodData) {
            $chartSeries[] = [
                'name' => $disciplineName,
                'data' => array_map(fn ($period) => $periodData[$period] ?? 0, $chartDays),
            ];
        }

        if (empty($chartSeries)) {
            $chartSeries[] = [
                'name' => 'Sin datos',
                'data' => array_fill(0, count($chartDays), 0),
            ];
        }

        $ordersByDisciplineQuery = OrderTask::selectRaw('disciplines.id as discipline_id, disciplines.name as discipline_name, count(*) as total')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('disciplines.department_id', $departmentId)
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
            ->where('order_tasks.status', 'COMPLETADO');
        $applyDateFilter($ordersByDisciplineQuery);
        $ordersByDiscipline = $ordersByDisciplineQuery
            ->groupBy('disciplines.id', 'disciplines.name')
            ->orderBy('total', 'desc')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->discipline_name => $row->total,
            ])
            ->toArray();

        $completionByDisciplineData = OrderTask::selectRaw(
                'disciplines.id as discipline_id, disciplines.name as discipline_name, '
                . 'SUM(CASE WHEN order_tasks.status = "COMPLETADO" THEN 1 ELSE 0 END) as completed, '
                . 'COUNT(*) as total'
            )
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('disciplines.department_id', $departmentId)
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
            ->when($weekStart, fn ($q) => $q->whereBetween('order_tasks.date', [$weekStart->toDateString(), $weekEnd->toDateString()]))
            ->groupBy('disciplines.id', 'disciplines.name')
            ->orderBy('disciplines.name')
            ->get();

        $completionByDiscipline = $completionByDisciplineData
            ->mapWithKeys(fn ($row) => [
                $row->discipline_name => $row->total > 0 ? round(($row->completed / $row->total) * 100, 1) : 0,
            ])
            ->toArray();

        $completionByDepartmentTotal = OrderTask::selectRaw(
                'SUM(CASE WHEN order_tasks.status = "COMPLETADO" THEN 1 ELSE 0 END) as completed, '
                . 'COUNT(*) as total'
            )
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('disciplines.department_id', $departmentId)
            ->when($weekStart, fn ($q) => $q->whereBetween('order_tasks.date', [$weekStart->toDateString(), $weekEnd->toDateString()]))
            ->first();

        $completionByDepartment = [
            $departmentName => $completionByDepartmentTotal ? ($completionByDepartmentTotal->total > 0 ? round(($completionByDepartmentTotal->completed / $completionByDepartmentTotal->total) * 100, 1) : 0) : 0,
        ];

        $completedByPeriod = $completedTasks->groupBy($useWeeklyChart ? 'task_date' : 'month_number')
            ->map(fn ($rows) => $rows->sum('total'));

        if ($useWeeklyChart) {
            $currentPeriodCompleted = $completedByPeriod[$chartDays[count($chartDays) - 1]] ?? 0;
            $previousPeriodCompleted = $completedByPeriod[$chartDays[count($chartDays) - 2]] ?? 0;
        } else {
            $currentPeriodCompleted = $completedByPeriod[$chartDays[count($chartDays) - 1]] ?? 0;
            $previousPeriodCompleted = $completedByPeriod[$chartDays[count($chartDays) - 2]] ?? 0;
        }

        if ($previousPeriodCompleted > 0) {
            $completedPercentage = round((($currentPeriodCompleted - $previousPeriodCompleted) / $previousPeriodCompleted) * 100, 1);
        } elseif ($currentPeriodCompleted > 0) {
            $completedPercentage = 100;
        } else {
            $completedPercentage = 0;
        }

        $completedCount = $completedTasks->sum('total');
        $disciplineCount = count($disciplineData);

        if ($request->wantsJson()) {
            return response()->json([
                'completedOrders' => $tasksByStatus['COMPLETADO'] ?? 0,
                'pendingOrders' => $tasksByStatus['PENDIENTE'] ?? 0,
                'reviewOrders' => $tasksByStatus['POR REVISION'] ?? 0,
                'notCompletedOrders' => $tasksByStatus['NO COMPLETADO'] ?? 0,
                'chartSeries' => $chartSeries,
                'chartCategories' => $chartCategories,
                'ordersByType' => $ordersByType,
                'ordersByName' => $ordersByDiscipline,
                'completionByDepartment' => $completionByDepartment,
                'completionByDiscipline' => $completionByDiscipline,
                'generalCompletionPercentage' => $generalCompletionPercentage,
            ]);
        }

        return view('stats', compact(
            'totalOrders',
            'ordersByType',
            'highRiskOrders',
            'extraPlanOrders',
            'tasksByStatus',
            'chartSeries',
            'chartCategories',
            'completedCount',
            'disciplineCount',
            'ordersByDiscipline',
            'completedPercentage',
            'generalCompletionPercentage',
            'departmentName',
            'weekOptions',
            'selectedWeekStart',
            'selectedDepartmentId',
            'selectedDisciplineId',
            'disciplineOptions',
            'completionByDepartment',
            'completionByDiscipline',
            'conversionLabel'
        ));
    }

    public function adminStats(Request $request)
    {
        $weekOptions = $this->weekOptions();
        $selectedWeekStart = $request->query('week_start');
        $selectedDepartmentId = $request->query('department_id');
        $selectedDisciplineId = $request->query('discipline_id');
        $weekStart = null;
        $weekEnd = null;

        $departmentOptions = Department::pluck('name', 'id')->toArray();
        $disciplinesByDepartment = Discipline::orderBy('name')
            ->get()
            ->groupBy('department_id')
            ->map(fn ($rows) => $rows->pluck('name', 'id')->toArray())
            ->toArray();
        $allDisciplineOptions = Discipline::pluck('name', 'id')->toArray();
        $disciplineOptions = $selectedDepartmentId
            ? Discipline::where('department_id', $selectedDepartmentId)->pluck('name', 'id')->toArray()
            : $allDisciplineOptions;

        if ($selectedWeekStart) {
            try {
                $weekStart = Carbon::parse($selectedWeekStart)->startOfDay();
                $weekStart = $this->getWeekStart($weekStart);
                $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();
                $selectedWeekStart = $weekStart->toDateString();
            } catch (\Exception $e) {
                $selectedWeekStart = null;
            }
        }

        $weekLabel = $weekStart ? 'Semana ' . $weekStart->format('W') . ' — ' . $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m') : null;
        $conversionLabel = $weekStart ? 'Semana ' . ($weekStart->format('W')) : 'Meses: ' . ($weekStart ? 7 : count($this->chartMonths(Carbon::now()->subMonths(5)->startOfMonth())));

        $applyDateFilter = function ($query) use ($weekStart, $weekEnd) {
            if ($weekStart) {
                $query->whereBetween('order_tasks.date', [$weekStart->toDateString(), $weekEnd->toDateString()]);
            }
        };

        $totalOrdersQuery = WorkOrder::query();
        if ($weekStart || $selectedDepartmentId || $selectedDisciplineId) {
            $totalOrdersQuery = WorkOrder::join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
                ->when($weekStart, fn ($q) => $q->whereBetween('order_tasks.date', [$weekStart->toDateString(), $weekEnd->toDateString()]))
                ->when($selectedDepartmentId, fn ($q) => $q->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
                    ->where('disciplines.department_id', $selectedDepartmentId))
                ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId));
        }
        $totalOrders = $totalOrdersQuery->distinct('work_orders.id')->count('work_orders.id');

        $ordersByTypeQuery = WorkOrder::selectRaw('work_orders.type, count(distinct work_orders.id) as total')
            ->join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
            ->when($selectedDepartmentId, fn ($q) => $q->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
                ->where('disciplines.department_id', $selectedDepartmentId))
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
            ->where('order_tasks.status', 'COMPLETADO');
        $applyDateFilter($ordersByTypeQuery);
        $ordersByType = $ordersByTypeQuery
            ->groupBy('work_orders.type')
            ->orderBy('work_orders.type')
            ->pluck('total', 'type')
            ->toArray();

        $highRiskOrdersQuery = WorkOrder::where('is_high_risk', true);
        if ($weekStart || $selectedDepartmentId || $selectedDisciplineId) {
            $highRiskOrdersQuery = WorkOrder::join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
                ->where('work_orders.is_high_risk', true)
                ->when($weekStart, fn ($q) => $q->whereBetween('order_tasks.date', [$weekStart->toDateString(), $weekEnd->toDateString()]))
                ->when($selectedDepartmentId, fn ($q) => $q->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
                    ->where('disciplines.department_id', $selectedDepartmentId))
                ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId));
            $highRiskOrders = $highRiskOrdersQuery->distinct('work_orders.id')->count('work_orders.id');
        } else {
            $highRiskOrders = $highRiskOrdersQuery->count();
        }

        $extraPlanOrdersQuery = WorkOrder::where('is_extraplan', true);
        if ($weekStart || $selectedDepartmentId || $selectedDisciplineId) {
            $extraPlanOrdersQuery = WorkOrder::join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
                ->where('work_orders.is_extraplan', true)
                ->when($weekStart, fn ($q) => $q->whereBetween('order_tasks.date', [$weekStart->toDateString(), $weekEnd->toDateString()]))
                ->when($selectedDepartmentId, fn ($q) => $q->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
                    ->where('disciplines.department_id', $selectedDepartmentId))
                ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId));
            $extraPlanOrders = $extraPlanOrdersQuery->distinct('work_orders.id')->count('work_orders.id');
        } else {
            $extraPlanOrders = $extraPlanOrdersQuery->count();
        }

        $tasksByStatusQuery = OrderTask::query()
            ->when($selectedDepartmentId, fn ($q) => $q->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
                ->where('disciplines.department_id', $selectedDepartmentId))
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId));
        $applyDateFilter($tasksByStatusQuery);
        $tasksByStatus = $tasksByStatusQuery->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $totalTasks = array_sum($tasksByStatus);
        $generalCompletionPercentage = $totalTasks > 0 ? round((($tasksByStatus['COMPLETADO'] ?? 0) / $totalTasks) * 100, 1) : 0;

        $useWeeklyChart = $weekStart !== null;

        if ($useWeeklyChart) {
            $completedTasks = OrderTask::selectRaw('disciplines.department_id, DATE(order_tasks.date) as task_date, count(*) as total')
                ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
                ->when($selectedDepartmentId, fn ($q) => $q->where('disciplines.department_id', $selectedDepartmentId))
                ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
                ->where('order_tasks.status', 'COMPLETADO')
                ->whereBetween('order_tasks.date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->groupBy('disciplines.department_id', 'task_date')
                ->orderBy('task_date')
                ->get();

            $chartDays = $this->chartWeekDays($weekStart);
            $chartCategories = $this->chartWeekCategories($weekStart);
        } else {
            $endDate = Carbon::now()->endOfMonth();
            $startDate = Carbon::now()->subMonths(5)->startOfMonth();

            $completedTasks = OrderTask::selectRaw('disciplines.department_id, MONTH(order_tasks.date) as month_number, count(*) as total')
                ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
                ->when($selectedDepartmentId, fn ($q) => $q->where('disciplines.department_id', $selectedDepartmentId))
                ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
                ->where('order_tasks.status', 'COMPLETADO')
                ->whereBetween('order_tasks.date', [$startDate->toDateString(), $endDate->toDateString()])
                ->groupBy('disciplines.department_id', 'month_number')
                ->orderBy('month_number')
                ->get();

            $departmentNames = Department::pluck('name', 'id')->toArray();
            $chartDays = $this->chartMonths($startDate);
            $chartCategories = $this->chartCategories($startDate, $this->monthLabels());
        }

        $departmentNames = Department::pluck('name', 'id')->toArray();
        $departmentData = [];
        foreach ($completedTasks as $task) {
            $key = $useWeeklyChart ? $task->task_date : $task->month_number;
            $departmentData[$departmentNames[$task->department_id] ?? 'Sin departamento'][$key] = $task->total;
        }

        $chartSeries = [];
        foreach ($departmentData as $departmentName => $periodData) {
            $chartSeries[] = [
                'name' => $departmentName,
                'data' => array_map(fn ($period) => $periodData[$period] ?? 0, $chartDays),
            ];
        }

        if (empty($chartSeries)) {
            $chartSeries[] = [
                'name' => 'Sin datos',
                'data' => array_fill(0, count($chartDays), 0),
            ];
        }

        $ordersByDepartmentQuery = OrderTask::selectRaw('disciplines.department_id, count(*) as total')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->when($selectedDepartmentId, fn ($q) => $q->where('disciplines.department_id', $selectedDepartmentId))
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
            ->where('order_tasks.status', 'COMPLETADO');
        $applyDateFilter($ordersByDepartmentQuery);

        $ordersByDepartmentRows = $ordersByDepartmentQuery
            ->groupBy('disciplines.department_id')
            ->orderBy('total', 'desc')
            ->get();

        $ordersByDepartment = $ordersByDepartmentRows
            ->mapWithKeys(fn ($row) => [
                $departmentNames[$row->department_id] ?? 'Sin departamento' => $row->total,
            ])
            ->toArray();

        $ordersByDepartmentLinks = $ordersByDepartmentRows
            ->map(fn ($row) => [
                'id' => $row->department_id,
                'name' => $departmentNames[$row->department_id] ?? 'Sin departamento',
                'count' => $row->total,
                'url' => route('admin.stats', array_filter(['week_start' => $selectedWeekStart, 'department_id' => $row->department_id])),
            ])
            ->toArray();

        $ordersByDisciplineQuery = OrderTask::selectRaw('disciplines.id as discipline_id, disciplines.name as discipline_name, count(*) as total')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->when($selectedDepartmentId, fn ($q) => $q->where('disciplines.department_id', $selectedDepartmentId))
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
            ->where('order_tasks.status', 'COMPLETADO');
        $applyDateFilter($ordersByDisciplineQuery);
        $ordersByDiscipline = $ordersByDisciplineQuery
            ->groupBy('disciplines.id', 'disciplines.name')
            ->orderBy('total', 'desc')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->discipline_name => $row->total,
            ])
            ->toArray();

        $completionByDepartmentData = OrderTask::selectRaw(
                'disciplines.department_id, departments.name as department_name, '
                . 'SUM(CASE WHEN order_tasks.status = "COMPLETADO" THEN 1 ELSE 0 END) as completed, '
                . 'COUNT(*) as total'
            )
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->join('departments', 'disciplines.department_id', '=', 'departments.id')
            ->when($selectedDepartmentId, fn ($q) => $q->where('disciplines.department_id', $selectedDepartmentId))
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
            ->when($weekStart, fn ($q) => $q->whereBetween('order_tasks.date', [$weekStart->toDateString(), $weekEnd->toDateString()]))
            ->groupBy('disciplines.department_id', 'departments.name')
            ->orderBy('departments.name')
            ->get();

        $completionByDepartment = $completionByDepartmentData
            ->mapWithKeys(fn ($row) => [
                $row->department_name => $row->total > 0 ? round(($row->completed / $row->total) * 100, 1) : 0,
            ])
            ->toArray();

        $ordersByDepartmentCompletion = $completionByDepartment;

        $completionByDisciplineData = OrderTask::selectRaw(
                'disciplines.id as discipline_id, disciplines.name as discipline_name, '
                . 'SUM(CASE WHEN order_tasks.status = "COMPLETADO" THEN 1 ELSE 0 END) as completed, '
                . 'COUNT(*) as total'
            )
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->when($selectedDepartmentId, fn ($q) => $q->where('disciplines.department_id', $selectedDepartmentId))
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
            ->when($weekStart, fn ($q) => $q->whereBetween('order_tasks.date', [$weekStart->toDateString(), $weekEnd->toDateString()]))
            ->groupBy('disciplines.id', 'disciplines.name')
            ->orderBy('disciplines.name')
            ->get();

        $completionByDiscipline = $completionByDisciplineData
            ->mapWithKeys(fn ($row) => [
                $row->discipline_name => $row->total > 0 ? round(($row->completed / $row->total) * 100, 1) : 0,
            ])
            ->toArray();

        $completedByPeriod = $completedTasks->groupBy($useWeeklyChart ? 'task_date' : 'month_number')
            ->map(fn ($rows) => $rows->sum('total'));

        $currentPeriodCompleted = $completedByPeriod[$chartDays[count($chartDays) - 1]] ?? 0;
        $previousPeriodCompleted = $completedByPeriod[$chartDays[count($chartDays) - 2]] ?? 0;

        if ($previousPeriodCompleted > 0) {
            $completedPercentage = round((($currentPeriodCompleted - $previousPeriodCompleted) / $previousPeriodCompleted) * 100, 1);
        } elseif ($currentPeriodCompleted > 0) {
            $completedPercentage = 100;
        } else {
            $completedPercentage = 0;
        }

        $completedCount = $completedTasks->sum('total');
        $departmentCount = count($departmentData);

        if ($request->wantsJson()) {
            return response()->json([
                'completedOrders' => $tasksByStatus['COMPLETADO'] ?? 0,
                'pendingOrders' => $tasksByStatus['PENDIENTE'] ?? 0,
                'reviewOrders' => $tasksByStatus['POR REVISION'] ?? 0,
                'notCompletedOrders' => $tasksByStatus['NO COMPLETADO'] ?? 0,
                'chartSeries' => $chartSeries,
                'chartCategories' => $chartCategories,
                'ordersByType' => $ordersByType,
                'ordersByName' => $selectedDepartmentId ? $ordersByDiscipline : $ordersByDepartment,
                'ordersByNameCompletion' => $selectedDepartmentId ? $completionByDiscipline : $ordersByDepartmentCompletion,
                'ordersByNameLinks' => $selectedDepartmentId ? [] : $ordersByDepartmentLinks,
                'ordersByDepartmentCompletion' => $ordersByDepartmentCompletion,
                'completionByDepartment' => $completionByDepartment,
                'completionByDiscipline' => $completionByDiscipline,
                'generalCompletionPercentage' => $generalCompletionPercentage,
            ]);
        }

        return view('stats', compact(
            'totalOrders',
            'ordersByType',
            'highRiskOrders',
            'extraPlanOrders',
            'tasksByStatus',
            'chartSeries',
            'chartCategories',
            'completedCount',
            'departmentCount',
            'ordersByDepartment',
            'ordersByDepartmentLinks',
            'ordersByDepartmentCompletion',
            'ordersByDiscipline',
            'completedPercentage',
            'generalCompletionPercentage',
            'completionByDepartment',
            'completionByDiscipline',
            'weekOptions',
            'selectedWeekStart',
            'selectedDepartmentId',
            'selectedDisciplineId',
            'departmentOptions',
            'disciplineOptions',
            'disciplinesByDepartment',
            'allDisciplineOptions',
            'conversionLabel'
        ));
    }

    public function adminStatsPdf(Request $request)
    {
        $data = $this->buildAdminStatsPdfData($request);
        $pdf = Pdf::loadView('stats.pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->stream('estadisticas.pdf');
    }

    public function supervisorStatsPdf(Request $request)
    {

        $data = $this->buildSupervisorStatsPdfData($request);
        $pdf = Pdf::loadView('stats.pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->stream('estadisticas.pdf');
    }

    private function buildAdminStatsPdfData(Request $request): array
    {
        $weekOptions = $this->weekOptions();
        $selectedWeekStart = $request->query('week_start');
        $selectedDepartmentId = $request->query('department_id');
        $selectedDisciplineId = $request->query('discipline_id');
        $weekStart = null;
        $weekEnd = null;
        $dateFrom = $request->query('dateFrom');
        $dateTo = $request->query('dateTo');

        $departmentOptions = Department::pluck('name', 'id')->toArray();
        $disciplinesByDepartment = Discipline::orderBy('name')
            ->get()
            ->groupBy('department_id')
            ->map(fn ($rows) => $rows->pluck('name', 'id')->toArray())
            ->toArray();
        $allDisciplineOptions = Discipline::pluck('name', 'id')->toArray();
        $disciplineOptions = $selectedDepartmentId
            ? Discipline::where('department_id', $selectedDepartmentId)->pluck('name', 'id')->toArray()
            : $allDisciplineOptions;

        if ($selectedWeekStart) {
            try {
                $weekStart = Carbon::parse($selectedWeekStart)->startOfDay();
                $weekStart = $this->getWeekStart($weekStart);
                $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();
                $selectedWeekStart = $weekStart->toDateString();
                $dateFrom = $dateFrom ?: $weekStart->toDateString();
                $dateTo = $dateTo ?: $weekEnd->toDateString();
            } catch (\Exception $e) {
                $selectedWeekStart = null;
            }
        }

        $weekLabel = $selectedWeekStart
            ? collect($weekOptions)->first(fn($week) => $week['value'] === $selectedWeekStart)['label'] ?? $selectedWeekStart
            : 'Todas las semanas';

        $selectedDepartmentName = 'Todos los departamentos';
        if ($selectedDepartmentId) {
            $selectedDepartmentName = Department::where('id', $selectedDepartmentId)->value('name');
        } elseif ($selectedDisciplineId) {
            $selectedDepartmentName = Discipline::find($selectedDisciplineId)?->department?->name ?? 'Sin departamento';
        }

        $selectedDisciplineName = $selectedDisciplineId
            ? Discipline::where('id', $selectedDisciplineId)->value('name')
            : 'Todas las disciplinas';

        $applyDateFilter = function ($query) use ($dateFrom, $dateTo, $weekStart, $weekEnd) {
            if ($dateFrom || $dateTo) {
                if ($dateFrom) {
                    $query->whereDate('order_tasks.date', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $query->whereDate('order_tasks.date', '<=', $dateTo);
                }
                return;
            }

            if ($weekStart) {
                $query->whereBetween('order_tasks.date', [$weekStart->toDateString(), $weekEnd->toDateString()]);
            }
        };

        $totalOrdersQuery = WorkOrder::query();
        if ($selectedWeekStart || $selectedDepartmentId || $selectedDisciplineId || $dateFrom || $dateTo) {
            $totalOrdersQuery = WorkOrder::join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
                ->when($selectedDepartmentId, fn ($q) => $q->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
                    ->where('disciplines.department_id', $selectedDepartmentId))
                ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId));
            $applyDateFilter($totalOrdersQuery);
        }
        $totalOrders = $totalOrdersQuery->distinct('work_orders.id')->count('work_orders.id');

        $ordersByTypeQuery = WorkOrder::selectRaw('work_orders.type, count(distinct work_orders.id) as total')
            ->join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
            ->when($selectedDepartmentId, fn ($q) => $q->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
                ->where('disciplines.department_id', $selectedDepartmentId))
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
            ->where('order_tasks.status', 'COMPLETADO');
        $applyDateFilter($ordersByTypeQuery);
        $ordersByType = $ordersByTypeQuery
            ->groupBy('work_orders.type')
            ->orderBy('work_orders.type')
            ->pluck('total', 'type')
            ->toArray();

        $highRiskOrdersQuery = WorkOrder::join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('work_orders.is_high_risk', true)
            ->when($selectedDepartmentId, fn ($q) => $q->where('disciplines.department_id', $selectedDepartmentId))
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId));
        $applyDateFilter($highRiskOrdersQuery);
        $highRiskOrders = $highRiskOrdersQuery->distinct('work_orders.id')->count('work_orders.id');

        $extraPlanOrdersQuery = WorkOrder::join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('work_orders.is_extraplan', true)
            ->when($selectedDepartmentId, fn ($q) => $q->where('disciplines.department_id', $selectedDepartmentId))
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId));
        $applyDateFilter($extraPlanOrdersQuery);
        $extraPlanOrders = $extraPlanOrdersQuery->distinct('work_orders.id')->count('work_orders.id');

        $tasksByStatusQuery = OrderTask::selectRaw('status, count(*) as total')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->when($selectedDepartmentId, fn ($q) => $q->where('disciplines.department_id', $selectedDepartmentId))
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId));
        $applyDateFilter($tasksByStatusQuery);
        $tasksByStatus = $tasksByStatusQuery->groupBy('status')->pluck('total', 'status')->toArray();

        $totalTasks = array_sum($tasksByStatus);
        $generalCompletionPercentage = $totalTasks > 0 ? round((($tasksByStatus['COMPLETADO'] ?? 0) / $totalTasks) * 100, 1) : 0;

        $useWeeklyChart = $weekStart !== null;

        if ($useWeeklyChart) {
            $completedTasks = OrderTask::selectRaw('disciplines.department_id, disciplines.id as discipline_id, disciplines.name as discipline_name, DATE(order_tasks.date) as task_date, count(*) as total')
                ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
                ->when($selectedDepartmentId, fn ($q) => $q->where('disciplines.department_id', $selectedDepartmentId))
                ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
                ->where('order_tasks.status', 'COMPLETADO')
                ->whereBetween('order_tasks.date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->groupBy('disciplines.department_id', 'disciplines.id', 'discipline_name', 'task_date')
                ->orderBy('task_date')
                ->get();

            $chartDays = $this->chartWeekDays($weekStart);
            $chartCategories = $this->chartWeekCategories($weekStart);
        } else {
            $endDate = Carbon::now()->endOfMonth();
            $startDate = Carbon::now()->subMonths(5)->startOfMonth();

            $completedTasks = OrderTask::selectRaw('disciplines.department_id, disciplines.id as discipline_id, disciplines.name as discipline_name, MONTH(order_tasks.date) as month_number, count(*) as total')
                ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
                ->when($selectedDepartmentId, fn ($q) => $q->where('disciplines.department_id', $selectedDepartmentId))
                ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
                ->where('order_tasks.status', 'COMPLETADO')
                ->whereBetween('order_tasks.date', [$startDate->toDateString(), $endDate->toDateString()])
                ->groupBy('disciplines.department_id', 'disciplines.id', 'discipline_name', 'month_number')
                ->orderBy('month_number')
                ->get();

            $chartDays = $this->chartMonths($startDate);
            $chartCategories = $this->chartCategories($startDate, $this->monthLabels());
        }

        $departmentNames = Department::pluck('name', 'id')->toArray();
        $departmentData = [];
        foreach ($completedTasks as $task) {
            $key = $useWeeklyChart ? $task->task_date : $task->month_number;
            $departmentData[$departmentNames[$task->department_id] ?? 'Sin departamento'][$key] = $task->total;
        }

        $chartSeries = [];
        foreach ($departmentData as $departmentName => $periodData) {
            $chartSeries[] = [
                'name' => $departmentName,
                'data' => array_map(fn ($period) => $periodData[$period] ?? 0, $chartDays),
            ];
        }

        if (empty($chartSeries)) {
            $chartSeries[] = [
                'name' => 'Sin datos',
                'data' => array_fill(0, count($chartDays), 0),
            ];
        }

        $ordersByDepartmentQuery = OrderTask::selectRaw('disciplines.department_id, count(*) as total')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->when($selectedDepartmentId, fn ($q) => $q->where('disciplines.department_id', $selectedDepartmentId))
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
            ->where('order_tasks.status', 'COMPLETADO');
        $applyDateFilter($ordersByDepartmentQuery);

        $ordersByDepartmentRows = $ordersByDepartmentQuery
            ->groupBy('disciplines.department_id')
            ->orderBy('total', 'desc')
            ->get();

        $ordersByDepartment = $ordersByDepartmentRows
            ->mapWithKeys(fn ($row) => [
                $departmentNames[$row->department_id] ?? 'Sin departamento' => $row->total,
            ])
            ->toArray();

        $ordersByDepartmentCompletion = OrderTask::selectRaw('disciplines.department_id, departments.name as department_name, '
                . 'SUM(CASE WHEN order_tasks.status = "COMPLETADO" THEN 1 ELSE 0 END) as completed, '
                . 'COUNT(*) as total')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->join('departments', 'disciplines.department_id', '=', 'departments.id')
            ->when($selectedDepartmentId, fn ($q) => $q->where('disciplines.department_id', $selectedDepartmentId))
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
            ->when($weekStart, fn ($q) => $q->whereBetween('order_tasks.date', [$weekStart->toDateString(), $weekEnd->toDateString()]))
            ->groupBy('disciplines.department_id', 'departments.name')
            ->orderBy('departments.name')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->department_name => $row->total > 0 ? round(($row->completed / $row->total) * 100, 1) : 0,
            ])
            ->toArray();

        $completionByDepartment = $ordersByDepartmentCompletion;

        $ordersByDisciplineQuery = OrderTask::selectRaw('disciplines.id as discipline_id, disciplines.name as discipline_name, count(*) as total')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->when($selectedDepartmentId, fn ($q) => $q->where('disciplines.department_id', $selectedDepartmentId))
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
            ->where('order_tasks.status', 'COMPLETADO');
        $applyDateFilter($ordersByDisciplineQuery);
        $ordersByDiscipline = $ordersByDisciplineQuery
            ->groupBy('disciplines.id', 'disciplines.name')
            ->orderBy('total', 'desc')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->discipline_name => $row->total,
            ])
            ->toArray();

        $completionByDiscipline = OrderTask::selectRaw('disciplines.id as discipline_id, disciplines.name as discipline_name, '
                . 'SUM(CASE WHEN order_tasks.status = "COMPLETADO" THEN 1 ELSE 0 END) as completed, '
                . 'COUNT(*) as total')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->when($selectedDepartmentId, fn ($q) => $q->where('disciplines.department_id', $selectedDepartmentId))
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
            ->when($weekStart, fn ($q) => $q->whereBetween('order_tasks.date', [$weekStart->toDateString(), $weekEnd->toDateString()]))
            ->groupBy('disciplines.id', 'disciplines.name')
            ->orderBy('disciplines.name')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->discipline_name => $row->total > 0 ? round(($row->completed / $row->total) * 100, 1) : 0,
            ])
            ->toArray();

        $departmentDisciplineReportQuery = Discipline::selectRaw(
                'disciplines.department_id, departments.name as department_name, disciplines.id as discipline_id, disciplines.name as discipline_name, '
                . 'SUM(CASE WHEN order_tasks.status = "COMPLETADO" THEN 1 ELSE 0 END) as completed, '
                . 'COUNT(order_tasks.id) as total'
            )
            ->join('departments', 'disciplines.department_id', '=', 'departments.id')
            ->leftJoin('order_tasks', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->when($selectedDepartmentId, fn ($q) => $q->where('disciplines.department_id', $selectedDepartmentId))
            ->when($selectedDisciplineId, fn ($q) => $q->where('disciplines.id', $selectedDisciplineId));
        $applyDateFilter($departmentDisciplineReportQuery);
        $departmentDisciplineReportRows = $departmentDisciplineReportQuery
            ->groupBy('disciplines.department_id', 'departments.name', 'disciplines.id', 'disciplines.name')
            ->orderBy('departments.name')
            ->orderBy('disciplines.name')
            ->get();

        $departmentDisciplineReport = [];
        foreach ($departmentDisciplineReportRows as $row) {
            $departmentDisciplineReport[$row->department_name][] = [
                'discipline_name' => $row->discipline_name,
                'completed' => $row->completed,
                'completion' => $row->total > 0 ? round(($row->completed / $row->total) * 100, 1) : 0,
            ];
        }

        $ordersByName = $selectedDepartmentId || $selectedDisciplineId ? $ordersByDiscipline : $ordersByDepartment;
        $ordersByNameCompletion = $selectedDepartmentId || $selectedDisciplineId ? $completionByDiscipline : $ordersByDepartmentCompletion;

        return [
            'weekOptions' => $weekOptions,
            'selectedWeekStart' => $selectedWeekStart,
            'selectedWeekLabel' => $weekLabel,
            'selectedDepartmentId' => $selectedDepartmentId,
            'selectedDepartmentName' => $selectedDepartmentName,
            'selectedDisciplineId' => $selectedDisciplineId,
            'selectedDisciplineName' => $selectedDisciplineName,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'departmentOptions' => $departmentOptions,
            'disciplineOptions' => $disciplineOptions,
            'disciplinesByDepartment' => $disciplinesByDepartment,
            'allDisciplineOptions' => $allDisciplineOptions,
            'ordersByType' => $ordersByType,
            'ordersByName' => $ordersByName,
            'ordersByNameCompletion' => $ordersByNameCompletion,
            'departmentDisciplineReport' => $departmentDisciplineReport,
            'ordersByDepartment' => $ordersByDepartment,
            'ordersByDepartmentCompletion' => $ordersByDepartmentCompletion,
            'chartSeries' => $chartSeries,
            'chartCategories' => $chartCategories,
            'tasksByStatus' => $tasksByStatus,
            'generalCompletionPercentage' => $generalCompletionPercentage,
            'isAdmin' => true,
            'departmentName' => null,
            'conversionLabel' => $selectedWeekStart ? 'Semana ' . Carbon::parse($selectedWeekStart)->format('W') : 'Meses',
        ];
    }

    private function buildSupervisorStatsPdfData(Request $request): array
    {
        $departmentId = Auth::user()->department_id;
        $departmentName = Department::where('id', $departmentId)->value('name') ?: 'Sin departamento';

        $weekOptions = $this->weekOptions();
        $selectedWeekStart = $request->query('week_start');
        $selectedDepartmentId = $departmentId;
        $selectedDisciplineId = $request->query('discipline_id');
        $weekStart = null;
        $weekEnd = null;
        $dateFrom = $request->query('dateFrom');
        $dateTo = $request->query('dateTo');

        $disciplineOptions = Discipline::where('department_id', $departmentId)
            ->pluck('name', 'id')
            ->toArray();
        $allDisciplineOptions = $disciplineOptions;

        if ($selectedWeekStart) {
            try {
                $weekStart = Carbon::parse($selectedWeekStart)->startOfDay();
                $weekStart = $this->getWeekStart($weekStart);
                $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();
                $selectedWeekStart = $weekStart->toDateString();
                $dateFrom = $dateFrom ?: $weekStart->toDateString();
                $dateTo = $dateTo ?: $weekEnd->toDateString();
            } catch (\Exception $e) {
                $selectedWeekStart = null;
            }
        }

        $weekLabel = $selectedWeekStart
            ? collect($weekOptions)->first(fn($week) => $week['value'] === $selectedWeekStart)['label'] ?? $selectedWeekStart
            : 'Todas las semanas';

        $selectedDisciplineName = $selectedDisciplineId
            ? Discipline::where('id', $selectedDisciplineId)->value('name')
            : 'Todas las disciplinas';

        $applyDateFilter = function ($query) use ($dateFrom, $dateTo, $weekStart, $weekEnd) {
            if ($dateFrom || $dateTo) {
                if ($dateFrom) {
                    $query->whereDate('order_tasks.date', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $query->whereDate('order_tasks.date', '<=', $dateTo);
                }
                return;
            }

            if ($weekStart) {
                $query->whereBetween('order_tasks.date', [$weekStart->toDateString(), $weekEnd->toDateString()]);
            }
        };

        $totalOrdersQuery = WorkOrder::join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('disciplines.department_id', $departmentId)
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId));
        $applyDateFilter($totalOrdersQuery);
        $totalOrders = $totalOrdersQuery->distinct('work_orders.id')->count('work_orders.id');

        $ordersByTypeQuery = WorkOrder::selectRaw('work_orders.type, count(distinct work_orders.id) as total')
            ->join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('disciplines.department_id', $departmentId)
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
            ->where('order_tasks.status', 'COMPLETADO');
        $applyDateFilter($ordersByTypeQuery);
        $ordersByType = $ordersByTypeQuery
            ->groupBy('work_orders.type')
            ->orderBy('work_orders.type')
            ->pluck('total', 'type')
            ->toArray();

        $highRiskOrdersQuery = WorkOrder::join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('work_orders.is_high_risk', true)
            ->where('disciplines.department_id', $departmentId)
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId));
        $applyDateFilter($highRiskOrdersQuery);
        $highRiskOrders = $highRiskOrdersQuery->distinct('work_orders.id')->count('work_orders.id');

        $extraPlanOrdersQuery = WorkOrder::join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('work_orders.is_extraplan', true)
            ->where('disciplines.department_id', $departmentId)
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId));
        $applyDateFilter($extraPlanOrdersQuery);
        $extraPlanOrders = $extraPlanOrdersQuery->distinct('work_orders.id')->count('work_orders.id');

        $tasksByStatusQuery = OrderTask::selectRaw('status, count(*) as total')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('disciplines.department_id', $departmentId)
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId));
        $applyDateFilter($tasksByStatusQuery);
        $tasksByStatus = $tasksByStatusQuery->groupBy('status')->pluck('total', 'status')->toArray();

        $totalTasks = array_sum($tasksByStatus);
        $generalCompletionPercentage = $totalTasks > 0 ? round((($tasksByStatus['COMPLETADO'] ?? 0) / $totalTasks) * 100, 1) : 0;

        $useWeeklyChart = $weekStart !== null;

        if ($useWeeklyChart) {
            $completedTasks = OrderTask::selectRaw('disciplines.id as discipline_id, disciplines.name as discipline_name, DATE(order_tasks.date) as task_date, count(*) as total')
                ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
                ->where('disciplines.department_id', $departmentId)
                ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
                ->where('order_tasks.status', 'COMPLETADO')
                ->whereBetween('order_tasks.date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->groupBy('disciplines.id', 'disciplines.name', 'task_date')
                ->orderBy('task_date')
                ->get();

            $chartDays = $this->chartWeekDays($weekStart);
            $chartCategories = $this->chartWeekCategories($weekStart);
        } else {
            $endDate = Carbon::now()->endOfMonth();
            $startDate = Carbon::now()->subMonths(5)->startOfMonth();

            $completedTasks = OrderTask::selectRaw('disciplines.id as discipline_id, disciplines.name as discipline_name, MONTH(order_tasks.date) as month_number, count(*) as total')
                ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
                ->where('disciplines.department_id', $departmentId)
                ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
                ->where('order_tasks.status', 'COMPLETADO')
                ->whereBetween('order_tasks.date', [$startDate->toDateString(), $endDate->toDateString()])
                ->groupBy('disciplines.id', 'disciplines.name', 'month_number')
                ->orderBy('month_number')
                ->get();

            $chartDays = $this->chartMonths($startDate);
            $chartCategories = $this->chartCategories($startDate, $this->monthLabels());
        }

        $departmentNames = Department::pluck('name', 'id')->toArray();
        $departmentData = [];
        foreach ($completedTasks as $task) {
            $key = $useWeeklyChart ? $task->task_date : $task->month_number;
            $departmentData[$departmentNames[$task->department_id] ?? 'Sin departamento'][$key] = $task->total;
        }

        $chartSeries = [];
        foreach ($departmentData as $departmentName => $periodData) {
            $chartSeries[] = [
                'name' => $departmentName,
                'data' => array_map(fn ($period) => $periodData[$period] ?? 0, $chartDays),
            ];
        }

        if (empty($chartSeries)) {
            $chartSeries[] = [
                'name' => 'Sin datos',
                'data' => array_fill(0, count($chartDays), 0),
            ];
        }

        $ordersByDisciplineQuery = OrderTask::selectRaw('disciplines.id as discipline_id, disciplines.name as discipline_name, count(*) as total')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('disciplines.department_id', $departmentId)
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
            ->where('order_tasks.status', 'COMPLETADO');
        $applyDateFilter($ordersByDisciplineQuery);
        $ordersByDiscipline = $ordersByDisciplineQuery
            ->groupBy('disciplines.id', 'disciplines.name')
            ->orderBy('total', 'desc')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->discipline_name => $row->total,
            ])
            ->toArray();

        $completionByDiscipline = OrderTask::selectRaw('disciplines.id as discipline_id, disciplines.name as discipline_name, '
                . 'SUM(CASE WHEN order_tasks.status = "COMPLETADO" THEN 1 ELSE 0 END) as completed, '
                . 'COUNT(*) as total')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('disciplines.department_id', $departmentId)
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
            ->when($weekStart, fn ($q) => $q->whereBetween('order_tasks.date', [$weekStart->toDateString(), $weekEnd->toDateString()]))
            ->groupBy('disciplines.id', 'disciplines.name')
            ->orderBy('disciplines.name')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->discipline_name => $row->total > 0 ? round(($row->completed / $row->total) * 100, 1) : 0,
            ])
            ->toArray();

        $ordersByDepartmentQuery = OrderTask::selectRaw('disciplines.department_id, count(*) as total')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('disciplines.department_id', $departmentId)
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
            ->where('order_tasks.status', 'COMPLETADO');
        $applyDateFilter($ordersByDepartmentQuery);
        $ordersByDepartment = $ordersByDepartmentQuery
            ->groupBy('disciplines.department_id')
            ->orderBy('total', 'desc')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $departmentName => $row->total,
            ])
            ->toArray();

        $ordersByDepartmentCompletion = OrderTask::selectRaw('disciplines.department_id, departments.name as department_name, '
                . 'SUM(CASE WHEN order_tasks.status = "COMPLETADO" THEN 1 ELSE 0 END) as completed, '
                . 'COUNT(*) as total')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->join('departments', 'disciplines.department_id', '=', 'departments.id')
            ->where('disciplines.department_id', $departmentId)
            ->when($selectedDisciplineId, fn ($q) => $q->where('order_tasks.discipline_id', $selectedDisciplineId))
            ->when($weekStart, fn ($q) => $q->whereBetween('order_tasks.date', [$weekStart->toDateString(), $weekEnd->toDateString()]))
            ->groupBy('disciplines.department_id', 'departments.name')
            ->orderBy('departments.name')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $departmentName => $row->total > 0 ? round(($row->completed / $row->total) * 100, 1) : 0,
            ])
            ->toArray();

        $departmentDisciplineReportQuery = Discipline::selectRaw(
                'disciplines.department_id, departments.name as department_name, disciplines.id as discipline_id, disciplines.name as discipline_name, '
                . 'SUM(CASE WHEN order_tasks.status = "COMPLETADO" THEN 1 ELSE 0 END) as completed, '
                . 'COUNT(order_tasks.id) as total'
            )
            ->join('departments', 'disciplines.department_id', '=', 'departments.id')
            ->leftJoin('order_tasks', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('disciplines.department_id', $departmentId)
            ->when($selectedDisciplineId, fn ($q) => $q->where('disciplines.id', $selectedDisciplineId));
        $applyDateFilter($departmentDisciplineReportQuery);
        $departmentDisciplineReportRows = $departmentDisciplineReportQuery
            ->groupBy('disciplines.department_id', 'departments.name', 'disciplines.id', 'disciplines.name')
            ->orderBy('departments.name')
            ->orderBy('disciplines.name')
            ->get();

        $departmentDisciplineReport = [];
        foreach ($departmentDisciplineReportRows as $row) {
            $departmentDisciplineReport[$row->department_name][] = [
                'discipline_name' => $row->discipline_name,
                'completed' => $row->completed,
                'completion' => $row->total > 0 ? round(($row->completed / $row->total) * 100, 1) : 0,
            ];
        }

        $ordersByName = $ordersByDiscipline;
        $ordersByNameCompletion = $completionByDiscipline;

        return [
            'weekOptions' => $weekOptions,
            'selectedWeekStart' => $selectedWeekStart,
            'selectedWeekLabel' => $weekLabel,
            'selectedDepartmentId' => $departmentId,
            'selectedDepartmentName' => $departmentName,
            'selectedDisciplineId' => $selectedDisciplineId,
            'selectedDisciplineName' => $selectedDisciplineName,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'departmentOptions' => [],
            'disciplineOptions' => $disciplineOptions,
            'disciplinesByDepartment' => [],
            'allDisciplineOptions' => $allDisciplineOptions,
            'ordersByType' => $ordersByType,
            'ordersByDepartment' => [$departmentName => $tasksByStatus['COMPLETADO'] ?? 0],
            'ordersByDepartmentCompletion' => [$departmentName => $totalTasks > 0 ? round((($tasksByStatus['COMPLETADO'] ?? 0) / $totalTasks) * 100, 1) : 0],
            'ordersByName' => $ordersByName,
            'ordersByNameCompletion' => $ordersByNameCompletion,
            'departmentDisciplineReport' => $departmentDisciplineReport,
            'chartSeries' => $chartSeries,
            'chartCategories' => $chartCategories,
            'tasksByStatus' => $tasksByStatus,
            'generalCompletionPercentage' => $generalCompletionPercentage,
            'isAdmin' => false,
            'selectedDepartmentId' => $departmentId,
            'departmentName' => $departmentName,
            'conversionLabel' => $selectedWeekStart ? 'Semana ' . Carbon::parse($selectedWeekStart)->format('W') : 'Meses',
        ];
    }

    private function getWeekStart(Carbon $date): Carbon
    {
        $weekday = $date->dayOfWeekIso;
        if ($weekday >= 4) {
            return $date->copy()->subDays($weekday - 4)->startOfDay();
        }

        return $date->copy()->subDays($weekday + 3)->startOfDay();
    }

    private function weekOptions(int $weeks = 8): array
    {
        $today = Carbon::now();
        $currentWeekStart = $this->getWeekStart($today);

        return collect(range(0, $weeks - 1))->map(function ($offset) use ($currentWeekStart) {
            $start = $currentWeekStart->copy()->subWeeks($offset);
            $end = $start->copy()->addDays(6);
            $weekNumber = (int) $start->format('W');

            return [
                'value' => $start->toDateString(),
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'label' => 'Semana ' . $weekNumber . ' — ' . $start->format('d/m') . ' - ' . $end->format('d/m'),
            ];
        })->all();
    }

    private function chartWeekDays(Carbon $weekStart): array
    {
        return collect(range(0, 6))
            ->map(fn ($offset) => $weekStart->copy()->addDays($offset)->toDateString())
            ->all();
    }

    private function chartWeekCategories(Carbon $weekStart): array
    {
        $labels = ['Jue', 'Vie', 'Sáb', 'Dom', 'Lun', 'Mar', 'Mié'];

        return collect(range(0, 6))
            ->map(fn ($offset) => $labels[$offset] . ' ' . $weekStart->copy()->addDays($offset)->format('d/m'))
            ->all();
    }

    private function monthLabels(): array
    {
        return [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic',
        ];
    }

    private function chartMonths(Carbon $startDate): array
    {
        return collect(range(0, 5))
            ->map(fn ($offset) => $startDate->copy()->addMonths($offset)->month)
            ->all();
    }

    private function chartCategories(Carbon $startDate, array $monthLabels): array
    {
        return collect(range(0, 5))
            ->map(fn ($offset) => $monthLabels[$startDate->copy()->addMonths($offset)->month])
            ->all();
    }
}
