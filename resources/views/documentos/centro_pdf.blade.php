<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-indigo-950">Centro de Generación de Documentos PDF Oficiales</h2>
            <p class="text-xs text-slate-500">Emisión inmediata de Contratos, Hojas de Medidas para Cementerio, Comprobantes, Convenios y Libro de Condolencias</p>
        </div>
    </x-slot>

    <!-- Buscador Rápido Unificado -->
    <form method="GET" action="{{ route('pdf.centro') }}" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 flex flex-wrap items-center gap-3 text-xs">
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por folio, fallecido, contratante, RUT, urna o comprobante..." class="flex-1 min-w-[240px] rounded-xl border-slate-300">
        <button type="submit" class="px-4 py-2 bg-[#34287d] text-white font-bold rounded-xl">Buscar</button>
        @if(request('buscar'))
            <a href="{{ route('pdf.centro') }}" class="px-3.5 py-2 bg-slate-200 rounded-xl font-semibold">Limpiar</a>
        @endif
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <!-- 1. Contratos, Convenios y Condolencias por Expediente -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 flex flex-col justify-between space-y-3">
            <div class="space-y-3">
                <h3 class="font-bold text-sm text-indigo-950">📑 Documentos por Expediente Funerario</h3>
                <div class="divide-y divide-slate-100">
                    @forelse($expedientes as $exp)
                        <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                            <div>
                                <span class="font-bold text-indigo-950 block">{{ $exp->folio }} — {{ $exp->fallecido->nombre_completo }}</span>
                                <span class="text-slate-500">Contratante: {{ $exp->cliente->nombre_completo }}</span>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                <a href="{{ route('expedientes.contrato-pdf', $exp) }}" target="_blank" class="px-2.5 py-1 bg-[#34287d] text-white rounded-lg font-bold">Contrato PDF</a>
                                <a href="{{ route('convenios.pdf', $exp) }}" target="_blank" class="px-2.5 py-1 bg-violet-100 text-violet-900 rounded-lg font-bold">Convenio PDF</a>
                                <a href="{{ route('condolencias.pdf', $exp) }}" target="_blank" class="px-2.5 py-1 bg-emerald-100 text-emerald-900 rounded-lg font-bold">Condolencias PDF</a>
                            </div>
                        </div>
                    @empty
                        <p class="py-6 text-center text-xs text-slate-500">No se encontraron expedientes.</p>
                    @endforelse
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100">
                {{ $expedientes->links() }}
            </div>
        </div>

        <!-- 2. Hojas de Medidas Cementerio y Comprobantes de Pago -->
        <div class="space-y-5">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 space-y-3">
                <h3 class="font-bold text-sm text-indigo-950">📏 Hojas de Medidas Técnicas para Cementerio (Por Urna)</h3>
                <div class="divide-y divide-slate-100 max-h-60 overflow-y-auto">
                    @forelse($urnas as $u)
                        <div class="py-2.5 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-slate-800 block">{{ $u->modelo }} ({{ $u->codigo_sku }})</span>
                                <span class="text-slate-500">{{ $u->largo_cm }} × {{ $u->ancho_cm }} × {{ $u->alto_cm }} cm</span>
                            </div>
                            <a href="{{ route('urnas.hoja-medidas', $u) }}" target="_blank" class="px-3 py-1 bg-indigo-50 text-indigo-800 font-bold rounded-lg">
                                Hoja Medidas PDF ↗
                            </a>
                        </div>
                    @empty
                        <p class="py-4 text-center text-xs text-slate-500">No hay urnas que coincidan con la búsqueda.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 space-y-3">
                <h3 class="font-bold text-sm text-indigo-950">🧾 Comprobantes de Pago Foliados</h3>
                <div class="divide-y divide-slate-100">
                    @forelse($abonos as $ab)
                        <div class="py-2.5 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-slate-800 block">{{ $ab->folio_comprobante }} — ${{ number_format($ab->monto_abonado, 0, ',', '.') }}</span>
                                <span class="text-slate-500">{{ $ab->expediente->folio }} ({{ $ab->fecha_pago->format('d/m/Y') }})</span>
                            </div>
                            <a href="{{ route('finanzas.comprobante-pdf', $ab) }}" target="_blank" class="px-3 py-1 bg-emerald-50 text-emerald-800 font-bold rounded-lg">
                                Comprobante PDF ↗
                            </a>
                        </div>
                    @empty
                        <p class="py-4 text-center text-xs text-slate-500">No se encontraron comprobantes emitidos.</p>
                    @endforelse
                </div>

                <div class="pt-2 border-t border-slate-100">
                    {{ $abonos->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>