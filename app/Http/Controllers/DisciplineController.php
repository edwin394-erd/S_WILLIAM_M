<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Discipline;

class DisciplineController extends Controller
{
    public function index()
    {
        $disciplines_department = Discipline::with('department')->get();
        return view('disciplines.index')->with('disciplines_department', $disciplines_department);
    }

    public function edit($id)
    {
        $discipline = Discipline::findOrFail($id);
        $departments = \App\Models\Department::pluck('name','id')->toArray();
        return view('disciplines.edit')->with('discipline', $discipline)->with('departments', $departments);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        $discipline = Discipline::findOrFail($id);
        $discipline->update([
            'name' => $request->name,
            'department_id' => $request->department_id,
        ]);

        return redirect()->route('admin.disciplines.index')->with('success', 'Disciplina actualizada correctamente.');
    }

    public function destroy($id)
    {
        Discipline::destroy($id);
        return redirect()->route('admin.disciplines.index')->with('success', 'Disciplina eliminada exitosamente.');
    }
}
