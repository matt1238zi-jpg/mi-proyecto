<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'ID_Usuario';

    // Si no tienes updated_at, puedes desactivarlo
    const CREATED_AT = 'Fecha_Creacion';
    const UPDATED_AT = null;

    protected $fillable = [
        'Email', 'Contrasena', 'ID_Rol', 'Estado',
    ];

    protected $hidden = [
        'Contrasena',
    ];

    // Laravel necesita saber qué columna es el password
    public function getAuthPassword()
    {
        return $this->Contrasena;
    }

    // Hashea automáticamente al asignar Contrasena
    public function setContrasenaAttribute($value)
    {
        $this->attributes['Contrasena'] = Hash::needsRehash($value)
            ? Hash::make($value)
            : $value;
    }

    // Autenticación por email
   
}
