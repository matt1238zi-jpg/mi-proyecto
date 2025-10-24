<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = Usuario::with('rol')->get();
        return view('usuarios.index', compact('usuarios'));
    }

    public function crear()
    {
        return view('usuarios.crear');
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'nombre' => 'required|min:3',
            'email' => 'required|email|unique:usuarios',
            'password' => 'required|min:6',
        ]);

        Usuario::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol_id' => 2,
            'estado' => 'activo',
        ]);

        return redirect()->route('usuarios.index')->with('exito', 'Usuario creado correctamente.');
    }
}
