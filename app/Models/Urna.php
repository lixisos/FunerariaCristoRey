<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Urna extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'urnas';

    protected $fillable = [
        'codigo_sku', 'modelo', 'descripcion', 'categoria_tamano',
        'acabado_tallado', 'color', 'largo_cm', 'ancho_cm', 'alto_cm',
        'precio_venta', 'stock_salon', 'stock_taller', 'proveedor_origen',
        'reservada_en_vida', 'rut_reserva_vida', 'nombre_reserva_vida',
        'foto_s3_url', 'galeria_fotos', 'estado_urna', 'creado_por_id', 'precio_compra',
    ];

    protected $casts = [
        'reservada_en_vida' => 'boolean',
        'galeria_fotos' => 'array',
    ];

    public function getStockTotalAttribute(): int
    {
        return (int) $this->stock_salon + (int) $this->stock_taller;
    }

    public function getTodasLasFotosAttribute(): array
    {
        $fotos = [];
        if ($this->foto_s3_url) {
            $fotos[] = $this->foto_s3_url;
        }
        if (is_array($this->galeria_fotos)) {
            foreach ($this->galeria_fotos as $extra) {
                if ($extra && ! in_array($extra, $fotos, true)) {
                    $fotos[] = $extra;
                }
            }
        }
        if (empty($fotos)) {
            $fotos[] = 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=800&q=80';
        }

        return $fotos;
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'urna_id');
    }
}