<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Discipline;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::all();
        return view('departments.index')->with('departments', $departments);
    }

    public function create()
    {
    
        return view('departments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'grupo_telegram_id' => 'nullable|string|max:255',
        ]);

        Department::create([
            'name' => $request->name,
            'grupo_telegram_id' => $request->grupo_telegram_id,
        ]);

        return redirect()->route('admin.departments.index')->with('success', 'Departamento creado exitosamente.');
    }

    public function show($id)
    {
        $department = Department::findOrFail($id);
        $users_department = User::where('department_id', $id)->get();
        $disciplines_department = Discipline::where('department_id', $id)->get();
        return view('departments.show')
            ->with('users_department', $users_department)
            ->with('disciplines_department', $disciplines_department)->with('department', $department);
    }

    public function edit($id)
    {
        $department = Department::findOrFail($id);
        return view('departments.edit')->with('department', $department);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'grupo_telegram_id' => 'nullable|string|max:255',
        ]);

        $department = Department::findOrFail($id);
        $department->update([
            'name' => $request->name,
            'grupo_telegram_id' => $request->grupo_telegram_id,
        ]);

        return redirect()->route('admin.departments.index')->with('success', 'Departamento actualizado correctamente.');
    }

    public function destroy($id)
    {
        Department::destroy($id);
        return redirect()->route('admin.departments.index')->with('success', 'Departamento eliminado exitosamente.');
    }

    public function tablePdf(Request $request)
    {
        $recordsJson = $request->input('records', '[]');
        $columnsJson = $request->input('columns', '[]');

        $records = json_decode($recordsJson, true) ?: [];
        $columns = json_decode($columnsJson, true) ?: (is_array($columnsJson) ? $columnsJson : []);

        $generatedAt = now()->format('d/m/Y H:i');
        $title = 'LISTADO DE DEPARTAMENTOS';

        $pdf = Pdf::loadView('exports.table-pdf', compact('records', 'columns', 'generatedAt', 'title'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('departamentos.pdf');
    }
}
