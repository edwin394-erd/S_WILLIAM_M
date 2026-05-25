<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipment;
use Barryvdh\DomPDF\Facade\Pdf;

class EquipmentController extends Controller
{
    public function index()
    {
        $equipment = Equipment::all();
        return view('equipments.index', compact('equipment'));
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
        $equip->update(['name' => $request->name]);

        return redirect()->route('admin.equipment.index')->with('success', 'Equipo actualizado correctamente.');
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
