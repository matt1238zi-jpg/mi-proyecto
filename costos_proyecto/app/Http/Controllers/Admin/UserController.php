<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Ej: sólo admin (ajusta según tu lógica)
    protected function ensureAdmin(Request $r)
    {
        if ((int)optional($r->user())->role_id === 2) { // 2 = rol restringido
            abort(403, 'No autorizado');
        }
    }

    public function index(Request $r)
    {
        $this->ensureAdmin($r);

        $q = trim((string)$r->query('q', ''));
        $users = User::query()
            ->when($q, fn($qq) => $qq->where(function($w) use ($q) {
                $w->where('nombre_completo', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('username', 'like', "%{$q}%");
            }))
            ->orderBy('id', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'total'        => $users->total(),
            ],
        ]);
    }

    public function store(Request $r)
    {
        $this->ensureAdmin($r);

        $data = $r->validate([
            'nombre_completo' => ['required','string','max:160'],
            'email'           => ['required','email','max:160','unique:users,email'],
            'username'        => ['required','string','max:60','unique:users,username'],
            'role_id'         => ['required','integer'],
            'password'        => ['required','string','min:6','confirmed'],
            'activo'          => ['boolean'],
        ]);

        $u = new User();
        $u->nombre_completo = $data['nombre_completo'];
        $u->email           = $data['email'];
        $u->username        = $data['username'];
        $u->role_id         = $data['role_id'];
        $u->password        = Hash::make($data['password']);
        $u->activo          = (bool)($data['activo'] ?? true);
        $u->save();

        return response()->json(['ok'=>true, 'id'=>$u->id]);
    }

    public function update(Request $r, $id)
    {
        $this->ensureAdmin($r);

        $u = User::findOrFail($id);
        $data = $r->validate([
            'nombre_completo' => ['required','string','max:160'],
            'email'           => ['required','email','max:160', Rule::unique('users','email')->ignore($u->id)],
            'username'        => ['required','string','max:60', Rule::unique('users','username')->ignore($u->id)],
            'role_id'         => ['required','integer'],
            'password'        => ['nullable','string','min:6','confirmed'],
            'activo'          => ['boolean'],
        ]);

        $u->nombre_completo = $data['nombre_completo'];
        $u->email           = $data['email'];
        $u->username        = $data['username'];
        $u->role_id         = $data['role_id'];
        if (!empty($data['password'])) {
            $u->password = Hash::make($data['password']);
        }
        if (array_key_exists('activo', $data)) {
            $u->activo = (bool)$data['activo'];
        }
        $u->save();

        return response()->json(['ok'=>true]);
    }

    public function destroy(Request $r, $id)
    {
        $this->ensureAdmin($r);
        $u = User::findOrFail($id);
        $u->delete();

        return response()->json(['ok'=>true]);
    }
}