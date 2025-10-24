<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Email'       => ['required','email','max:100', Rule::unique('usuarios','Email')],
            'Contrasena'  => ['required','string','min:6','confirmed'], // necesita Contrasena_confirmation
        ]);

        $user = Usuario::create([
            'Email'      => $data['Email'],
            'Contrasena' => $data['Contrasena'], // se hashea por el mutator
            'ID_Rol'     => 4,                    // por defecto Huésped
            'Estado'     => 'Activo',
        ]);

        auth()->login($user);

        return redirect('/dashboard');
    }
}
