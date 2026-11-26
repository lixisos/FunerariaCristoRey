<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConvenioPrevisional extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'convenios_previsionales';

    protected $fillable = [
        'nombre', 'tipo', 'monto_tope_clp', 'monto_tope_uf',
        'requiere_resolucion', 'estado_convenio', 'creado_por_id',
    ];

    protected $casts = [
        'requiere_resolucion' => 'boolean',
        'monto_tope_uf' => 'decimal:2',
    ];

    public function expedientes(): HasMany
    {
        return $this->hasMany(Expediente::class, 'convenio_id');
    }
}
