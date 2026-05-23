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
        $users = User::with(['department', 'discipline'])->get();
        $departments_with_disciplines = Department::with('disciplines')->get();

        return view('users.index')->with('users', $users)
            ->with('departments', Department::all())
            ->with('departments_with_disciplines', $departments_with_disciplines);
    }

    public function create()
    {
        $departments_with_disciplines = Department::with('disciplines')->get();
        return view('users.create')->with('departments_with_disciplines', $departments_with_disciplines);
    }
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $departments_with_disciplines = Department::with('disciplines')->get();
        return view('users.edit')
            ->with('user', $user)
            ->with('departments_with_disciplines', $departments_with_disciplines);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'discipline_id' => 'nullable|exists:disciplines,id',
            'password' => 'nullable|string|min:6',
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->department_id = $request->department_id ?: null;
        $user->discipline_id = $request->discipline_id ?: null;
        if ($request->filled('password')) {
            $user->password = $request->password; // casted in model
        }
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'discipline_id' => 'nullable|exists:disciplines,id',
            
        ]);

        $password = 'admin123'; 

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'department_id' => $request->department_id ?: null,
            'discipline_id' => $request->discipline_id ?: null,
            'password' => $password, // casted in model
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function destroy($id)
    {
        User::destroy($id);
        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
    public function show($id)
    {
        // Lógica para mostrar los detalles de un usuario específico
    }
}
