<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckSup;
use App\Http\Middleware\CheckTecnico;
use App\Http\Middleware\CheckPlan;

use App\Http\Controllers\WorkSheetController;
use App\Http\Controllers\OrderTaskController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DisciplineController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\InstallationController;


Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', function () {
     if(auth()->user()){
        return back();
    }
    return view('login');
})->name('login');

Route::post('/login', [LoginController::class, 'store'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');





Route::resource('worksheets', WorkSheetController::class)->names('admin.worksheets');
Route::resource('workorders', WorkOrderController::class)->names('admin.workorders');

Route::middleware(['auth', CheckSup::class])->prefix('supervisor')->group(function () {
    Route::get('/stats', function () {
         $saludo = "HOLA SUPERVISOR";
        return view('stats')->with('saludo', $saludo);
    })->name('supervisor.stats');

});

Route::middleware(['auth', CheckAdmin::class])->prefix('admin')->group(function () {
    Route::get('/stats', function () {
         $saludo = "HOLA ADMIN";
        return view('stats')->with('saludo', $saludo);
    })->name('admin.stats');

    Route::resource('departments', DepartmentController::class)->names('admin.departments');
    Route::resource('users', UserController::class)->names('admin.users');
    Route::resource('equipment', EquipmentController::class)->names('admin.equipment');
    Route::resource('installations', InstallationController::class)->names('admin.installations');
    Route::resource('disciplines', DisciplineController::class)->names('admin.disciplines');


});

// Grupo de rutas para los Supervisores (Solo requiere estar logueado)
Route::middleware(['auth'])->prefix('supervisor')->group(function () {
    Route::get('/tareas', [OrderTaskController::class, 'index']);
    Route::post('/reportar/{task}', [OrderTaskController::class, 'update']);
});
