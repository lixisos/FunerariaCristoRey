<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fallecido extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'fallecidos';
    protected $fillable = [
        'rut', 'nombre_completo', 'fecha_nacimiento', 'fecha_fallecimiento',
        'lugar_fallecimiento', 'comuna_defuncion', 'causa_muerte_preliminar',
        'intervencion_sml', 'numero_oficio_fiscalia', 'estado_fallecido', 'creado_por_id', 'escolaridad',
        'profesion', 'estado_civil',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_fallecimiento' => 'datetime',
        'intervencion_sml' => 'boolean',
    ];

    public function expedientes()
    {
    return $this->hasMany(Fallecido::class ? Expediente::class : Expediente::class, 'fallecido_id');
    }
}