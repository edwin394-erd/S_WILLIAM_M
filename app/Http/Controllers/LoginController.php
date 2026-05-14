<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth; 


use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function store(Request $request)
{
   

    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        // Lógica de redirección basada en el ROL definido en nuestra migración
        if (Auth::user()->role === 'admin' || Auth::user()->role === 'planificador') {
            return redirect()->route('admin.stats');// Verá estadísticas y creará la sábana
        }

        if (Auth::user()->role === 'supervisor') {
            dd('supervisor');
            return redirect()->route('supervisor.stats'); // Verá estadísticas y creará la sábana
        }

            if (Auth::user()->role === 'tecnico') {
            $disciplineId = Auth::user()->discipline_id; // Asumiendo que el usuario tiene este campo
            return redirect()->route('tecnico.actividades', ['id_disciplina' => $disciplineId]);
        }
            }

   $request->session()->flash('error', 'Las credenciales proporcionadas no son correctas.');
    return back();
}

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
