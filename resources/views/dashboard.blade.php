<x-app-layout>
    <!-- 1. HERO BANNER CON RELOJ EN VIVO Y RESUMEN OPERATIVO DE CASA MATRIZ -->
    <section class="relative rounded-2xl overflow-hidden bg-gradient-to-r from-[#231b59] via-[#34287d] to-[#4838a3] text-white shadow-lg"
             x-data="{
                 hora: '',
                 fecha: '',
                 actualizarReloj() {
                     const ahora = new Date();
                     this.hora = ahora.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                     const f = ahora.toLocaleDateString('es-CL', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                     this.fecha = f.charAt(0).toUpperCase() + f.slice(1);
                 }
             }"
             x-init="actualizarReloj(); setInterval(() => actualizarReloj(), 1000)">

        <img src="{{ asset('images/hero-cristo-rey.jpg') }}"
             alt="Funeraria Cristo Rey"
             class="absolute inset-0 w-full h-full object-cover object-right opacity-30 mix-blend-luminosity pointer-events-none">
        <div class="absolute inset-0 bg-gradient-to-r from-[#1e174d]/95 via-[#2c226e]/80 to-transparent pointer-events-none"></div>

        <div class="relative z-10 p-6 sm:px-8 sm:py-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/15 text-[11px] font-semibold text-violet-100">
                    Panel de Control Gerencial · Casa Matriz Calle Manuel Blanco, Temuco
                </span>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">
                    Bienvenido, {{ Auth::user()->name }}
                </h1>
                <p class="text-xs sm:text-sm text-indigo-200">
                    Estado general en tiempo real: <strong>{{ $expedientesActivos->count() }}</strong> servicios activos en curso,
                    <strong>{{ $urnasDisponibles }}</strong> urnas en inventario y <strong>{{ $personalActivo->count() }}</strong> colaboradores disponibles hoy.
                </p>
            </div>

            <div class="lg:text-right space-y-1 shrink-0 bg-white/10 backdrop-blur-sm px-4 py-3 rounded-xl border border-white/15">
                <p class="text-xs text-indigo-200" x-text="fecha"></p>
                <p class="text-2xl sm:text-3xl font-extrabold tracking-tight font-mono" x-text="hora"></p>
                <div class="flex lg:justify-end gap-2 pt-1">
                    <a href="{{ route('expedientes.index') }}" class="px-3 py-1 rounded-lg bg-white text-indigo-950 text-xs font-bold hover:bg-indigo-50 transition">
                        + Nuevo Expediente
                    </a>
                    <a href="{{ route('finanzas.index') }}" class="px-3 py-1 rounded-lg bg-indigo-500/40 border border-white/25 text-white text-xs font-semibold hover:bg-indigo-500/60 transition">
                        Registrar Abono
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. FILA DE 6 TARJETAS KPI CON DESGLOSE NUMÉRICO REAL -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <!-- KPI 1: Servicios -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col justify-between space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Servicios del mes</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-bold">📋</span>
            </div>
            <div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-extrabold text-slate-900">{{ $serviciosMes }}</span>
                    <span class="text-[11px] font-semibold text-slate-500">({{ $serviciosActivosTotal }} acumulados)</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-1">
                    Mes anterior: <strong class="text-slate-700">{{ $serviciosMesAnterior }}</strong> ·
                    {{ $estadosStats['pendientes'] }} abiertos / {{ $estadosStats['en_proceso'] }} en curso
                </p>
            </div>
        </div>

        <!-- KPI 2: Facturación Contratada -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col justify-between space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Contratado mes</span>
                <span class="w-8 h-8 rounded-lg bg-violet-100 text-violet-700 flex items-center justify-center text-sm font-bold">📑</span>
            </div>
            <div>
                <span class="text-xl font-extrabold text-slate-900 block">${{ number_format($facturadoMes, 0, ',', '.') }}</span>
                <p class="text-[11px] text-slate-500 mt-1">
                    Ticket prom.: <strong class="text-slate-700">${{ number_format($ticketPromedio, 0, ',', '.') }}</strong> · {{ $familiasAtendidas }} familias
                </p>
            </div>
        </div>

        <!-- KPI 3: Ingresos Reales en Caja -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col justify-between space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Recaudado caja</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-extrabold">$</span>
            </div>
            <div>
                <span class="text-xl font-extrabold text-emerald-700 block">${{ number_format($ingresosMes, 0, ',', '.') }}</span>
                <p class="text-[11px] text-slate-500 mt-1">
                    <strong class="text-slate-700">{{ $comprobantesMes }}</strong> comprobantes foliados este mes
                </p>
            </div>
        </div>

        <!-- KPI 4: Convenios IPS / AFP -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col justify-between space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Convenios IPS/AFP</span>
                <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold">🏛️</span>
            </div>
            <div>
                <span class="text-xl font-extrabold text-indigo-900 block">${{ number_format($coberturaConveniosTotal, 0, ',', '.') }}</span>
                <p class="text-[11px] text-slate-500 mt-1">
                    Bonificado en <strong class="text-slate-700">{{ $serviciosConConvenio }}</strong> de {{ $serviciosActivosTotal }} servicios
                </p>
            </div>
        </div>

        <!-- KPI 5: Saldo Por Cobrar -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col justify-between space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Saldo por cobrar</span>
                <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-bold">💳</span>
            </div>
            <div>
                <span class="text-xl font-extrabold text-amber-700 block">${{ number_format($saldoPorCobrar, 0, ',', '.') }}</span>
                <p class="text-[11px] text-slate-500 mt-1">
                    En <strong class="text-slate-700">{{ $cantidadDeudores }}</strong> expedientes ({{ $deudoresCriticos30 }} con +30 días)
                </p>
            </div>
        </div>

        <!-- KPI 6: Doble Bodega + Flota -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col justify-between space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Bodega y Flota</span>
                <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-sm font-bold">⚰️</span>
            </div>
            <div>
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-extrabold text-slate-900">{{ $urnasDisponibles }}</span>
                    <span class="text-[11px] font-bold text-indigo-700">Salón {{ $stockSalon }}/24 · Taller {{ $stockTaller }}/30</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-1">
                    Flota: <strong class="text-slate-700">{{ $vehiculosDisponibles }}/{{ $vehiculos->count() }}</strong> disp. · <strong class="text-slate-700">{{ $urnasReservadasVida }}</strong> res. en vida
                </p>
            </div>
        </div>
    </section>

    <!-- 3. CENTRO UNIFICADO DE ALERTAS OPERATIVAS Y LEGALES + AGENDA DEL DÍA -->
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <!-- Izquierda (7 cols): Centro Unificado de Alertas -->
        <div class="lg:col-span-7 bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-sm sm:text-base text-slate-800 flex items-center gap-2">
                        <span class="text-rose-600">▲</span> Centro de Alertas Prioritarias (Legal, Bodega y Caja)
                    </h2>
                    <p class="text-[11px] text-slate-500">Plazo 48 hrs Registro Civil/Seremi, Oficios Fiscalía SML, stock normativo y saldos pendientes</p>
                </div>
                <a href="{{ route('notificaciones.index') }}" class="text-xs font-semibold text-indigo-700 hover:underline shrink-0">Ver centro →</a>
            </div>

            <div class="space-y-2.5">
                @foreach($alertasSeremi as $alertaS)
                    <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded bg-rose-600 text-white text-[10px] font-bold">PLAZO SEREMI 48 HRS</span>
                                <span class="font-bold text-rose-950">{{ $alertaS->folio }} — {{ $alertaS->fallecido->nombre_completo }}</span>
                            </div>
                            <p class="text-[11px] text-rose-800">
                                Vence: <strong>{{ $alertaS->limite_legal_seremi_at->format('d/m/Y H:i') }} hrs</strong> · Documentos cargados: <strong>{{ $alertaS->documentos_count }}</strong>
                            </p>
                        </div>
                        <a href="{{ route('expedientes.show', $alertaS) }}" class="px-3 py-1.5 rounded-lg bg-white border border-rose-300 text-rose-900 font-bold hover:bg-rose-100 shrink-0">
                            Revisar
                        </a>
                    </div>
                @endforeach

                @foreach($alertasSmlSinOficio as $alertaSml)
                    <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded bg-amber-600 text-white text-[10px] font-bold">TRÁMITE SML / FISCALÍA</span>
                                <span class="font-bold text-amber-950">{{ $alertaSml->folio }} — {{ $alertaSml->fallecido->nombre_completo }}</span>
                            </div>
                            <p class="text-[11px] text-amber-900">
                                Caso con intervención Servicio Médico Legal pendiente de registrar N° de Oficio de Fiscalía.
                            </p>
                        </div>
                        <a href="{{ route('expedientes.show', $alertaSml) }}" class="px-3 py-1.5 rounded-lg bg-white border border-amber-300 text-amber-900 font-bold hover:bg-amber-100 shrink-0">
                            Completar
                        </a>
                    </div>
                @endforeach

                @foreach($urnasStockCritico->where('stock_total', '<=', 2) as $uCrit)
                    <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-purple-50 border border-purple-200 text-xs">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded bg-purple-700 text-white text-[10px] font-bold">STOCK CRÍTICO BODEGA</span>
                                <span class="font-bold text-purple-950">{{ $uCrit->codigo_sku }} — {{ $uCrit->modelo }}</span>
                            </div>
                            <p class="text-[11px] text-purple-900">
                                Quedan <strong>{{ $uCrit->stock_total }} unidad(es)</strong> (Salón: {{ $uCrit->stock_salon }} · Taller: {{ $uCrit->stock_taller }}) · Proveedor: {{ \App\Support\Etiquetas::texto($uCrit->proveedor_origen) }}
                            </p>
                        </div>
                        <a href="{{ route('bodega.index') }}" class="px-3 py-1.5 rounded-lg bg-white border border-purple-300 text-purple-900 font-bold hover:bg-purple-100 shrink-0">
                            Reponer
                        </a>
                    </div>
                @endforeach

                @if($alertasSeremi->isEmpty() && $alertasSmlSinOficio->isEmpty() && $urnasStockCritico->where('stock_total', '<=', 2)->isEmpty())
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 font-medium">
                        Sin alertas críticas pendientes en plazos legales ni quiebres de stock en bodega.
                    </div>
                @endif
            </div>
        </div>

        <!-- Derecha (5 cols): Agenda Operativa Detallada -->
        <div class="lg:col-span-5 bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h2 class="font-bold text-sm sm:text-base text-slate-800 flex items-center gap-2">
                            <span>📅</span> Próximos Servicios y Despacho de Flota
                        </h2>
                        <p class="text-[11px] text-slate-500">Horarios de velatorio, cortejo, carroza y chofer asignado</p>
                    </div>
                    <a href="{{ route('calendario.index') }}" class="text-xs font-semibold text-indigo-700 hover:underline shrink-0">Ver agenda →</a>
                </div>

                <div class="space-y-2.5 text-xs">
                    @forelse($expedientesActivos->take(4) as $agenda)
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/70 space-y-1">
                            <div class="flex items-center justify-between">
                                <a href="{{ route('expedientes.show', $agenda) }}" class="font-bold text-indigo-950 hover:underline">
                                    {{ $agenda->folio }} · {{ $agenda->fallecido->nombre_completo }}
                                </a>
                                <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-[10px] font-bold">
                                    {{ \App\Support\Etiquetas::texto($agenda->estado_expediente) }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-600">
                                <strong>Cortejo:</strong> {{ $agenda->fecha_cortejo->format('d/m H:i') }} hrs → {{ $agenda->cementerio_destino }}
                            </p>
                            <p class="text-[11px] text-slate-500">
                                🚐 {{ $agenda->vehiculo ? $agenda->vehiculo->patente : 'Sin vehículo' }} ·
                                👤 Chofer: <strong>{{ $agenda->chofer->name ?? 'Pendiente asignar' }}</strong>
                            </p>
                        </div>
                    @empty
                        <p class="text-slate-500 py-6 text-center">No hay servicios activos programados.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <!-- 4. FILA DE 3 GRÁFICOS ANALÍTICOS CON CIFRAS EXPLÍCITAS -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Gráfico 1: Evolución Semestral -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-sm text-slate-800">📊 Servicios por mes <span class="text-xs font-normal text-slate-400">(Últimos 6 meses)</span></h3>
                <p class="text-[11px] text-slate-500 mb-3">
                    Total semestre: <strong>{{ array_sum($mesesValores) }}</strong> servicios · Facturado: <strong>${{ number_format(array_sum($mesesMontos), 0, ',', '.') }}</strong>
                </p>
            </div>
            <div class="h-44">
                <canvas id="chartServiciosMes"></canvas>
            </div>
        </div>

        <!-- Gráfico 2: Convenios Previsionales con Montos -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-sm text-slate-800">🏛️ Distribución por Convenio Previsional</h3>
                <p class="text-[11px] text-slate-500 mb-2">Cantidad de servicios y monto total cubierto por institución</p>
            </div>
            <div class="h-36 flex items-center justify-center">
                <canvas id="chartConvenios"></canvas>
            </div>
            <div class="grid grid-cols-2 gap-x-4 gap-y-1 pt-2 mt-2 border-t border-slate-100 text-[11px]">
                @foreach($conveniosMontos as $nombreConv => $montoConv)
                    <div class="flex justify-between">
                        <span class="text-slate-600">{{ $nombreConv }} ({{ $conveniosStats[$nombreConv] }}):</span>
                        <strong class="text-slate-800">${{ number_format($montoConv, 0, ',', '.') }}</strong>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Gráfico 3: Estado de Expedientes y Salud Financiera -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-sm text-slate-800">🕒 Estado de Expedientes y Cartera</h3>
                <p class="text-[11px] text-slate-500 mb-2">Desglose operativo y avance de recuperación de ingresos</p>
            </div>
            <div class="h-36 flex items-center justify-center">
                <canvas id="chartEstados"></canvas>
            </div>
            @php
                $baseCartera = max(1, $facturadoTotal);
                $pctRecaudado = min(100, (int) round(($ingresosTotal / $baseCartera) * 100));
                $pctConvenios = min(100 - $pctRecaudado, (int) round(($coberturaConveniosTotal / $baseCartera) * 100));
            @endphp
            <div class="pt-2 mt-2 border-t border-slate-100 space-y-1.5 text-[11px]">
                <div class="flex justify-between text-slate-600">
                    <span>Caja: <strong>{{ $pctRecaudado }}%</strong> · Convenios: <strong>{{ $pctConvenios }}%</strong></span>
                    <span>Por cobrar: <strong class="text-amber-700">${{ number_format($saldoPorCobrar, 0, ',', '.') }}</strong></span>
                </div>
                <div class="w-full h-2.5 rounded-full bg-amber-100 overflow-hidden flex">
                    <div class="bg-emerald-500 h-full" style="width: {{ $pctRecaudado }}%" title="Recaudado en caja"></div>
                    <div class="bg-indigo-500 h-full" style="width: {{ $pctConvenios }}%" title="Cubierto por convenios"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. TABLAS OPERATIVAS: ÚLTIMOS SERVICIOS + COBRANZAS + INVENTARIO DETALLADO -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Tabla 1: Últimos servicios registrados -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 overflow-x-auto">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-sm text-slate-800">Últimos servicios registrados</h3>
                <a href="{{ route('expedientes.index') }}" class="text-xs font-semibold text-indigo-700 hover:underline">Ver todos →</a>
            </div>
            <table class="w-full text-xs text-left">
                <thead class="text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="py-2">Folio / Fallecido</th>
                        <th class="py-2">Total</th>
                        <th class="py-2 text-right">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($ultimosServicios as $srv)
                        <tr>
                            <td class="py-2.5">
                                <a href="{{ route('expedientes.show', $srv) }}" class="font-bold text-indigo-900 hover:underline block">{{ $srv->folio }}</a>
                                <span class="text-[11px] text-slate-600">{{ Str::limit($srv->fallecido->nombre_completo, 22) }}</span>
                            </td>
                            <td class="py-2.5 font-semibold text-slate-800">
                                ${{ number_format($srv->valor_total_servicio, 0, ',', '.') }}
                                <span class="block text-[10px] text-slate-400">{{ $srv->created_at->format('d/m/Y') }}</span>
                            </td>
                            <td class="py-2.5 text-right">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $srv->estado_expediente === 'CERRADO' ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800' }}">
                                    {{ \App\Support\Etiquetas::texto($srv->estado_expediente) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Tabla 2: Cobranzas pendientes -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 overflow-x-auto">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-sm text-slate-800">Cobranzas pendientes ({{ $cantidadDeudores }})</h3>
                <a href="{{ route('cobranzas.index') }}" class="text-xs font-semibold text-indigo-700 hover:underline">Gestionar →</a>
            </div>
            <table class="w-full text-xs text-left">
                <thead class="text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="py-2">Servicio / Familia</th>
                        <th class="py-2">Saldo</th>
                        <th class="py-2 text-right">Antigüedad</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($cobranzasPendientes as $cob)
                        @php
                            $badgeDias = $cob->dias_atraso >= 30 ? 'bg-rose-100 text-rose-800' : ($cob->dias_atraso >= 15 ? 'bg-amber-100 text-amber-800' : 'bg-yellow-100 text-yellow-800');
                        @endphp
                        <tr>
                            <td class="py-2.5">
                                <a href="{{ route('expedientes.show', $cob) }}" class="font-bold text-slate-800 hover:underline block">{{ $cob->folio }}</a>
                                <span class="text-[11px] text-slate-500">{{ Str::limit($cob->cliente->nombre_completo, 20) }}</span>
                            </td>
                            <td class="py-2.5 font-bold text-slate-900">${{ number_format($cob->saldo_pendiente, 0, ',', '.') }}</td>
                            <td class="py-2.5 text-right">
                                <span class="px-2 py-0.5 rounded font-bold {{ $badgeDias }}">{{ $cob->dias_atraso }} días</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-4 text-center text-slate-400">No hay saldos pendientes por cobrar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Tabla 3: Inventario Salón vs. Taller -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 overflow-x-auto">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="font-bold text-sm text-slate-800">Inventario Salón vs. Taller</h3>
                    <p class="text-[10px] text-slate-500">Norma Seremi: Niño ({{ $stockNinoSeremi }} u.) · Especial XL ({{ $stockEspecialXl }} u.)</p>
                </div>
                <a href="{{ route('bodega.index') }}" class="text-xs font-semibold text-indigo-700 hover:underline">Bodega →</a>
            </div>
            <table class="w-full text-xs text-left">
                <thead class="text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="py-2">Modelo</th>
                        <th class="py-2 text-center">Salón / Taller</th>
                        <th class="py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($urnasStockCritico as $u)
                        @php
                            $totalU = $u->stock_salon + $u->stock_taller;
                            $badgeStock = $totalU <= 2 ? 'bg-rose-100 text-rose-800' : ($totalU <= 4 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800');
                        @endphp
                        <tr>
                            <td class="py-2.5">
                                <span class="font-bold text-slate-800 block">{{ Str::limit($u->modelo, 20) }}</span>
                                <span class="text-[10px] text-slate-500">{{ $u->color }}</span>
                            </td>
                            <td class="py-2.5 text-center text-[11px] font-semibold text-slate-600">
                                {{ $u->stock_salon }} / {{ $u->stock_taller }}
                            </td>
                            <td class="py-2.5 text-right">
                                <span class="px-2 py-0.5 rounded font-bold text-[10px] {{ $badgeStock }}">{{ $totalU }} u.</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <!-- 6. FILA INFERIOR: RECAUDACIÓN Y BITÁCORA + PERSONAL EN TURNO + FLOTA -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Panel 1: Desglose de Caja por Medio de Pago + Bitácora -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 space-y-4">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-bold text-sm text-slate-800">Recaudación por medio de pago</h3>
                    <a href="{{ route('finanzas.index') }}" class="text-xs font-semibold text-indigo-700 hover:underline">Ver caja →</a>
                </div>
                <div class="space-y-1.5 text-xs">
                    @forelse($pagosPorMetodo as $pm)
                        <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50">
                            <span class="font-medium text-slate-700">{{ \App\Support\Etiquetas::texto($pm->metodo_pago) }} ({{ $pm->cantidad }})</span>
                            <strong class="text-slate-900">${{ number_format($pm->total, 0, ',', '.') }}</strong>
                        </div>
                    @empty
                        <p class="text-slate-400 text-xs">Sin pagos registrados aún.</p>
                    @endforelse
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-bold text-xs text-slate-700">Actividad reciente auditada</h4>
                    <a href="{{ route('bitacora.index') }}" class="text-[11px] font-semibold text-indigo-700 hover:underline">Bitácora →</a>
                </div>
                <div class="space-y-1.5 text-xs">
                    @foreach($bitacora->take(3) as $log)
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-slate-600 truncate">{{ $log->created_at->format('H:i') }} · {{ $log->usuario->name ?? 'Sistema' }}</span>
                            <span class="px-2 py-0.5 rounded font-bold text-[10px] shrink-0 {{ $log->claseBadgeAccion() }}">{{ $log->etiquetaAccion() }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Panel 2: Dotación de Personal y Delegaciones -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-sm text-slate-800">Dotación de Personal ({{ $personalActivo->count() }}/{{ $personal->count() }})</h3>
                    <p class="text-[11px] text-slate-500">Disponibilidad operativa y reemplazos activos</p>
                </div>
                <a href="{{ route('usuarios.index') }}" class="text-xs font-semibold text-indigo-700 hover:underline">Gestionar →</a>
            </div>

            <div class="space-y-2 text-xs">
                @foreach($personal as $colab)
                    <div class="flex items-center justify-between p-2 rounded-xl border {{ $colab->en_vacaciones ? 'bg-rose-50/70 border-rose-200' : 'bg-slate-50/70 border-slate-100' }}">
                        <div class="min-w-0">
                            <p class="font-bold text-slate-800 truncate">{{ $colab->name }}</p>
                            <p class="text-[11px] text-slate-500">
                                {{ $colab->rol->nombre ?? 'Colaborador' }}
                                @if($colab->en_vacaciones && $colab->delegadoA)
                                    · Delegado a: <strong>{{ $colab->delegadoA->name }}</strong>
                                @endif
                            </p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 {{ $colab->en_vacaciones ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ $colab->en_vacaciones ? \App\Support\Etiquetas::texto($colab->tipo_ausencia) : 'En turno' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Panel 3: Estado Detallado de Flota -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 overflow-x-auto">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="font-bold text-sm text-slate-800">Flota de Carrozas y Furgones</h3>
                    <p class="text-[11px] text-slate-500">{{ $vehiculosDisponibles }} disp. · {{ $vehiculosEnServicio }} en servicio · {{ $vehiculosMantencion }} mantención</p>
                </div>
                <a href="{{ route('vehiculos.index') }}" class="text-xs font-semibold text-indigo-700 hover:underline">Ver flota →</a>
            </div>
            <table class="w-full text-xs text-left">
                <thead class="text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="py-2">Patente / Unidad</th>
                        <th class="py-2">Km</th>
                        <th class="py-2 text-right">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($vehiculos as $veh)
                        @php
                            $badgeVeh = $veh->estado_vehiculo === 'DISPONIBLE'
                                ? 'bg-emerald-100 text-emerald-800'
                                : ($veh->estado_vehiculo === 'EN_SERVICIO' ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800');
                        @endphp
                        <tr>
                            <td class="py-2.5">
                                <span class="font-bold text-slate-800 block">{{ $veh->patente }}</span>
                                <span class="text-[11px] text-slate-500">{{ Str::limit($veh->marca_modelo, 22) }}</span>
                            </td>
                            <td class="py-2.5 text-slate-600 font-mono text-[11px]">{{ number_format($veh->kilometraje ?? 0, 0, ',', '.') }} km</td>
                            <td class="py-2.5 text-right">
                                <span class="px-2 py-0.5 rounded font-bold text-[10px] {{ $badgeVeh }}">{{ \App\Support\Etiquetas::texto($veh->estado_vehiculo) }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <!-- Scripts de Gráficos (Chart.js) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new Chart(document.getElementById('chartServiciosMes'), {
                type: 'bar',
                data: {
                    labels: @json($mesesLabels),
                    datasets: [{
                        label: 'Servicios realizados',
                        data: @json($mesesValores),
                        backgroundColor: '#5c4db8',
                        borderRadius: 6,
                        barThickness: 24
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: '#f1f5f9' } },
                        x: { ticks: { font: { size: 10 } }, grid: { display: false } }
                    }
                }
            });

            @php
                $totalConv = max(1, array_sum($conveniosStats));
                $labelsConv = [];
                foreach ($conveniosStats as $k => $v) {
                    $pct = round(($v / $totalConv) * 100);
                    $labelsConv[] = "{$k}: {$v} ({$pct}%)";
                }

                $totalEst = max(1, $estadosStats['completados'] + $estadosStats['en_proceso'] + $estadosStats['pendientes']);
                $labelsEst = [
                    'Completados: ' . $estadosStats['completados'] . ' (' . round(($estadosStats['completados'] / $totalEst) * 100) . '%)',
                    'En proceso: ' . $estadosStats['en_proceso'] . ' (' . round(($estadosStats['en_proceso'] / $totalEst) * 100) . '%)',
                    'Abiertos: ' . $estadosStats['pendientes'] . ' (' . round(($estadosStats['pendientes'] / $totalEst) * 100) . '%)',
                ];
            @endphp

            new Chart(document.getElementById('chartConvenios'), {
                type: 'doughnut',
                data: {
                    labels: @json($labelsConv),
                    datasets: [{
                        data: @json(array_values($conveniosStats)),
                        backgroundColor: ['#4f46e5', '#8b5cf6', '#f59e0b', '#ec4899', '#94a3b8'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10.5 } } }
                    }
                }
            });

            new Chart(document.getElementById('chartEstados'), {
                type: 'doughnut',
                data: {
                    labels: @json($labelsEst),
                    datasets: [{
                        data: [{{ $estadosStats['completados'] }}, {{ $estadosStats['en_proceso'] }}, {{ $estadosStats['pendientes'] }}],
                        backgroundColor: ['#10b981', '#6366f1', '#f59e0b'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10.5 } } }
                    }
                }
            });
        });
    </script>
</x-app-layout>