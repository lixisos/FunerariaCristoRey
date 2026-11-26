<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expediente extends Model
{
    use SoftDeletes, Auditable;

    protected $table = 'expedientes';
    protected $fillable = [
        'folio', 'cliente_id', 'fallecido_id', 'urna_id', 'vehiculo_id',
        'chofer_id', 'convenio_id', 'tipo_servicio', 'lugar_velatorio',
        'fecha_inicio_velatorio', 'fecha_cortejo', 'cementerio_destino',
        'metadatos_ruta_json', 'limite_legal_seremi_at',
        'valor_total_servicio', 'monto_cobertura_convenio', 'saldo_pendiente',
        'estado_expediente', 'creado_por_id', 'ayudante_id', 'fecha_instalacion',
        'fecha_misa', 'lugar_misa', 'fecha_retiro_carroza', 'fecha_vencimiento_pago',
    ];

    protected $casts = [
        'fecha_inicio_velatorio' => 'datetime',
        'fecha_cortejo' => 'datetime',
        'limite_legal_seremi_at' => 'datetime',
        'metadatos_ruta_json' => 'array',
        'fecha_instalacion' => 'datetime',
        'fecha_misa' => 'datetime',
        'fecha_retiro_carroza' => 'datetime',
        'fecha_vencimiento_pago' => 'date',
    ];

    public function cliente() { return $this->belongsTo(Cliente::class, 'cliente_id'); }
    public function fallecido() { return $this->belongsTo(Fallecido::class, 'fallecido_id'); }
    public function urna() { return $this->belongsTo(Urna::class, 'urna_id'); }
    public function vehiculo() { return $this->belongsTo(Vehiculo::class, 'vehiculo_id'); }
    public function chofer() { return $this->belongsTo(User::class, 'chofer_id'); }
    public function ayudante() { return $this-> belongsTo(User::class, 'ayudante_id'); }
    public function convenio() { return $this->belongsTo(ConvenioPrevisional::class, 'convenio_id'); }
    public function documentos() { return $this->hasMany(DocumentoExpediente::class, 'expediente_id'); }
    public function abonos() { return $this->hasMany(AbonoPago::class, 'expediente_id'); }
    public function condolencias() { return $this->hasMany(Condolencia::class, 'expediente_id'); }
}