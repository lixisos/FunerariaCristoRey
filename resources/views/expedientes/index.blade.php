<x-app-layout>
    <x-slot name="header">
        <div x-data="{ openExp: {{ $errors->any() ? 'true' : 'false' }} }" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl text-indigo-950">Servicios y Expedientes Funerarios</h2>
                <p class="text-xs text-slate-500">Gestión integral de contratos, semáforo documental Seremi, flota y asignación de personal</p>
            </div>
            @if(Auth::user()->tieneRol(['admin', 'coordinador']))
                <button @click="openExp = true" class="px-4 py-2.5 bg-[#34287d] hover:bg-[#231b59] text-white text-xs sm:text-sm font-bold rounded-xl shadow">
                    + Nuevo Expediente Funerario
                </button>
            @endif

            <!-- Modal Apertura de Expediente -->
            <div x-show="openExp" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
                <div class="bg-white rounded-2xl max-w-4xl w-full p-6 shadow-2xl space-y-4 my-8 max-h-[92vh] overflow-y-auto">
                    <div class="flex justify-between items-center border-b pb-3">
                        <h3 class="font-bold text-lg text-indigo-950">Apertura de Nuevo Expediente Funerario</h3>
                        <button @click="openExp = false" class="text-slate-400 hover:text-slate-700 font-bold">✕</button>
                    </div>

                    @if($errors->any())
                        <div class="p-3.5 rounded-xl bg-rose-50 border-l-4 border-rose-600 text-rose-900 text-xs space-y-1">
                            <p class="font-bold">Corrige los siguientes datos antes de crear el expediente:</p>
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('expedientes.store') }}" class="space-y-4 text-sm">
                        @csrf
                        <!-- 1. Contratante -->
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="font-bold text-indigo-900 text-xs uppercase tracking-wider">1. Contratante Responsable (Búsqueda automática por RUT)</h4>
                                <span id="badge_historial_rut" class="hidden px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold"></span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="text-xs font-semibold">RUT Contratante *</label>
                                    <input type="text" id="cliente_rut" name="cliente_rut" value="{{ old('cliente_rut') }}" required maxlength="12" placeholder="12.345.678-9"
                                           oninput="formatearRutInput(this)" onblur="validarRutCampo(this, 'err_cliente_rut'); buscarClientePorRut(this.value)"
                                           class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                    <p id="err_cliente_rut" class="hidden text-[11px] text-rose-600 font-semibold mt-1"></p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="text-xs font-semibold">Nombre Completo *</label>
                                    <input type="text" id="cliente_nombre" name="cliente_nombre" value="{{ old('cliente_nombre') }}" required minlength="5" placeholder="Ej: María Elena Riquelme Castro" class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold">Teléfono *</label>
                                    <input type="text" id="cliente_telefono" name="cliente_telefono" value="{{ old('cliente_telefono') }}" required maxlength="16" placeholder="+56 9 8765 4321" class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold">Dirección *</label>
                                    <input type="text" id="cliente_direccion" name="cliente_direccion" value="{{ old('cliente_direccion') }}" required minlength="5" placeholder="Ej: Av. Alemania 0845" class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold">Comuna *</label>
                                    <input type="text" id="cliente_comuna" name="cliente_comuna" value="{{ old('cliente_comuna', 'Temuco') }}" required class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                </div>
                            </div>
                        </div>

                        <!-- 2. Fallecido -->
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                            <h4 class="font-bold text-indigo-900 text-xs uppercase tracking-wider">2. Datos del Fallecido (Inscripción Registro Civil y SML)</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="text-xs font-semibold">RUT Fallecido</label>
                                    <input type="text" name="fallecido_rut" value="{{ old('fallecido_rut') }}" maxlength="12" placeholder="Opcional si NN"
                                           oninput="formatearRutInput(this)" onblur="validarRutCampo(this, 'err_fallecido_rut', true)"
                                           class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                    <p id="err_fallecido_rut" class="hidden text-[11px] text-rose-600 font-semibold mt-1"></p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="text-xs font-semibold">Nombre Completo del Fallecido *</label>
                                    <input type="text" name="fallecido_nombre" value="{{ old('fallecido_nombre') }}" required minlength="5" placeholder="Nombre y dos apellidos" class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold">Fecha y Hora Defunción *</label>
                                    <input type="datetime-local" name="fecha_fallecimiento" value="{{ old('fecha_fallecimiento') }}" min="2020-01-01T00:00" max="2035-12-31T23:59" required class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold">Lugar Fallecimiento *</label>
                                    <input type="text" name="lugar_fallecimiento" value="{{ old('lugar_fallecimiento') }}" required placeholder="Hospital / Domicilio" class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold">Comuna Defunción *</label>
                                    <input type="text" name="comuna_defuncion" value="{{ old('comuna_defuncion', 'Temuco') }}" required class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold">Escolaridad (Reg. Civil)</label>
                                    <select name="escolaridad" class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                        @foreach(['Media Completa', 'Básica Completa', 'Superior / Universitaria', 'Sin Escolaridad'] as $esc)
                                            <option value="{{ $esc }}" {{ old('escolaridad') === $esc ? 'selected' : '' }}>{{ $esc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold">Profesión / Oficio</label>
                                    <input type="text" name="profesion" value="{{ old('profesion') }}" placeholder="Ej: Agricultor, Dueña de casa" class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold">Estado Civil</label>
                                    <select name="estado_civil" class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                        @foreach(['Casado(a)', 'Viudo(a)', 'Soltero(a)', 'Divorciado(a)'] as $ec)
                                            <option value="{{ $ec }}" {{ old('estado_civil') === $ec ? 'selected' : '' }}>{{ $ec }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-3 flex flex-wrap items-center gap-4 pt-1">
                                    <label class="inline-flex items-center text-xs font-bold text-amber-900">
                                        <input type="checkbox" name="intervencion_sml" value="1" {{ old('intervencion_sml') ? 'checked' : '' }} class="rounded text-amber-600 mr-2">
                                        Intervención Servicio Médico Legal (SML)
                                    </label>
                                    <input type="text" name="numero_oficio_fiscalia" value="{{ old('numero_oficio_fiscalia') }}" placeholder="N° Oficio Fiscalía (obligatorio si marca SML)" class="rounded-xl border-slate-300 text-xs w-64">
                                </div>
                            </div>
                        </div>

                        <!-- 3. Urna, Flota y Agenda -->
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                            <h4 class="font-bold text-indigo-900 text-xs uppercase tracking-wider">3. Urna, Vehículo, Personal Activo y Horarios</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-semibold">Urna Seleccionada *</label>
                                    <select name="urna_id" required class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                        @foreach($urnasDisponibles as $u)
                                            <option value="{{ $u->id }}" {{ old('urna_id') == $u->id ? 'selected' : '' }}>
                                                {{ $u->modelo }} ({{ $u->color }}) — Salón: {{ $u->stock_salon }} / Taller: {{ $u->stock_taller }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold">Tipo de Servicio *</label>
                                    <select name="tipo_servicio" class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                        <option value="VELATORIO_Y_SEPULTACION" {{ old('tipo_servicio') === 'VELATORIO_Y_SEPULTACION' ? 'selected' : '' }}>Velatorio y Sepultación</option>
                                        <option value="CREMACION" {{ old('tipo_servicio') === 'CREMACION' ? 'selected' : '' }}>Cremación</option>
                                        <option value="TRASLADO_REGIONAL" {{ old('tipo_servicio') === 'TRASLADO_REGIONAL' ? 'selected' : '' }}>Traslado Regional</option>
                                        <option value="TRASLADO_NACIONAL" {{ old('tipo_servicio') === 'TRASLADO_NACIONAL' ? 'selected' : '' }}>Traslado Nacional</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold">Vehículo / Carroza</label>
                                    <select name="vehiculo_id" class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                        <option value="">-- Sin asignar aún --</option>
                                        @foreach($vehiculos as $v)
                                            <option value="{{ $v->id }}" {{ old('vehiculo_id') == $v->id ? 'selected' : '' }}>
                                                {{ $v->patente }} — {{ $v->marca_modelo }} ({{ \App\Support\Etiquetas::texto($v->estado_vehiculo) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="text-xs font-semibold">Chofer (Solo activos)</label>
                                        <select name="chofer_id" class="w-full mt-1 rounded-xl border-slate-300 text-xs">
                                            <option value="">-- Chofer --</option>
                                            @foreach($personalOperativo as $p)
                                                <option value="{{ $p->id }}" {{ old('chofer_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold">Ayudante (Solo activos)</label>
                                        <select name="ayudante_id" class="w-full mt-1 rounded-xl border-slate-300 text-xs">
                                            <option value="">-- Ayudante --</option>
                                            @foreach($personalOperativo as $p)
                                                <option value="{{ $p->id }}" {{ old('ayudante_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold">Lugar Velatorio *</label>
                                    <input type="text" name="lugar_velatorio" value="{{ old('lugar_velatorio', 'Salón Velatorio Calle Blanco, Temuco') }}" required class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold">Cementerio / Destino Final *</label>
                                    <input type="text" name="cementerio_destino" value="{{ old('cementerio_destino') }}" required placeholder="Cementerio General / Parque del Sendero" class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold">Inicio Velatorio *</label>
                                    <input type="datetime-local" name="fecha_inicio_velatorio" value="{{ old('fecha_inicio_velatorio') }}" min="2020-01-01T00:00" max="2035-12-31T23:59" required class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold">Fecha y Hora Cortejo / Funeral *</label>
                                    <input type="datetime-local" name="fecha_cortejo" value="{{ old('fecha_cortejo') }}" min="2020-01-01T00:00" max="2035-12-31T23:59" required class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold">Fecha y Hora Misa (Opcional)</label>
                                    <input type="datetime-local" name="fecha_misa" value="{{ old('fecha_misa') }}" min="2020-01-01T00:00" max="2035-12-31T23:59" class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold">Parroquia / Lugar Misa</label>
                                    <input type="text" name="lugar_misa" value="{{ old('lugar_misa') }}" placeholder="Ej: Parroquia San José" class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                </div>
                            </div>
                        </div>

                        <!-- 4. Convenio IPS / AFP -->
                        <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-200 grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="text-xs font-semibold text-indigo-950">Convenio Previsional</label>
                                <select name="convenio_id" class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                                    @foreach($convenios as $conv)
                                        <option value="{{ $conv->id }}" {{ old('convenio_id') == $conv->id ? 'selected' : '' }}>{{ $conv->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-indigo-950">Valor UF Hoy (15 UF AFP)</label>
                                <input type="number" name="valor_uf_actual" value="{{ old('valor_uf_actual', 38500) }}" min="30000" max="60000" class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-indigo-950">Valor Total Servicio (CLP) *</label>
                                <input type="number" name="valor_total_servicio" value="{{ old('valor_total_servicio', 1450000) }}" min="150000" max="50000000" required class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <button type="button" @click="openExp = false" class="px-4 py-2 bg-slate-200 rounded-xl font-semibold text-xs">Cancelar</button>
                            <button type="submit" class="px-5 py-2.5 bg-[#34287d] text-white rounded-xl font-bold text-xs">Crear Expediente y Descontar Urna</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Filtros -->
    <form method="GET" action="{{ route('expedientes.index') }}" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs sm:text-sm">
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Folio, RUT o nombre..." class="rounded-xl border-slate-300">

        <select name="estado" class="rounded-xl border-slate-300">
            <option value="">Todos los estados</option>
            <option value="ABIERTO" {{ request('estado') === 'ABIERTO' ? 'selected' : '' }}>Abierto</option>
            <option value="EN_PROCESO" {{ request('estado') === 'EN_PROCESO' ? 'selected' : '' }}>En Proceso</option>
            <option value="CERRADO" {{ request('estado') === 'CERRADO' ? 'selected' : '' }}>Cerrado</option>
            <option value="ANULADO_INACTIVO" {{ request('estado') === 'ANULADO_INACTIVO' ? 'selected' : '' }}>Anulado / Inactivo</option>
        </select>

        <select name="convenio_id" class="rounded-xl border-slate-300">
            <option value="">Todos los convenios</option>
            @foreach($convenios as $c)
                <option value="{{ $c->id }}" {{ request('convenio_id') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
            @endforeach
        </select>

        <label class="inline-flex items-center px-3 py-2 bg-rose-50 border border-rose-200 rounded-xl text-xs font-bold text-rose-800">
            <input type="checkbox" name="urgente_seremi" value="1" {{ request('urgente_seremi') ? 'checked' : '' }} class="rounded text-rose-600 mr-2">
            Solo Semáforo Rojo (&lt; 3 hrs)
        </label>

        <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-[#34287d] text-white font-semibold rounded-xl px-3 py-2">Buscar</button>
            <a href="{{ route('expedientes.index') }}" class="px-3 py-2 bg-slate-200 rounded-xl font-semibold">Limpiar</a>
        </div>
    </form>

    <!-- Tabla de Expedientes con Semáforo Documental -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
        <table class="w-full text-xs sm:text-sm text-left">
            <thead class="bg-[#231b56] text-white text-xs uppercase">
                <tr>
                    <th class="px-4 py-3.5">Folio / Estado</th>
                    <th class="px-4 py-3.5">Fallecido / Contratante</th>
                    <th class="px-4 py-3.5">Urna y Flota</th>
                    <th class="px-4 py-3.5">Semáforo Documental</th>
                    <th class="px-4 py-3.5">Saldo Pendiente</th>
                    <th class="px-4 py-3.5 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($expedientes as $exp)
                    @php
                        $esCritico = $exp->limite_legal_seremi_at && $exp->limite_legal_seremi_at->diffInHours(now(), false) >= -3 && in_array($exp->estado_expediente, ['ABIERTO', 'EN_PROCESO']);
                        $docsCount = $exp->documentos_count ?? 0;
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <span class="font-bold text-indigo-950 block">{{ $exp->folio }}</span>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-violet-100 text-violet-800">{{ \App\Support\Etiquetas::texto($exp->estado_expediente) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-bold text-slate-900">{{ $exp->fallecido->nombre_completo }}</p>
                            <p class="text-xs text-slate-500">Contratante: {{ $exp->cliente->nombre_completo }} ({{ $exp->cliente->rut }})</p>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            <p class="font-semibold">{{ $exp->urna->modelo }}</p>
                            <p class="text-slate-500">Carroza: {{ $exp->vehiculo->patente ?? 'Pendiente' }} | Cortejo: {{ $exp->fecha_cortejo->format('d/m H:i') }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            @if($esCritico)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 font-bold">
                                    🔴 Crítico ({{ $exp->limite_legal_seremi_at->format('H:i') }})
                                </span>
                            @elseif($docsCount < 2)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-bold">
                                    🟡 Pendiente ({{ $docsCount }} doc)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold">
                                    🟢 Completo ({{ $docsCount }} docs)
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-bold {{ $exp->saldo_pendiente > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                            ${{ number_format($exp->saldo_pendiente, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('expedientes.show', $exp) }}" class="px-3.5 py-1.5 bg-[#34287d] hover:bg-[#231b59] text-white text-xs font-bold rounded-xl">
                                    Gestionar / Editar
                                </a>
                                @if(Auth::user()->tieneRol(['admin', 'coordinador']))
                                    <form method="POST" action="{{ route('expedientes.destroy', $exp) }}" onsubmit="return confirm('¿Anular y archivar el expediente {{ $exp->folio }}? Esta acción quedará registrada en la bitácora.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white text-xs font-bold rounded-xl transition">
                                            Eliminar
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $expedientes->links() }}
    </div>

    <script>
        function formatearRutInput(input) {
            let v = input.value.replace(/[^0-9kK]/g, '').toUpperCase();
            if (v.length <= 1) {
                input.value = v;
                return;
            }
            const cuerpo = v.slice(0, -1).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            const dv = v.slice(-1);
            input.value = `${cuerpo}-${dv}`;
        }

        function validarRutModulo11(rutStr) {
            const limpio = rutStr.replace(/[^0-9kK]/g, '').toUpperCase();
            if (limpio.length < 8 || limpio.length > 9) return false;
            const cuerpo = limpio.slice(0, -1);
            const dv = limpio.slice(-1);
            if (!/^\d+$/.test(cuerpo) || /^(\d)\1+$/.test(cuerpo)) return false;

            let suma = 0, mul = 2;
            for (let i = cuerpo.length - 1; i >= 0; i--) {
                suma += parseInt(cuerpo[i], 10) * mul;
                mul = mul === 7 ? 2 : mul + 1;
            }
            const resto = 11 - (suma % 11);
            const esperado = resto === 11 ? '0' : (resto === 10 ? 'K' : String(resto));
            return dv === esperado;
        }

        function validarRutCampo(input, errorId, opcional = false) {
            const msg = document.getElementById(errorId);
            if (opcional && !input.value.trim()) {
                msg.classList.add('hidden');
                input.classList.remove('border-rose-500');
                return true;
            }
            if (!validarRutModulo11(input.value)) {
                msg.textContent = 'RUT inválido: revisa los dígitos o el dígito verificador.';
                msg.classList.remove('hidden');
                input.classList.add('border-rose-500');
                return false;
            }
            msg.classList.add('hidden');
            input.classList.remove('border-rose-500');
            return true;
        }

        async function buscarClientePorRut(rut) {
            if (!validarRutModulo11(rut)) return;
            const res = await fetch(`{{ route('clientes.buscar') }}?rut=${encodeURIComponent(rut)}`);
            const data = await res.json();
            const badge = document.getElementById('badge_historial_rut');
            if (data.encontrado && data.cliente) {
                document.getElementById('cliente_nombre').value = data.cliente.nombre_completo;
                document.getElementById('cliente_telefono').value = data.cliente.telefono;
                document.getElementById('cliente_direccion').value = data.cliente.direccion;
                document.getElementById('cliente_comuna').value = data.cliente.comuna;
                badge.textContent = `✓ Familia registrada (${data.total_servicios} servicio(s) previos)`;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    </script>
</x-app-layout>