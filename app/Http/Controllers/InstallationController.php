<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InstallationController extends Controller
{
    public function index()
    {
        $installations = \App\Models\Installation::all();
        return view('installations.index')->with('installations', $installations);
    }

    public function create()
    {
        return view('installations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'impact' => 'required|integer|min:0',
        ]);

        \App\Models\Installation::create([
            'name' => $request->name,
            'impact' => $request->impact,
        ]);

        return redirect()->route('admin.installations.index')->with('success', 'Instalación creada correctamente.');
    }

    public function show($id)
    {
        // Lógica para mostrar los detalles de una instalación específica
    }

    public function edit($id)
    {
        $installation = \App\Models\Installation::findOrFail($id);
        return view('installations.edit')->with('installation', $installation);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'impact' => 'required|integer|min:0',
        ]);

        $installation = \App\Models\Installation::findOrFail($id);
        $installation->update([
            'name' => $request->name,
            'impact' => $request->impact,
        ]);

        return redirect()->route('admin.installations.index')->with('success', 'Instalación actualizada correctamente.');
    }

    public function destroy($id)
    {
        \App\Models\Installation::destroy($id);
        return redirect()->route('admin.installations.index')->with('success', 'Instalación eliminada exitosamente.');
    }

    public function tablePdf(Request $request)
    {
        $recordsJson = $request->input('records', '[]');
        $columnsJson = $request->input('columns', '[]');

        $records = json_decode($recordsJson, true) ?: [];
        $columns = json_decode($columnsJson, true) ?: (is_array($columnsJson) ? $columnsJson : []);

        $generatedAt = now()->format('d/m/Y H:i');
        $title = 'LISTADO DE INSTALACIONES';

        $pdf = Pdf::loadView('exports.table-pdf', compact('records', 'columns', 'generatedAt', 'title'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('instalaciones.pdf');
    }
}
