<x-app-layout>
    <div x-data="{ openUpload: false }" class="space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl text-indigo-950">Repositorio de Documentos Digitales</h2>
                <p class="text-xs text-slate-500">Certificados de defunción, carnets, pases de sepultación, oficios SML y resoluciones Seremi</p>
            </div>
            @if(Auth::user()->tieneRol(['admin', 'coordinador', 'finanzas']))
                <button type="button" @click="openUpload = true" class="px-4 py-2.5 bg-[#34287d] hover:bg-indigo-900 text-white text-xs font-bold rounded-xl shadow">
                    + Subir Documento Digital
                </button>
            @endif
        </div>

        <!-- Filtros -->
        <form method="GET" action="{{ route('documentos.index') }}" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Archivo, folio o fallecido..." class="sm:col-span-4 rounded-xl border-slate-300">

            <select name="expediente_id" class="sm:col-span-3 rounded-xl border-slate-300">
                <option value="">Todos los expedientes</option>
                @foreach($expedientes as $e)
                    <option value="{{ $e->id }}" {{ request('expediente_id') == $e->id ? 'selected' : '' }}>{{ $e->folio }} — {{ $e->fallecido->nombre_completo }}</option>
                @endforeach
            </select>

            <select name="tipo_documento" class="sm:col-span-3 rounded-xl border-slate-300">
                <option value="">Todos los tipos</option>
                @foreach([
                    'CERTIFICADO_DEFUNCION' => 'Certificado de defunción',
                    'PASE_SEPULTACION' => 'Pase de sepultación',
                    'CARNET_CONTRATANTE' => 'Cédula contratante',
                    'CARNET_FALLECIDO' => 'Cédula fallecido',
                    'OFICIO_FISCALIA_SML' => 'Oficio Fiscalía / SML',
                    'RESOLUCION_SEREMI' => 'Resolución Seremi',
                    'CONTRATO_FIRMADO' => 'Contrato firmado',
                    'HOJA_MEDIDAS_CEMENTERIO' => 'Hoja de medidas',
                    'OTRO' => 'Otro documento',
                ] as $codigo => $etiqueta)
                    <option value="{{ $codigo }}" {{ request('tipo_documento') === $codigo ? 'selected' : '' }}>{{ $etiqueta }}</option>
                @endforeach
            </select>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-[#34287d] text-white font-bold rounded-xl">Filtrar</button>
                @if(request('buscar') || request('expediente_id') || request('tipo_documento'))
                    <a href="{{ route('documentos.index') }}" class="px-3 py-2 bg-slate-200 rounded-xl font-semibold flex items-center">Limpiar</a>
                @endif
            </div>
        </form>

        <!-- Tabla de Documentos -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-[#231b56] text-white uppercase">
                    <tr>
                        <th class="px-4 py-3">Expediente / Fallecido</th>
                        <th class="px-4 py-3">Tipo Documento</th>
                        <th class="px-4 py-3">Archivo</th>
                        <th class="px-4 py-3">Fecha / Usuario</th>
                        <th class="px-4 py-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($documentos as $doc)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <span class="font-bold text-indigo-950 block">{{ $doc->expediente->folio ?? 'N/A' }}</span>
                                <span class="text-slate-500">{{ $doc->expediente->fallecido->nombre_completo ?? '' }}</span>
                            </td>
                            <td class="px-4 py-3 font-semibold">{{ \App\Support\Etiquetas::texto($doc->tipo_documento) }}</td>
                            <td class="px-4 py-3 text-slate-700 font-medium">{{ $doc->nombre_archivo }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $doc->created_at->format('d/m/Y H:i') }} ({{ $doc->subidoPor->name ?? 'Sistema' }})</td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('documentos.ver', $doc) }}" target="_blank" class="px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-800 font-bold rounded-lg">
                                        Ver / Descargar ↗
                                    </a>
                                    @if(Auth::user()->tieneRol(['admin', 'coordinador']))
                                        <form method="POST" action="{{ route('documentos.destroy', $doc) }}" onsubmit="return confirm('¿Eliminar el documento {{ $doc->nombre_archivo }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white font-bold rounded-lg transition">
                                                Eliminar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">No se encontraron documentos digitales con los filtros aplicados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $documentos->links() }}
        </div>

        <!-- Modal Subida Directa de Documento -->
        <div x-show="openUpload" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex justify-between items-center border-b pb-3">
                    <h3 class="font-bold text-base text-indigo-950">Subir Documento al Repositorio</h3>
                    <button type="button" @click="openUpload = false" class="text-slate-400 font-bold">✕</button>
                </div>

                <form method="POST" action="{{ route('documentos.store') }}" enctype="multipart/form-data" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="font-semibold">Expediente Funerario *</label>
                        <select name="expediente_id" required class="w-full mt-1 rounded-xl border-slate-300">
                            <option value="">Seleccione un expediente...</option>
                            @foreach($expedientes as $e)
                                <option value="{{ $e->id }}">{{ $e->folio }} — {{ $e->fallecido->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="font-semibold">Tipo de Documento Legal *</label>
                        <select name="tipo_documento" required class="w-full mt-1 rounded-xl border-slate-300">
                            <option value="CERTIFICADO_DEFUNCION">Certificado de defunción</option>
                            <option value="PASE_SEPULTACION">Pase de sepultación</option>
                            <option value="CARNET_CONTRATANTE">Cédula contratante</option>
                            <option value="CARNET_FALLECIDO">Cédula fallecido</option>
                            <option value="OFICIO_FISCALIA_SML">Oficio Fiscalía / SML</option>
                            <option value="RESOLUCION_SEREMI">Resolución Seremi</option>
                            <option value="CONTRATO_FIRMADO">Contrato firmado</option>
                            <option value="HOJA_MEDIDAS_CEMENTERIO">Hoja de medidas</option>
                            <option value="OTRO">Otro documento</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-semibold">Archivo (PDF, JPG o PNG, máx. 5 MB) *</label>
                        <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png" required class="w-full mt-1 text-xs border border-slate-300 rounded-xl p-2 bg-slate-50">
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="openUpload = false" class="px-4 py-2 bg-slate-200 rounded-xl font-semibold">Cancelar</button>
                        <button type="submit" class="px-5 py-2 bg-[#34287d] text-white rounded-xl font-bold">Guardar Documento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>