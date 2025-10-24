<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    // app/Http/Controllers/Admin/UsuarioController.php

public function index(Request $request)
{
    $q      = trim((string)$request->query('q',''));
    $rol    = $request->filled('rol') ? (string)$request->query('rol') : '';
    $estado = $request->filled('estado') ? (string)$request->query('estado') : '';

    $usuarios = Usuario::query()
        ->when($q !== '', fn($qq) => $qq->where('Email','like',"%{$q}%"))
        ->when($rol !== '', fn($qq) => $qq->where('ID_Rol',(int)$rol))         // asegura tipo numérico
        ->when($estado !== '', fn($qq) => $qq->where('Estado',$estado))         // compara exacto
        ->orderByDesc('ID_Usuario')
        ->paginate(10)
        ->appends($request->query()); // mantiene filtros en la paginación

    return view('admin.usuarios.index', compact('usuarios','q','estado','rol'));
}


    public function create()
    {
        return view('admin.usuarios.form', ['usuario'=>new Usuario()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Email'       => ['required','email','max:100', Rule::unique('usuarios','Email')],
            'Contrasena'  => ['required','string','min:6'],
            'ID_Rol'      => ['required','integer','in:1,2,3,4'],
            'Estado'      => ['required','in:Activo,Inactivo'],
        ]);

        Usuario::create($data); // mutator hashea la contraseña
        return redirect()->route('admin.usuarios.index')->with('ok','Usuario creado');
    }

    public function edit($id)
    {
        $usuario = Usuario::findOrFail($id);
        return view('admin.usuarios.form', compact('usuario'));
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $data = $request->validate([
            'Email'       => ['required','email','max:100', Rule::unique('usuarios','Email')->ignore($usuario->ID_Usuario,'ID_Usuario')],
            'Contrasena'  => ['nullable','string','min:6'],
            'ID_Rol'      => ['required','integer','in:1,2,3,4'],
            'Estado'      => ['required','in:Activo,Inactivo'],
        ]);

        if (empty($data['Contrasena'])) unset($data['Contrasena']); // no cambiar si viene vacía
        $usuario->update($data);

        return redirect()->route('admin.usuarios.index')->with('ok','Usuario actualizado');
    }

    public function destroy($id)
    {
        $u = Usuario::findOrFail($id);
        $u->delete();
        return back()->with('ok','Usuario eliminado');
    }
}
