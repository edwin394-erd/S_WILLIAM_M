<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\OrderTask;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StatsController extends Controller
{
    public function supervisorStats()
    {
        $departmentId = Auth::user()->department_id;
        $departmentName = Department::find($departmentId)->name ?? 'Sin departamento';

        $totalOrders = WorkOrder::join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('disciplines.department_id', $departmentId)
            ->distinct('work_orders.id')
            ->count('work_orders.id');

        $ordersByType = WorkOrder::selectRaw('work_orders.type, count(distinct work_orders.id) as total')
            ->join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('disciplines.department_id', $departmentId)
            ->where('order_tasks.status', 'COMPLETADO')
            ->groupBy('work_orders.type')
            ->orderBy('work_orders.type')
            ->pluck('total', 'type')
            ->toArray();

        $highRiskOrders = WorkOrder::join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('work_orders.is_high_risk', true)
            ->where('disciplines.department_id', $departmentId)
            ->distinct('work_orders.id')
            ->count('work_orders.id');

        $extraPlanOrders = WorkOrder::join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('work_orders.is_extraplan', true)
            ->where('disciplines.department_id', $departmentId)
            ->distinct('work_orders.id')
            ->count('work_orders.id');

        $tasksByStatus = OrderTask::selectRaw('status, count(*) as total')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('disciplines.department_id', $departmentId)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $endDate = Carbon::now()->endOfMonth();
        $startDate = Carbon::now()->subMonths(5)->startOfMonth();

        $completedTasks = OrderTask::selectRaw('disciplines.id as discipline_id, disciplines.name as discipline_name, MONTH(order_tasks.date) as month_number, count(*) as total')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('disciplines.department_id', $departmentId)
            ->where('order_tasks.status', 'COMPLETADO')
            ->whereBetween('order_tasks.date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('disciplines.id', 'disciplines.name', 'month_number')
            ->orderBy('month_number')
            ->get();

        $monthLabels = $this->monthLabels();
        $chartMonths = $this->chartMonths($startDate);
        $chartCategories = $this->chartCategories($startDate, $monthLabels);

        $disciplineData = [];
        foreach ($completedTasks as $task) {
            $disciplineData[$task->discipline_name][$task->month_number] = $task->total;
        }

        $chartSeries = [];
        foreach ($disciplineData as $disciplineName => $monthData) {
            $chartSeries[] = [
                'name' => $disciplineName,
                'data' => array_map(fn ($month) => $monthData[$month] ?? 0, $chartMonths),
            ];
        }

        if (empty($chartSeries)) {
            $chartSeries[] = [
                'name' => 'Sin datos',
                'data' => array_fill(0, count($chartMonths), 0),
            ];
        }

        $ordersByDiscipline = OrderTask::selectRaw('disciplines.id as discipline_id, disciplines.name as discipline_name, count(*) as total')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('disciplines.department_id', $departmentId)
            ->where('order_tasks.status', 'COMPLETADO')
            ->groupBy('disciplines.id', 'disciplines.name')
            ->orderBy('total', 'desc')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->discipline_name => $row->total,
            ])
            ->toArray();

        $completedByMonth = $completedTasks->groupBy('month_number')
            ->map(fn ($rows) => $rows->sum('total'));

        $currentMonth = $chartMonths[count($chartMonths) - 1];
        $previousMonth = $chartMonths[count($chartMonths) - 2];
        $currentMonthCompleted = $completedByMonth[$currentMonth] ?? 0;
        $previousMonthCompleted = $completedByMonth[$previousMonth] ?? 0;

        if ($previousMonthCompleted > 0) {
            $completedPercentage = round((($currentMonthCompleted - $previousMonthCompleted) / $previousMonthCompleted) * 100, 1);
        } elseif ($currentMonthCompleted > 0) {
            $completedPercentage = 100;
        } else {
            $completedPercentage = 0;
        }

        $completedCount = $completedTasks->sum('total');
        $disciplineCount = count($disciplineData);

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
            'departmentName'
        ));
    }

    public function adminStats()
    {
        $totalOrders = WorkOrder::count();

        $ordersByType = WorkOrder::selectRaw('work_orders.type, count(distinct work_orders.id) as total')
            ->join('order_tasks', 'work_orders.id', '=', 'order_tasks.work_order_id')
            ->where('order_tasks.status', 'COMPLETADO')
            ->groupBy('work_orders.type')
            ->orderBy('work_orders.type')
            ->pluck('total', 'type')
            ->toArray();

        $highRiskOrders = WorkOrder::where('is_high_risk', true)->count();
        $extraPlanOrders = WorkOrder::where('is_extraplan', true)->count();
        $tasksByStatus = OrderTask::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $endDate = Carbon::now()->endOfMonth();
        $startDate = Carbon::now()->subMonths(5)->startOfMonth();

        $completedTasks = OrderTask::selectRaw('disciplines.department_id, MONTH(order_tasks.date) as month_number, count(*) as total')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('order_tasks.status', 'COMPLETADO')
            ->whereBetween('order_tasks.date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('disciplines.department_id', 'month_number')
            ->orderBy('month_number')
            ->get();

        $monthLabels = $this->monthLabels();
        $chartMonths = $this->chartMonths($startDate);
        $chartCategories = $this->chartCategories($startDate, $monthLabels);

        $departmentNames = Department::pluck('name', 'id')->toArray();
        $departmentData = [];
        foreach ($completedTasks as $task) {
            $departmentData[$departmentNames[$task->department_id] ?? 'Sin departamento'][$task->month_number] = $task->total;
        }

        $chartSeries = [];
        foreach ($departmentData as $departmentName => $monthData) {
            $chartSeries[] = [
                'name' => $departmentName,
                'data' => array_map(fn ($month) => $monthData[$month] ?? 0, $chartMonths),
            ];
        }

        if (empty($chartSeries)) {
            $chartSeries[] = [
                'name' => 'Sin datos',
                'data' => array_fill(0, count($chartMonths), 0),
            ];
        }

        $ordersByDepartment = OrderTask::selectRaw('disciplines.department_id, count(*) as total')
            ->join('disciplines', 'order_tasks.discipline_id', '=', 'disciplines.id')
            ->where('order_tasks.status', 'COMPLETADO')
            ->groupBy('disciplines.department_id')
            ->orderBy('total', 'desc')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $departmentNames[$row->department_id] ?? 'Sin departamento' => $row->total,
            ])
            ->toArray();

        $completedByMonth = $completedTasks->groupBy('month_number')
            ->map(fn ($rows) => $rows->sum('total'));

        $currentMonth = $chartMonths[count($chartMonths) - 1];
        $previousMonth = $chartMonths[count($chartMonths) - 2];
        $currentMonthCompleted = $completedByMonth[$currentMonth] ?? 0;
        $previousMonthCompleted = $completedByMonth[$previousMonth] ?? 0;

        if ($previousMonthCompleted > 0) {
            $completedPercentage = round((($currentMonthCompleted - $previousMonthCompleted) / $previousMonthCompleted) * 100, 1);
        } elseif ($currentMonthCompleted > 0) {
            $completedPercentage = 100;
        } else {
            $completedPercentage = 0;
        }

        $completedCount = $completedTasks->sum('total');
        $departmentCount = count($departmentData);

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
            'completedPercentage'
        ));
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
