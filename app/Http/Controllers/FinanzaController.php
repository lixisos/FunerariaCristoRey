<?php

namespace App\Http\Controllers;

use App\Models\AbonoPago;
use App\Models\BitacoraAuditoria;
use App\Models\ConvenioPrevisional;
use App\Models\Expediente;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FinanzaController extends Controller
{
    public function index(Request $request): View
    {
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $totales = DB::table('expedientes')
            ->whereNull('deleted_at')
            ->where('estado_expediente', '!=', 'ANULADO_INACTIVO')
            ->selectRaw(
                'COALESCE(SUM(valor_total_servicio), 0) AS total_facturado,
                 COALESCE(SUM(monto_cobertura_convenio), 0) AS total_convenios,
                 COALESCE(SUM(saldo_pendiente), 0) AS total_por_cobrar'
            )
            ->first();

        $totalFacturado = (int) ($totales->total_facturado ?? 0);
        $totalConvenios = (int) ($totales->total_convenios ?? 0);
        $totalPorCobrar = (int) ($totales->total_por_cobrar ?? 0);

        // 1. Consulta filtrada y paginada de Cuentas por Expediente
        $expQuery = Expediente::with(['cliente', 'fallecido', 'convenio'])
            ->where('estado_expediente', '!=', 'ANULADO_INACTIVO');

        if ($request->filled('buscar_cuenta')) {
            $term = '%'.trim((string) $request->buscar_cuenta).'%';
            $expQuery->where(function ($q) use ($term, $like): void {
                $q->where('folio', $like, $term)
                    ->orWhereHas('cliente', fn ($c) => $c->where('nombre_completo', $like, $term)->orWhere('rut', $like, $term))
                    ->orWhereHas('fallecido', fn ($f) => $f->where('nombre_completo', $like, $term));
            });
        }

        if ($request->estado_cuenta === 'pendiente') {
            $expQuery->where('saldo_pendiente', '>', 0);
        } elseif ($request->estado_cuenta === 'pagado') {
            $expQuery->where('saldo_pendiente', '<=', 0);
        }

        $expedientes = $expQuery
            ->latest()
            ->paginate(10, ['*'], 'page_cuentas')
            ->withQueryString();

        // 2. Consulta filtrada y paginada de Comprobantes Emitidos
        $abonoQuery = AbonoPago::with(['expediente.cliente'])
            ->where('estado_pago', 'VALIDO');

        if ($request->filled('buscar_comprobante')) {
            $termComp = '%'.trim((string) $request->buscar_comprobante).'%';
            $abonoQuery->where(function ($q) use ($termComp, $like): void {
                $q->where('folio_comprobante', $like, $termComp)
                    ->orWhereHas('expediente', function ($e) use ($termComp, $like): void {
                        $e->where('folio', $like, $termComp)
                            ->orWhereHas('cliente', fn ($c) => $c->where('nombre_completo', $like, $termComp)->orWhere('rut', $like, $termComp));
                    });
            });
        }

        if ($request->filled('metodo_pago')) {
            $abonoQuery->where('metodo_pago', $request->metodo_pago);
        }

        $abonos = $abonoQuery
            ->latest('fecha_pago')
            ->paginate(10, ['*'], 'page_comprobantes')
            ->withQueryString();

        $convenios = ConvenioPrevisional::where('estado_convenio', 'HABILITADO')->get();

        return view('finanzas.index', compact(
            'expedientes',
            'abonos',
            'convenios',
            'totalFacturado',
            'totalConvenios',
            'totalPorCobrar'
        ));
    }

    public function registrarAbono(Request $request, Expediente $expediente): RedirectResponse
    {
        $data = $request->validate([
            'monto_abonado' => 'required|integer|min:1000',
            'metodo_pago' => 'required|in:EFECTIVO,TRANSFERENCIA,TARJETA_DEBITO,TARJETA_CREDITO,CONVENIO_DIRECTO',
            'observacion' => 'nullable|string|max:255',
        ]);

        if ($data['monto_abonado'] > $expediente->saldo_pendiente) {
            return back()->with('error', 'El monto ingresado supera el saldo pendiente ($'.number_format($expediente->saldo_pendiente, 0, ',', '.').').');
        }

        $abono = DB::transaction(function () use ($data, $expediente): AbonoPago {
            $folioComp = 'COMP-'.date('Y').'-'.str_pad((string) (AbonoPago::withTrashed()->count() + 1), 4, '0', STR_PAD_LEFT);

            $nuevoAbono = AbonoPago::create([
                'folio_comprobante' => $folioComp,
                'expediente_id' => $expediente->id,
                'monto_abonado' => $data['monto_abonado'],
                'metodo_pago' => $data['metodo_pago'],
                'fecha_pago' => now(),
                'observacion' => $data['observacion'] ?? 'Abono registrado en caja',
                'registrado_por_id' => Auth::id(),
            ]);

            $expediente->decrement('saldo_pendiente', (int) $data['monto_abonado']);

            return $nuevoAbono;
        });

        return back()->with('success', "Abono registrado con folio {$abono->folio_comprobante}. Saldo actualizado.");
    }

    public function anularAbono(AbonoPago $abono): RedirectResponse
    {
        if ($abono->estado_pago !== 'VALIDO') {
            return back()->with('error', 'Este comprobante ya se encuentra anulado.');
        }

        DB::transaction(function () use ($abono): void {
            $abono->update(['estado_pago' => 'ANULADO_INACTIVO']);
            if ($abono->expediente) {
                $abono->expediente->increment('saldo_pendiente', (int) $abono->monto_abonado);
            }

            BitacoraAuditoria::create([
                'modulo' => 'Finanzas / Caja',
                'tabla_afectada' => 'abonos_pagos',
                'registro_id' => $abono->id,
                'accion' => 'ANULACION_COMPROBANTE',
                'valores_anteriores_json' => [
                    'folio' => $abono->folio_comprobante,
                    'monto' => $abono->monto_abonado,
                ],
                'valores_nuevos_json' => ['estado_pago' => 'ANULADO_INACTIVO'],
                'ip_origen' => request()->ip(),
                'usuario_id' => Auth::id(),
            ]);
        });

        return back()->with('success', "Comprobante {$abono->folio_comprobante} anulado y saldo reintegrado al expediente.");
    }

    public function comprobantePdf(AbonoPago $abono): Response
    {
        $abono->load(['expediente.cliente', 'expediente.fallecido', 'expediente.convenio']);

        BitacoraAuditoria::create([
            'modulo' => 'Finanzas / PDF',
            'tabla_afectada' => 'abonos_pagos',
            'registro_id' => $abono->id,
            'accion' => 'EMISION_PDF',
            'valores_nuevos_json' => ['documento' => 'Comprobante Foliado', 'folio' => $abono->folio_comprobante],
            'ip_origen' => request()->ip(),
            'usuario_id' => Auth::id(),
        ]);

        $pdf = Pdf::loadView('pdf.comprobante_pago', compact('abono'));

        return $pdf->stream("{$abono->folio_comprobante}.pdf");
    }

    public function cobranzas(Request $request): View
    {
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $baseQuery = Expediente::where('estado_expediente', '!=', 'ANULADO_INACTIVO')
            ->where('saldo_pendiente', '>', 0);

        $todosSaldos = (clone $baseQuery)
            ->select(['id', 'saldo_pendiente', 'created_at'])
            ->get()
            ->map(function (Expediente $exp): Expediente {
                $dias = (int) Carbon::parse($exp->created_at)->diffInDays(now());
                $exp->tramo_mora = $dias >= 30 ? 'CRITICA_30' : ($dias >= 20 ? 'GESTION_20' : ($dias >= 10 ? 'PREVENTIVA_10' : 'AL_DIA'));

                return $exp;
            });

        $tramo10 = $todosSaldos->where('tramo_mora', 'PREVENTIVA_10');
        $tramo20 = $todosSaldos->where('tramo_mora', 'GESTION_20');
        $tramo30 = $todosSaldos->where('tramo_mora', 'CRITICA_30');

        $deudoresQuery = clone $baseQuery;

        if ($request->filled('buscar')) {
            $term = '%'.trim((string) $request->buscar).'%';
            $deudoresQuery->where(function ($q) use ($term, $like): void {
                $q->where('folio', $like, $term)
                    ->orWhereHas('cliente', fn ($c) => $c->where('nombre_completo', $like, $term)->orWhere('rut', $like, $term))
                    ->orWhereHas('fallecido', fn ($f) => $f->where('nombre_completo', $like, $term));
            });
        }

        if ($request->filled('tramo')) {
            $idsTramo = $todosSaldos->where('tramo_mora', $request->tramo)->pluck('id');
            $deudoresQuery->whereIn('id', $idsTramo);
        }

        $deudores = $deudoresQuery
            ->with(['cliente', 'fallecido', 'convenio'])
            ->orderBy('created_at', 'asc')
            ->paginate(12)
            ->withQueryString();

        $deudores->getCollection()->transform(function (Expediente $exp): Expediente {
            $dias = (int) Carbon::parse($exp->created_at)->diffInDays(now());
            $exp->dias_mora = $dias;
            $exp->tramo_mora = $dias >= 30 ? 'CRITICA_30' : ($dias >= 20 ? 'GESTION_20' : ($dias >= 10 ? 'PREVENTIVA_10' : 'AL_DIA'));

            return $exp;
        });

        return view('finanzas.cobranzas', compact('deudores', 'tramo10', 'tramo20', 'tramo30'));
    }

    public function convenios(Request $request): View
    {
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $convenios = ConvenioPrevisional::withCount('expedientes')->orderBy('nombre')->get();

        $expConvQuery = Expediente::with(['cliente', 'fallecido', 'convenio'])
            ->where('estado_expediente', '!=', 'ANULADO_INACTIVO')
            ->whereNotNull('convenio_id');

        if ($request->filled('buscar')) {
            $term = '%'.trim((string) $request->buscar).'%';
            $expConvQuery->where(function ($q) use ($term, $like): void {
                $q->where('folio', $like, $term)
                    ->orWhereHas('cliente', fn ($c) => $c->where('nombre_completo', $like, $term)->orWhere('rut', $like, $term))
                    ->orWhereHas('fallecido', fn ($f) => $f->where('nombre_completo', $like, $term)->orWhere('rut', $like, $term));
            });
        }

        if ($request->filled('convenio_id')) {
            $expConvQuery->where('convenio_id', $request->convenio_id);
        }

        $expedientesConvenio = $expConvQuery
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('finanzas.convenios', compact('convenios', 'expedientesConvenio'));
    }

    public function storeConvenio(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:IPS,AFP,FFAA_ORDEN,SINDICATO,PARTICULAR',
            'monto_tope_clp' => 'required|integer|min:0',
            'monto_tope_uf' => 'nullable|numeric|min:0|max:50',
            'requiere_resolucion' => 'nullable|boolean',
        ]);

        ConvenioPrevisional::create([
            'nombre' => $data['nombre'],
            'tipo' => $data['tipo'],
            'monto_tope_clp' => $data['monto_tope_clp'],
            'monto_tope_uf' => $data['monto_tope_uf'] ?? 0,
            'requiere_resolucion' => $request->boolean('requiere_resolucion'),
            'estado_convenio' => 'HABILITADO',
            'creado_por_id' => Auth::id(),
        ]);

        return back()->with('success', 'Convenio previsional registrado correctamente.');
    }

    public function updateConvenio(Request $request, ConvenioPrevisional $convenio): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:IPS,AFP,FFAA_ORDEN,SINDICATO,PARTICULAR',
            'monto_tope_clp' => 'required|integer|min:0',
            'monto_tope_uf' => 'nullable|numeric|min:0|max:50',
        ]);

        $convenio->update([
            'nombre' => $data['nombre'],
            'tipo' => $data['tipo'],
            'monto_tope_clp' => $data['monto_tope_clp'],
            'monto_tope_uf' => $data['monto_tope_uf'] ?? 0,
        ]);

        return back()->with('success', "Convenio \"{$convenio->nombre}\" actualizado correctamente.");
    }

    public function destroyConvenio(ConvenioPrevisional $convenio): RedirectResponse
    {
        $enUsoActivo = $convenio->expedientes()
            ->whereIn('estado_expediente', ['ABIERTO', 'EN_PROCESO'])
            ->exists();

        if ($enUsoActivo) {
            return back()->with('error', "No se puede eliminar el convenio \"{$convenio->nombre}\" porque tiene expedientes activos asociados.");
        }

        $nombre = $convenio->nombre;
        $convenio->delete();

        return back()->with('success', "Convenio \"{$nombre}\" eliminado correctamente.");
    }

    public function convenioPdf(Expediente $expediente): Response
    {
        $expediente->load(['cliente', 'fallecido', 'convenio', 'urna']);

        BitacoraAuditoria::create([
            'modulo' => 'Finanzas / Convenios',
            'tabla_afectada' => 'expedientes',
            'registro_id' => $expediente->id,
            'accion' => 'EMISION_PDF',
            'valores_nuevos_json' => ['documento' => 'Liquidación Convenio Previsional', 'folio' => $expediente->folio],
            'ip_origen' => request()->ip(),
            'usuario_id' => Auth::id(),
        ]);

        $pdf = Pdf::loadView('pdf.liquidacion_convenios', compact('expediente'));

        return $pdf->stream("Liquidacion_Convenio_{$expediente->folio}.pdf");
    }
}