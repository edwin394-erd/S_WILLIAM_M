<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPlanAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && in_array($user->role, ['admin', 'planificador'], true)) {
            return $next($request);
        }

        if ($user && $user->role === 'supervisor') {
            if ($request->is('worksheets') || $request->is('worksheets/*')) {
                $worksheet = $request->route('worksheet');

                if ($request->is('worksheets/*/pdf') && $worksheet) {
                    return redirect()->route('supervisor.worksheets.pdf', $worksheet);
                }

                if ($worksheet) {
                    return redirect()->route('supervisor.worksheets.show', $worksheet);
                }

                return redirect()->route('supervisor.worksheets');
            }
        }

        abort(403, 'No tienes permisos de administrador.');
    }
}