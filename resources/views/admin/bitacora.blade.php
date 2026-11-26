<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-indigo-950">Bitácora de Auditoría</h2>
            <p class="text-xs text-slate-500">Trazabilidad compacta de cambios exactos (Antes → Después), creaciones y emisión de documentos</p>
        </div>
    </x-slot>

    <!-- Filtros -->
    <form method="GET" action="{{ route('bitacora.index') }}" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Folio, ID registro o texto..." class="sm:col-span-3 rounded-xl border-slate-300">

        <select name="usuario_id" class="sm:col-span-2 rounded-xl border-slate-300">
            <option value="">Todos los usuarios</option>
            @foreach($usuariosFiltro as $uf)
                <option value="{{ $uf->id }}" {{ request('usuario_id') == $uf->id ? 'selected' : '' }}>{{ $uf->name }}</option>
            @endforeach
        </select>

        <select name="accion" class="sm:col-span-2 rounded-xl border-slate-300">
            <option value="">Todas las acciones</option>
            <option value="CREACION" {{ request('accion') === 'CREACION' ? 'selected' : '' }}>Creación</option>
            <option value="ACTUALIZACION" {{ request('accion') === 'ACTUALIZACION' ? 'selected' : '' }}>Actualización</option>
            <option value="MODIFICACION_CONTRATO" {{ request('accion') === 'MODIFICACION_CONTRATO' ? 'selected' : '' }}>Modificación contrato</option>
            <option value="CAMBIO_ESTADO_FLOTA" {{ request('accion') === 'CAMBIO_ESTADO_FLOTA' ? 'selected' : '' }}>Cambio estado flota</option>
            <option value="ASIGNACION_FLOTA_PERSONAL" {{ request('accion') === 'ASIGNACION_FLOTA_PERSONAL' ? 'selected' : '' }}>Asignación flota</option>
            <option value="CAMBIO_ESTADO_INACTIVO" {{ request('accion') === 'CAMBIO_ESTADO_INACTIVO' ? 'selected' : '' }}>Desactivación</option>
            <option value="ELIMINACION" {{ request('accion') === 'ELIMINACION' ? 'selected' : '' }}>Eliminación</option>
            <option value="EMISION_PDF" {{ request('accion') === 'EMISION_PDF' ? 'selected' : '' }}>Emisión PDF</option>
        </select>

        <select name="tabla" class="sm:col-span-2 rounded-xl border-slate-300">
            <option value="">Todos los módulos</option>
            <option value="expedientes" {{ request('tabla') === 'expedientes' ? 'selected' : '' }}>Expedientes</option>
            <option value="clientes" {{ request('tabla') === 'clientes' ? 'selected' : '' }}>Clientes</option>
            <option value="fallecidos" {{ request('tabla') === 'fallecidos' ? 'selected' : '' }}>Fallecidos</option>
            <option value="urnas" {{ request('tabla') === 'urnas' ? 'selected' : '' }}>Urnas</option>
            <option value="movimientos_inventario" {{ request('tabla') === 'movimientos_inventario' ? 'selected' : '' }}>Bodega (Kardex)</option>
            <option value="vehiculos" {{ request('tabla') === 'vehiculos' ? 'selected' : '' }}>Vehículos</option>
            <option value="convenios_previsionales" {{ request('tabla') === 'convenios_previsionales' ? 'selected' : '' }}>Convenios</option>
            <option value="abonos_pagos" {{ request('tabla') === 'abonos_pagos' ? 'selected' : '' }}>Abonos y pagos</option>
            <option value="documentos_expediente" {{ request('tabla') === 'documentos_expediente' ? 'selected' : '' }}>Documentos</option>
            <option value="condolencias" {{ request('tabla') === 'condolencias' ? 'selected' : '' }}>Condolencias</option>
            <option value="users" {{ request('tabla') === 'users' ? 'selected' : '' }}>Usuarios</option>
            <option value="roles" {{ request('tabla') === 'roles' ? 'selected' : '' }}>Roles</option>
        </select>

        <input type="date" name="fecha" value="{{ request('fecha') }}" class="sm:col-span-1 rounded-xl border-slate-300">

        <div class="sm:col-span-2 flex gap-2">
            <button type="submit" class="flex-1 px-3 py-2 bg-[#34287d] text-white font-bold rounded-xl">Filtrar</button>
            @if(request()->anyFilled(['buscar', 'usuario_id', 'accion', 'tabla', 'fecha']))
                <a href="{{ route('bitacora.index') }}" class="px-3 py-2 bg-slate-200 rounded-xl font-semibold flex items-center">Limpiar</a>
            @endif
        </div>
    </form>

    <!-- Tabla Compacta -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead class="bg-[#231b56] text-white uppercase">
                <tr>
                    <th class="px-4 py-3">Fecha y Responsable</th>
                    <th class="px-4 py-3">Acción / Entidad</th>
                    <th class="px-4 py-3">Qué ocurrió o qué cambió (Antes → Después)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($registros as $reg)
                    @php
                        $esCambio = in_array($reg->accion, ['ACTUALIZACION', 'MODIFICACION_CONTRATO', 'CAMBIO_ESTADO_FLOTA', 'ASIGNACION_FLOTA_PERSONAL', 'CAMBIO_ESTADO_INACTIVO'], true);
                        $diffs = $esCambio ? $reg->diferenciasCompactas() : [];
                        $datosFicha = $esCambio ? [] : $reg->valoresPriorizados($reg->accion === 'ELIMINACION' ? $reg->valores_anteriores_json : $reg->valores_nuevos_json);
                    @endphp
                    <tr class="align-top hover:bg-slate-50/80">
                        <td class="px-4 py-3 whitespace-nowrap w-52">
                            <span class="font-bold text-indigo-950 block">{{ $reg->usuario->name ?? 'Sistema inicial' }}</span>
                            <span class="text-slate-500">{{ \Carbon\Carbon::parse($reg->created_at)->format('d/m/Y H:i') }} • IP {{ $reg->ip_origen ?? 'Local' }}</span>
                        </td>

                        <td class="px-4 py-3 whitespace-nowrap w-56">
                            <span class="px-2.5 py-0.5 rounded-full font-bold text-[11px] inline-block {{ $reg->claseBadgeAccion() }}">
                                {{ $reg->etiquetaAccion() }}
                            </span>
                            <span class="text-slate-800 font-bold mt-1 block">
                                {{ $reg->etiquetaTabla() }} #{{ $reg->registro_id }}
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            @if($esCambio && count($diffs) > 0)
                                <div x-data="{ expandido: false }" class="space-y-1">
                                    @foreach(array_slice($diffs, 0, 2) as $d)
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="font-bold text-slate-700">{{ $d['campo'] }}:</span>
                                            @if($d['antes'] !== null)
                                                <span class="px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 line-through">{{ $d['antes'] }}</span>
                                                <span class="text-slate-400 font-bold">→</span>
                                            @endif
                                            <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-800 font-semibold">{{ $d['despues'] }}</span>
                                        </div>
                                    @endforeach

                                    @if(count($diffs) > 2)
                                        <div x-show="expandido" x-cloak class="space-y-1 pt-1">
                                            @foreach(array_slice($diffs, 2) as $d)
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    <span class="font-bold text-slate-700">{{ $d['campo'] }}:</span>
                                                    @if($d['antes'] !== null)
                                                        <span class="px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 line-through">{{ $d['antes'] }}</span>
                                                        <span class="text-slate-400 font-bold">→</span>
                                                    @endif
                                                    <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-800 font-semibold">{{ $d['despues'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                        <button type="button" @click="expandido = !expandido" class="text-[11px] font-bold text-indigo-700 hover:underline">
                                            <span x-text="expandido ? 'Ocultar cambios extra' : '+ Ver ' + {{ count($diffs) - 2 }} + ' cambio(s) más'"></span>
                                        </button>
                                    @endif
                                </div>
                            @elseif(count($datosFicha) > 0)
                                <div x-data="{ verFicha: false }" class="space-y-1">
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                        @foreach(array_slice($datosFicha, 0, 2, true) as $campo => $valor)
                                            <span>
                                                <strong class="text-slate-700">{{ $campo }}:</strong>
                                                <span class="{{ $reg->accion === 'ELIMINACION' ? 'text-rose-700' : 'text-slate-800' }}">{{ $valor }}</span>
                                            </span>
                                        @endforeach
                                        @if(count($datosFicha) > 2)
                                            <button type="button" @click="verFicha = !verFicha" class="text-[11px] font-bold text-indigo-700 hover:underline">
                                                <span x-text="verFicha ? 'Ocultar ficha' : '+ ' + {{ count($datosFicha) - 2 }} + ' dato(s)'"></span>
                                            </button>
                                        @endif
                                    </div>

                                    @if(count($datosFicha) > 2)
                                        <div x-show="verFicha" x-cloak class="pt-1.5 mt-1 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 text-[11px] text-slate-600">
                                            @foreach(array_slice($datosFicha, 2, null, true) as $campo => $valor)
                                                <div><strong>{{ $campo }}:</strong> {{ $valor }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="text-slate-400 italic">Movimiento de sesión o actualización interna</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-slate-400">No se encontraron movimientos en la bitácora con los filtros seleccionados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $registros->links() }}
    </div>
</x-app-layout>