<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Solo permite el paso si el rol es estrictamente 'admin'
        if (Auth::user() && Auth::user()->role === 'admin') {
            return $next($request);
        }

        abort(403, 'No tienes permisos de administrador.');
    }
}