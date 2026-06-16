<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipment;
use App\Services\AuditLogService;
use Barryvdh\DomPDF\Facade\Pdf;

class EquipmentController extends Controller
{
    public function index()
    {
        $equipment = Equipment::all();
        return view('equipments.index', compact('equipment'));
    }

    public function create()
    {
        return view('equipments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Equipment::create([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.equipment.index')->with('success', 'Equipo creado correctamente.');
    }

    public function edit($id)
    {
        $equip = Equipment::findOrFail($id);
        return view('equipments.edit')->with('equip', $equip);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $equip = Equipment::findOrFail($id);
        $equip = \App\Models\Equipment::findOrFail($id);
        $oldValues = $equip->only(['name']);
        $equip->update(['name' => $request->name]);

        AuditLogService::record(
            "Equipo actualizado: {$equip->name}",
            $equip,
            [
                'old' => $oldValues,
                'new' => $equip->only(['name']),
            ]
        );

        return redirect()->route('admin.equipment.index')->with('success', 'Equipo actualizado correctamente.');
    }

    public function destroy($id)
    {
        $equipment = \App\Models\Equipment::find($id);
        if ($equipment) {
            AuditLogService::record(
                "Equipo eliminado: {$equipment->name}",
                $equipment,
                ['old' => $equipment->only(['name'])]
            );
        }

        \App\Models\Equipment::destroy($id);
        return redirect()->route('admin.equipment.index')->with('success', 'Equipo eliminado exitosamente.');
    }

    public function tablePdf(Request $request)
    {
        $recordsJson = $request->input('records', '[]');
        $columnsJson = $request->input('columns', '[]');

        $records = json_decode($recordsJson, true) ?: [];
        $columns = json_decode($columnsJson, true) ?: (is_array($columnsJson) ? $columnsJson : []);

        $generatedAt = now()->format('d/m/Y H:i');
        $title = 'LISTADO DE EQUIPOS';

        $pdf = Pdf::loadView('exports.table-pdf', compact('records', 'columns', 'generatedAt', 'title'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('equipos.pdf');
    }

}
