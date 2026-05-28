<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Discipline;
use Barryvdh\DomPDF\Facade\Pdf;

class DisciplineController extends Controller
{
    public function index()
    {
        $disciplines_department = Discipline::with('department')->get();
        return view('disciplines.index')->with('disciplines_department', $disciplines_department);
    }

    public function create()
    {
        $departments = \App\Models\Department::pluck('name','id')->toArray();
        return view('disciplines.create')->with('departments', $departments);
    }

    public function edit($id)
    {
        $discipline = Discipline::findOrFail($id);
        $departments = \App\Models\Department::pluck('name','id')->toArray();
        return view('disciplines.edit')->with('discipline', $discipline)->with('departments', $departments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        Discipline::create([
            'name' => $request->name,
            'department_id' => $request->department_id,
        ]);

        return redirect()->route('admin.disciplines.index')->with('success', 'Disciplina creada correctamente.');
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
        $discipline = Discipline::withCount(['users', 'tasks'])->findOrFail($id);

        if ($discipline->users_count > 0 || $discipline->tasks_count > 0) {
            $message = 'No se puede eliminar la disciplina porque tiene ';
            $parts = [];

            if ($discipline->users_count > 0) {
                $parts[] = 'usuarios asignados';
            }
            if ($discipline->tasks_count > 0) {
                $parts[] = 'tareas asociadas';
            }

            $message .= implode(' y ', $parts) . '.';

            return redirect()->route('admin.disciplines.index')->with('error', $message);
        }

        $discipline->delete();

        return redirect()->route('admin.disciplines.index')->with('success', 'Disciplina eliminada exitosamente.');
    }

    public function tablePdf(Request $request)
    {
        $recordsJson = $request->input('records', '[]');
        $columnsJson = $request->input('columns', '[]');

        $records = json_decode($recordsJson, true) ?: [];
        $columns = json_decode($columnsJson, true) ?: (is_array($columnsJson) ? $columnsJson : []);

        $generatedAt = now()->format('d/m/Y H:i');
        $title = 'LISTADO DE DISCIPLINAS';

        $pdf = Pdf::loadView('exports.table-pdf', compact('records', 'columns', 'generatedAt', 'title'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('disciplinas.pdf');
    }
}
