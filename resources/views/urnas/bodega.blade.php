<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl text-indigo-950">Bodega y Kardex de Movimientos</h2>
                <p class="text-xs text-slate-500">Control de ingresos de proveedores (San Javier / Santiago), traspasos a Salón y salidas de inventario</p>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <span class="px-3 py-1.5 rounded-xl bg-emerald-100 text-emerald-900 font-bold">Salón: {{ $totalSalon }}/24</span>
                <span class="px-3 py-1.5 rounded-xl bg-indigo-100 text-indigo-900 font-bold">Taller: {{ $totalTaller }}</span>
            </div>
        </div>
    </x-slot>

    <!-- Formularios Operativos de Bodega: Entrada + Traspaso + Salida -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Tarjeta 1: Entrada de Inventario (Proveedor) -->
        <div x-data="{ prov: 'SAN_JAVIER' }" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 space-y-3">
            <h3 class="font-bold text-sm text-indigo-950">📥 Entrada de Proveedor</h3>
            <form method="POST" action="{{ route('urnas.reponer', $urnas->first() ?? 1) }}"
                  onsubmit="this.action = '/urnas/' + this.urna_select.value + '/reponer'"
                  class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                @csrf
                <div class="sm:col-span-2">
                    <label class="font-semibold">Modelo de Urna *</label>
                    <select name="urna_select" required class="w-full mt-1 rounded-xl border-slate-300">
                        @foreach($urnas as $u)
                            <option value="{{ $u->id }}">{{ $u->modelo }} ({{ $u->codigo_sku }}) — Total: {{ $u->stock_salon + $u->stock_taller }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="font-semibold">Proveedor *</label>
                    <select name="proveedor" x-model="prov" class="w-full mt-1 rounded-xl border-slate-300">
                        <option value="SAN_JAVIER">San Javier (Flexible)</option>
                        <option value="SANTIAGO">Santiago (Mín. 24)</option>
                    </select>
                </div>
                <div>
                    <label class="font-semibold">Destino *</label>
                    <select name="destino" class="w-full mt-1 rounded-xl border-slate-300">
                        <option value="TALLER">Bodega Taller</option>
                        <option value="SALON">Salón Calle Blanco</option>
                    </select>
                </div>
                <div>
                    <label class="font-semibold">Cantidad *</label>
                    <input type="number" name="cantidad" :value="prov === 'SANTIAGO' ? 24 : 4" min="1" required class="w-full mt-1 rounded-xl border-slate-300">
                </div>
                <div>
                    <label class="font-semibold">N° Guía / Nota</label>
                    <input type="text" name="observacion" placeholder="Guía #..." class="w-full mt-1 rounded-xl border-slate-300">
                </div>
                <div x-show="prov === 'SANTIAGO'" x-cloak class="sm:col-span-2 p-2 rounded-xl bg-amber-50 text-amber-900 font-semibold text-[11px]">
                    ⚠️ Fábrica Santiago exige camión completo (mínimo 24 unidades).
                </div>
                <div class="sm:col-span-2 flex justify-end pt-1">
                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl">
                        Registrar Entrada
                    </button>
                </div>
            </form>
        </div>

        <!-- Tarjeta 2: Traspaso Interno Taller -> Salón -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 space-y-3">
            <h3 class="font-bold text-sm text-indigo-950">🔄 Traspaso Taller → Salón</h3>
            <form method="POST" action="{{ route('urnas.traspasar', $urnas->first() ?? 1) }}"
                  onsubmit="this.action = '/urnas/' + this.urna_select.value + '/traspasar'"
                  class="space-y-2.5 text-xs">
                @csrf
                <div>
                    <label class="font-semibold">Modelo en Bodega Taller *</label>
                    <select name="urna_select" required class="w-full mt-1 rounded-xl border-slate-300">
                        @foreach($urnas as $u)
                            <option value="{{ $u->id }}">{{ $u->modelo }} — Taller: {{ $u->stock_taller }} / Salón: {{ $u->stock_salon }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="font-semibold">Cantidad a Exhibir en Salón *</label>
                    <input type="number" name="cantidad" value="1" min="1" max="24" required class="w-full mt-1 rounded-xl border-slate-300">
                </div>
                <p class="text-[11px] text-slate-500">Cupo disponible en Salón Calle Blanco: <strong>{{ max(0, 24 - $totalSalon) }} unidad(es)</strong>.</p>
                <div class="pt-1">
                    <button type="submit" class="w-full py-2 bg-violet-700 hover:bg-violet-800 text-white font-bold rounded-xl">
                        Traspasar a Salón
                    </button>
                </div>
            </form>
        </div>

        <!-- Tarjeta 3: Salida / Ajuste de Bodega -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 space-y-3">
            <h3 class="font-bold text-sm text-indigo-950">📤 Salida / Ajuste de Bodega</h3>
            <form method="POST" action="{{ route('urnas.salida', $urnas->first() ?? 1) }}"
                  onsubmit="this.action = '/urnas/' + this.urna_select.value + '/salida'"
                  class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                @csrf
                <div class="sm:col-span-2">
                    <label class="font-semibold">Modelo de Urna *</label>
                    <select name="urna_select" required class="w-full mt-1 rounded-xl border-slate-300">
                        @foreach($urnas as $u)
                            <option value="{{ $u->id }}">{{ $u->modelo }} — Salón: {{ $u->stock_salon }} / Taller: {{ $u->stock_taller }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="font-semibold">Origen *</label>
                    <select name="ubicacion" class="w-full mt-1 rounded-xl border-slate-300">
                        <option value="TALLER">Bodega Taller</option>
                        <option value="SALON">Salón Calle Blanco</option>
                    </select>
                </div>
                <div>
                    <label class="font-semibold">Cantidad *</label>
                    <input type="number" name="cantidad" value="1" min="1" required class="w-full mt-1 rounded-xl border-slate-300">
                </div>
                <div class="sm:col-span-2">
                    <label class="font-semibold">Motivo (Traslado sucursal / Ajuste) *</label>
                    <input type="text" name="motivo" required placeholder="Ej: Envío a sucursal o ajuste físico" class="w-full mt-1 rounded-xl border-slate-300">
                </div>
                <div class="sm:col-span-2 pt-1">
                    <button type="submit" class="w-full py-2 bg-[#34287d] hover:bg-[#231b59] text-white font-bold rounded-xl">
                        Registrar Salida
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Filtros Kardex -->
    <form method="GET" action="{{ route('bodega.index') }}" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 flex flex-wrap gap-3 text-xs">
        <select name="urna_id" class="rounded-xl border-slate-300">
            <option value="">Todas las urnas</option>
            @foreach($urnas as $u)
                <option value="{{ $u->id }}" {{ request('urna_id') == $u->id ? 'selected' : '' }}>{{ $u->modelo }} ({{ $u->codigo_sku }})</option>
            @endforeach
        </select>

        <select name="tipo_movimiento" class="rounded-xl border-slate-300">
            <option value="">Todos los movimientos</option>
            <option value="INGRESO_PROVEEDOR" {{ request('tipo_movimiento') === 'INGRESO_PROVEEDOR' ? 'selected' : '' }}>Ingresos Proveedor</option>
            <option value="TRASPASO_TALLER_SALON" {{ request('tipo_movimiento') === 'TRASPASO_TALLER_SALON' ? 'selected' : '' }}>Traspaso Taller → Salón</option>
            <option value="REBAJA_VENTA" {{ request('tipo_movimiento') === 'REBAJA_VENTA' ? 'selected' : '' }}>Salida por Servicio Funerario</option>
            <option value="AJUSTE_INVENTARIO" {{ request('tipo_movimiento') === 'AJUSTE_INVENTARIO' ? 'selected' : '' }}>Ajuste / Reposición</option>
        </select>

        <button type="submit" class="px-4 py-2 bg-[#34287d] text-white font-bold rounded-xl">Filtrar Kardex</button>
        <a href="{{ route('bodega.index') }}" class="px-4 py-2 bg-slate-200 rounded-xl font-semibold">Limpiar</a>
    </form>

    <!-- Tabla Kardex Histórico Completo -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead class="bg-[#231b56] text-white uppercase">
                <tr>
                    <th class="px-4 py-3">Fecha / Hora</th>
                    <th class="px-4 py-3">Tipo Movimiento</th>
                    <th class="px-4 py-3">Urna</th>
                    <th class="px-4 py-3 text-center">Cantidad</th>
                    <th class="px-4 py-3">Origen → Destino</th>
                    <th class="px-4 py-3">Observación / Responsable</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($movimientos as $mov)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-500">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2.5 py-0.5 rounded-full font-bold text-[10px] {{ $mov->tipo_movimiento === 'INGRESO_PROVEEDOR' ? 'bg-emerald-100 text-emerald-800' : ($mov->tipo_movimiento === 'REBAJA_VENTA' ? 'bg-rose-100 text-rose-800' : 'bg-indigo-100 text-indigo-800') }}">
                                {{ \App\Support\Etiquetas::texto($mov->tipo_movimiento) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-bold text-slate-800">{{ $mov->urna->modelo ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-center font-extrabold">{{ $mov->cantidad }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $mov->origen }} → {{ $mov->destino }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $mov->observacion }} ({{ $mov->registradoPor->name ?? 'Sistema' }})</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">No hay movimientos en Kardex con los filtros aplicados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $movimientos->links() }}
    </div>
</x-app-layout>