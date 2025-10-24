<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'nombre_completo' => ['required','string','max:120'],
            'email'           => ['required','email','max:120', Rule::unique('usuario','email')],
            'username'        => ['required','string','max:60', Rule::unique('usuario','username')],
            'password'        => ['required','string','min:6'],
        ]);

        $user = Usuario::create([
            'nombre_completo' => $data['nombre_completo'],
            'email'           => $data['email'],
            'username'        => $data['username'],
            'password_hash'   => Hash::make($data['password']),
            'rol_id'          => 1,
            'activo'          => 1,
        ]);

        Auth::login($user);
        return response()->json(['ok' => true, 'user' => $user->only('id','nombre_completo','email','username')]);
    }

    public function login(Request $request)
    {
        $cred = $request->validate([
            'login'    => ['required','string'],   // email o username
            'password' => ['required','string'],
        ]);

        // Buscar por email o username
        $user = Usuario::where('email', $cred['login'])
                 ->orWhere('username', $cred['login'])
                 ->first();

        if (!$user || !Hash::check($cred['password'], $user->password_hash)) {
            return response()->json(['ok' => false, 'msg' => 'Credenciales inválidas'], 422);
        }
        if ((int)$user->activo !== 1) {
            return response()->json(['ok' => false, 'msg' => 'Usuario inactivo'], 403);
        }

        Auth::login($user);
        return response()->json(['ok' => true, 'user' => $user->only('id','nombre_completo','email','username')]);
    }

    public function me()
    {
        return response()->json(['user' => Auth::user()?->only('id','nombre_completo','email','username')]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json(['ok' => true]);
    }
}
