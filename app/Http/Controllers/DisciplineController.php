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

    public function destroy($id)
    {
        Discipline::destroy($id);
        return redirect()->route('admin.disciplines.index')->with('success', 'Disciplina eliminada exitosamente.');
    }
}
