<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        // Lógica para almacenar una nueva instalación
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
}
