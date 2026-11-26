<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehiculo extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'vehiculos';
    
    protected $fillable = [
        'patente', 'marca_modelo', 'tipo_vehiculo', 'anio',
        'kilometraje', 'estado_vehiculo', 'creado_por_id', 'tipo_vehiculo',
    ];

    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'vehiculo_id');
    }
}