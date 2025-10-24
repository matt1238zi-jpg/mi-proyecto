<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    protected $table = 'proyecto';

    // la tabla usa creado_en / actualizado_en
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'codigo','nombre','cliente','ubicacion',
        'moneda_id','responsable_id','fecha_inicio','fecha_fin','estado',
        // 'descripcion', // <- si creas la columna
    ];
}
