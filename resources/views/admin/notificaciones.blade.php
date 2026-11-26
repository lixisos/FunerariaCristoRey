<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-indigo-950">Centro de Alertas y Agenda Operativa</h2>
            <p class="text-xs text-slate-500">Pendientes urgentes de operación diaria, plazos Seremi, flota, bodega y cobranzas</p>
        </div>
    </x-slot>

    <!-- Resumen Rápido -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-rose-200">
            <p class="text-[11px] font-bold uppercase text-rose-700">Plazo Seremi (&lt; 6h)</p>
            <p class="text-2xl font-extrabold text-rose-900 mt-1">{{ $alertasSeremi->count() }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-indigo-200">
            <p class="text-[11px] font-bold uppercase text-indigo-700">Cortejos Hoy / Mañana</p>
            <p class="text-2xl font-extrabold text-indigo-950 mt-1">{{ $serviciosProximos->count() }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-amber-200">
            <p class="text-[11px] font-bold uppercase text-amber-800">Sin Flota / Stock Bajo</p>
            <p class="text-2xl font-extrabold text-amber-950 mt-1">{{ $sinFlotaAsignada->count() + $alertasStock->count() }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-emerald-200">
            <p class="text-[11px] font-bold uppercase text-emerald-800">Cobranzas Vencidas</p>
            <p class="text-2xl font-extrabold text-emerald-950 mt-1">{{ $alertasCobranza->count() }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <!-- 1. Semáforo Legal Seremi -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-rose-200 space-y-3">
            <h3 class="font-bold text-sm text-rose-800">🚨 Plazo Legal Seremi (&lt; 6 horas)</h3>
            @forelse($alertasSeremi as $a)
                <div class="p-3 rounded-xl bg-rose-50 text-xs flex justify-between items-center gap-2">
                    <div>
                        <span class="font-bold text-rose-950 block">{{ $a->folio }} — {{ $a->fallecido->nombre_completo }}</span>
                        <span class="text-rose-700">Vence: {{ $a->limite_legal_seremi_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <a href="{{ route('expedientes.show', $a) }}" class="px-3 py-1.5 bg-rose-600 text-white font-bold rounded-lg shrink-0">Revisar →</a>
                </div>
            @empty
                <p class="text-xs text-slate-400">Sin vencimientos legales próximos.</p>
            @endforelse
        </div>

        <!-- 2. Cortejos Programados Hoy / Mañana -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-indigo-200 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-sm text-indigo-950">📅 Servicios y Cortejos (Hoy y Mañana)</h3>
                <a href="{{ route('calendario.index') }}" class="text-xs font-bold text-indigo-700 hover:underline">Ver agenda →</a>
            </div>
            @forelse($serviciosProximos as $sp)
                <div class="p-3 rounded-xl bg-indigo-50 text-xs flex justify-between items-center gap-2">
                    <div>
                        <span class="font-bold text-indigo-950 block">{{ $sp->fecha_cortejo->format('d/m H:i') }} — {{ $sp->fallecido->nombre_completo }}</span>
                        <span class="text-slate-600">Destino: {{ $sp->cementerio_destino }} • Carroza: {{ $sp->vehiculo->patente ?? 'Pendiente' }}</span>
                    </div>
                    <a href="{{ route('expedientes.show', $sp) }}" class="px-3 py-1.5 bg-[#34287d] text-white font-bold rounded-lg shrink-0">Ficha →</a>
                </div>
            @empty
                <p class="text-xs text-slate-400">No hay cortejos programados para hoy o mañana.</p>
            @endforelse
        </div>

        <!-- 3. Expedientes sin Carroza o Chofer -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-amber-200 space-y-3">
            <h3 class="font-bold text-sm text-amber-900">🚐 Expedientes Activos sin Vehículo o Chofer</h3>
            @forelse($sinFlotaAsignada as $sf)
                <div class="p-3 rounded-xl bg-amber-50 text-xs flex justify-between items-center gap-2">
                    <div>
                        <span class="font-bold text-amber-950 block">{{ $sf->folio }} — {{ $sf->fallecido->nombre_completo }}</span>
                        <span class="text-amber-800">Cortejo: {{ $sf->fecha_cortejo->format('d/m/Y H:i') }}</span>
                    </div>
                    <a href="{{ route('vehiculos.index') }}" class="px-3 py-1.5 bg-amber-600 text-white font-bold rounded-lg shrink-0">Asignar flota →</a>
                </div>
            @empty
                <p class="text-xs text-slate-400">Todos los servicios activos tienen carroza y chofer asignados.</p>
            @endforelse
        </div>

        <!-- 4. Alertas de Stock Bajo -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-amber-200 space-y-3">
            <h3 class="font-bold text-sm text-amber-900">📦 Alertas de Stock Bajo (&le; 2 unidades)</h3>
            @forelse($alertasStock as $u)
                <div class="p-3 rounded-xl bg-amber-50 text-xs flex justify-between items-center gap-2">
                    <span><strong>{{ $u->modelo }}</strong>: quedan {{ $u->stock_salon + $u->stock_taller }} unidad(es)</span>
                    <a href="{{ route('bodega.index') }}" class="px-3 py-1.5 bg-amber-600 text-white font-bold rounded-lg shrink-0">Reponer →</a>
                </div>
            @empty
                <p class="text-xs text-slate-400">Todo el catálogo sobre el stock mínimo.</p>
            @endforelse
        </div>

        <!-- 5. Cobranzas Pendientes con WhatsApp Directo -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-emerald-200 space-y-3">
            <h3 class="font-bold text-sm text-emerald-950">💲 Cobranzas Vencidas (&ge; 10 días)</h3>
            @forelse($alertasCobranza as $c)
                @php
                    $tel = preg_replace('/[^0-9]/', '', $c->cliente->telefono ?? '');
                    $msg = urlencode("Estimado/a {$c->cliente->nombre_completo}, le saludamos de Funeraria Cristo Rey respecto al saldo pendiente de $" . number_format($c->saldo_pendiente, 0, ',', '.') . " del servicio {$c->folio}.");
                @endphp
                <div class="p-3 rounded-xl bg-emerald-50 text-xs flex justify-between items-center gap-2">
                    <div>
                        <span class="font-bold text-slate-900 block">{{ $c->folio }} — {{ $c->cliente->nombre_completo }}</span>
                        <span class="text-rose-700 font-bold">Saldo: ${{ number_format($c->saldo_pendiente, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex gap-1.5 shrink-0">
                        <a href="https://wa.me/{{ $tel }}?text={{ $msg }}" target="_blank" class="px-2.5 py-1.5 bg-emerald-600 text-white font-bold rounded-lg">WhatsApp ↗</a>
                        <a href="{{ route('cobranzas.index') }}" class="px-2.5 py-1.5 bg-white border border-slate-300 text-slate-800 font-bold rounded-lg">Gestionar</a>
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-400">Sin cobranzas vencidas.</p>
            @endforelse
        </div>

        <!-- 6. Personal Ausente y Moderación -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-sm text-slate-800">🏖️ Personal Ausente y Moderación</h3>
                <a href="{{ route('usuarios.index') }}" class="text-xs font-bold text-indigo-700 hover:underline">Gestionar equipo →</a>
            </div>
            @forelse($personalAusente as $p)
                <div class="p-3 rounded-xl bg-slate-100 text-xs">
                    <strong>{{ $p->name }}</strong> ({{ \App\Support\Etiquetas::texto($p->tipo_ausencia) }}
                    @if($p->ausencia_hasta) hasta {{ $p->ausencia_hasta->format('d/m/Y') }} @endif)
                    @if($p->delegadoA)
                        — Permisos delegados a <strong>{{ $p->delegadoA->name }}</strong>
                    @endif
                </div>
            @empty
                <p class="text-xs text-slate-400">Todo el personal se encuentra activo hoy.</p>
            @endforelse

            @if($condolenciasOcultas > 0)
                <div class="p-3 rounded-xl bg-violet-50 text-xs flex justify-between items-center">
                    <span>Hay <strong>{{ $condolenciasOcultas }}</strong> condolencia(s) ocultas/pendientes.</span>
                    <a href="{{ route('condolencias.admin') }}" class="font-bold text-violet-900 underline">Revisar →</a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>