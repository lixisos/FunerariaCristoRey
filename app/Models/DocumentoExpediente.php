<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoExpediente extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'documentos_expediente';

    protected $fillable = [
        'expediente_id', 'tipo_documento', 'nombre_archivo',
        'ruta_s3', 'validado', 'subido_por_id',
    ];

    protected $casts = ['validado' => 'boolean'];

    public function expediente(): BelongsTo
    {
        return $this->belongsTo(Expediente::class, 'expediente_id');
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por_id');
    }
}
