<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Condolencia extends Model
{
    protected $table = 'condolencias';
    protected $fillable = [
        'expediente_id', 'nombre_remitente', 'relacion', 'mensaje', 'aprobado',
    ];

    protected $casts = ['aprobado' => 'boolean'];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }
}