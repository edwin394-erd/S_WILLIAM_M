<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkSheet;
use App\Models\Department;

class WorkSheetController extends Controller
{
    public function index()
    {
        $worksheets = WorkSheet::all();
        return view('worksheets.index')->with('worksheets', $worksheets);
    }


    public function create()
    {
        $departments = Department::all();
        return view('worksheets.create')->with('departments', $departments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'week_number' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'department_id' => 'required|exists:departments,id',
        ]);
        
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
    ])->findOrFail($id);

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
        // Lógica para eliminar una hoja de trabajo
    }
}
