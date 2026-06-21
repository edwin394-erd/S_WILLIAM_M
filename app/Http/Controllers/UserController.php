<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Department;
use App\Models\Discipline;
use App\Services\AuditLogService;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['department', 'discipline', 'disciplines'])->get();
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
        $user = User::with('disciplines')->findOrFail($id);
        $departments_with_disciplines = Department::with('disciplines')->get();
        return view('users.edit')
            ->with('user', $user)
            ->with('departments_with_disciplines', $departments_with_disciplines);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'discipline_id' => 'nullable|exists:disciplines,id',
            'discipline_ids' => 'nullable|array',
            'discipline_ids.*' => 'exists:disciplines,id',
            'password' => 'nullable|string|min:6',
        ];

        $user = User::findOrFail($id);
        $departmentIdForValidation = $request->input('department_id') ?: $user->department_id;

        if (in_array($request->role, ['supervisor', 'tecnico'], true)) {
            $rules['department_id'] = 'required|exists:departments,id';
        }

        if ($request->role === 'tecnico') {
            $rules['discipline_id'] = [
                'required',
                Rule::exists('disciplines', 'id')->where('department_id', $departmentIdForValidation),
            ];
        }

        if ($request->role === 'supervisor') {
            $rules['discipline_ids'] = 'required|array|min:1';
            $rules['discipline_ids.*'] = [
                'required',
                Rule::exists('disciplines', 'id')->where('department_id', $departmentIdForValidation),
            ];
        }

        $request->validate($rules);

        $disciplineIds = $request->input('discipline_ids', []);
        $disciplineId = $request->input('discipline_id', $user->discipline_id);

        if ($request->role !== 'supervisor') {
            $disciplineIds = $disciplineId ? [$disciplineId] : [];
        } elseif ($request->filled('discipline_id') && empty($disciplineIds)) {
            $disciplineIds = [$disciplineId];
        }

        $user = User::findOrFail($id);
        $oldValues = $user->only(['name', 'email', 'role', 'department_id', 'discipline_id']);
        $oldValues['disciplines'] = $user->disciplines->pluck('name')->all();

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->department_id = $request->department_id ?: null;
        $user->discipline_id = $disciplineId ?: null;
        if ($request->filled('password')) {
            $user->password = $request->password; // casted in model
        }

        $user->save();
        $user->disciplines()->sync($disciplineIds);
        $user->load('disciplines');

        $newValues = $user->only(['name', 'email', 'role', 'department_id', 'discipline_id']);
        $newValues['disciplines'] = $user->disciplines->pluck('name')->all();

        AuditLogService::record(
            "Usuario actualizado: {$user->name}",
            $user,
            [
                'old' => $oldValues,
                'new' => $newValues,
            ]
        );

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
    }
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'discipline_id' => 'nullable|exists:disciplines,id',
            'discipline_ids' => 'nullable|array',
            'discipline_ids.*' => 'exists:disciplines,id',
        ];

        if (in_array($request->role, ['supervisor', 'tecnico'], true)) {
            $rules['department_id'] = 'required|exists:departments,id';
        }

        if ($request->role === 'tecnico') {
            $rules['discipline_id'] = [
                'required',
                Rule::exists('disciplines', 'id')->where('department_id', $request->department_id),
            ];
        }

        if ($request->role === 'supervisor') {
            $rules['discipline_ids'] = 'required|array|min:1';
            $rules['discipline_ids.*'] = [
                'required',
                Rule::exists('disciplines', 'id')->where('department_id', $request->department_id),
            ];
        }

        $request->validate($rules);

        $disciplineIds = $request->input('discipline_ids', []);
        if ($request->filled('discipline_id') && empty($disciplineIds)) {
            $disciplineIds = [$request->discipline_id];
        }

        $password = 'admin123'; 

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'department_id' => $request->department_id ?: null,
            'discipline_id' => $disciplineIds[0] ?? null,
            'password' => $password, // casted in model
        ]);

        $user->disciplines()->sync($disciplineIds);
        $user->load('disciplines');

        $newValues = $user->only(['name', 'email', 'role', 'department_id', 'discipline_id']);
        $newValues['disciplines'] = $user->disciplines->pluck('name')->all();

        AuditLogService::record(
            "Usuario creado: {$user->name}",
            $user,
            [
                'new' => $newValues,
            ]
        );

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if ($user) {
            AuditLogService::record(
                "Usuario eliminado: {$user->name}",
                $user,
                [
                    'old' => $user->only(['name', 'email', 'role', 'department_id', 'discipline_id']),
                ]
            );
        }

        User::destroy($id);
        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
    public function show($id)
    {
        // Lógica para mostrar los detalles de un usuario específico
    }

    public function tablePdf(Request $request)
    {
        $recordsJson = $request->input('records', '[]');
        $columnsJson = $request->input('columns', '[]');

        $records = json_decode($recordsJson, true) ?: [];
        $columns = json_decode($columnsJson, true) ?: (is_array($columnsJson) ? $columnsJson : []);

        $generatedAt = now()->format('d/m/Y H:i');

        $pdf = Pdf::loadView('users.pdf', compact('records', 'columns', 'generatedAt'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('usuarios.pdf');
    }
}
