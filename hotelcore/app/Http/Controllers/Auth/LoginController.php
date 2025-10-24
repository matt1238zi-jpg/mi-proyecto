<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'Email'      => ['required','email'],
            'Contrasena' => ['required','string','min:6'],
        ]);

        $ok = Auth::attempt([
            'Email'    => $credentials['Email'],
            'password' => $credentials['Contrasena'],
        ], $request->boolean('remember'));

        if ($ok) {
            $request->session()->regenerate();

            // ⬇️ redirige por rol
            return redirect()->route(
                auth()->user()->ID_Rol == 1 ? 'admin.home' : 'dashboard'
            );
        }

        return back()->withErrors([
            'Email' => 'Credenciales inválidas.',
        ])->onlyInput('Email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
