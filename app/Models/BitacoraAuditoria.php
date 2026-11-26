<?php

namespace App\Models;

use App\Support\Etiquetas;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraAuditoria extends Model
{
    public $timestamps = false;

    protected $table = 'bitacora_auditoria';

    protected $fillable = [
        'modulo', 'tabla_afectada', 'registro_id', 'accion',
        'valores_anteriores_json', 'valores_nuevos_json',
        'ip_origen', 'user_agent', 'usuario_id', 'created_at',
    ];

    protected $casts = [
        'valores_anteriores_json' => 'array',
        'valores_nuevos_json' => 'array',
        'created_at' => 'datetime',
    ];

    /** @var list<string> */
    private const CAMPOS_OCULTOS = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
        'creado_por_id',
        'registrado_por_id',
        'subido_por_id',
        'email_verified_at',
        'password',
        'remember_token',
        'foto_s3_url',
        'ruta_s3',
    ];

    /** @var array<string, string> */
    private const ETIQUETAS_CAMPOS = [
        'rut' => 'RUT',
        'name' => 'Nombre',
        'nombre' => 'Nombre',
        'nombre_completo' => 'Nombre completo',
        'nombre_remitente' => 'Remitente',
        'relacion' => 'Parentesco / Relación',
        'mensaje' => 'Mensaje',
        'aprobado' => 'Visible en portal',
        'email' => 'Correo',
        'telefono' => 'Teléfono',
        'direccion' => 'Dirección',
        'comuna' => 'Comuna',
        'rol_id' => 'Rol',
        'slug' => 'Identificador',
        'descripcion' => 'Descripción',
        'estado_usuario' => 'Estado',
        'estado_rol' => 'Estado',
        'estado_urna' => 'Estado',
        'estado_vehiculo' => 'Estado',
        'estado_convenio' => 'Estado',
        'estado_expediente' => 'Estado',
        'estado_pago' => 'Estado',
        'estado' => 'Estado',
        'en_vacaciones' => 'Ausente',
        'tipo_ausencia' => 'Disponibilidad',
        'ausencia_hasta' => 'Ausente hasta',
        'delegado_a_id' => 'Delegado a',
        'patente' => 'Patente',
        'marca_modelo' => 'Marca y modelo',
        'tipo_vehiculo' => 'Tipo de vehículo',
        'anio' => 'Año',
        'kilometraje' => 'Kilometraje',
        'codigo_sku' => 'Código SKU',
        'modelo' => 'Modelo',
        'categoria_tamano' => 'Tamaño',
        'acabado_tallado' => 'Acabado',
        'color' => 'Color',
        'largo_cm' => 'Largo',
        'ancho_cm' => 'Ancho',
        'alto_cm' => 'Alto',
        'precio_compra' => 'Precio compra',
        'precio_venta' => 'Precio venta',
        'stock_salon' => 'Stock salón',
        'stock_taller' => 'Stock taller',
        'proveedor_origen' => 'Proveedor',
        'reservada_en_vida' => 'Reserva en vida',
        'rut_reserva_vida' => 'RUT reserva',
        'nombre_reserva_vida' => 'Titular reserva',
        'tipo' => 'Tipo',
        'monto_tope_clp' => 'Tope CLP',
        'monto_tope_uf' => 'Tope UF',
        'requiere_resolucion' => 'Requiere resolución',
        'folio' => 'Folio',
        'folio_comprobante' => 'Comprobante',
        'tipo_servicio' => 'Tipo de servicio',
        'lugar_velatorio' => 'Lugar velatorio',
        'fecha_instalacion' => 'Instalación',
        'fecha_inicio_velatorio' => 'Inicio velatorio',
        'fecha_misa' => 'Fecha misa',
        'lugar_misa' => 'Lugar misa',
        'fecha_cortejo' => 'Fecha cortejo',
        'fecha_retiro_carroza' => 'Retiro carroza',
        'cementerio_destino' => 'Cementerio',
        'limite_legal_seremi_at' => 'Límite Seremi',
        'valor_total_servicio' => 'Valor total',
        'monto_cobertura_convenio' => 'Cobertura convenio',
        'saldo_pendiente' => 'Saldo pendiente',
        'fecha_vencimiento_pago' => 'Vencimiento pago',
        'fecha_nacimiento' => 'Fecha nacimiento',
        'fecha_fallecimiento' => 'Fecha defunción',
        'lugar_fallecimiento' => 'Lugar defunción',
        'comuna_defuncion' => 'Comuna defunción',
        'escolaridad' => 'Escolaridad',
        'profesion' => 'Profesión',
        'estado_civil' => 'Estado civil',
        'causa_muerte_preliminar' => 'Causa preliminar',
        'intervencion_sml' => 'Intervención SML',
        'numero_oficio_fiscalia' => 'Oficio Fiscalía',
        'monto_abonado' => 'Monto abonado',
        'metodo_pago' => 'Medio de pago',
        'fecha_pago' => 'Fecha de pago',
        'observacion' => 'Observación',
        'tipo_documento' => 'Tipo de documento',
        'nombre_archivo' => 'Archivo',
        'validado' => 'Validado',
        'documento' => 'Documento emitido',
        'consulta_archivo' => 'Archivo consultado',
        'motivo' => 'Motivo',
        'urna' => 'Urna',
        'velatorio' => 'Velatorio',
        'cortejo' => 'Cortejo',
        'cementerio' => 'Cementerio',
        'vehiculo' => 'Vehículo',
        'convenio' => 'Convenio',
        'monto_total' => 'Monto total',
        'metadatos_ruta_json' => 'Datos de ruta',
        'cliente_id' => 'ID cliente',
        'fallecido_id' => 'ID fallecido',
        'urna_id' => 'ID urna',
        'vehiculo_id' => 'ID vehículo',
        'chofer_id' => 'ID chofer',
        'ayudante_id' => 'ID ayudante',
        'convenio_id' => 'ID convenio',
        'expediente_id' => 'ID expediente',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function etiquetaAccion(): string
    {
        return match ($this->accion) {
            'CREACION' => 'Creación',
            'ACTUALIZACION' => 'Actualización',
            'MODIFICACION_CONTRATO' => 'Modificación de contrato',
            'CAMBIO_ESTADO_FLOTA' => 'Cambio de estado flota',
            'ASIGNACION_FLOTA_PERSONAL' => 'Asignación de flota',
            'CAMBIO_ESTADO_INACTIVO' => 'Desactivación',
            'ELIMINACION' => 'Eliminación',
            'TRASPASO_STOCK' => 'Traspaso de stock',
            'EMISION_PDF' => 'Emisión de PDF',
            default => ucfirst(strtolower(str_replace('_', ' ', (string) $this->accion))),
        };
    }

    public function claseBadgeAccion(): string
    {
        return match ($this->accion) {
            'CREACION' => 'bg-emerald-100 text-emerald-800',
            'ACTUALIZACION', 'MODIFICACION_CONTRATO' => 'bg-indigo-100 text-indigo-800',
            'CAMBIO_ESTADO_FLOTA', 'ASIGNACION_FLOTA_PERSONAL', 'TRASPASO_STOCK' => 'bg-amber-100 text-amber-900',
            'EMISION_PDF' => 'bg-violet-100 text-violet-800',
            'CAMBIO_ESTADO_INACTIVO', 'ELIMINACION' => 'bg-rose-100 text-rose-800',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    public function etiquetaTabla(): string
    {
        return match ($this->tabla_afectada) {
            'expedientes' => 'Expediente',
            'clientes' => 'Cliente',
            'fallecidos' => 'Fallecido',
            'urnas' => 'Urna',
            'vehiculos' => 'Vehículo',
            'convenios_previsionales' => 'Convenio previsional',
            'abonos_pagos' => 'Abono / Pago',
            'documentos_expediente' => 'Documento legal',
            'condolencias' => 'Condolencia',
            'users' => 'Usuario',
            'roles' => 'Rol',
            'movimientos_inventario' => 'Movimiento de bodega',
            default => ucfirst(str_replace('_', ' ', (string) $this->tabla_afectada)),
        };
    }

    /**
     * Convierte un payload JSON de auditoría en pares Etiqueta => Valor legibles en español.
     *
     * @param  array<string, mixed>|null  $valores
     * @return array<string, string>
     */
    public function formatearValores(?array $valores): array
    {
        if (empty($valores)) {
            return [];
        }

        $resultado = [];

        foreach ($valores as $clave => $valor) {
            if (in_array($clave, self::CAMPOS_OCULTOS, true)) {
                continue;
            }

            if ($valor === null || $valor === '') {
                continue;
            }

            $etiqueta = self::ETIQUETAS_CAMPOS[$clave] ?? ucfirst(str_replace('_', ' ', (string) $clave));
            $resultado[$etiqueta] = $this->formatearValorIndividual((string) $clave, $valor);
        }

        return $resultado;
    }

    private function formatearValorIndividual(string $clave, mixed $valor): string
    {
        if (is_bool($valor)) {
            return $valor ? 'Sí' : 'No';
        }

        if (is_array($valor)) {
            $partes = [];
            foreach ($valor as $subClave => $subValor) {
                if ($subValor === null || $subValor === '') {
                    continue;
                }
                $subEtiqueta = ucfirst(str_replace('_', ' ', (string) $subClave));
                $subTexto = is_bool($subValor) ? ($subValor ? 'Sí' : 'No') : (string) $subValor;
                $partes[] = "{$subEtiqueta}: {$subTexto}";
            }

            return $partes !== [] ? implode(' · ', $partes) : '—';
        }

        if (is_numeric($valor)) {
            if (str_ends_with($clave, '_uf')) {
                return number_format((float) $valor, 2, ',', '.').' UF';
            }

            if (preg_match('/^(precio_|valor_|monto_|saldo_)/', $clave) === 1) {
                return '$'.number_format((float) $valor, 0, ',', '.').' CLP';
            }

            if ($clave === 'kilometraje') {
                return number_format((int) $valor, 0, ',', '.').' km';
            }

            if (str_ends_with($clave, '_cm')) {
                return $valor.' cm';
            }
        }

        $texto = (string) $valor;

        if (preg_match('/^[A-Z0-9_]{3,}$/', $texto) === 1 && str_contains($texto, '_') || in_array($texto, ['DISPONIBLE', 'MANTENCION', 'INACTIVO', 'HABILITADO', 'ACTIVO', 'VACACIONES', 'PERMISO', 'ABIERTO', 'CERRADO', 'VALIDO', 'ANULADO', 'CREMACION', 'ESTANDAR', 'LISA', 'MARQUETEADA', 'PERSONALIZADA', 'SANTIAGO', 'SINDICATO', 'PARTICULAR', 'EFECTIVO', 'TRANSFERENCIA', 'OTRO', 'CARROZA'], true)) {
            return Etiquetas::texto($texto, $texto);
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}(?:[ T]\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:?\d{2})?)?$/', $texto) === 1) {
            try {
                $fecha = Carbon::parse($texto);

                return $fecha->format('H:i:s') === '00:00:00'
                    ? $fecha->format('d/m/Y')
                    : $fecha->format('d/m/Y H:i');
            } catch (\Throwable) {
                return $texto;
            }
        }

        return $texto;
    }
    /**
     * Genera lista compacta de cambios [campo, antes, despues] para actualizaciones.
     *
     * @return list<array{campo: string, antes: ?string, despues: string}>
     */
    public function diferenciasCompactas(): array
    {
        $antes = $this->formatearValores($this->valores_anteriores_json);
        $despues = $this->formatearValores($this->valores_nuevos_json);
        $cambios = [];

        foreach ($despues as $campo => $valorNuevo) {
            $valorAnterior = $antes[$campo] ?? null;
            if ($valorAnterior !== null && $valorAnterior === $valorNuevo) {
                continue;
            }
            $cambios[] = [
                'campo' => $campo,
                'antes' => $valorAnterior,
                'despues' => $valorNuevo,
            ];
        }

        return $cambios;
    }

    /**
     * Ordena los pares clave/valor poniendo primero los identificadores más útiles.
     *
     * @return array<string, string>
     */
    public function valoresPriorizados(?array $valores): array
    {
        $formateados = $this->formatearValores($valores);
        if (count($formateados) <= 2) {
            return $formateados;
        }

        $prioridad = [
            'Folio', 'Comprobante', 'Nombre', 'Nombre completo', 'RUT',
            'Modelo', 'Código SKU', 'Patente', 'Marca y modelo',
            'Tipo', 'Tipo de servicio', 'Tipo de documento', 'Archivo',
            'Documento emitido', 'Valor total', 'Monto abonado', 'Estado',
        ];

        $ordenados = [];
        foreach ($prioridad as $clave) {
            if (isset($formateados[$clave])) {
                $ordenados[$clave] = $formateados[$clave];
            }
        }
        foreach ($formateados as $clave => $valor) {
            if (! isset($ordenados[$clave])) {
                $ordenados[$clave] = $valor;
            }
        }

        return $ordenados;
    }

}
