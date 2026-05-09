<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth; 


use Illuminate\Http\Request;

class loginController extends Controller
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
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.stats');// Verá estadísticas y creará la sábana
        }

        // dd(auth()->user()->role);
        return redirect()->route('supervisor.stats');

    }

    return back()->withErrors([
        'email' => 'Las credenciales no coinciden con nuestros registros.',
    ]);
}

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
