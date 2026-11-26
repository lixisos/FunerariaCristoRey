<?php

namespace App\Traits;

use App\Models\BitacoraAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            self::registrarBitacora($model, 'CREACION', null, $model->getAttributes());
        });

        static::updated(function (Model $model): void {
            $cambios = $model->getChanges();
            unset($cambios['updated_at'], $cambios['remember_token']);

            if (empty($cambios)) {
                return;
            }

            $anteriores = array_intersect_key($model->getOriginal(), $cambios);
            $accion = 'ACTUALIZACION';

            foreach ($cambios as $campo => $valor) {
                if (str_starts_with($campo, 'estado_') && in_array($valor, ['INACTIVO', 'ANULADO_INACTIVO'], true)) {
                    $accion = 'CAMBIO_ESTADO_INACTIVO';
                    break;
                }
            }

            self::registrarBitacora($model, $accion, $anteriores, $cambios);
        });

        static::deleted(function (Model $model): void {
            self::registrarBitacora($model, 'ELIMINACION', $model->getOriginal(), null);
        });
    }

    protected static function registrarBitacora(Model $model, string $accion, ?array $antes, ?array $despues): void
    {
        if ($antes) {
            unset($antes['password'], $antes['remember_token']);
        }
        if ($despues) {
            unset($despues['password'], $despues['remember_token']);
        }

        BitacoraAuditoria::create([
            'tabla_afectada' => $model->getTable(),
            'registro_id' => $model->getKey(),
            'accion' => $accion,
            'valores_anteriores_json' => $antes,
            'valores_nuevos_json' => $despues,
            'ip_origen' => request()?->ip() ?? '127.0.0.1',
            'user_agent' => substr((string) (request()?->userAgent() ?? 'CLI/Seeder'), 0, 255),
            'usuario_id' => Auth::id(),
        ]);
    }
}
