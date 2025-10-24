<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'usuario';
    protected $primaryKey = 'id';

    // columnas de fecha personalizadas
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    protected $fillable = [
        'nombre_completo', 'email', 'username', 'password_hash', 'rol_id', 'activo'
    ];

    protected $hidden = ['password_hash'];

    // Para que Auth use password_hash en vez de password
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
