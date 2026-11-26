<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'clientes';
    protected $fillable = [
        'rut', 'nombre_completo', 'telefono', 'email',
        'direccion', 'comuna', 'estado_cliente', 'creado_por_id',
    ];

    public function expedientes()
    {
        return $this->hasMany(Expediente::class, 'cliente_id');
    }
}