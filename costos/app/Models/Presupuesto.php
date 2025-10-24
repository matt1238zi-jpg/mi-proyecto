<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presupuesto extends Model
{
    protected $table = 'presupuestos';
    protected $fillable = ['nombre', 'proyecto_id', 'total', 'fecha_creacion', 'estado'];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'presupuesto_id');
    }
}
