<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use Illuminate\Http\Request;

class ProyectoController extends Controller
{
    public function index()
    {
        $proyectos = Proyecto::with('usuario')->get();
        return view('proyectos.index', compact('proyectos'));
    }

    public function crear()
    {
        return view('proyectos.crear');
    }

    public function guardar(Request $request)
    {
        $request->validate(['nombre' => 'required']);
        Proyecto::create($request->all());
        return redirect()->route('proyectos.index')->with('exito', 'Proyecto creado correctamente.');
    }
}
