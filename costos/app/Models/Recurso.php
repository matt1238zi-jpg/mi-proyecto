<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recurso extends Model
{
    protected $table = 'recursos';
    protected $fillable = ['nombre', 'tipo', 'unidad_id', 'costo_unitario'];

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }
}
