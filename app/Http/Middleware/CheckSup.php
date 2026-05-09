<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSup
{
    public function handle(Request $request, Closure $next)
    {
        // Solo permite el paso si el rol es estrictamente 'supervisor'
        if (Auth::user() && Auth::user()->role === 'supervisor') {
            return $next($request);
        }

        abort(403, 'No tienes permisos de supervisor.');
    }
}