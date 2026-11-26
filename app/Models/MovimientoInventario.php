<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';
    protected $fillable = [
        'urna_id', 'tipo_movimiento', 'cantidad',
        'origen', 'destino', 'observacion', 'registrado_por_id',
    ];

    public function urna()
    {
        return $this->belongsTo(Urna::class, 'urna_id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por_id');
    }
}