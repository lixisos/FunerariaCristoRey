<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="font-bold text-xl text-indigo-950">Finanzas, Abonos y Calculadora Previsional</h2>
                <p class="text-xs text-slate-500">Liquidación automática IPS / AFP (15 UF) y emisión de comprobantes foliados</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('cobranzas.index') }}" class="px-3.5 py-2 bg-amber-100 text-amber-900 rounded-xl text-xs font-bold">Ver Cobranzas 10/20/30+ días</a>
                <a href="{{ route('convenios.index') }}" class="px-3.5 py-2 bg-indigo-100 text-indigo-900 rounded-xl text-xs font-bold">Convenios IPS/AFP</a>
            </div>
        </div>
    </x-slot>

    <!-- KPIs Financieros -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
            <p class="text-xs font-bold uppercase text-slate-400">Total Contratado</p>
            <p class="text-2xl font-extrabold text-indigo-950 mt-1">${{ number_format($totalFacturado, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
            <p class="text-xs font-bold uppercase text-slate-400">Cobertura Convenios (IPS/AFP)</p>
            <p class="text-2xl font-extrabold text-violet-700 mt-1">${{ number_format($totalConvenios, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
            <p class="text-xs font-bold uppercase text-slate-400">Saldo Por Cobrar</p>
            <p class="text-2xl font-extrabold text-rose-600 mt-1">${{ number_format($totalPorCobrar, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Calculadora Rápida Previsional en Vivo -->
    <div x-data="{ total: 1450000, uf: 38500, tipo: 'IPS' }" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 space-y-3">
        <h3 class="font-bold text-sm text-indigo-950">🧮 Simulador Instantáneo de Copago Familiar (IPS / AFP 15 UF)</h3>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs items-end">
            <div>
                <label class="font-semibold">Valor Plan Funerario (CLP)</label>
                <input type="number" x-model.number="total" class="w-full mt-1 rounded-xl border-slate-300">
            </div>
            <div>
                <label class="font-semibold">Beneficio Previsional</label>
                <select x-model="tipo" class="w-full mt-1 rounded-xl border-slate-300">
                    <option value="IPS">IPS Asignación Muerte ($1.070.000)</option>
                    <option value="AFP">AFP Cuota Mortuoria (15 UF)</option>
                    <option value="DIPRECA">DIPRECA / CAPREDENA ($950.000)</option>
                    <option value="PARTICULAR">Particular Sin Convenio ($0)</option>
                </select>
            </div>
            <div>
                <label class="font-semibold">Valor UF Referencia</label>
                <input type="number" x-model.number="uf" class="w-full mt-1 rounded-xl border-slate-300">
            </div>
            <div class="bg-indigo-50 p-3 rounded-xl border border-indigo-200">
                <span class="text-[11px] text-indigo-700 block font-semibold">Copago Estimado Familia:</span>
                <span class="text-lg font-extrabold text-indigo-950"
                      x-text="'$' + Math.max(0, total - (tipo === 'IPS' ? 1070000 : (tipo === 'AFP' ? Math.round(15 * uf) : (tipo === 'DIPRECA' ? 950000 : 0)))).toLocaleString('es-CL')">
                </span>
            </div>
        </div>
    </div>

    <!-- Registro de Abonos por Expediente -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
        <div class="p-4 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <h3 class="font-bold text-sm text-indigo-950">Estado de Cuenta por Expediente y Registro de Abonos</h3>

            <!-- Filtros Cuentas -->
            <form method="GET" action="{{ route('finanzas.index') }}" class="flex flex-wrap items-center gap-2 text-xs">
                <input type="hidden" name="buscar_comprobante" value="{{ request('buscar_comprobante') }}">
                <input type="hidden" name="metodo_pago" value="{{ request('metodo_pago') }}">

                <input type="text" name="buscar_cuenta" value="{{ request('buscar_cuenta') }}" placeholder="Folio, cliente, RUT o fallecido..." class="rounded-xl border-slate-300 text-xs w-52">
                <select name="estado_cuenta" class="rounded-xl border-slate-300 text-xs">
                    <option value="">Todos los estados</option>
                    <option value="pendiente" {{ request('estado_cuenta') === 'pendiente' ? 'selected' : '' }}>Con saldo pendiente</option>
                    <option value="pagado" {{ request('estado_cuenta') === 'pagado' ? 'selected' : '' }}>Pagado completo</option>
                </select>
                <button type="submit" class="px-3.5 py-2 bg-[#34287d] text-white font-bold rounded-xl">Filtrar</button>
                @if(request('buscar_cuenta') || request('estado_cuenta'))
                    <a href="{{ route('finanzas.index', request()->only(['buscar_comprobante', 'metodo_pago'])) }}" class="px-3 py-2 bg-slate-200 rounded-xl font-semibold">Limpiar</a>
                @endif
            </form>
        </div>

        <table class="w-full text-xs text-left">
            <thead class="bg-[#231b56] text-white uppercase">
                <tr>
                    <th class="px-4 py-3">Folio / Contratante</th>
                    <th class="px-4 py-3">Total / Convenio</th>
                    <th class="px-4 py-3">Saldo Pendiente</th>
                    <th class="px-4 py-3">Registrar Nuevo Abono</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($expedientes as $exp)
                    <tr x-data="{ monto: '' }">
                        <td class="px-4 py-3">
                            <span class="font-bold text-indigo-950 block">{{ $exp->folio }}</span>
                            <span class="text-slate-600">{{ $exp->cliente->nombre_completo }} ({{ $exp->cliente->rut }})</span>
                            <span class="text-[11px] text-slate-400 block">Servicio: {{ $exp->fallecido->nombre_completo ?? '' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="block">Total: <strong>${{ number_format($exp->valor_total_servicio, 0, ',', '.') }}</strong></span>
                            <span class="text-violet-700">Convenio: -${{ number_format($exp->monto_cobertura_convenio, 0, ',', '.') }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($exp->saldo_pendiente > 0)
                                <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 font-bold">
                                    ${{ number_format($exp->saldo_pendiente, 0, ',', '.') }}
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold">PAGADO</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($exp->saldo_pendiente > 0)
                                <form method="POST" action="{{ route('finanzas.abonos.store', $exp) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    <input type="number" name="monto_abonado" x-model="monto" min="1000" max="{{ $exp->saldo_pendiente }}" placeholder="Monto CLP" required class="w-28 rounded-xl border-slate-300 text-xs">
                                    <button type="button" @click="monto = {{ $exp->saldo_pendiente }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl" title="Rellenar saldo total">
                                        Saldar
                                    </button>
                                    <select name="metodo_pago" class="rounded-xl border-slate-300 text-xs">
                                        <option value="TRANSFERENCIA">Transferencia</option>
                                        <option value="EFECTIVO">Efectivo</option>
                                        <option value="TARJETA_DEBITO">Débito</option>
                                        <option value="TARJETA_CREDITO">Crédito</option>
                                        <option value="CONVENIO_DIRECTO">Convenio directo</option>
                                    </select>
                                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl">Abonar</button>
                                </form>
                            @else
                                <span class="text-slate-400">Sin deuda pendiente</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-slate-500">No se encontraron cuentas con los filtros aplicados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100">
            {{ $expedientes->links() }}
        </div>
    </div>

    <!-- Últimos Comprobantes Emitidos -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
        <div class="p-4 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <h3 class="font-bold text-sm text-indigo-950">Comprobantes Foliados Emitidos</h3>

            <!-- Filtros Comprobantes -->
            <form method="GET" action="{{ route('finanzas.index') }}" class="flex flex-wrap items-center gap-2 text-xs">
                <input type="hidden" name="buscar_cuenta" value="{{ request('buscar_cuenta') }}">
                <input type="hidden" name="estado_cuenta" value="{{ request('estado_cuenta') }}">

                <input type="text" name="buscar_comprobante" value="{{ request('buscar_comprobante') }}" placeholder="Folio comprobante, expediente o cliente..." class="rounded-xl border-slate-300 text-xs w-56">
                <select name="metodo_pago" class="rounded-xl border-slate-300 text-xs">
                    <option value="">Todos los medios</option>
                    <option value="TRANSFERENCIA" {{ request('metodo_pago') === 'TRANSFERENCIA' ? 'selected' : '' }}>Transferencia</option>
                    <option value="EFECTIVO" {{ request('metodo_pago') === 'EFECTIVO' ? 'selected' : '' }}>Efectivo</option>
                    <option value="TARJETA_DEBITO" {{ request('metodo_pago') === 'TARJETA_DEBITO' ? 'selected' : '' }}>Tarjeta de débito</option>
                    <option value="TARJETA_CREDITO" {{ request('metodo_pago') === 'TARJETA_CREDITO' ? 'selected' : '' }}>Tarjeta de crédito</option>
                    <option value="CONVENIO_DIRECTO" {{ request('metodo_pago') === 'CONVENIO_DIRECTO' ? 'selected' : '' }}>Convenio directo</option>
                </select>
                <button type="submit" class="px-3.5 py-2 bg-[#34287d] text-white font-bold rounded-xl">Filtrar</button>
                @if(request('buscar_comprobante') || request('metodo_pago'))
                    <a href="{{ route('finanzas.index', request()->only(['buscar_cuenta', 'estado_cuenta'])) }}" class="px-3 py-2 bg-slate-200 rounded-xl font-semibold">Limpiar</a>
                @endif
            </form>
        </div>

        <table class="w-full text-xs text-left">
            <thead class="bg-slate-100 text-slate-700 uppercase">
                <tr>
                    <th class="px-4 py-2.5">Folio Comprobante</th>
                    <th class="px-4 py-2.5">Expediente / Cliente</th>
                    <th class="px-4 py-2.5">Medio / Fecha</th>
                    <th class="px-4 py-2.5">Monto</th>
                    <th class="px-4 py-2.5 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($abonos as $ab)
                    <tr>
                        <td class="px-4 py-2.5 font-bold text-indigo-900">{{ $ab->folio_comprobante }}</td>
                        <td class="px-4 py-2.5">{{ $ab->expediente->folio }} — {{ $ab->expediente->cliente->nombre_completo }}</td>
                        <td class="px-4 py-2.5">{{ \App\Support\Etiquetas::texto($ab->metodo_pago) }} ({{ $ab->fecha_pago->format('d/m/Y H:i') }})</td>
                        <td class="px-4 py-2.5 font-bold text-emerald-700">${{ number_format($ab->monto_abonado, 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5 text-right space-x-1.5">
                            <a href="{{ route('finanzas.comprobante-pdf', $ab) }}" target="_blank" class="inline-block px-3 py-1 bg-indigo-50 text-indigo-800 font-bold rounded-lg hover:bg-indigo-100">
                                Comprobante PDF ↗
                            </a>
                            @if(Auth::user()->tieneRol(['admin', 'finanzas']))
                                <form method="POST" action="{{ route('finanzas.abonos.anular', $ab) }}" class="inline-flex" onsubmit="return confirm('¿Anular el comprobante {{ $ab->folio_comprobante }} y devolver el saldo al expediente?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white font-bold rounded-lg transition">
                                        Anular
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No hay comprobantes emitidos con los filtros seleccionados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t border-slate-100">
            {{ $abonos->links() }}
        </div>
    </div>
</x-app-layout>