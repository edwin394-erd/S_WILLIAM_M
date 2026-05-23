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

        if (Auth::user()->role === 'tecnico') {
            $intended = $request->session()->get('url.intended');

            if ($this->shouldIgnoreIntendedForTechnician($intended)) {
                $request->session()->forget('url.intended');
            }
        }

        $fallback = $this->getFallbackRedirect();

        return redirect()->intended($fallback);
    }

    $request->session()->flash('error', 'Las credenciales proporcionadas no son correctas.');
    return back();
}

private function shouldIgnoreIntendedForTechnician(?string $intended): bool
{
    if (! $intended) {
        return false;
    }

    $path = parse_url($intended, PHP_URL_PATH) ?: '';

    return str_contains($path, '/worksheets')
        || str_contains($path, '/workorders')
        || str_contains($path, '/admin');
}

private function getFallbackRedirect()
{
    if (Auth::user()->role === 'admin' || Auth::user()->role === 'planificador') {
        return route('admin.stats');
    }

    if (Auth::user()->role === 'supervisor') {
        return route('supervisor.stats');
    }

    if (Auth::user()->role === 'tecnico') {
        return route('tecnico.actividades', ['id_disciplina' => Auth::user()->discipline_id]);
    }

    return route('home');
}

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
