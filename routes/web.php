<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckSup;
use App\Http\Middleware\CheckTecnico;
use App\Http\Middleware\CheckPlan;
use App\Http\Middleware\CheckPlanAdmin;


use App\Http\Controllers\WorkSheetController;
use App\Http\Controllers\OrderTaskController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DisciplineController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\StatsController;
use App\Models\WorkOrder;
use App\Models\OrderTask;
use App\Models\Department;


Route::get('/', function () {
    if (Auth::check()) {
        $role = Auth::user()->role;

        if ($role === 'admin' || $role === 'planificador') {
            return redirect()->route('admin.stats');
        } elseif ($role === 'supervisor') {
            return redirect()->route('supervisor.stats');
        } elseif ($role === 'tecnico') {
            $disciplineId = Auth::user()->discipline_id; // Asumiendo que el usuario tiene este campo
            return redirect()->route('tecnico.actividades', ['id_disciplina' => $disciplineId]);
        }
    }   else {
        return redirect()->route('login');
    }
})->name('home');

Route::middleware(['guest'])->get('/login', function () {
    return view('login');
})->name('login');


Route::post('/login', [LoginController::class, 'store'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', CheckTecnico::class])->group(function () {
    Route::get('/disciplina/{id_disciplina}/actividades',[ WorkOrderController::class, 'actividades'])->name('tecnico.actividades');
    Route::get('/disciplina/{id_disciplina}/actividades/{work_order}/reportar', [WorkOrderController::class, 'formulario'])->name('tecnico.reportar.formulario');
    Route::post('/disciplina/actividades/{work_order}/reportar', [WorkOrderController::class, 'reportar'])->name('tecnico.reportar');
});




Route::middleware(['auth', CheckPlanAdmin::class])->group(function () {
    Route::resource('worksheets', WorkSheetController::class)->names('admin.worksheets');
    Route::get('worksheets/{worksheet}/pdf', [WorkSheetController::class, 'generatePdf'])->name('admin.worksheets.pdf');
    Route::post('worksheets/{worksheet}/send-telegram', [WorkSheetController::class, 'sendToTelegram'])->name('admin.worksheets.send-telegram');
    Route::get('workorders/schedule-info', [WorkOrderController::class, 'scheduleInfo'])->name('admin.workorders.schedule-info');
    Route::get('workorders/historial', [WorkOrderController::class, 'historial'])->name('admin.workorders.historial');
    Route::get('workorders/historial/pdf', [WorkOrderController::class, 'historialPdf'])->name('admin.workorders.historial.pdf');
    Route::resource('workorders', WorkOrderController::class)->names('admin.workorders');    
});


Route::middleware(['auth', CheckSup::class])->prefix('supervisor')->group(function () {
    Route::get('/stats', [StatsController::class, 'supervisorStats'])->name('supervisor.stats');
    Route::get('/worksheets', [WorkSheetController::class, 'supervisorWorksheets'])->name('supervisor.worksheets');
    Route::get('/worksheets/{worksheet}', [WorkSheetController::class, 'show'])->name('supervisor.worksheets.show');
    Route::get('/worksheets/{worksheet}/pdf', [WorkSheetController::class, 'generatePdf'])->name('supervisor.worksheets.pdf');
    
    // Mover aquí (ANTES de las rutas que usan parámetros o resource)
    Route::get('/workorders/historial', [WorkOrderController::class, 'historial'])->name('supervisor.workorders.historial');
    Route::get('/workorders/historial/pdf', [WorkOrderController::class, 'historialPdf'])->name('supervisor.workorders.historial.pdf');

    Route::get('/disciplina/{id_disciplina}/actividades/{work_order}/reportar', [WorkOrderController::class, 'formulario'])->name('supervisor.reportar.formulario');
    Route::post('/disciplina/actividades/{work_order}/reportar', [WorkOrderController::class, 'reportar'])->name('supervisor.reportar.orden');
    
    Route::get('/workorders', [WorkOrderController::class, 'supervisorWorkOrders'])->name('supervisor.workorders.index');
    Route::get('/workorders/{work_order}', [WorkOrderController::class, 'show'])->name('supervisor.workorders.show');
});

Route::middleware(['auth', CheckPlanAdmin::class])->prefix('admin')->group(function () {
    Route::get('/stats', [StatsController::class, 'adminStats'])->name('admin.stats');

    Route::resource('departments', DepartmentController::class)->names('admin.departments');
    Route::post('departments/table/pdf', [DepartmentController::class, 'tablePdf'])->name('admin.departments.pdf');
    Route::post('users/table/pdf', [UserController::class, 'tablePdf'])->name('admin.users.pdf');
    Route::resource('users', UserController::class)->names('admin.users');
    Route::resource('equipment', EquipmentController::class)->names('admin.equipment');
    Route::post('equipment/table/pdf', [EquipmentController::class, 'tablePdf'])->name('admin.equipment.pdf');
    Route::resource('installations', InstallationController::class)->names('admin.installations');
    Route::post('installations/table/pdf', [InstallationController::class, 'tablePdf'])->name('admin.installations.pdf');
    Route::resource('disciplines', DisciplineController::class)->names('admin.disciplines');
    Route::post('disciplines/table/pdf', [DisciplineController::class, 'tablePdf'])->name('admin.disciplines.pdf');
    

});

// Grupo de rutas para los Supervisores (Solo requiere estar logueado)
Route::middleware(['auth'])->prefix('supervisor')->group(function () {
    Route::get('/tareas', [OrderTaskController::class, 'index']);
    Route::post('/reportar/{task}', [OrderTaskController::class, 'update'])->name('supervisor.reportar');
});

Route::middleware(['auth'])->post('/workorders/{work_order}/complete-closure', [WorkOrderController::class, 'completeClosure'])->name('workorders.complete-closure');
