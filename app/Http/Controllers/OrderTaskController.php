<?php

namespace App\Http\Controllers;

use App\Models\OrderTask;
use Illuminate\Http\Request;

class OrderTaskController extends Controller
{
    public function update(Request $request, $task)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'supervisor'])) {
            abort(403, 'No tienes permiso para actualizar la observación.');
        }

        $request->validate([
            'observation' => 'nullable|string|max:2000',
        ]);

       
        $orderTask = OrderTask::findOrFail($task);
         $message = "La tarea programada para el {$orderTask->date->format('d/m/Y')} ha sido marcada como COMPLETADA.";
        
        if($orderTask->status === 'COMPLETADO') {
            $message = " Observación Actualizada";
        
        }
        $orderTask->update([
            'observation' => $request->input('observation'),
            'status' => 'COMPLETADO',
        ]);

        

         return redirect()->back()->with('success', $message);
        }
    

       
}
