<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbonoPago extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'abonos_pagos';
    protected $fillable = [
        'folio_comprobante', 'expediente_id', 'monto_abonado',
        'metodo_pago', 'fecha_pago', 'observacion', 'estado_pago', 'registrado_por_id',
    ];

    protected $casts = ['fecha_pago' => 'datetime'];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }
}