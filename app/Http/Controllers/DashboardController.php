<?php

namespace App\Http\Controllers;

use App\Models\BitacoraAuditoria;
use App\Models\Cliente;
use App\Models\Expediente;
use App\Models\Urna;
use App\Models\User;
use App\Models\Vehiculo;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $inicioMes = now()->startOfMonth();
        $inicioMesAnterior = now()->subMonthNoOverflow()->startOfMonth();

        // 1. Consolidado financiero y operativo de expedientes
        $expResumen = DB::table('expedientes')
            ->whereNull('deleted_at')
            ->selectRaw(
                "SUM(CASE WHEN estado_expediente != 'ANULADO_INACTIVO' AND created_at >= ? THEN 1 ELSE 0 END) AS servicios_mes,
                 SUM(CASE WHEN estado_expediente != 'ANULADO_INACTIVO' AND created_at >= ? AND created_at < ? THEN 1 ELSE 0 END) AS servicios_mes_anterior,
                 SUM(CASE WHEN estado_expediente != 'ANULADO_INACTIVO' THEN 1 ELSE 0 END) AS servicios_activos_total,
                 COALESCE(SUM(CASE WHEN estado_expediente != 'ANULADO_INACTIVO' AND created_at >= ? THEN valor_total_servicio ELSE 0 END), 0) AS facturado_mes,
                 COALESCE(SUM(CASE WHEN estado_expediente != 'ANULADO_INACTIVO' THEN valor_total_servicio ELSE 0 END), 0) AS facturado_total,
                 COALESCE(SUM(CASE WHEN estado_expediente != 'ANULADO_INACTIVO' THEN monto_cobertura_convenio ELSE 0 END), 0) AS convenios_total,
                 SUM(CASE WHEN estado_expediente != 'ANULADO_INACTIVO' AND convenio_id IS NOT NULL THEN 1 ELSE 0 END) AS servicios_con_convenio,
                 COALESCE(SUM(CASE WHEN estado_expediente != 'ANULADO_INACTIVO' THEN saldo_pendiente ELSE 0 END), 0) AS saldo_por_cobrar,
                 SUM(CASE WHEN estado_expediente != 'ANULADO_INACTIVO' AND saldo_pendiente > 0 THEN 1 ELSE 0 END) AS cantidad_deudores,
                 SUM(CASE WHEN estado_expediente = 'CERRADO' THEN 1 ELSE 0 END) AS completados,
                 SUM(CASE WHEN estado_expediente = 'EN_PROCESO' THEN 1 ELSE 0 END) AS en_proceso,
                 SUM(CASE WHEN estado_expediente = 'ABIERTO' THEN 1 ELSE 0 END) AS pendientes",
                [$inicioMes, $inicioMesAnterior, $inicioMes, $inicioMes]
            )
            ->first();

        $serviciosMes = (int) ($expResumen->servicios_mes ?? 0);
        $serviciosMesAnterior = (int) ($expResumen->servicios_mes_anterior ?? 0);
        $serviciosActivosTotal = (int) ($expResumen->servicios_activos_total ?? 0);

        $facturadoMes = (int) ($expResumen->facturado_mes ?? 0);
        $facturadoTotal = (int) ($expResumen->facturado_total ?? 0);
        $ticketPromedio = $serviciosActivosTotal > 0 ? (int) round($facturadoTotal / $serviciosActivosTotal) : 0;

        $coberturaConveniosTotal = (int) ($expResumen->convenios_total ?? 0);
        $serviciosConConvenio = (int) ($expResumen->servicios_con_convenio ?? 0);
        $saldoPorCobrar = (int) ($expResumen->saldo_por_cobrar ?? 0);
        $cantidadDeudores = (int) ($expResumen->cantidad_deudores ?? 0);

        $estadosStats = [
            'completados' => (int) ($expResumen->completados ?? 0),
            'en_proceso' => (int) ($expResumen->en_proceso ?? 0),
            'pendientes' => (int) ($expResumen->pendientes ?? 0),
        ];

        $familiasAtendidas = Cliente::count();

        // 2. Ingresos en caja y desglose por medio de pago
        $abonosResumen = DB::table('abonos_pagos')
            ->whereNull('deleted_at')
            ->where('estado_pago', 'VALIDO')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN fecha_pago >= ? THEN monto_abonado ELSE 0 END), 0) AS ingresos_mes,
                 SUM(CASE WHEN fecha_pago >= ? THEN 1 ELSE 0 END) AS comprobantes_mes,
                 COALESCE(SUM(monto_abonado), 0) AS ingresos_total',
                [$inicioMes, $inicioMes]
            )
            ->first();

        $ingresosMes = (int) ($abonosResumen->ingresos_mes ?? 0);
        $comprobantesMes = (int) ($abonosResumen->comprobantes_mes ?? 0);
        $ingresosTotal = (int) ($abonosResumen->ingresos_total ?? 0);

        $pagosPorMetodo = DB::table('abonos_pagos')
            ->whereNull('deleted_at')
            ->where('estado_pago', 'VALIDO')
            ->selectRaw('metodo_pago, COUNT(*) AS cantidad, COALESCE(SUM(monto_abonado), 0) AS total')
            ->groupBy('metodo_pago')
            ->get();

        // 3. Inventario real (Salón Calle Blanco vs. Taller + Normativa Seremi)
        $urnasResumen = DB::table('urnas')
            ->whereNull('deleted_at')
            ->where('estado_urna', 'HABILITADO')
            ->selectRaw(
                "COALESCE(SUM(stock_salon), 0) AS salon,
                 COALESCE(SUM(stock_taller), 0) AS taller,
                 SUM(CASE WHEN reservada_en_vida THEN 1 ELSE 0 END) AS reservadas,
                 SUM(CASE WHEN categoria_tamano = 'NINO_SEREMI' THEN (stock_salon + stock_taller) ELSE 0 END) AS stock_nino,
                 SUM(CASE WHEN categoria_tamano = 'ESPECIAL_XL' THEN (stock_salon + stock_taller) ELSE 0 END) AS stock_xl"
            )
            ->first();

        $stockSalon = (int) ($urnasResumen->salon ?? 0);
        $stockTaller = (int) ($urnasResumen->taller ?? 0);
        $urnasDisponibles = $stockSalon + $stockTaller;
        $urnasReservadasVida = (int) ($urnasResumen->reservadas ?? 0);
        $stockNinoSeremi = (int) ($urnasResumen->stock_nino ?? 0);
        $stockEspecialXl = (int) ($urnasResumen->stock_xl ?? 0);

        // 4. Flota vehicular y personal en turno
        $vehiculos = Vehiculo::orderBy('patente')->get();
        $vehiculosDisponibles = $vehiculos->where('estado_vehiculo', 'DISPONIBLE')->count();
        $vehiculosEnServicio = $vehiculos->where('estado_vehiculo', 'EN_SERVICIO')->count();
        $vehiculosMantencion = $vehiculos->where('estado_vehiculo', 'MANTENCION')->count();

        $personal = User::with(['rol', 'delegadoA'])
            ->where('estado_usuario', 'HABILITADO')
            ->orderBy('name')
            ->get();
        $personalActivo = $personal->where('en_vacaciones', false);
        $personalAusente = $personal->where('en_vacaciones', true);

        // 5. Expedientes activos y alertas críticas reales
        $expedientesActivos = Expediente::with(['fallecido', 'cliente', 'vehiculo', 'chofer', 'ayudante', 'convenio'])
            ->withCount('documentos')
            ->whereIn('estado_expediente', ['ABIERTO', 'EN_PROCESO'])
            ->orderBy('fecha_cortejo')
            ->get();

        $alertasSeremi = $expedientesActivos->filter(
            fn (Expediente $exp): bool => $exp->limite_legal_seremi_at !== null
                && $exp->limite_legal_seremi_at->diffInHours(now(), false) >= -6
        );

        $alertasSmlSinOficio = $expedientesActivos->filter(
            fn (Expediente $exp): bool => (bool) ($exp->fallecido?->intervencion_sml)
                && empty($exp->fallecido?->numero_oficio_fiscalia)
        );

        // 6. Evolución últimos 6 meses (cantidad y facturación)
        $desdeSeisMeses = now()->subMonths(5)->startOfMonth();
        $monthExpr = match (DB::getDriverName()) {
            'pgsql' => "TO_CHAR(created_at, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', created_at)",
            default => "DATE_FORMAT(created_at, '%Y-%m')",
        };

        $conteosPorMes = DB::table('expedientes')
            ->whereNull('deleted_at')
            ->where('estado_expediente', '!=', 'ANULADO_INACTIVO')
            ->where('created_at', '>=', $desdeSeisMeses)
            ->selectRaw("{$monthExpr} AS ym, COUNT(*) AS total, COALESCE(SUM(valor_total_servicio), 0) AS monto")
            ->groupByRaw($monthExpr)
            ->get()
            ->keyBy('ym');

        $mesesLabels = [];
        $mesesValores = [];
        $mesesMontos = [];
        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $ym = $fecha->format('Y-m');
            $mesesLabels[] = ucfirst($fecha->locale('es')->isoFormat('MMM YYYY'));
            $mesesValores[] = (int) ($conteosPorMes[$ym]->total ?? 0);
            $mesesMontos[] = (int) ($conteosPorMes[$ym]->monto ?? 0);
        }

        // 7. Convenios previsionales (cantidad + monto bonificado)
        $conveniosDetalle = DB::table('expedientes')
            ->leftJoin('convenios_previsionales', 'expedientes.convenio_id', '=', 'convenios_previsionales.id')
            ->whereNull('expedientes.deleted_at')
            ->where('expedientes.estado_expediente', '!=', 'ANULADO_INACTIVO')
            ->selectRaw(
                "CASE
                    WHEN UPPER(COALESCE(convenios_previsionales.nombre, '')) LIKE '%IPS%' THEN 'IPS'
                    WHEN UPPER(COALESCE(convenios_previsionales.nombre, '')) LIKE '%AFP%' THEN 'AFP'
                    WHEN UPPER(COALESCE(convenios_previsionales.nombre, '')) LIKE '%DIPRECA%' THEN 'Dipreca'
                    WHEN UPPER(COALESCE(convenios_previsionales.nombre, '')) LIKE '%CAPREDENA%' THEN 'Capredena'
                    ELSE 'Particular'
                 END AS grupo,
                 COUNT(*) AS cantidad,
                 COALESCE(SUM(expedientes.monto_cobertura_convenio), 0) AS monto_total"
            )
            ->groupBy('grupo')
            ->get()
            ->keyBy('grupo');

        $conveniosStats = [
            'IPS' => (int) ($conveniosDetalle['IPS']->cantidad ?? 0),
            'AFP' => (int) ($conveniosDetalle['AFP']->cantidad ?? 0),
            'Dipreca' => (int) ($conveniosDetalle['Dipreca']->cantidad ?? 0),
            'Capredena' => (int) ($conveniosDetalle['Capredena']->cantidad ?? 0),
            'Particular' => (int) ($conveniosDetalle['Particular']->cantidad ?? 0),
        ];

        $conveniosMontos = [
            'IPS' => (int) ($conveniosDetalle['IPS']->monto_total ?? 0),
            'AFP' => (int) ($conveniosDetalle['AFP']->monto_total ?? 0),
            'Dipreca' => (int) ($conveniosDetalle['Dipreca']->monto_total ?? 0),
            'Capredena' => (int) ($conveniosDetalle['Capredena']->monto_total ?? 0),
            'Particular' => (int) ($conveniosDetalle['Particular']->monto_total ?? 0),
        ];

        $totalExpCount = max(1, array_sum($conveniosStats));

        // 8. Últimos servicios, cobranzas pendientes y stock crítico
        $ultimosServicios = Expediente::with(['fallecido', 'convenio'])
            ->latest()
            ->take(5)
            ->get();

        $cobranzasPendientes = Expediente::with(['cliente', 'fallecido'])
            ->where('saldo_pendiente', '>', 0)
            ->where('estado_expediente', '!=', 'ANULADO_INACTIVO')
            ->orderBy('created_at')
            ->take(6)
            ->get()
            ->map(function (Expediente $exp): Expediente {
                $baseDate = $exp->fecha_vencimiento_pago ?? $exp->created_at;
                $exp->dias_atraso = max(0, (int) Carbon::parse($baseDate)->diffInDays(now()));

                return $exp;
            });

        $deudoresCriticos30 = $cobranzasPendientes->where('dias_atraso', '>=', 30)->count();

        $urnasStockCritico = Urna::where('estado_urna', 'HABILITADO')
            ->selectRaw('*, (stock_salon + stock_taller) as stock_total')
            ->orderBy('stock_total')
            ->take(6)
            ->get();

        // 9. Bitácora reciente
        $bitacora = BitacoraAuditoria::with('usuario')
            ->latest('created_at')
            ->take(6)
            ->get();

        return view('dashboard', compact(
            'serviciosMes',
            'serviciosMesAnterior',
            'serviciosActivosTotal',
            'facturadoMes',
            'facturadoTotal',
            'ticketPromedio',
            'coberturaConveniosTotal',
            'serviciosConConvenio',
            'familiasAtendidas',
            'ingresosMes',
            'comprobantesMes',
            'ingresosTotal',
            'pagosPorMetodo',
            'saldoPorCobrar',
            'cantidadDeudores',
            'deudoresCriticos30',
            'urnasDisponibles',
            'stockSalon',
            'stockTaller',
            'urnasReservadasVida',
            'stockNinoSeremi',
            'stockEspecialXl',
            'vehiculos',
            'vehiculosDisponibles',
            'vehiculosEnServicio',
            'vehiculosMantencion',
            'personal',
            'personalActivo',
            'personalAusente',
            'expedientesActivos',
            'alertasSeremi',
            'alertasSmlSinOficio',
            'mesesLabels',
            'mesesValores',
            'mesesMontos',
            'conveniosStats',
            'conveniosMontos',
            'totalExpCount',
            'estadosStats',
            'ultimosServicios',
            'cobranzasPendientes',
            'urnasStockCritico',
            'bitacora'
        ));
    }

    public function actualizarDelegacion(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'en_vacaciones' => 'nullable|boolean',
            'tipo_ausencia' => 'nullable|in:ACTIVO,VACACIONES,PERMISO,LICENCIA_MEDICA',
            'ausencia_hasta' => 'nullable|date',
            'delegado_a_id' => 'nullable|exists:users,id',
        ]);

        $enVacaciones = $request->boolean('en_vacaciones');
        $user->update([
            'en_vacaciones' => $enVacaciones,
            'tipo_ausencia' => $enVacaciones ? ($data['tipo_ausencia'] ?? 'VACACIONES') : 'ACTIVO',
            'ausencia_hasta' => $enVacaciones ? ($data['ausencia_hasta'] ?? null) : null,
            'delegado_a_id' => $enVacaciones ? ($data['delegado_a_id'] ?? null) : null,
        ]);

        return back()->with('success', "Disponibilidad y delegación de {$user->name} actualizada correctamente.");
    }
}