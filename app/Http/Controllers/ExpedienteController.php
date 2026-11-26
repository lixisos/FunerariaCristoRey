<?php

namespace App\Http\Controllers;

use App\Models\BitacoraAuditoria;
use App\Models\Cliente;
use App\Models\ConvenioPrevisional;
use App\Models\DocumentoExpediente;
use App\Models\Expediente;
use App\Models\Fallecido;
use App\Models\MovimientoInventario;
use App\Models\Urna;
use App\Models\User;
use App\Models\Vehiculo;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpedienteController extends Controller
{
    public function index(Request $request)
    {
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $query = Expediente::with(['cliente', 'fallecido', 'urna', 'vehiculo', 'chofer', 'ayudante', 'convenio'])
            ->withCount('documentos');

        if ($request->filled('buscar')) {
            $term = '%'.trim((string) $request->buscar).'%';
            $query->where(function ($q) use ($term, $like): void {
                $q->where('folio', $like, $term)
                    ->orWhereHas('cliente', fn ($c) => $c->where('rut', $like, $term)->orWhere('nombre_completo', $like, $term))
                    ->orWhereHas('fallecido', fn ($f) => $f->where('rut', $like, $term)->orWhere('nombre_completo', $like, $term));
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado_expediente', $request->estado);
        }

        if ($request->filled('convenio_id')) {
            $query->where('convenio_id', $request->convenio_id);
        }

        if ($request->boolean('urgente_seremi')) {
            $query->whereIn('estado_expediente', ['ABIERTO', 'EN_PROCESO'])
                ->where('limite_legal_seremi_at', '<=', now()->addHours(3));
        }

        $expedientes = $query->latest()->paginate(15)->withQueryString();

        $urnasDisponibles = Urna::where('estado_urna', 'HABILITADO')
            ->whereRaw('(stock_salon + stock_taller) > 0')
            ->orderBy('modelo')
            ->get();

        $vehiculos = Vehiculo::whereIn('estado_vehiculo', ['DISPONIBLE', 'EN_SERVICIO'])->orderBy('patente')->get();

        // Solo personal operativo que NO esté ausente (vacaciones / permiso / licencia)
        $personalOperativo = User::with('rol')
            ->where('estado_usuario', 'HABILITADO')
            ->where('en_vacaciones', false)
            ->orderBy('name')
            ->get();

        $convenios = ConvenioPrevisional::where('estado_convenio', 'HABILITADO')->get();

        return view('expedientes.index', compact(
            'expedientes',
            'urnasDisponibles',
            'vehiculos',
            'personalOperativo',
            'convenios'
        ));
    }

    public function buscarCliente(Request $request)
    {
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $rut = trim((string) $request->query('rut'));
        $cliente = Cliente::with(['expedientes.fallecido', 'expedientes.convenio'])
            ->where('rut', $like, $rut)
            ->first();

        return response()->json([
            'encontrado' => (bool) $cliente,
            'cliente' => $cliente,
            'total_servicios' => $cliente ? $cliente->expedientes->count() : 0,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // Contratante
            'cliente_rut' => ['required', 'string', 'max:15', new \App\Rules\RutChileno],
            'cliente_nombre' => ['required', 'string', 'min:5', 'max:150', 'regex:/^[\pL\s\.\-\']+$/u'],
            'cliente_telefono' => ['required', 'string', 'min:8', 'max:20', 'regex:/^(\+?56)?\s?0?[2-9]\d{7,8}$/'],
            'cliente_email' => 'nullable|email|max:120',
            'cliente_direccion' => 'required|string|min:5|max:255',
            'cliente_comuna' => ['required', 'string', 'min:3', 'max:80', 'regex:/^[\pL\s\.\-]+$/u'],
            // Fallecido (Registro Civil)
            'fallecido_rut' => ['nullable', 'string', 'max:15', new \App\Rules\RutChileno],
            'fallecido_nombre' => ['required', 'string', 'min:5', 'max:150', 'regex:/^[\pL\s\.\-\']+$/u'],
            'fecha_fallecimiento' => 'required|date|after_or_equal:2020-01-01|before_or_equal:2035-12-31',
            'lugar_fallecimiento' => 'required|string|min:3|max:180',
            'comuna_defuncion' => ['required', 'string', 'min:3', 'max:80', 'regex:/^[\pL\s\.\-]+$/u'],
            'escolaridad' => 'nullable|string|max:80',
            'profesion' => 'nullable|string|max:100',
            'estado_civil' => 'nullable|string|max:40',
            'causa_muerte_preliminar' => 'nullable|string|max:200',
            'intervencion_sml' => 'nullable|boolean',
            'numero_oficio_fiscalia' => 'required_if:intervencion_sml,1|nullable|string|min:3|max:60',
            // Logística, Urna y Agenda
            'urna_id' => 'required|exists:urnas,id',
            'vehiculo_id' => 'nullable|exists:vehiculos,id',
            'chofer_id' => 'nullable|exists:users,id',
            'ayudante_id' => 'nullable|exists:users,id',
            'tipo_servicio' => 'required|in:VELATORIO_Y_SEPULTACION,CREMACION,TRASLADO_REGIONAL,TRASLADO_NACIONAL',
            'lugar_velatorio' => 'required|string|min:4|max:200',
            'fecha_instalacion' => 'nullable|date|after_or_equal:2020-01-01|before_or_equal:2035-12-31',
            'fecha_inicio_velatorio' => 'required|date|after_or_equal:fecha_fallecimiento|before_or_equal:2035-12-31',
            'fecha_misa' => 'nullable|date|after_or_equal:fecha_inicio_velatorio|before_or_equal:2035-12-31',
            'lugar_misa' => 'nullable|string|max:180',
            'fecha_cortejo' => 'required|date|after_or_equal:fecha_inicio_velatorio|before_or_equal:2035-12-31',
            'fecha_retiro_carroza' => 'nullable|date|after_or_equal:fecha_cortejo|before_or_equal:2035-12-31',
            'cementerio_destino' => 'required|string|min:4|max:150',
            'sector_rural' => 'nullable|boolean',
            'observaciones_chofer' => 'nullable|string|max:255',
            // Finanzas y Convenio
            'convenio_id' => 'nullable|exists:convenios_previsionales,id',
            'valor_uf_actual' => 'nullable|numeric|min:30000|max:60000',
            'valor_total_servicio' => 'required|integer|min:150000|max:50000000',
            'fecha_vencimiento_pago' => 'nullable|date|after_or_equal:today|before_or_equal:2035-12-31',
        ], [
            'cliente_telefono.regex' => 'Ingresa un teléfono chileno válido (ej: +56 9 8765 4321).',
            'fecha_fallecimiento.before_or_equal' => 'El año de defunción no puede superar 2035.',
            'fecha_inicio_velatorio.after_or_equal' => 'El inicio del velatorio no puede ser anterior a la defunción.',
            'fecha_cortejo.after_or_equal' => 'El cortejo debe ser igual o posterior al inicio del velatorio.',
            'fecha_cortejo.before_or_equal' => 'El año del cortejo no puede superar 2035.',
            'numero_oficio_fiscalia.required_if' => 'Si hubo intervención del SML, debes ingresar el N° de Oficio de Fiscalía.',
            'valor_total_servicio.max' => 'El valor total del servicio no puede superar $50.000.000.',
        ]);

        $data['cliente_rut'] = \App\Rules\RutChileno::formatear($data['cliente_rut']);
        $data['fallecido_rut'] = \App\Rules\RutChileno::formatear($data['fallecido_rut'] ?? null);

        // Validar que el chofer o ayudante no esté ausente
        foreach (['chofer_id', 'ayudante_id'] as $campoStaff) {
            if (! empty($data[$campoStaff])) {
                $staff = User::find($data[$campoStaff]);
                if ($staff && $staff->en_vacaciones) {
                    return back()->withInput()->with('error', "Bloqueo de Personal: {$staff->name} se encuentra en {$staff->tipo_ausencia} y no puede ser asignado.");
                }
            }
        }

        $urna = Urna::findOrFail($data['urna_id']);

        // 1. Validación Reserva en Vida
        if ($urna->reservada_en_vida && strcasecmp(trim((string) $urna->rut_reserva_vida), trim($data['cliente_rut'])) !== 0) {
            return back()->withInput()->with('error', "Bloqueo de Inventario: La urna {$urna->modelo} está reservada en vida para {$urna->nombre_reserva_vida} ({$urna->rut_reserva_vida}).");
        }

        if (($urna->stock_salon + $urna->stock_taller) <= 0) {
            return back()->withInput()->with('error', 'La urna seleccionada no tiene stock disponible en Salón ni en Taller.');
        }

        // 2. Validación Anti-Solapamiento de Flota (±3 horas alrededor del cortejo)
        if (! empty($data['vehiculo_id'])) {
            $cortejo = Carbon::parse($data['fecha_cortejo']);
            $conflicto = Expediente::where('vehiculo_id', $data['vehiculo_id'])
                ->whereIn('estado_expediente', ['ABIERTO', 'EN_PROCESO'])
                ->whereBetween('fecha_cortejo', [$cortejo->copy()->subHours(3), $cortejo->copy()->addHours(3)])
                ->first();

            if ($conflicto) {
                return back()->withInput()->with('error', "Conflicto de Flota: La carroza seleccionada ya está asignada al expediente {$conflicto->folio} en ese bloque horario.");
            }
        }

        $expediente = DB::transaction(function () use ($data, $urna, $request) {
            $cliente = Cliente::updateOrCreate(
                ['rut' => trim($data['cliente_rut'])],
                [
                    'nombre_completo' => $data['cliente_nombre'],
                    'telefono' => $data['cliente_telefono'],
                    'email' => $data['cliente_email'] ?? null,
                    'direccion' => $data['cliente_direccion'],
                    'comuna' => $data['cliente_comuna'],
                    'creado_por_id' => Auth::id(),
                ]
            );

            $fallecido = Fallecido::create([
                'rut' => $data['fallecido_rut'] ?? null,
                'nombre_completo' => $data['fallecido_nombre'],
                'fecha_fallecimiento' => $data['fecha_fallecimiento'],
                'lugar_fallecimiento' => $data['lugar_fallecimiento'],
                'comuna_defuncion' => $data['comuna_defuncion'],
                'escolaridad' => $data['escolaridad'] ?? null,
                'profesion' => $data['profesion'] ?? null,
                'estado_civil' => $data['estado_civil'] ?? null,
                'causa_muerte_preliminar' => $data['causa_muerte_preliminar'] ?? null,
                'intervencion_sml' => $request->boolean('intervencion_sml'),
                'numero_oficio_fiscalia' => $request->boolean('intervencion_sml') ? ($data['numero_oficio_fiscalia'] ?? null) : null,
                'creado_por_id' => Auth::id(),
            ]);

            // Descuento automático de Urna
            $origenDescuento = $urna->stock_salon > 0 ? 'Salón Calle Blanco' : 'Bodega Taller';
            if ($urna->stock_salon > 0) {
                $urna->decrement('stock_salon', 1);
            } else {
                $urna->decrement('stock_taller', 1);
            }

            if ($urna->reservada_en_vida) {
                $urna->update(['reservada_en_vida' => false]);
            }

            $folio = 'EXP-'.date('Y').'-'.str_pad((string) (Expediente::withTrashed()->count() + 1), 4, '0', STR_PAD_LEFT);

            MovimientoInventario::create([
                'urna_id' => $urna->id,
                'tipo_movimiento' => 'REBAJA_VENTA',
                'cantidad' => 1,
                'origen' => $origenDescuento,
                'destino' => "Expediente {$folio}",
                'observacion' => "Asignada a servicio de {$fallecido->nombre_completo}",
                'registrado_por_id' => Auth::id(),
            ]);

            $cobertura = $this->calcularCobertura($data['convenio_id'] ?? null, (int) $data['valor_total_servicio'], (float) ($data['valor_uf_actual'] ?? 38500));
            $saldoPendiente = max(0, (int) $data['valor_total_servicio'] - $cobertura);
            $limiteSeremi = Carbon::parse($data['fecha_fallecimiento'])->addHours(48);

            if (! empty($data['vehiculo_id'])) {
                Vehiculo::where('id', $data['vehiculo_id'])->update(['estado_vehiculo' => 'EN_SERVICIO']);
            }

            return Expediente::create([
                'folio' => $folio,
                'cliente_id' => $cliente->id,
                'fallecido_id' => $fallecido->id,
                'urna_id' => $urna->id,
                'vehiculo_id' => $data['vehiculo_id'] ?? null,
                'chofer_id' => $data['chofer_id'] ?? null,
                'ayudante_id' => $data['ayudante_id'] ?? null,
                'convenio_id' => $data['convenio_id'] ?? null,
                'tipo_servicio' => $data['tipo_servicio'],
                'lugar_velatorio' => $data['lugar_velatorio'],
                'fecha_instalacion' => $data['fecha_instalacion'] ?? Carbon::parse($data['fecha_inicio_velatorio'])->subHour(),
                'fecha_inicio_velatorio' => $data['fecha_inicio_velatorio'],
                'fecha_misa' => $data['fecha_misa'] ?? null,
                'lugar_misa' => $data['lugar_misa'] ?? null,
                'fecha_cortejo' => $data['fecha_cortejo'],
                'fecha_retiro_carroza' => $data['fecha_retiro_carroza'] ?? Carbon::parse($data['fecha_cortejo'])->addHours(2),
                'cementerio_destino' => $data['cementerio_destino'],
                'metadatos_ruta_json' => [
                    'sector_rural' => $request->boolean('sector_rural'),
                    'observaciones_chofer' => $data['observaciones_chofer'] ?? 'Sin observaciones adicionales',
                ],
                'limite_legal_seremi_at' => $limiteSeremi,
                'valor_total_servicio' => $data['valor_total_servicio'],
                'monto_cobertura_convenio' => $cobertura,
                'saldo_pendiente' => $saldoPendiente,
                'fecha_vencimiento_pago' => $data['fecha_vencimiento_pago'] ?? now()->addDays(15)->toDateString(),
                'estado_expediente' => 'ABIERTO',
                'creado_por_id' => Auth::id(),
            ]);
        });

        return redirect()->route('expedientes.show', $expediente)
            ->with('success', "Expediente {$expediente->folio} abierto exitosamente. Inventario descontado y cobertura calculada.");
    }

    public function show(Expediente $expediente)
    {
        $expediente->load(['cliente', 'fallecido', 'urna', 'vehiculo', 'chofer', 'ayudante', 'convenio', 'documentos', 'abonos', 'condolencias']);

        $urnasDisponibles = Urna::where('estado_urna', 'HABILITADO')
            ->where(function ($q) use ($expediente) {
                $q->whereRaw('(stock_salon + stock_taller) > 0')
                    ->orWhere('id', $expediente->urna_id);
            })
            ->orderBy('modelo')
            ->get();

        $vehiculos = Vehiculo::orderBy('patente')->get();
        $personalOperativo = User::with('rol')
            ->where('estado_usuario', 'HABILITADO')
            ->where('en_vacaciones', false)
            ->orderBy('name')
            ->get();
        $convenios = ConvenioPrevisional::where('estado_convenio', 'HABILITADO')->get();

        $historialCambios = BitacoraAuditoria::with('usuario')
            ->where('tabla_afectada', 'expedientes')
            ->where('registro_id', $expediente->id)
            ->latest()
            ->get();

        return view('expedientes.show', compact(
            'expediente',
            'urnasDisponibles',
            'vehiculos',
            'personalOperativo',
            'convenios',
            'historialCambios'
        ));
    }

    public function update(Request $request, Expediente $expediente)
    {
        $data = $request->validate([
            'urna_id' => 'required|exists:urnas,id',
            'vehiculo_id' => 'nullable|exists:vehiculos,id',
            'chofer_id' => 'nullable|exists:users,id',
            'ayudante_id' => 'nullable|exists:users,id',
            'tipo_servicio' => 'required|in:VELATORIO_Y_SEPULTACION,CREMACION,TRASLADO_REGIONAL,TRASLADO_NACIONAL',
            'lugar_velatorio' => 'required|string|max:200',
            'fecha_inicio_velatorio' => 'required|date',
            'fecha_misa' => 'nullable|date',
            'lugar_misa' => 'nullable|string|max:180',
            'fecha_cortejo' => 'required|date',
            'cementerio_destino' => 'required|string|max:150',
            'convenio_id' => 'nullable|exists:convenios_previsionales,id',
            'valor_uf_actual' => 'nullable|numeric|min:30000|max:60000',
            'valor_total_servicio' => 'required|integer|min:100000',
            'motivo_cambio' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($data, $expediente) {
            $expediente->load(['urna', 'vehiculo', 'convenio', 'chofer', 'ayudante']);

            $antes = [
                'urna' => $expediente->urna->modelo ?? 'N/A',
                'velatorio' => $expediente->lugar_velatorio,
                'cortejo' => $expediente->fecha_cortejo->format('d/m/Y H:i'),
                'cementerio' => $expediente->cementerio_destino,
                'vehiculo' => $expediente->vehiculo->patente ?? 'Sin asignar',
                'convenio' => $expediente->convenio->nombre ?? 'Particular',
                'monto_total' => '$'.number_format($expediente->valor_total_servicio, 0, ',', '.'),
            ];

            // Si cambió de Urna: devolver stock de la urna anterior y rebajar la nueva
            if ((int) $data['urna_id'] !== (int) $expediente->urna_id) {
                $urnaAnterior = Urna::find($expediente->urna_id);
                if ($urnaAnterior) {
                    $urnaAnterior->increment('stock_taller', 1);
                    MovimientoInventario::create([
                        'urna_id' => $urnaAnterior->id,
                        'tipo_movimiento' => 'DEVOLUCION_ANULACION',
                        'cantidad' => 1,
                        'origen' => "Expediente {$expediente->folio}",
                        'destino' => 'Bodega Taller',
                        'observacion' => "Reingreso por cambio de urna en {$expediente->folio}",
                        'registrado_por_id' => Auth::id(),
                    ]);
                }

                $urnaNueva = Urna::findOrFail($data['urna_id']);
                if ($urnaNueva->stock_salon > 0) {
                    $urnaNueva->decrement('stock_salon', 1);
                } else {
                    $urnaNueva->decrement('stock_taller', 1);
                }

                MovimientoInventario::create([
                    'urna_id' => $urnaNueva->id,
                    'tipo_movimiento' => 'REBAJA_VENTA',
                    'cantidad' => 1,
                    'origen' => 'Salón / Taller',
                    'destino' => "Expediente {$expediente->folio}",
                    'observacion' => "Cambio de urna en contrato ({$data['motivo_cambio']})",
                    'registrado_por_id' => Auth::id(),
                ]);
            }

            // Recalcular cobertura y saldo descontando abonos ya pagados
            $cobertura = $this->calcularCobertura($data['convenio_id'] ?? null, (int) $data['valor_total_servicio'], (float) ($data['valor_uf_actual'] ?? 38500));
            $totalAbonado = $expediente->abonos()->where('estado_pago', 'VALIDO')->sum('monto_abonado');
            $nuevoSaldo = max(0, (int) $data['valor_total_servicio'] - $cobertura - (int) $totalAbonado);

            $expediente->update([
                'urna_id' => $data['urna_id'],
                'vehiculo_id' => $data['vehiculo_id'] ?? null,
                'chofer_id' => $data['chofer_id'] ?? null,
                'ayudante_id' => $data['ayudante_id'] ?? null,
                'tipo_servicio' => $data['tipo_servicio'],
                'lugar_velatorio' => $data['lugar_velatorio'],
                'fecha_inicio_velatorio' => $data['fecha_inicio_velatorio'],
                'fecha_misa' => $data['fecha_misa'] ?? null,
                'lugar_misa' => $data['lugar_misa'] ?? null,
                'fecha_cortejo' => $data['fecha_cortejo'],
                'cementerio_destino' => $data['cementerio_destino'],
                'convenio_id' => $data['convenio_id'] ?? null,
                'valor_total_servicio' => $data['valor_total_servicio'],
                'monto_cobertura_convenio' => $cobertura,
                'saldo_pendiente' => $nuevoSaldo,
            ]);

            $expediente->refresh()->load(['urna', 'vehiculo', 'convenio']);

            $despues = [
                'motivo' => $data['motivo_cambio'],
                'urna' => $expediente->urna->modelo ?? 'N/A',
                'velatorio' => $expediente->lugar_velatorio,
                'cortejo' => $expediente->fecha_cortejo->format('d/m/Y H:i'),
                'cementerio' => $expediente->cementerio_destino,
                'vehiculo' => $expediente->vehiculo->patente ?? 'Sin asignar',
                'convenio' => $expediente->convenio->nombre ?? 'Particular',
                'monto_total' => '$'.number_format($expediente->valor_total_servicio, 0, ',', '.'),
            ];

            BitacoraAuditoria::create([
                'modulo' => 'Operaciones / Expedientes',
                'tabla_afectada' => 'expedientes',
                'registro_id' => $expediente->id,
                'accion' => 'MODIFICACION_CONTRATO',
                'valores_anteriores_json' => $antes,
                'valores_nuevos_json' => $despues,
                'ip_origen' => request()->ip(),
                'usuario_id' => Auth::id(),
            ]);
        });

        return back()->with('success', "Contrato {$expediente->folio} modificado. El cambio quedó registrado en la Bitácora de Auditoría.");
    }

    public function subirDocumento(Request $request, Expediente $expediente)
    {
        $request->validate([
            'tipo_documento' => 'required|in:CERTIFICADO_DEFUNCION,PASE_SEPULTACION,CARNET_CONTRATANTE,CARNET_FALLECIDO,OFICIO_FISCALIA_SML,RESOLUCION_SEREMI,CONTRATO_FIRMADO,HOJA_MEDIDAS_CEMENTERIO,OTRO',
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $file = $request->file('archivo');
        $ruta = $file->store("expedientes/{$expediente->folio}", 'public');

        DocumentoExpediente::create([
            'expediente_id' => $expediente->id,
            'tipo_documento' => $request->tipo_documento,
            'nombre_archivo' => $file->getClientOriginalName(),
            'ruta_s3' => $ruta,
            'validado' => true,
            'subido_por_id' => Auth::id(),
        ]);

        return back()->with('success', 'Documento legal cargado y resguardado en el expediente.');
    }

    public function verDocumento(DocumentoExpediente $documento)
    {
        BitacoraAuditoria::create([
            'modulo' => 'Documentos Digitales',
            'tabla_afectada' => 'documentos_expediente',
            'registro_id' => $documento->id,
            'accion' => 'EMISION_PDF',
            'valores_nuevos_json' => ['consulta_archivo' => $documento->nombre_archivo],
            'ip_origen' => request()->ip(),
            'usuario_id' => Auth::id(),
        ]);

        $ruta = (string) $documento->ruta_s3;

        if (str_starts_with($ruta, 's3://')) {
            return back()->with('success', "Enlace seguro verificado para archivo de muestra: {$documento->nombre_archivo}");
        }

        if ($ruta === '' || str_contains($ruta, '..') || str_starts_with($ruta, '/') || str_starts_with($ruta, '\\')) {
            abort(403, 'Ruta de archivo no permitida.');
        }

        if (! Storage::disk('public')->exists($ruta)) {
            return back()->with('error', "El archivo {$documento->nombre_archivo} no se encuentra disponible en almacenamiento.");
        }

        return Storage::disk('public')->response($ruta);
    }

    public function destroy(Expediente $expediente)
    {
        $folio = $expediente->folio;

        DB::transaction(function () use ($expediente): void {
            if ($expediente->vehiculo_id) {
                Vehiculo::where('id', $expediente->vehiculo_id)->update(['estado_vehiculo' => 'DISPONIBLE']);
            }

            $expediente->update(['estado_expediente' => 'ANULADO_INACTIVO']);
            $expediente->delete();
        });

        return redirect()->route('expedientes.index')
            ->with('success', "Expediente {$folio} anulado y archivado correctamente (borrado lógico auditable).");
    }

    public function cambiarEstado(Request $request, Expediente $expediente)
    {
        $request->validate([
            'estado_expediente' => 'required|in:ABIERTO,EN_PROCESO,CERRADO,ANULADO_INACTIVO',
        ]);

        $nuevoEstado = $request->estado_expediente;
        $expediente->update(['estado_expediente' => $nuevoEstado]);

        if (in_array($nuevoEstado, ['CERRADO', 'ANULADO_INACTIVO']) && $expediente->vehiculo_id) {
            Vehiculo::where('id', $expediente->vehiculo_id)->update(['estado_vehiculo' => 'DISPONIBLE']);
        }

        return back()->with('success', "Estado del expediente {$expediente->folio} actualizado a {$nuevoEstado}.");
    }

    public function contratoPdf(Expediente $expediente)
    {
        $expediente->load(['cliente', 'fallecido', 'urna', 'vehiculo', 'chofer', 'ayudante', 'convenio']);

        BitacoraAuditoria::create([
            'modulo' => 'Documentos / PDF',
            'tabla_afectada' => 'expedientes',
            'registro_id' => $expediente->id,
            'accion' => 'EMISION_PDF',
            'valores_nuevos_json' => ['documento' => 'Contrato de Prestación de Servicios', 'folio' => $expediente->folio],
            'ip_origen' => request()->ip(),
            'usuario_id' => Auth::id(),
        ]);

        $pdf = Pdf::loadView('pdf.contrato_servicio', compact('expediente'));

        return $pdf->stream("Contrato_{$expediente->folio}.pdf");
    }

    private function calcularCobertura(?int $convenioId, int $valorTotal, float $valorUf): int
    {
        if (! $convenioId) {
            return 0;
        }
        $convenio = ConvenioPrevisional::find($convenioId);
        if (! $convenio) {
            return 0;
        }
        if ($convenio->tipo === 'AFP' && $convenio->monto_tope_uf > 0) {
            $cobertura = (int) round($convenio->monto_tope_uf * $valorUf);
        } else {
            $cobertura = (int) $convenio->monto_tope_clp;
        }

        return min($cobertura, $valorTotal);
    }
}
