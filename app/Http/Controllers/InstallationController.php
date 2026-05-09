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
}
