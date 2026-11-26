<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-indigo-950">Registro de Fallecidos — Datos para Inscripción Registro Civil</h2>
            <p class="text-xs text-slate-500">Control de escolaridad, profesión, estado civil, lugar de defunción, SML y cementerio destino</p>
        </div>
    </x-slot>

    @if($errors->any())
        <div class="p-3.5 rounded-2xl bg-rose-50 border-l-4 border-rose-600 text-rose-900 text-xs space-y-1">
            <p class="font-bold">No se pudo guardar la ficha por los siguientes errores:</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" action="{{ route('fallecidos.index') }}" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 grid grid-cols-1 sm:grid-cols-12 gap-3 text-sm">
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre, RUT o lugar de fallecimiento..." class="sm:col-span-6 rounded-xl border-slate-300">

        <select name="filtro" class="sm:col-span-4 rounded-xl border-slate-300">
            <option value="">Todos los fallecidos</option>
            <option value="incompletos" {{ request('filtro') === 'incompletos' ? 'selected' : '' }}>Faltan datos Registro Civil</option>
            <option value="sml" {{ request('filtro') === 'sml' ? 'selected' : '' }}>Con intervención SML / Fiscalía</option>
            <option value="sin_rut" {{ request('filtro') === 'sin_rut' ? 'selected' : '' }}>Sin RUT registrado (NN)</option>
        </select>

        <div class="sm:col-span-2 flex gap-2">
            <button type="submit" class="flex-1 px-4 py-2 bg-[#34287d] text-white font-bold rounded-xl text-xs">Filtrar</button>
            @if(request('buscar') || request('filtro'))
                <a href="{{ route('fallecidos.index') }}" class="px-3 py-2 bg-slate-200 rounded-xl font-semibold text-xs flex items-center">Limpiar</a>
            @endif
        </div>
    </form>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
        <table class="w-full text-xs sm:text-sm text-left">
            <thead class="bg-[#231b56] text-white text-xs uppercase">
                <tr>
                    <th class="px-4 py-3">Fallecido / RUT</th>
                    <th class="px-4 py-3">Defunción</th>
                    <th class="px-4 py-3">Datos Registro Civil</th>
                    <th class="px-4 py-3">SML / Cementerio</th>
                    <th class="px-4 py-3 text-right">Ficha</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($fallecidos as $fal)
                    @php $exp = $fal->expedientes->first(); @endphp
                    <tr x-data="{ editFal: false }" class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <p class="font-bold text-slate-900">{{ $fal->nombre_completo }}</p>
                            <p class="text-xs text-slate-500">RUT: {{ $fal->rut ?: 'No registrado' }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            <p class="font-semibold">{{ $fal->fecha_fallecimiento->format('d/m/Y H:i') }} hrs</p>
                            <p class="text-slate-500">{{ $fal->lugar_fallecimiento }} ({{ $fal->comuna_defuncion }})</p>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            <p><strong>Estado Civil:</strong> {{ $fal->estado_civil ?: 'Sin dato' }}</p>
                            <p><strong>Escolaridad:</strong> {{ $fal->escolaridad ?: 'Sin dato' }} | <strong>Prof.:</strong> {{ $fal->profesion ?: 'Sin dato' }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            <p class="font-semibold">{{ $exp->cementerio_destino ?? 'Por definir' }}</p>
                            @if($fal->intervencion_sml)
                                <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-900 font-bold text-[10px]">SML Oficio: {{ $fal->numero_oficio_fiscalia ?: 'Pendiente' }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right space-x-1">
                            <button @click="editFal = true" class="px-3 py-1.5 bg-indigo-50 text-indigo-800 font-bold rounded-xl text-xs hover:bg-indigo-100">
                                Completar Datos
                            </button>

                            <!-- Modal Edición Registro Civil -->
                            <div x-show="editFal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4 text-left">
                                <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl space-y-4">
                                    <div class="flex justify-between items-center border-b pb-3">
                                        <h3 class="font-bold text-base text-indigo-950">Ficha Registro Civil — {{ $fal->nombre_completo }}</h3>
                                        <button @click="editFal = false" class="text-slate-400 font-bold">✕</button>
                                    </div>
                                    <form method="POST" action="{{ route('fallecidos.update', $fal) }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                        @csrf
                                        @method('PUT')
                                        <div>
                                            <label class="font-semibold">Nombre Completo *</label>
                                            <input type="text" name="nombre_completo" value="{{ $fal->nombre_completo }}" required minlength="5" class="w-full mt-1 rounded-xl border-slate-300">
                                        </div>
                                        <div>
                                            <label class="font-semibold">RUT (Opcional si NN)</label>
                                            <input type="text" name="rut" value="{{ $fal->rut }}" maxlength="12" placeholder="12.345.678-9" oninput="formatearRutFal(this)" class="w-full mt-1 rounded-xl border-slate-300">
                                        </div>
                                        <div>
                                            <label class="font-semibold">Fecha y Hora Defunción *</label>
                                            <input type="datetime-local" name="fecha_fallecimiento" value="{{ $fal->fecha_fallecimiento->format('Y-m-d\TH:i') }}" min="2020-01-01T00:00" max="2035-12-31T23:59" required class="w-full mt-1 rounded-xl border-slate-300">
                                        </div>
                                        <div>
                                            <label class="font-semibold">Lugar Fallecimiento *</label>
                                            <input type="text" name="lugar_fallecimiento" value="{{ $fal->lugar_fallecimiento }}" required class="w-full mt-1 rounded-xl border-slate-300">
                                        </div>
                                        <div>
                                            <label class="font-semibold">Comuna Defunción *</label>
                                            <input type="text" name="comuna_defuncion" value="{{ $fal->comuna_defuncion }}" required class="w-full mt-1 rounded-xl border-slate-300">
                                        </div>
                                        <div>
                                            <label class="font-semibold">Estado Civil (Reg. Civil)</label>
                                            <select name="estado_civil" class="w-full mt-1 rounded-xl border-slate-300">
                                                <option value="">-- Seleccionar --</option>
                                                @foreach(['Casado(a)', 'Viudo(a)', 'Soltero(a)', 'Divorciado(a)'] as $ec)
                                                    <option value="{{ $ec }}" {{ $fal->estado_civil === $ec ? 'selected' : '' }}>{{ $ec }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="font-semibold">Escolaridad (Reg. Civil)</label>
                                            <select name="escolaridad" class="w-full mt-1 rounded-xl border-slate-300">
                                                <option value="">-- Seleccionar --</option>
                                                @foreach(['Media Completa', 'Básica Completa', 'Superior / Universitaria', 'Sin Escolaridad'] as $esc)
                                                    <option value="{{ $esc }}" {{ $fal->escolaridad === $esc ? 'selected' : '' }}>{{ $esc }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="font-semibold">Profesión / Oficio (Reg. Civil)</label>
                                            <input type="text" name="profesion" value="{{ $fal->profesion }}" class="w-full mt-1 rounded-xl border-slate-300">
                                        </div>
                                        <div class="sm:col-span-2 flex flex-wrap items-center gap-3 pt-1">
                                            <label class="inline-flex items-center font-bold text-amber-900">
                                                <input type="checkbox" name="intervencion_sml" value="1" {{ $fal->intervencion_sml ? 'checked' : '' }} class="rounded text-amber-600 mr-2">
                                                Intervención Servicio Médico Legal (SML)
                                            </label>
                                            <input type="text" name="numero_oficio_fiscalia" value="{{ $fal->numero_oficio_fiscalia }}" placeholder="N° Oficio Fiscalía" class="rounded-xl border-slate-300 text-xs w-60">
                                        </div>
                                        <div class="sm:col-span-2 flex justify-end gap-2 pt-2">
                                            <button type="button" @click="editFal = false" class="px-4 py-2 bg-slate-200 rounded-xl font-semibold">Cancelar</button>
                                            <button type="submit" class="px-5 py-2 bg-[#34287d] text-white rounded-xl font-bold">Guardar Ficha</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">No se encontraron registros con los filtros seleccionados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $fallecidos->links() }}
    </div>

    <script>
        function formatearRutFal(input) {
            let v = input.value.replace(/[^0-9kK]/g, '').toUpperCase();
            if (v.length <= 1) {
                input.value = v;
                return;
            }
            const cuerpo = v.slice(0, -1).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            const dv = v.slice(-1);
            input.value = `${cuerpo}-${dv}`;
        }
    </script>
</x-app-layout>