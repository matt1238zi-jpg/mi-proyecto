<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string)$r->query('q',''));

        $users = User::query()
            ->when($q, fn($qq) => $qq->where(function($w) use ($q){
                $w->where('nombre_completo','like',"%{$q}%")
                  ->orWhere('email','like',"%{$q}%")
                  ->orWhere('username','like',"%{$q}%");
            }))
            ->orderByDesc('id')
            ->paginate(10); // page, per_page por defecto

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'total'       => $users->total(),
                'currentPage' => $users->currentPage(),
                'lastPage'    => $users->lastPage(),
            ],
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'nombre_completo' => ['required','string','max:160'],
            'email'           => ['required','email','max:160','unique:users,email'],
            'username'        => ['required','string','max:60','unique:users,username'],
            'password'        => ['required','string','min:6','confirmed'],
            'role_id'         => ['nullable','integer'], // ajusta a tu esquema
            'active'          => ['nullable','boolean'],
        ]);

        $u = new User();
        $u->nombre_completo = $data['nombre_completo'];
        $u->email           = $data['email'];
        $u->username        = $data['username'];
        $u->password        = Hash::make($data['password']);
        $u->role_id         = $data['role_id'] ?? 2; // ejemplo
        $u->active          = $data['active'] ?? 1;
        $u->save();

        return response()->json(['ok'=>true,'user'=>$u], 201);
    }

    public function update(Request $r, User $user)
    {
        $data = $r->validate([
            'nombre_completo' => ['sometimes','required','string','max:160'],
            'email'           => ['sometimes','required','email','max:160', Rule::unique('users','email')->ignore($user->id)],
            'username'        => ['sometimes','required','string','max:60', Rule::unique('users','username')->ignore($user->id)],
            'password'        => ['nullable','string','min:6','confirmed'],
            'role_id'         => ['nullable','integer'],
            'active'          => ['nullable','boolean'],
        ]);

        $user->fill($data);
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return response()->json(['ok'=>true,'user'=>$user]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['ok'=>true]);
    }
}