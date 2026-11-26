<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-indigo-950">Convenios Previsionales (IPS / AFP / DIPRECA / Sindicatos)</h2>
            <p class="text-xs text-slate-500">Mantenedor de topes legales y emisión de Liquidación de Convenio en PDF</p>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Nuevo Convenio -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 space-y-3">
            <h3 class="font-bold text-sm text-indigo-950">+ Registrar Convenio / Institución</h3>
            <form method="POST" action="{{ route('convenios.store') }}" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="font-semibold">Nombre Institución *</label>
                    <input type="text" name="nombre" required placeholder="Ej: Sindicato Trabajadores Temuco" class="w-full mt-1 rounded-xl border-slate-300">
                </div>
                <div>
                    <label class="font-semibold">Tipo *</label>
                    <select name="tipo" class="w-full mt-1 rounded-xl border-slate-300">
                        <option value="IPS">IPS</option>
                        <option value="AFP">AFP (Cuota Mortuoria UF)</option>
                        <option value="FFAA_ORDEN">FF.AA. y Orden (DIPRECA/CAPREDENA)</option>
                        <option value="SINDICATO">Sindicato / Empresa</option>
                        <option value="PARTICULAR">Particular</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="font-semibold">Tope CLP *</label>
                        <input type="number" name="monto_tope_clp" value="1070000" required class="w-full mt-1 rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="font-semibold">Tope UF (Si aplica)</label>
                        <input type="number" step="0.01" name="monto_tope_uf" value="0" class="w-full mt-1 rounded-xl border-slate-300">
                    </div>
                </div>
                <button type="submit" class="w-full py-2 bg-[#34287d] text-white font-bold rounded-xl">Guardar Convenio</button>
            </form>
        </div>

        <!-- Listado de Convenios Vigentes -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-[#231b56] text-white uppercase">
                    <tr>
                        <th class="px-4 py-3">Institución</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Tope Cobertura</th>
                        <th class="px-4 py-3 text-center">Servicios</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($convenios as $conv)
                        <tr x-data="{ editConv: false }">
                            <td class="px-4 py-3 font-bold text-indigo-950">{{ $conv->nombre }}</td>
                            <td class="px-4 py-3">{{ \App\Support\Etiquetas::texto($conv->tipo) }}</td>
                            <td class="px-4 py-3 font-bold text-emerald-700">
                                {{ $conv->monto_tope_uf > 0 ? $conv->monto_tope_uf . ' UF' : '$' . number_format($conv->monto_tope_clp, 0, ',', '.') . ' CLP' }}
                            </td>
                            <td class="px-4 py-3 text-center font-bold">{{ $conv->expedientes_count }}</td>
                            <td class="px-4 py-3 text-right space-x-1">
                                <button type="button" @click="editConv = true" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-500 text-amber-800 hover:text-white rounded-lg font-bold transition">
                                    Editar
                                </button>
                                @if(Auth::user()->tieneRol(['admin', 'finanzas']))
                                    <form method="POST" action="{{ route('convenios.destroy', $conv) }}" class="inline-flex" onsubmit="return confirm('¿Eliminar el convenio {{ $conv->nombre }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white rounded-lg font-bold transition">
                                            Eliminar
                                        </button>
                                    </form>
                                @endif

                                <!-- Modal Edición de Convenio -->
                                <div x-show="editConv" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4 text-left">
                                    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
                                        <div class="flex justify-between items-center border-b pb-3">
                                            <h3 class="font-bold text-base text-indigo-950">Editar Convenio</h3>
                                            <button type="button" @click="editConv = false" class="text-slate-400 font-bold">✕</button>
                                        </div>
                                        <form method="POST" action="{{ route('convenios.update', $conv) }}" class="space-y-3 text-xs">
                                            @csrf
                                            @method('PUT')
                                            <div>
                                                <label class="font-semibold">Nombre Institución *</label>
                                                <input type="text" name="nombre" value="{{ $conv->nombre }}" required class="w-full mt-1 rounded-xl border-slate-300">
                                            </div>
                                            <div>
                                                <label class="font-semibold">Tipo *</label>
                                                <select name="tipo" class="w-full mt-1 rounded-xl border-slate-300">
                                                    @foreach(['IPS' => 'IPS', 'AFP' => 'AFP', 'FFAA_ORDEN' => 'FF.AA. y Orden', 'SINDICATO' => 'Sindicato / Empresa', 'PARTICULAR' => 'Particular'] as $k => $v)
                                                        <option value="{{ $k }}" {{ $conv->tipo === $k ? 'selected' : '' }}>{{ $v }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="font-semibold">Tope CLP *</label>
                                                    <input type="number" name="monto_tope_clp" value="{{ $conv->monto_tope_clp }}" required class="w-full mt-1 rounded-xl border-slate-300">
                                                </div>
                                                <div>
                                                    <label class="font-semibold">Tope UF (Si aplica)</label>
                                                    <input type="number" step="0.01" name="monto_tope_uf" value="{{ $conv->monto_tope_uf }}" class="w-full mt-1 rounded-xl border-slate-300">
                                                </div>
                                            </div>
                                            <div class="flex justify-end gap-2 pt-2">
                                                <button type="button" @click="editConv = false" class="px-4 py-2 bg-slate-200 rounded-xl font-semibold">Cancelar</button>
                                                <button type="submit" class="px-5 py-2 bg-[#34287d] text-white rounded-xl font-bold">Guardar Cambios</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Expedientes con Convenio y Descarga PDF -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
        <div class="p-4 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <h3 class="font-bold text-sm text-indigo-950">Liquidaciones de Convenio por Expediente (PDF para Tramitación)</h3>

            <form method="GET" action="{{ route('convenios.index') }}" class="flex flex-wrap items-center gap-2 text-xs">
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Folio, fallecido, cliente o RUT..." class="rounded-xl border-slate-300 text-xs w-56">
                <select name="convenio_id" class="rounded-xl border-slate-300 text-xs">
                    <option value="">Todos los convenios</option>
                    @foreach($convenios as $conv)
                        <option value="{{ $conv->id }}" {{ request('convenio_id') == $conv->id ? 'selected' : '' }}>{{ $conv->nombre }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3.5 py-2 bg-[#34287d] text-white font-bold rounded-xl">Filtrar</button>
                @if(request('buscar') || request('convenio_id'))
                    <a href="{{ route('convenios.index') }}" class="px-3 py-2 bg-slate-200 rounded-xl font-semibold">Limpiar</a>
                @endif
            </form>
        </div>

        <table class="w-full text-xs text-left">
            <thead class="bg-slate-100 text-slate-700 uppercase">
                <tr>
                    <th class="px-4 py-2.5">Expediente</th>
                    <th class="px-4 py-2.5">Fallecido / Contratante</th>
                    <th class="px-4 py-2.5">Convenio</th>
                    <th class="px-4 py-2.5">Monto Bonificado</th>
                    <th class="px-4 py-2.5 text-right">Documento PDF</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($expedientesConvenio as $ec)
                    <tr>
                        <td class="px-4 py-2.5 font-bold">{{ $ec->folio }}</td>
                        <td class="px-4 py-2.5">{{ $ec->fallecido->nombre_completo }} ({{ $ec->cliente->rut }})</td>
                        <td class="px-4 py-2.5">{{ $ec->convenio->nombre }}</td>
                        <td class="px-4 py-2.5 font-bold text-violet-800">${{ number_format($ec->monto_cobertura_convenio, 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5 text-right">
                            <a href="{{ route('convenios.pdf', $ec) }}" target="_blank" class="px-3 py-1 bg-indigo-50 text-indigo-800 font-bold rounded-lg hover:bg-indigo-100">
                                Liquidación PDF ↗
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No se encontraron liquidaciones con los filtros aplicados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100">
            {{ $expedientesConvenio->links() }}
        </div>
    </div>
</x-app-layout>