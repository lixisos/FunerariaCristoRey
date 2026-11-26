<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-indigo-950">Control de Cobranzas por Antigüedad (10 / 20 / 30+ Días)</h2>
            <p class="text-xs text-slate-500">Seguimiento preventivo y gestión de saldos pendientes con recordatorio directo por WhatsApp</p>
        </div>
    </x-slot>

    <!-- Tarjetas de Tramos de Mora (Clicables para filtrar) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('cobranzas.index', ['tramo' => request('tramo') === 'PREVENTIVA_10' ? null : 'PREVENTIVA_10']) }}"
           class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-2xl transition hover:shadow-md {{ request('tramo') === 'PREVENTIVA_10' ? 'ring-2 ring-amber-600' : '' }}">
            <p class="text-xs font-bold uppercase text-amber-800">Aviso Preventivo (10 a 19 días)</p>
            <p class="text-2xl font-extrabold text-amber-950 mt-1">{{ $tramo10->count() }} caso(s)</p>
            <p class="text-xs text-amber-800 mt-0.5">${{ number_format($tramo10->sum('saldo_pendiente'), 0, ',', '.') }} CLP</p>
        </a>
        <a href="{{ route('cobranzas.index', ['tramo' => request('tramo') === 'GESTION_20' ? null : 'GESTION_20']) }}"
           class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded-2xl transition hover:shadow-md {{ request('tramo') === 'GESTION_20' ? 'ring-2 ring-orange-600' : '' }}">
            <p class="text-xs font-bold uppercase text-orange-800">Gestión Activa (20 a 29 días)</p>
            <p class="text-2xl font-extrabold text-orange-950 mt-1">{{ $tramo20->count() }} caso(s)</p>
            <p class="text-xs text-orange-800 mt-0.5">${{ number_format($tramo20->sum('saldo_pendiente'), 0, ',', '.') }} CLP</p>
        </a>
        <a href="{{ route('cobranzas.index', ['tramo' => request('tramo') === 'CRITICA_30' ? null : 'CRITICA_30']) }}"
           class="bg-rose-50 border-l-4 border-rose-600 p-4 rounded-2xl transition hover:shadow-md {{ request('tramo') === 'CRITICA_30' ? 'ring-2 ring-rose-600' : '' }}">
            <p class="text-xs font-bold uppercase text-rose-800">Mora Crítica (30+ días)</p>
            <p class="text-2xl font-extrabold text-rose-950 mt-1">{{ $tramo30->count() }} caso(s)</p>
            <p class="text-xs text-rose-800 mt-0.5">${{ number_format($tramo30->sum('saldo_pendiente'), 0, ',', '.') }} CLP</p>
        </a>
    </div>

    <!-- Barra de Filtros -->
    <form method="GET" action="{{ route('cobranzas.index') }}" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs sm:text-sm">
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por folio, cliente, RUT o fallecido..." class="sm:col-span-6 rounded-xl border-slate-300">

        <select name="tramo" class="sm:col-span-4 rounded-xl border-slate-300">
            <option value="">Todos los tramos de antigüedad</option>
            <option value="AL_DIA" {{ request('tramo') === 'AL_DIA' ? 'selected' : '' }}>Recientes (0 a 9 días)</option>
            <option value="PREVENTIVA_10" {{ request('tramo') === 'PREVENTIVA_10' ? 'selected' : '' }}>Aviso Preventivo (10 a 19 días)</option>
            <option value="GESTION_20" {{ request('tramo') === 'GESTION_20' ? 'selected' : '' }}>Gestión Activa (20 a 29 días)</option>
            <option value="CRITICA_30" {{ request('tramo') === 'CRITICA_30' ? 'selected' : '' }}>Mora Crítica (30+ días)</option>
        </select>

        <div class="sm:col-span-2 flex gap-2">
            <button type="submit" class="flex-1 bg-[#34287d] text-white font-bold rounded-xl px-3 py-2 text-xs">Filtrar</button>
            @if(request('buscar') || request('tramo'))
                <a href="{{ route('cobranzas.index') }}" class="px-3 py-2 bg-slate-200 rounded-xl font-semibold text-xs flex items-center">Limpiar</a>
            @endif
        </div>
    </form>

    <!-- Tabla Detallada de Cartera Por Cobrar -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead class="bg-[#231b56] text-white uppercase">
                <tr>
                    <th class="px-4 py-3">Expediente / Contratante</th>
                    <th class="px-4 py-3">Teléfono</th>
                    <th class="px-4 py-3">Antigüedad</th>
                    <th class="px-4 py-3">Saldo Pendiente</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($deudores as $d)
                    @php
                        $telLimpio = preg_replace('/[^0-9]/', '', $d->cliente->telefono ?? '');
                        $msg = urlencode("Estimado/a {$d->cliente->nombre_completo}, le saludamos cordialmente de Funeraria Cristo Rey respecto al saldo pendiente de $" . number_format($d->saldo_pendiente, 0, ',', '.') . " del servicio {$d->folio}.");
                    @endphp
                    <tr x-data="{ openAbono: false, monto: {{ $d->saldo_pendiente }} }" class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <span class="font-bold text-indigo-950 block">{{ $d->folio }} — {{ $d->cliente->nombre_completo }}</span>
                            <span class="text-slate-500">Servicio: {{ $d->fallecido->nombre_completo }}</span>
                        </td>
                        <td class="px-4 py-3 font-medium">{{ $d->cliente->telefono }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2.5 py-1 rounded-full font-bold {{ $d->dias_mora >= 30 ? 'bg-rose-100 text-rose-800' : ($d->dias_mora >= 20 ? 'bg-orange-100 text-orange-800' : ($d->dias_mora >= 10 ? 'bg-amber-100 text-amber-900' : 'bg-slate-100 text-slate-700')) }}">
                                {{ $d->dias_mora }} día(s)
                            </span>
                        </td>
                        <td class="px-4 py-3 font-extrabold text-rose-700">${{ number_format($d->saldo_pendiente, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right space-x-1.5">
                            <a href="https://wa.me/{{ $telLimpio }}?text={{ $msg }}" target="_blank" class="inline-block px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg">
                                WhatsApp ↗
                            </a>
                            <button type="button" @click="openAbono = true" class="px-3 py-1 bg-indigo-100 hover:bg-indigo-200 text-indigo-900 font-bold rounded-lg">
                                Registrar Abono
                            </button>

                            <!-- Modal Abono Directo -->
                            <div x-show="openAbono" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4 text-left">
                                <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
                                    <div class="flex justify-between items-center border-b pb-3">
                                        <h3 class="font-bold text-base text-indigo-950">Registrar Abono — {{ $d->folio }}</h3>
                                        <button type="button" @click="openAbono = false" class="text-slate-400 font-bold">✕</button>
                                    </div>
                                    <p class="text-xs text-slate-600">
                                        Cliente: <strong>{{ $d->cliente->nombre_completo }}</strong><br>
                                        Saldo pendiente actual: <strong class="text-rose-700">${{ number_format($d->saldo_pendiente, 0, ',', '.') }}</strong>
                                    </p>
                                    <form method="POST" action="{{ route('finanzas.abonos.store', $d) }}" class="space-y-3 text-xs">
                                        @csrf
                                        <div>
                                            <label class="font-semibold">Monto a Abonar (CLP) *</label>
                                            <input type="number" name="monto_abonado" x-model="monto" min="1000" max="{{ $d->saldo_pendiente }}" required class="w-full mt-1 rounded-xl border-slate-300">
                                        </div>
                                        <div>
                                            <label class="font-semibold">Medio de Pago *</label>
                                            <select name="metodo_pago" class="w-full mt-1 rounded-xl border-slate-300">
                                                <option value="TRANSFERENCIA">Transferencia</option>
                                                <option value="EFECTIVO">Efectivo</option>
                                                <option value="TARJETA_DEBITO">Tarjeta de débito</option>
                                                <option value="TARJETA_CREDITO">Tarjeta de crédito</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="font-semibold">Observación</label>
                                            <input type="text" name="observacion" placeholder="Ej: Transferencia recibida tras gestión de cobranza" class="w-full mt-1 rounded-xl border-slate-300">
                                        </div>
                                        <div class="flex justify-end gap-2 pt-2">
                                            <button type="button" @click="openAbono = false" class="px-4 py-2 bg-slate-200 rounded-xl font-semibold">Cancelar</button>
                                            <button type="submit" class="px-5 py-2 bg-emerald-600 text-white rounded-xl font-bold">Confirmar Abono</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400">No existen saldos pendientes con los filtros seleccionados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $deudores->links() }}
    </div>
</x-app-layout>