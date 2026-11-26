<x-app-layout>
    <x-slot name="header">
        <div x-data="{ openEdit: false }" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl text-indigo-950">Expediente {{ $expediente->folio }} — {{ $expediente->fallecido->nombre_completo }}</h2>
                <p class="text-xs text-slate-500">Contratante: {{ $expediente->cliente->nombre_completo }} ({{ $expediente->cliente->rut }})</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if(Auth::user()->tieneRol(['admin', 'coordinador']))
                    <button @click="openEdit = true" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-sm">
                        ✎ Modificar Contrato
                    </button>
                @endif
                <a href="{{ route('expedientes.contrato-pdf', $expediente) }}" target="_blank" class="px-3.5 py-2 bg-[#34287d] text-white text-xs font-bold rounded-xl">
                    Contrato PDF ↗
                </a>
                <a href="{{ route('urnas.hoja-medidas', $expediente->urna) }}" target="_blank" class="px-3.5 py-2 bg-violet-700 text-white text-xs font-bold rounded-xl">
                    Hoja Medidas ↗
                </a>
                <a href="{{ route('publico.memorial', $expediente->folio) }}" target="_blank" class="px-3.5 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl">
                    Condolencias ↗
                </a>
            </div>

            <!-- Modal Modificar Contrato (Audita Antes -> Después) -->
            <div x-show="openEdit" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
                <div class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-2xl space-y-4 my-8">
                    <div class="flex justify-between items-center border-b pb-3">
                        <div>
                            <h3 class="font-bold text-lg text-indigo-950">Modificar Contrato {{ $expediente->folio }}</h3>
                            <p class="text-xs text-slate-500">Cualquier cambio de urna, recorrido, personal o monto quedará auditado (Antes → Después)</p>
                        </div>
                        <button @click="openEdit = false" class="text-slate-400 hover:text-slate-700 font-bold">✕</button>
                    </div>

                    <form method="POST" action="{{ route('expedientes.update', $expediente) }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-sm">
                        @csrf
                        @method('PUT')

                        <div class="sm:col-span-2 bg-amber-50 border border-amber-200 p-3 rounded-xl">
                            <label class="text-xs font-bold text-amber-900">Motivo de la Modificación (Obligatorio para Bitácora) *</label>
                            <input type="text" name="motivo_cambio" required placeholder="Ej: Familia solicita cambio de urna y ajuste horario de cortejo" class="w-full mt-1 rounded-xl border-amber-300 text-xs">
                        </div>

                        <div>
                            <label class="text-xs font-semibold">Urna Asignada (Ajusta stock automático)</label>
                            <select name="urna_id" class="w-full mt-1 rounded-xl border-slate-300 text-xs">
                                @foreach($urnasDisponibles as $u)
                                    <option value="{{ $u->id }}" {{ $expediente->urna_id == $u->id ? 'selected' : '' }}>
                                        {{ $u->modelo }} ({{ $u->color }}) — Stock: {{ $u->stock_salon + $u->stock_taller }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-semibold">Tipo de Servicio</label>
                            <select name="tipo_servicio" class="w-full mt-1 rounded-xl border-slate-300 text-xs">
                                @foreach(['VELATORIO_Y_SEPULTACION', 'CREMACION', 'TRASLADO_REGIONAL', 'TRASLADO_NACIONAL'] as $ts)
                                    <option value="{{ $ts }}" {{ $expediente->tipo_servicio === $ts ? 'selected' : '' }}>{{ \App\Support\Etiquetas::texto($ts) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-semibold">Vehículo / Carroza</label>
                            <select name="vehiculo_id" class="w-full mt-1 rounded-xl border-slate-300 text-xs">
                                <option value="">-- Sin asignar --</option>
                                @foreach($vehiculos as $v)
                                    <option value="{{ $v->id }}" {{ $expediente->vehiculo_id == $v->id ? 'selected' : '' }}>{{ $v->patente }} — {{ $v->marca_modelo }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-xs font-semibold">Chofer</label>
                                <select name="chofer_id" class="w-full mt-1 rounded-xl border-slate-300 text-xs">
                                    <option value="">-- Chofer --</option>
                                    @foreach($personalOperativo as $p)
                                        <option value="{{ $p->id }}" {{ $expediente->chofer_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-semibold">Ayudante</label>
                                <select name="ayudante_id" class="w-full mt-1 rounded-xl border-slate-300 text-xs">
                                    <option value="">-- Ayudante --</option>
                                    @foreach($personalOperativo as $p)
                                        <option value="{{ $p->id }}" {{ $expediente->ayudante_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-semibold">Lugar Velatorio</label>
                            <input type="text" name="lugar_velatorio" value="{{ $expediente->lugar_velatorio }}" required class="w-full mt-1 rounded-xl border-slate-300 text-xs">
                        </div>

                        <div>
                            <label class="text-xs font-semibold">Cementerio / Recorrido Destino</label>
                            <input type="text" name="cementerio_destino" value="{{ $expediente->cementerio_destino }}" required class="w-full mt-1 rounded-xl border-slate-300 text-xs">
                        </div>

                        <div>
                            <label class="text-xs font-semibold">Inicio Velatorio</label>
                            <input type="datetime-local" name="fecha_inicio_velatorio" value="{{ $expediente->fecha_inicio_velatorio->format('Y-m-d\TH:i') }}" required class="w-full mt-1 rounded-xl border-slate-300 text-xs">
                        </div>

                        <div>
                            <label class="text-xs font-semibold">Fecha y Hora Cortejo</label>
                            <input type="datetime-local" name="fecha_cortejo" value="{{ $expediente->fecha_cortejo->format('Y-m-d\TH:i') }}" required class="w-full mt-1 rounded-xl border-slate-300 text-xs">
                        </div>

                        <div>
                            <label class="text-xs font-semibold">Convenio Previsional</label>
                            <select name="convenio_id" class="w-full mt-1 rounded-xl border-slate-300 text-xs">
                                @foreach($convenios as $conv)
                                    <option value="{{ $conv->id }}" {{ $expediente->convenio_id == $conv->id ? 'selected' : '' }}>{{ $conv->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-semibold">Monto Total Servicio (CLP)</label>
                            <input type="number" name="valor_total_servicio" value="{{ $expediente->valor_total_servicio }}" required class="w-full mt-1 rounded-xl border-slate-300 text-xs font-bold">
                        </div>

                        <div class="sm:col-span-2 flex justify-end gap-2 pt-2">
                            <button type="button" @click="openEdit = false" class="px-4 py-2 bg-slate-200 rounded-xl text-xs font-semibold">Cancelar</button>
                            <button type="submit" class="px-5 py-2 bg-[#34287d] text-white rounded-xl text-xs font-bold">Guardar Cambios y Auditar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Ficha Operativa y Documental -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="font-bold text-indigo-950">Detalle Operativo, Flota y Registro Civil</h3>
                    <form method="POST" action="{{ route('expedientes.estado', $expediente) }}" class="flex items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <select name="estado_expediente" class="text-xs rounded-xl border-slate-300">
                            @foreach(['ABIERTO', 'EN_PROCESO', 'CERRADO', 'ANULADO_INACTIVO'] as $est)
                                <option value="{{ $est }}" {{ $expediente->estado_expediente === $est ? 'selected' : '' }}>{{ \App\Support\Etiquetas::texto($est) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white text-xs font-semibold rounded-xl">Actualizar</button>
                    </form>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                    <div>
                        <p class="text-xs text-slate-500">Urna Asignada</p>
                        <p class="font-bold">{{ $expediente->urna->modelo }} ({{ $expediente->urna->largo_cm }}×{{ $expediente->urna->ancho_cm }}×{{ $expediente->urna->alto_cm }} cm)</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Carroza y Personal Asignado</p>
                        <p class="font-bold">{{ $expediente->vehiculo->patente ?? 'Sin asignar' }} — Chofer: {{ $expediente->chofer->name ?? 'Pendiente' }} | Ayudante: {{ $expediente->ayudante->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Velatorio</p>
                        <p class="font-semibold">{{ $expediente->lugar_velatorio }} ({{ $expediente->fecha_inicio_velatorio->format('d/m/Y H:i') }})</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Cortejo y Cementerio</p>
                        <p class="font-semibold">{{ $expediente->cementerio_destino }} ({{ $expediente->fecha_cortejo->format('d/m/Y H:i') }})</p>
                    </div>
                </div>

                <!-- Carpeta Documental Legal -->
                <div class="pt-4 border-t border-slate-200 space-y-3">
                    <h4 class="font-bold text-indigo-950 text-sm">Carpeta Documental Legal (Semáforo Seremi / SML / Registro Civil)</h4>

                    <form method="POST" action="{{ route('expedientes.documentos.store', $expediente) }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-2 bg-slate-50 p-3 rounded-xl border">
                        @csrf
                        <select name="tipo_documento" class="rounded-xl border-slate-300 text-xs">
                            <option value="CERTIFICADO_DEFUNCION">Certificado de Defunción</option>
                            <option value="PASE_SEPULTACION">Pase de Sepultación</option>
                            <option value="CARNET_CONTRATANTE">Cédula Identidad Contratante</option>
                            <option value="CARNET_FALLECIDO">Cédula Identidad Fallecido</option>
                            <option value="OFICIO_FISCALIA_SML">Oficio Fiscalía / SML</option>
                            <option value="RESOLUCION_SEREMI">Resolución Sanitaria Seremi</option>
                            <option value="CONTRATO_FIRMADO">Contrato Firmado</option>
                        </select>
                        <input type="file" name="archivo" required class="text-xs flex-1">
                        <button type="submit" class="px-4 py-1.5 bg-[#34287d] text-white text-xs font-bold rounded-xl">Subir Archivo</button>
                    </form>

                    <div class="divide-y divide-slate-200 border rounded-xl">
                        @forelse($expediente->documentos as $doc)
                            <div class="p-3 flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-bold text-indigo-900">{{ \App\Support\Etiquetas::texto($doc->tipo_documento) }}</span>
                                    <p class="text-slate-500">{{ $doc->nombre_archivo }}</p>
                                </div>
                                <a href="{{ route('documentos.ver', $doc) }}" target="_blank" class="px-3 py-1 bg-indigo-50 text-indigo-700 font-bold rounded-lg hover:bg-indigo-100">
                                    Visualizar ↗
                                </a>
                            </div>
                        @empty
                            <p class="p-4 text-xs text-slate-500 text-center">No hay documentos adjuntos todavía.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Historial de Cambios del Contrato (Antes -> Después) -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 space-y-3">
                <h3 class="font-bold text-indigo-950 text-sm">Historial de Modificaciones del Contrato (Auditoría Antes → Después)</h3>
                <div class="divide-y divide-slate-100">
                    @forelse($historialCambios as $cambio)
                        <div class="py-3 text-xs space-y-1">
                            <div class="flex justify-between">
                                <span class="font-bold text-slate-800">{{ $cambio->usuario->name ?? 'Sistema inicial' }} — {{ $cambio->etiquetaAccion() }}</span>
                                <span class="text-slate-400">{{ $cambio->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($cambio->valores_anteriores_json && $cambio->valores_nuevos_json)
                                <p class="text-amber-800 font-semibold">Motivo: {{ $cambio->valores_nuevos_json['motivo'] ?? 'Actualización' }}</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-slate-50 p-2.5 rounded-lg border text-[11px]">
                                    <div>
                                        <span class="font-bold text-rose-700 block">Antes:</span>
                                        <span>Urna: {{ $cambio->valores_anteriores_json['urna'] ?? '' }} | Monto: {{ $cambio->valores_anteriores_json['monto_total'] ?? '' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-bold text-emerald-700 block">Después:</span>
                                        <span>Urna: {{ $cambio->valores_nuevos_json['urna'] ?? '' }} | Monto: {{ $cambio->valores_nuevos_json['monto_total'] ?? '' }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-slate-500">Este expediente conserva sus condiciones originales de apertura.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Resumen Financiero -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80 space-y-4 h-fit">
            <h3 class="font-bold text-indigo-950 border-b pb-3">Estado Financiero</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Convenio:</span>
                    <span class="font-semibold text-right">{{ $expediente->convenio->nombre ?? 'Particular' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Valor Total Servicio:</span>
                    <span class="font-bold">${{ number_format($expediente->valor_total_servicio, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-emerald-700">
                    <span>Cobertura IPS / AFP:</span>
                    <span class="font-bold">- ${{ number_format($expediente->monto_cobertura_convenio, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t text-base font-extrabold text-indigo-950">
                    <span>Saldo Pendiente:</span>
                    <span>${{ number_format($expediente->saldo_pendiente, 0, ',', '.') }}</span>
                </div>
            </div>

            <a href="{{ route('finanzas.index') }}" class="block w-full text-center py-2.5 bg-violet-700 hover:bg-violet-800 text-white text-xs font-bold rounded-xl">
                Ir a Caja / Registrar Abonos →
            </a>
        </div>
    </div>
</x-app-layout>