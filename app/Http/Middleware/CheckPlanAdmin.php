<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPlanAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Solo permite el paso si el rol es 'admin' o 'planificador'
        if (Auth::user() && in_array(Auth::user()->role, ['admin', 'planificador'], true)) {
            return $next($request);
        }

        abort(403, 'No tienes permisos de administrador.');
    }
}