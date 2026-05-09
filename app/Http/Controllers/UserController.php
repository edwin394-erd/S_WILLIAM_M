<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Department;
use App\Models\Discipline;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['department', 'department.disciplines'])->get();
        return view('users.index')->with('users', $users);
    }

    public function create()
    {
        $departments_with_disciplines = Department::with('disciplines')->get();
        return view('users.create')->with('departments_with_disciplines', $departments_with_disciplines);
    }
    public function store(Request $request)
    {
        // Lógica para almacenar un nuevo usuario
    }
    public function show($id)
    {
        // Lógica para mostrar los detalles de un usuario específico
    }
}
