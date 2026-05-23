<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipment;

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

}
