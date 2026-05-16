<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Discipline;
use App\Models\User;
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
        // Lógica para mostrar el formulario de edición de un departamento
    }

    public function update(Request $request, $id)
    {
        // Lógica para actualizar un departamento existente
    }

    public function destroy($id)
    {
        Department::destroy($id);
        return redirect()->route('admin.departments.index')->with('success', 'Departamento eliminado exitosamente.');
    }
}
