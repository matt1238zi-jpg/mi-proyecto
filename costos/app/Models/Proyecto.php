<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    protected $table = 'proyectos';
    protected $fillable = ['nombre', 'descripcion', 'fecha_inicio', 'fecha_fin', 'usuario_id'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function presupuestos()
    {
        return $this->hasMany(Presupuesto::class, 'proyecto_id');
    }
}
