<x-app-layout>
    <div x-data="{ openNueva: false }" class="space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl text-indigo-950">Libro Digital de Condolencias</h2>
                <p class="text-xs text-slate-500">Moderación de dedicatorias familiares y descarga en PDF conmemorativo</p>
            </div>
            @if(Auth::user()->tieneRol(['admin', 'coordinador']))
                <button type="button" @click="openNueva = true" class="px-4 py-2.5 bg-[#34287d] hover:bg-indigo-900 text-white text-xs font-bold rounded-xl shadow">
                    + Registrar Condolencia Manual
                </button>
            @endif
        </div>

        <!-- Descarga Libro PDF y Enlace al Memorial por Servicio -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 space-y-3">
            <h3 class="font-bold text-sm text-indigo-950">📖 Exportar Libro de Condolencias en PDF para la Familia</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($expedientes as $exp)
                    <div class="p-3.5 rounded-xl border border-slate-200 flex items-center justify-between gap-2 text-xs">
                        <div>
                            <span class="font-bold text-indigo-950 block">{{ $exp->fallecido->nombre_completo }}</span>
                            <span class="text-slate-500">{{ $exp->folio }} • {{ $exp->condolencias_aprobadas_count }} aprobada(s)</span>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <a href="{{ route('publico.memorial', $exp->folio) }}" target="_blank" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg" title="Ver Memorial Público">
                                Memorial ↗
                            </a>
                            <a href="{{ route('condolencias.pdf', $exp) }}" target="_blank" class="px-3 py-1.5 bg-[#34287d] text-white font-bold rounded-lg">
                                Libro PDF ↗
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Filtros de Moderación -->
        <form method="GET" action="{{ route('condolencias.admin') }}" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Remitente, mensaje, folio o fallecido..." class="sm:col-span-4 rounded-xl border-slate-300">

            <select name="expediente_id" class="sm:col-span-3 rounded-xl border-slate-300">
                <option value="">Todos los servicios</option>
                @foreach($expedientes as $exp)
                    <option value="{{ $exp->id }}" {{ request('expediente_id') == $exp->id ? 'selected' : '' }}>{{ $exp->folio }} — {{ $exp->fallecido->nombre_completo }}</option>
                @endforeach
            </select>

            <select name="estado" class="sm:col-span-3 rounded-xl border-slate-300">
                <option value="">Todos los estados</option>
                <option value="visible" {{ request('estado') === 'visible' ? 'selected' : '' }}>Visibles (Aprobados)</option>
                <option value="oculto" {{ request('estado') === 'oculto' ? 'selected' : '' }}>Ocultos</option>
            </select>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-[#34287d] text-white font-bold rounded-xl">Filtrar</button>
                @if(request('buscar') || request('expediente_id') || request('estado'))
                    <a href="{{ route('condolencias.admin') }}" class="px-3 py-2 bg-slate-200 rounded-xl font-semibold flex items-center">Limpiar</a>
                @endif
            </div>
        </form>

        <!-- Tabla de Moderación -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-[#231b56] text-white uppercase">
                    <tr>
                        <th class="px-4 py-3">Servicio / Fallecido</th>
                        <th class="px-4 py-3">Remitente</th>
                        <th class="px-4 py-3">Mensaje</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3 text-right">Moderación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($condolencias as $c)
                        <tr>
                            <td class="px-4 py-3">
                                <span class="font-bold text-indigo-950 block">{{ $c->expediente->fallecido->nombre_completo ?? 'N/A' }}</span>
                                <span class="text-slate-400">{{ $c->expediente->folio ?? '' }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $c->nombre_remitente }} ({{ $c->relacion }})</td>
                            <td class="px-4 py-3 text-slate-600 max-w-md">{{ $c->mensaje }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full font-bold {{ $c->aprobado ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $c->aprobado ? 'Visible' : 'Oculto' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <form method="POST" action="{{ route('condolencias.toggle', $c) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-3 py-1 bg-slate-200 hover:bg-slate-300 rounded-lg font-bold">
                                            {{ $c->aprobado ? 'Ocultar' : 'Aprobar' }}
                                        </button>
                                    </form>
                                    @if(Auth::user()->tieneRol(['admin', 'coordinador']))
                                        <form method="POST" action="{{ route('condolencias.destroy', $c) }}" onsubmit="return confirm('¿Eliminar esta condolencia?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white rounded-lg font-bold transition">
                                                Eliminar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">No hay mensajes de condolencias con los filtros aplicados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $condolencias->links() }}
        </div>

        <!-- Modal Registrar Condolencia Manual -->
        <div x-show="openNueva" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex justify-between items-center border-b pb-3">
                    <h3 class="font-bold text-base text-indigo-950">Transcribir Dedicatoria al Libro</h3>
                    <button type="button" @click="openNueva = false" class="text-slate-400 font-bold">✕</button>
                </div>

                <form method="POST" action="{{ route('condolencias.store') }}" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="font-semibold">Servicio / Fallecido *</label>
                        <select name="expediente_id" required class="w-full mt-1 rounded-xl border-slate-300">
                            <option value="">Seleccione un servicio...</option>
                            @foreach($expedientes as $exp)
                                <option value="{{ $exp->id }}">{{ $exp->folio }} — {{ $exp->fallecido->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="font-semibold">Nombre Remitente *</label>
                            <input type="text" name="nombre_remitente" required placeholder="Ej: Familia Pérez Soto" class="w-full mt-1 rounded-xl border-slate-300">
                        </div>
                        <div>
                            <label class="font-semibold">Parentesco / Relación</label>
                            <input type="text" name="relacion" placeholder="Ej: Amigos / Vecinos" class="w-full mt-1 rounded-xl border-slate-300">
                        </div>
                    </div>
                    <div>
                        <label class="font-semibold">Mensaje de Condolencia *</label>
                        <textarea name="mensaje" rows="3" required placeholder="Escriba la dedicatoria recibida..." class="w-full mt-1 rounded-xl border-slate-300"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="openNueva = false" class="px-4 py-2 bg-slate-200 rounded-xl font-semibold">Cancelar</button>
                        <button type="submit" class="px-5 py-2 bg-[#34287d] text-white rounded-xl font-bold">Publicar Dedicatoria</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>