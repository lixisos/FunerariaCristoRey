<x-app-layout>
    <x-slot name="header">
        <div x-data="{ openVeh: false }" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl text-indigo-950">Flota de Vehículos y Asignación de Personal</h2>
                <p class="text-xs text-slate-500">Control de carrozas, furgones de traslado y despacho de Chofer + Ayudante disponibles</p>
            </div>
            @if(Auth::user()->tieneRol(['admin', 'coordinador']))
                <button @click="openVeh = true" class="px-4 py-2 bg-[#34287d] hover:bg-[#231b59] text-white text-xs sm:text-sm font-bold rounded-xl shadow">
                    + Registrar Vehículo
                </button>
            @endif

            <!-- Modal Registrar Vehículo -->
            <div x-show="openVeh" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
                    <div class="flex justify-between items-center border-b pb-3">
                        <h3 class="font-bold text-lg text-indigo-950">Incorporar Vehículo a Flota</h3>
                        <button @click="openVeh = false" class="text-slate-400 font-bold">✕</button>
                    </div>
                    <form method="POST" action="{{ route('vehiculos.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        @csrf
                        <div>
                            <label class="text-xs font-semibold">Patente *</label>
                            <input type="text" name="patente" required placeholder="ABCD-12" class="w-full mt-1 rounded-xl border-slate-300 uppercase">
                        </div>
                        <div>
                            <label class="text-xs font-semibold">Tipo de Vehículo *</label>
                            <select name="tipo_vehiculo" class="w-full mt-1 rounded-xl border-slate-300">
                                <option value="CARROZA_PANORAMICA">Carroza Panorámica</option>
                                <option value="FURGON_TRASLADO">Furgón / Van de Traslado</option>
                                <option value="VEHICULO_ACOMPANAMIENTO">Vehículo de Apoyo</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold">Marca y Modelo *</label>
                            <input type="text" name="marca_modelo" required placeholder="Ej: Mercedes-Benz Vito / Toyota Hiace" class="w-full mt-1 rounded-xl border-slate-300">
                        </div>
                        <div>
                            <label class="text-xs font-semibold">Año</label>
                            <input type="number" name="anio" value="{{ date('Y') }}" class="w-full mt-1 rounded-xl border-slate-300">
                        </div>
                        <div>
                            <label class="text-xs font-semibold">Estado Inicial *</label>
                            <select name="estado_vehiculo" class="w-full mt-1 rounded-xl border-slate-300">
                                <option value="DISPONIBLE">Disponible</option>
                                <option value="EN_SERVICIO">En Servicio</option>
                                <option value="MANTENCION">Mantención</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2 flex justify-end gap-2 pt-2">
                            <button type="button" @click="openVeh = false" class="px-4 py-2 bg-slate-200 rounded-xl text-xs font-semibold">Cancelar</button>
                            <button type="submit" class="px-5 py-2 bg-[#34287d] text-white rounded-xl text-xs font-bold">Guardar Vehículo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Alerta de Personal Ausente -->
    @if($personalAusente->count() > 0)
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-2xl text-xs flex flex-wrap items-center gap-2">
            <span class="font-bold text-rose-900">🚫 Personal bloqueado para asignación hoy:</span>
            @foreach($personalAusente as $aus)
                <span class="px-2.5 py-1 rounded-full bg-rose-200/80 text-rose-950 font-bold">
                    {{ $aus->name }} ({{ \App\Support\Etiquetas::texto($aus->tipo_ausencia) }}{{ $aus->ausencia_hasta ? ' hasta ' . $aus->ausencia_hasta->format('d/m') : '' }})
                </span>
            @endforeach
        </div>
    @endif

    <!-- Barra de Filtros de Flota -->
    <form method="GET" action="{{ route('vehiculos.index') }}" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs sm:text-sm">
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Patente o marca/modelo..." class="lg:col-span-2 rounded-xl border-slate-300">

        <select name="tipo_vehiculo" class="rounded-xl border-slate-300">
            <option value="">Todos los tipos</option>
            <option value="CARROZA_PANORAMICA" {{ request('tipo_vehiculo') === 'CARROZA_PANORAMICA' ? 'selected' : '' }}>Carroza Panorámica</option>
            <option value="FURGON_TRASLADO" {{ request('tipo_vehiculo') === 'FURGON_TRASLADO' ? 'selected' : '' }}>Furgón de Traslado</option>
            <option value="VEHICULO_ACOMPANAMIENTO" {{ request('tipo_vehiculo') === 'VEHICULO_ACOMPANAMIENTO' ? 'selected' : '' }}>Vehículo de Apoyo</option>
        </select>

        <select name="estado_vehiculo" class="rounded-xl border-slate-300">
            <option value="">Todos los estados</option>
            <option value="DISPONIBLE" {{ request('estado_vehiculo') === 'DISPONIBLE' ? 'selected' : '' }}>Disponible</option>
            <option value="EN_SERVICIO" {{ request('estado_vehiculo') === 'EN_SERVICIO' ? 'selected' : '' }}>En Servicio</option>
            <option value="MANTENCION" {{ request('estado_vehiculo') === 'MANTENCION' ? 'selected' : '' }}>En Mantención</option>
            <option value="INACTIVO" {{ request('estado_vehiculo') === 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
        </select>

        <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-[#34287d] text-white font-semibold rounded-xl px-3 py-2">Filtrar</button>
            <a href="{{ route('vehiculos.index') }}" class="px-3 py-2 bg-slate-200 rounded-xl font-semibold flex items-center">Limpiar</a>
        </div>
    </form>

    <!-- Tarjetas de Flota de Vehículos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($vehiculos as $veh)
            @php
                $badgeColor = $veh->estado_vehiculo === 'DISPONIBLE'
                    ? 'bg-emerald-100 text-emerald-800'
                    : ($veh->estado_vehiculo === 'EN_SERVICIO' ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800');
            @endphp
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex flex-col justify-between space-y-4">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-base text-indigo-950 tracking-wider">{{ $veh->patente }}</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $badgeColor }}">{{ \App\Support\Etiquetas::texto($veh->estado_vehiculo) }}</span>
                    </div>
                    <p class="font-bold text-sm text-slate-800">{{ $veh->marca_modelo }}</p>
                    <p class="text-xs text-slate-500">{{ \App\Support\Etiquetas::texto($veh->tipo_vehiculo) }} • Año {{ $veh->anio ?? 'N/A' }}</p>
                    @if($veh->expedientes->count() > 0)
                        <p class="text-[11px] font-semibold text-indigo-700 pt-1">
                            Asignado a: {{ $veh->expedientes->first()->folio }} ({{ $veh->expedientes->first()->fallecido->nombre_completo ?? '' }})
                        </p>
                    @endif
                </div>

                @if(Auth::user()->tieneRol(['admin', 'coordinador', 'bodega']))
                    <div class="pt-3 border-t border-slate-100 space-y-2">
                        <form method="POST" action="{{ route('vehiculos.estado', $veh) }}" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="estado_vehiculo" class="flex-1 rounded-xl border-slate-300 text-xs py-1.5">
                                @foreach(['DISPONIBLE', 'EN_SERVICIO', 'MANTENCION', 'INACTIVO'] as $st)
                                    <option value="{{ $st }}" {{ $veh->estado_vehiculo === $st ? 'selected' : '' }}>{{ \App\Support\Etiquetas::texto($st) }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded-xl text-xs font-bold">Cambiar</button>
                        </form>
                        @if(Auth::user()->tieneRol(['admin', 'coordinador']))
                            <form method="POST" action="{{ route('vehiculos.destroy', $veh) }}" onsubmit="return confirm('¿Retirar el vehículo {{ $veh->patente }} de la flota?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full py-1.5 bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white rounded-xl text-xs font-bold transition">
                                    Eliminar Vehículo
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="lg:col-span-3 bg-white rounded-2xl p-8 text-center text-sm text-slate-500 border border-slate-200/80">
                No se encontraron vehículos con los filtros aplicados.
            </div>
        @endforelse
    </div>

    <div>
        {{ $vehiculos->links() }}
    </div>

    <!-- Panel de Asignación Rápida de Vehículo + Chofer + Ayudante a Servicios Activos -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
        <div>
            <h3 class="font-bold text-base text-indigo-950">Asignación de Flota, Chofer y Ayudante a Servicios Activos</h3>
            <p class="text-xs text-slate-500">El sistema oculta automáticamente al personal que está con vacaciones, permiso o licencia médica</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-[#231b56] text-white uppercase">
                    <tr>
                        <th class="px-4 py-3">Expediente / Fallecido</th>
                        <th class="px-4 py-3">Cortejo / Destino</th>
                        <th class="px-4 py-3">Asignar Vehículo, Chofer y Ayudante</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($expedientesActivos as $exp)
                        <tr>
                            <td class="px-4 py-3">
                                <span class="font-bold text-indigo-950 block">{{ $exp->folio }}</span>
                                <span class="text-slate-600">{{ $exp->fallecido->nombre_completo }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-semibold block">{{ $exp->fecha_cortejo->format('d/m/Y H:i') }} hrs</span>
                                <span class="text-slate-500">{{ $exp->cementerio_destino }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('vehiculos.asignar', $exp) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="vehiculo_id" class="rounded-xl border-slate-300 text-xs">
                                        <option value="">-- Vehículo --</option>
                                        @foreach($vehiculosOperativos as $v)
                                            <option value="{{ $v->id }}" {{ $exp->vehiculo_id == $v->id ? 'selected' : '' }}>
                                                {{ $v->patente }} ({{ $v->marca_modelo }})
                                            </option>
                                        @endforeach
                                    </select>

                                    <select name="chofer_id" class="rounded-xl border-slate-300 text-xs">
                                        <option value="">-- Chofer --</option>
                                        @foreach($personalActivo as $p)
                                            <option value="{{ $p->id }}" {{ $exp->chofer_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                        @endforeach
                                    </select>

                                    <select name="ayudante_id" class="rounded-xl border-slate-300 text-xs">
                                        <option value="">-- Ayudante --</option>
                                        @foreach($personalActivo as $p)
                                            <option value="{{ $p->id }}" {{ $exp->ayudante_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                        @endforeach
                                    </select>

                                    @if(Auth::user()->tieneRol(['admin', 'coordinador']))
                                        <button type="submit" class="px-3.5 py-1.5 bg-[#34287d] hover:bg-[#231b59] text-white font-bold rounded-xl">
                                            Asignar
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-slate-500">No hay expedientes activos pendientes de asignación.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $expedientesActivos->links() }}
        </div>
    </div>
</x-app-layout>