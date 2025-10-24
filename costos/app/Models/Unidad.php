<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{
    protected $table = 'unidades';
    protected $fillable = ['nombre', 'simbolo'];

    public function recursos()
    {
        return $this->hasMany(Recurso::class, 'unidad_id');
    }
}
