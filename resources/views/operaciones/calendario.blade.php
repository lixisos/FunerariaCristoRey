<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl text-indigo-950">Calendario Operacional de Servicios</h2>
                <p class="text-xs text-slate-500">Programación en tiempo real de instalaciones, velatorios, misas, funerales, cremaciones y traslados</p>
            </div>

            <!-- Selector Vista Día / Semana / Mes + Navegación -->
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <div class="inline-flex rounded-xl bg-slate-100 p-1">
                    <a href="{{ route('calendario.index', ['vista' => 'dia', 'fecha' => $fechaBase->toDateString(), 'tipo' => $tipoFiltro]) }}"
                       class="px-3 py-1.5 rounded-lg font-bold {{ $vista === 'dia' ? 'bg-[#34287d] text-white' : 'text-slate-600' }}">Día</a>
                    <a href="{{ route('calendario.index', ['vista' => 'semana', 'fecha' => $fechaBase->toDateString(), 'tipo' => $tipoFiltro]) }}"
                       class="px-3 py-1.5 rounded-lg font-bold {{ $vista === 'semana' ? 'bg-[#34287d] text-white' : 'text-slate-600' }}">Semana</a>
                    <a href="{{ route('calendario.index', ['vista' => 'mes', 'fecha' => $fechaBase->toDateString(), 'tipo' => $tipoFiltro]) }}"
                       class="px-3 py-1.5 rounded-lg font-bold {{ $vista === 'mes' ? 'bg-[#34287d] text-white' : 'text-slate-600' }}">Mes</a>
                </div>

                @php
                    $prev = $vista === 'dia' ? $fechaBase->copy()->subDay() : ($vista === 'mes' ? $fechaBase->copy()->subMonth() : $fechaBase->copy()->subWeek());
                    $next = $vista === 'dia' ? $fechaBase->copy()->addDay() : ($vista === 'mes' ? $fechaBase->copy()->addMonth() : $fechaBase->copy()->addWeek());
                @endphp
                <a href="{{ route('calendario.index', ['vista' => $vista, 'fecha' => $prev->toDateString(), 'tipo' => $tipoFiltro]) }}" class="px-3 py-1.5 bg-white border rounded-xl font-bold">← Anterior</a>
                <a href="{{ route('calendario.index', ['vista' => $vista, 'fecha' => now()->toDateString(), 'tipo' => $tipoFiltro]) }}" class="px-3 py-1.5 bg-indigo-50 text-indigo-800 border border-indigo-200 rounded-xl font-bold">Hoy</a>
                <a href="{{ route('calendario.index', ['vista' => $vista, 'fecha' => $next->toDateString(), 'tipo' => $tipoFiltro]) }}" class="px-3 py-1.5 bg-white border rounded-xl font-bold">Siguiente →</a>
            </div>
        </div>
    </x-slot>

    <!-- Filtros Clicables por Tipo de Hito -->
    @php
        $tiposHito = [
            '' => ['Todos', 'bg-slate-200 text-slate-800'],
            'INSTALACION' => ['● Instalación', 'bg-violet-100 text-violet-900'],
            'VELATORIO' => ['● Velatorio', 'bg-indigo-100 text-indigo-900'],
            'MISA' => ['● Misa / Responso', 'bg-sky-100 text-sky-900'],
            'FUNERAL' => ['● Funeral / Cortejo', 'bg-rose-100 text-rose-900'],
            'CREMACION' => ['● Cremación', 'bg-amber-100 text-amber-900'],
            'TRASLADO' => ['● Retiro / Traslado', 'bg-emerald-100 text-emerald-900'],
        ];
    @endphp
    <div class="flex flex-wrap gap-2 text-xs">
        @foreach($tiposHito as $clave => [$etiqueta, $clases])
            <a href="{{ route('calendario.index', ['vista' => $vista, 'fecha' => $fechaBase->toDateString(), 'tipo' => $clave ?: null]) }}"
               class="px-3 py-1 rounded-full font-bold transition {{ $clases }} {{ ($tipoFiltro ?? '') === $clave ? 'ring-2 ring-indigo-950' : 'opacity-80 hover:opacity-100' }}">
                {{ $etiqueta }}
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="font-bold text-indigo-950">
                Rango: {{ $inicio->format('d/m/Y') }} al {{ $fin->format('d/m/Y') }}
            </h3>
            <span class="text-xs font-semibold text-slate-500">{{ $eventosFiltrados->count() }} actividad(es) programada(s)</span>
        </div>

        @if($vista === 'mes')
            <!-- Vista Mensual en Grilla de 7 Días -->
            <div class="grid grid-cols-7 gap-2 text-xs">
                @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $diaNombre)
                    <div class="text-center font-bold text-slate-500 py-1">{{ $diaNombre }}</div>
                @endforeach

                @php $cursor = $inicio->copy(); @endphp
                @while($cursor <= $fin)
                    @php
                        $eventosDia = $eventosFiltrados->filter(fn($e) => $e['fecha']->isSameDay($cursor));
                    @endphp
                    <div class="min-h-[105px] p-2 rounded-xl border {{ $cursor->isToday() ? 'bg-indigo-50/70 border-indigo-400' : 'bg-slate-50/50 border-slate-200' }}">
                        <span class="font-bold {{ $cursor->month === $fechaBase->month ? 'text-slate-800' : 'text-slate-400' }}">{{ $cursor->day }}</span>
                        <div class="mt-1 space-y-1">
                            @foreach($eventosDia as $ev)
                                <a href="{{ route('expedientes.show', $ev['expediente_id']) }}"
                                   class="block px-1.5 py-1 rounded border text-[10px] font-semibold truncate {{ $ev['color'] }}">
                                    {{ $ev['fecha']->format('H:i') }} {{ \App\Support\Etiquetas::texto($ev['tipo']) }} ({{ $ev['folio'] }})
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @php $cursor->addDay(); @endphp
                @endwhile
            </div>
        @else
            <!-- Vista Día o Semana Paginada (8 por página) -->
            <div class="space-y-3">
                @forelse($eventosPaginados as $ev)
                    <div class="p-4 rounded-xl border {{ $ev['color'] }} flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-white/80 text-[11px] font-extrabold">{{ \App\Support\Etiquetas::texto($ev['tipo']) }}</span>
                                <span class="font-bold text-sm">{{ $ev['fecha']->format('d/m/Y — H:i') }} hrs</span>
                                <span class="text-xs font-bold opacity-80">({{ $ev['folio'] }})</span>
                            </div>
                            <p class="font-bold text-base">{{ $ev['titulo'] }}</p>
                            <p class="text-xs opacity-90">📍 {{ $ev['lugar'] }} &nbsp;|&nbsp; 🚐 Vehículo: {{ $ev['vehiculo'] }} &nbsp;|&nbsp; 👤 Chofer: {{ $ev['chofer'] }}</p>
                        </div>
                        <a href="{{ route('expedientes.show', $ev['expediente_id']) }}" class="px-4 py-2 bg-white text-indigo-950 text-xs font-bold rounded-xl shadow-sm shrink-0 text-center">
                            Abrir Expediente →
                        </a>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 py-8 text-center">No hay actividades programadas en este rango de fechas.</p>
                @endforelse
            </div>

            <div class="pt-2">
                {{ $eventosPaginados->links() }}
            </div>
        @endif
    </div>
</x-app-layout>