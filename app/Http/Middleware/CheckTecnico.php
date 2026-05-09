<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckTecnico
{
    public function handle(Request $request, Closure $next)
    {
        // Solo permite el paso si el rol es estrictamente 'technician'
        if (Auth::user() && Auth::user()->role === 'tecnico') {
            return $next($request);
        }

        abort(403, 'No tienes permisos de tecnico.');
    }
}