<x-app-layout>
    <x-slot name="header">
        <div x-data="{ openModal: false }" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl text-indigo-950">Inventario y Catálogo Visual de Urnas</h2>
                <p class="text-xs text-slate-500">
                    Salón Calle Blanco: <strong>{{ $totalSalon }}/24</strong> | Bodega Taller: <strong>{{ $totalTaller }}</strong> | Total Disponible: <strong>{{ $totalSalon + $totalTaller }}</strong>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="inline-flex rounded-xl bg-slate-100 p-1 text-xs">
                    <a href="{{ route('urnas.index', array_merge(request()->query(), ['vista' => 'inventario'])) }}"
                       class="px-3 py-1.5 rounded-lg font-bold {{ $vista !== 'catalogo' ? 'bg-[#34287d] text-white' : 'text-slate-600' }}">
                        Tabla Inventario
                    </a>
                    <a href="{{ route('urnas.index', array_merge(request()->query(), ['vista' => 'catalogo'])) }}"
                       class="px-3 py-1.5 rounded-lg font-bold {{ $vista === 'catalogo' ? 'bg-[#34287d] text-white' : 'text-slate-600' }}">
                        Catálogo Sala (Sin Precios)
                    </a>
                </div>

                @if(Auth::user()->tieneRol(['admin', 'coordinador', 'bodega']))
                    <button @click="openModal = true" class="px-4 py-2 bg-[#34287d] hover:bg-[#231b59] text-white text-xs font-bold rounded-xl shadow">
                        + Registrar Urna
                    </button>
                @endif
            </div>

            <!-- Modal Registro Nueva Urna -->
            <div x-show="openModal" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
                <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl space-y-4 my-8">
                    <div class="flex justify-between items-center border-b pb-3">
                        <h3 class="font-bold text-lg text-indigo-950">Registrar Nuevo Modelo de Urna</h3>
                        <button @click="openModal = false" class="text-slate-400 hover:text-slate-700 font-bold">✕</button>
                    </div>
                    <form method="POST" action="{{ route('urnas.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        @csrf
                        <div>
                            <label class="text-xs font-semibold">Código SKU *</label>
                            <input type="text" name="codigo_sku" required placeholder="URN-020" class="w-full mt-1 rounded-xl border-slate-300 uppercase">
                        </div>
                        <div>
                            <label class="text-xs font-semibold">Modelo *</label>
                            <input type="text" name="modelo" required placeholder="Ej: Roble Tallado Paloma" class="w-full mt-1 rounded-xl border-slate-300">
                        </div>
                        <div>
                            <label class="text-xs font-semibold">Categoría Tamaño *</label>
                            <select name="categoria_tamano" class="w-full mt-1 rounded-xl border-slate-300">
                                <option value="ESTANDAR">Estándar Adulto</option>
                                <option value="NINO_SEREMI">Niño / Angelito (Seremi)</option>
                                <option value="ESPECIAL_XL">Especial XL</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold">Acabado / Tallado *</label>
                            <select name="acabado_tallado" class="w-full mt-1 rounded-xl border-slate-300">
                                <option value="LISA">Lisa</option>
                                <option value="PALOMA_BIBLIA">Paloma Biblia</option>
                                <option value="MARQUETEADA">Marqueteada</option>
                                <option value="PERSONALIZADA">Personalizada</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold">Color *</label>
                            <input type="text" name="color" required placeholder="Caoba / Nogal / Blanco" class="w-full mt-1 rounded-xl border-slate-300">
                        </div>
                        <div>
                            <label class="text-xs font-semibold">Proveedor Origen *</label>
                            <select name="proveedor_origen" class="w-full mt-1 rounded-xl border-slate-300">
                                <option value="SAN_JAVIER">Fábrica San Javier</option>
                                <option value="SANTIAGO">Fábrica Santiago (Lote 24)</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="text-xs font-semibold">Largo cm *</label>
                                <input type="number" name="largo_cm" value="195" required class="w-full mt-1 rounded-xl border-slate-300">
                            </div>
                            <div>
                                <label class="text-xs font-semibold">Ancho cm *</label>
                                <input type="number" name="ancho_cm" value="62" required class="w-full mt-1 rounded-xl border-slate-300">
                            </div>
                            <div>
                                <label class="text-xs font-semibold">Alto cm *</label>
                                <input type="number" name="alto_cm" value="55" required class="w-full mt-1 rounded-xl border-slate-300">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-xs font-semibold">Precio Compra (CLP)</label>
                                <input type="number" name="precio_compra" value="280000" class="w-full mt-1 rounded-xl border-slate-300">
                            </div>
                            <div>
                                <label class="text-xs font-semibold">Precio Venta (CLP) *</label>
                                <input type="number" name="precio_venta" value="690000" required class="w-full mt-1 rounded-xl border-slate-300">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-xs font-semibold">Stock Salón *</label>
                                <input type="number" name="stock_salon" value="1" min="0" max="24" required class="w-full mt-1 rounded-xl border-slate-300">
                            </div>
                            <div>
                                <label class="text-xs font-semibold">Stock Taller *</label>
                                <input type="number" name="stock_taller" value="2" min="0" required class="w-full mt-1 rounded-xl border-slate-300">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-semibold">Foto Principal (Portada)</label>
                            <input type="file" name="foto" accept="image/*" class="w-full mt-1 text-xs">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold">Fotos Adicionales / Otros Ángulos (Puedes seleccionar varias)</label>
                            <input type="file" name="fotos_galeria[]" accept="image/*" multiple class="w-full mt-1 text-xs">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold">Descripción para Clientes (WhatsApp)</label>
                            <textarea name="descripcion" rows="2" placeholder="Ej: Madera noble barnizada, interior acolchado en raso blanco y manillas metálicas." class="w-full mt-1 rounded-xl border-slate-300 text-xs"></textarea>
                        </div>
                        <div class="sm:col-span-2 bg-amber-50 p-3 rounded-xl border border-amber-200 space-y-2">
                            <label class="inline-flex items-center text-xs font-bold text-amber-900">
                                <input type="checkbox" name="reservada_en_vida" value="1" class="rounded text-amber-600 mr-2">
                                Bloquear como Reserva en Vida (Uso exclusivo titular)
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" name="rut_reserva_vida" placeholder="RUT Titular (Ej: 12.345.678-9)" class="rounded-xl border-slate-300 text-xs">
                                <input type="text" name="nombre_reserva_vida" placeholder="Nombre Completo Titular" class="rounded-xl border-slate-300 text-xs">
                            </div>
                        </div>
                        <div class="sm:col-span-2 flex justify-end gap-2 pt-2">
                            <button type="button" @click="openModal = false" class="px-4 py-2 bg-slate-200 rounded-xl font-semibold text-xs">Cancelar</button>
                            <button type="submit" class="px-5 py-2 bg-[#34287d] text-white rounded-xl font-bold text-xs">Guardar Urna</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Notificación Flotante al Copiar Link WhatsApp -->
    <div id="toast-wsp" class="hidden fixed bottom-6 right-6 z-50 bg-emerald-700 text-white px-4 py-3 rounded-2xl shadow-2xl text-xs font-bold">
        ✅ Enlace de urna (con precio y galería) copiado para WhatsApp
    </div>

    <!-- Banner Alerta Stock Bajo -->
    @if($alertasStock->count() > 0)
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-2xl shadow-sm flex flex-wrap items-center justify-between gap-2 text-xs">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-bold text-amber-950">⚠️ Alertas de Stock Bajo:</span>
                @foreach($alertasStock as $al)
                    <span class="px-2.5 py-1 rounded-full bg-amber-200/80 text-amber-950 font-bold">
                        {{ $al->modelo }}: quedan {{ $al->stock_salon + $al->stock_taller }} unidad(es)
                    </span>
                @endforeach
            </div>
            <a href="{{ route('bodega.index') }}" class="font-bold text-indigo-800 hover:underline">Reponer en Bodega →</a>
        </div>
    @endif

    <!-- Filtros -->
    <form method="GET" action="{{ route('urnas.index') }}" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs sm:text-sm">
        <input type="hidden" name="vista" value="{{ $vista }}">
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Modelo, SKU o color..." class="rounded-xl border-slate-300">

        <select name="categoria_tamano" class="rounded-xl border-slate-300">
            <option value="">Todas las categorías</option>
            <option value="ESTANDAR" {{ request('categoria_tamano') === 'ESTANDAR' ? 'selected' : '' }}>Estándar Adulto</option>
            <option value="NINO_SEREMI" {{ request('categoria_tamano') === 'NINO_SEREMI' ? 'selected' : '' }}>Niño / Angelito (Seremi)</option>
            <option value="ESPECIAL_XL" {{ request('categoria_tamano') === 'ESPECIAL_XL' ? 'selected' : '' }}>Especial XL</option>
        </select>

        <select name="acabado_tallado" class="rounded-xl border-slate-300">
            <option value="">Todos los tallados</option>
            <option value="LISA" {{ request('acabado_tallado') === 'LISA' ? 'selected' : '' }}>Lisa</option>
            <option value="PALOMA_BIBLIA" {{ request('acabado_tallado') === 'PALOMA_BIBLIA' ? 'selected' : '' }}>Paloma Biblia</option>
            <option value="MARQUETEADA" {{ request('acabado_tallado') === 'MARQUETEADA' ? 'selected' : '' }}>Marqueteada</option>
            <option value="PERSONALIZADA" {{ request('acabado_tallado') === 'PERSONALIZADA' ? 'selected' : '' }}>Personalizada</option>
        </select>

        <select name="ubicacion" class="rounded-xl border-slate-300">
            <option value="">Todas las ubicaciones</option>
            <option value="salon" {{ request('ubicacion') === 'salon' ? 'selected' : '' }}>En Salón Calle Blanco</option>
            <option value="taller" {{ request('ubicacion') === 'taller' ? 'selected' : '' }}>En Bodega Taller</option>
            <option value="reservada" {{ request('ubicacion') === 'reservada' ? 'selected' : '' }}>Reservadas en Vida</option>
        </select>

        <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-[#34287d] text-white font-semibold rounded-xl px-3 py-2">Filtrar</button>
            <a href="{{ route('urnas.index', ['vista' => $vista]) }}" class="px-3 py-2 bg-slate-200 rounded-xl font-semibold">Limpiar</a>
        </div>
    </form>

    @if($vista === 'catalogo')
        <!-- VISTA GALERÍA VISUAL SIN PRECIOS (Para mostrar en sala o tablet a la familia) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($urnas as $urna)
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-200/80 flex flex-col justify-between">
                    <div class="h-48 bg-slate-100 relative">
                        <img src="{{ $urna->todas_las_fotos[0] }}" alt="{{ $urna->modelo }}" class="w-full h-full object-cover">
                        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-indigo-950/80 text-white text-[10px] font-bold">
                            {{ $urna->codigo_sku }} • {{ count($urna->todas_las_fotos) }} foto(s)
                        </span>
                        @if(($urna->stock_salon + $urna->stock_taller) > 0)
                            <span class="absolute bottom-3 right-3 px-2.5 py-1 rounded-full bg-emerald-600 text-white text-[10px] font-bold">
                                Disponible ({{ $urna->stock_salon + $urna->stock_taller }})
                            </span>
                        @endif
                    </div>
                    <div class="p-4 space-y-2">
                        <h3 class="font-bold text-base text-indigo-950">{{ $urna->modelo }}</h3>
                        <p class="text-xs text-slate-600">
                            <strong>Color:</strong> {{ $urna->color }} &nbsp;|&nbsp; <strong>Tallado:</strong> {{ \App\Support\Etiquetas::texto($urna->acabado_tallado) }}
                        </p>
                        <p class="text-[11px] text-slate-500">
                            Medidas exteriores: {{ $urna->largo_cm }} × {{ $urna->ancho_cm }} × {{ $urna->alto_cm }} cm
                        </p>
                        <div class="pt-2 flex justify-between items-center border-t border-slate-100 gap-2">
                            <button type="button" onclick="copiarLinkUrna('{{ route('publico.urna', $urna->codigo_sku) }}')"
                                    class="text-xs font-bold text-emerald-700 hover:underline">
                                📲 Copiar Link WhatsApp
                            </button>
                            <a href="{{ route('urnas.hoja-medidas', $urna) }}" target="_blank" class="text-xs font-bold text-indigo-800 hover:underline">
                                Medidas PDF ↗
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- VISTA TABLA DE INVENTARIO COMPLETA -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
            <table class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-[#231b56] text-white text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">Urna / Medidas</th>
                        <th class="px-4 py-3">Proveedor / Tallado</th>
                        <th class="px-4 py-3">Stock Salón / Taller</th>
                        <th class="px-4 py-3">Compra / Venta</th>
                        <th class="px-4 py-3 text-right">Acciones Rápidas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($urnas as $urna)
                        <tr x-data="{ editUrna: false }" class="hover:bg-slate-50">
                            <td class="px-4 py-3 flex items-center gap-3">
                                <div class="relative shrink-0">
                                    <img src="{{ $urna->todas_las_fotos[0] }}" alt="{{ $urna->modelo }}" class="w-12 h-12 rounded-xl object-cover border">
                                    <span class="absolute -bottom-1 -right-1 px-1.5 py-0.2 rounded-full bg-indigo-950 text-white text-[9px] font-bold">
                                        {{ count($urna->todas_las_fotos) }}📷
                                    </span>
                                </div>
                                <div>
                                    <span class="font-bold text-indigo-950 block">{{ $urna->modelo }}</span>
                                    <span class="text-xs text-slate-500">{{ $urna->codigo_sku }} | {{ $urna->color }} | {{ $urna->largo_cm }}×{{ $urna->ancho_cm }}×{{ $urna->alto_cm }} cm</span>
                                    @if($urna->reservada_en_vida)
                                        <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-900 rounded">
                                            RESERVA EN VIDA: {{ $urna->nombre_reserva_vida }} ({{ $urna->rut_reserva_vida }})
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <span class="font-semibold block">{{ \App\Support\Etiquetas::texto($urna->proveedor_origen) }}</span>
                                <span class="text-slate-500">{{ \App\Support\Etiquetas::texto($urna->acabado_tallado) }} ({{ \App\Support\Etiquetas::texto($urna->categoria_tamano) }})</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Salón: {{ $urna->stock_salon }}</span>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 ml-1">Taller: {{ $urna->stock_taller }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs whitespace-nowrap">
                                <p class="text-slate-500">Compra: ${{ number_format($urna->precio_compra ?? 0, 0, ',', '.') }}</p>
                                <p class="font-bold text-slate-900">Venta: ${{ number_format($urna->precio_venta, 0, ',', '.') }}</p>
                            </td>
                            <td class="px-4 py-3 text-right space-y-1.5">
                                <div class="inline-flex flex-wrap justify-end gap-1.5">
                                    <button type="button"
                                            onclick="copiarLinkUrna('{{ route('publico.urna', $urna->codigo_sku) }}')"
                                            class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white rounded-lg text-xs font-bold transition">
                                        📲 Link WhatsApp
                                    </button>

                                    <button type="button" @click="editUrna = true"
                                            class="px-2.5 py-1 bg-amber-50 hover:bg-amber-500 text-amber-800 hover:text-white rounded-lg text-xs font-bold transition">
                                        ✏️ Editar
                                    </button>

                                    <a href="{{ route('urnas.hoja-medidas', $urna) }}" target="_blank"
                                       class="px-2.5 py-1 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg text-xs font-bold">
                                        PDF ↗
                                    </a>

                                    @if($urna->stock_taller > 0)
                                        <form method="POST" action="{{ route('urnas.traspasar', $urna) }}" class="inline-flex">
                                            @csrf
                                            <input type="hidden" name="cantidad" value="1">
                                            <button type="submit" class="px-2.5 py-1 bg-violet-700 hover:bg-violet-800 text-white rounded-lg text-xs font-semibold">
                                                → Salón
                                            </button>
                                        </form>
                                    @endif

                                    @if(Auth::user()->tieneRol(['admin', 'coordinador']))
                                        <form method="POST" action="{{ route('urnas.destroy', $urna) }}" class="inline-flex" onsubmit="return confirm('¿Dar de baja la urna {{ $urna->codigo_sku }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white rounded-lg text-xs font-bold transition">
                                                Eliminar
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                <!-- Modal Edición de Urna + Galería Multi-Ángulo -->
                                <div x-show="editUrna" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto text-left">
                                    <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl space-y-4 my-8">
                                        <div class="flex justify-between items-center border-b pb-3">
                                            <h3 class="font-bold text-base text-indigo-950">Editar Urna — {{ $urna->modelo }} ({{ $urna->codigo_sku }})</h3>
                                            <button type="button" @click="editUrna = false" class="text-slate-400 hover:text-slate-700 font-bold">✕</button>
                                        </div>

                                        <form method="POST" action="{{ route('urnas.update', $urna) }}" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                            @csrf
                                            @method('PUT')

                                            <div>
                                                <label class="font-semibold">Código SKU *</label>
                                                <input type="text" name="codigo_sku" value="{{ $urna->codigo_sku }}" required class="w-full mt-1 rounded-xl border-slate-300 uppercase">
                                            </div>
                                            <div>
                                                <label class="font-semibold">Modelo *</label>
                                                <input type="text" name="modelo" value="{{ $urna->modelo }}" required class="w-full mt-1 rounded-xl border-slate-300">
                                            </div>
                                            <div>
                                                <label class="font-semibold">Categoría Tamaño *</label>
                                                <select name="categoria_tamano" class="w-full mt-1 rounded-xl border-slate-300">
                                                    @foreach(['ESTANDAR' => 'Estándar Adulto', 'NINO_SEREMI' => 'Niño / Angelito (Seremi)', 'ESPECIAL_XL' => 'Especial XL'] as $k => $v)
                                                        <option value="{{ $k }}" {{ $urna->categoria_tamano === $k ? 'selected' : '' }}>{{ $v }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="font-semibold">Acabado / Tallado *</label>
                                                <select name="acabado_tallado" class="w-full mt-1 rounded-xl border-slate-300">
                                                    @foreach(['LISA' => 'Lisa', 'PALOMA_BIBLIA' => 'Paloma Biblia', 'MARQUETEADA' => 'Marqueteada', 'PERSONALIZADA' => 'Personalizada'] as $k => $v)
                                                        <option value="{{ $k }}" {{ $urna->acabado_tallado === $k ? 'selected' : '' }}>{{ $v }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="font-semibold">Color *</label>
                                                <input type="text" name="color" value="{{ $urna->color }}" required class="w-full mt-1 rounded-xl border-slate-300">
                                            </div>
                                            <div>
                                                <label class="font-semibold">Proveedor Origen *</label>
                                                <select name="proveedor_origen" class="w-full mt-1 rounded-xl border-slate-300">
                                                    <option value="SAN_JAVIER" {{ $urna->proveedor_origen === 'SAN_JAVIER' ? 'selected' : '' }}>Fábrica San Javier</option>
                                                    <option value="SANTIAGO" {{ $urna->proveedor_origen === 'SANTIAGO' ? 'selected' : '' }}>Fábrica Santiago (Lote 24)</option>
                                                </select>
                                            </div>
                                            <div class="grid grid-cols-3 gap-2">
                                                <div>
                                                    <label class="font-semibold">Largo cm *</label>
                                                    <input type="number" name="largo_cm" value="{{ $urna->largo_cm }}" required class="w-full mt-1 rounded-xl border-slate-300">
                                                </div>
                                                <div>
                                                    <label class="font-semibold">Ancho cm *</label>
                                                    <input type="number" name="ancho_cm" value="{{ $urna->ancho_cm }}" required class="w-full mt-1 rounded-xl border-slate-300">
                                                </div>
                                                <div>
                                                    <label class="font-semibold">Alto cm *</label>
                                                    <input type="number" name="alto_cm" value="{{ $urna->alto_cm }}" required class="w-full mt-1 rounded-xl border-slate-300">
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="font-semibold">Precio Compra</label>
                                                    <input type="number" name="precio_compra" value="{{ $urna->precio_compra ?? 0 }}" class="w-full mt-1 rounded-xl border-slate-300">
                                                </div>
                                                <div>
                                                    <label class="font-semibold">Precio Venta *</label>
                                                    <input type="number" name="precio_venta" value="{{ $urna->precio_venta }}" required class="w-full mt-1 rounded-xl border-slate-300">
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="font-semibold">Stock Salón *</label>
                                                    <input type="number" name="stock_salon" value="{{ $urna->stock_salon }}" min="0" max="24" required class="w-full mt-1 rounded-xl border-slate-300">
                                                </div>
                                                <div>
                                                    <label class="font-semibold">Stock Taller *</label>
                                                    <input type="number" name="stock_taller" value="{{ $urna->stock_taller }}" min="0" required class="w-full mt-1 rounded-xl border-slate-300">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="font-semibold">Cambiar Foto Principal</label>
                                                <input type="file" name="foto" accept="image/*" class="w-full mt-1">
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="font-semibold">Agregar Más Fotos / Ángulos (Puedes seleccionar varias)</label>
                                                <input type="file" name="fotos_galeria[]" accept="image/*" multiple class="w-full mt-1">
                                            </div>

                                            <!-- Fotos Actuales con opción de quitar -->
                                            @if($urna->foto_s3_url || !empty($urna->galeria_fotos))
                                                <div class="sm:col-span-2 bg-slate-50 p-3 rounded-xl border border-slate-200 space-y-2">
                                                    <p class="font-bold text-slate-700">Fotos actuales (marca las que desees eliminar):</p>
                                                    <div class="flex flex-wrap gap-3">
                                                        @foreach($urna->todas_las_fotos as $fotoItem)
                                                            @if(str_starts_with($fotoItem, '/storage/'))
                                                                <label class="relative block w-20 cursor-pointer">
                                                                    <img src="{{ $fotoItem }}" class="w-20 h-16 object-cover rounded-lg border">
                                                                    <span class="mt-1 flex items-center gap-1 text-[10px] text-rose-700 font-bold">
                                                                        <input type="checkbox" name="eliminar_fotos[]" value="{{ $fotoItem }}" class="rounded text-rose-600">
                                                                        Quitar
                                                                    </span>
                                                                </label>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="sm:col-span-2">
                                                <label class="font-semibold">Descripción para Clientes (WhatsApp)</label>
                                                <textarea name="descripcion" rows="2" class="w-full mt-1 rounded-xl border-slate-300">{{ $urna->descripcion }}</textarea>
                                            </div>

                                            <div class="sm:col-span-2 bg-amber-50 p-3 rounded-xl border border-amber-200 space-y-2">
                                                <label class="inline-flex items-center font-bold text-amber-900">
                                                    <input type="checkbox" name="reservada_en_vida" value="1" {{ $urna->reservada_en_vida ? 'checked' : '' }} class="rounded text-amber-600 mr-2">
                                                    Bloquear como Reserva en Vida
                                                </label>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <input type="text" name="rut_reserva_vida" value="{{ $urna->rut_reserva_vida }}" placeholder="RUT Titular" class="rounded-xl border-slate-300">
                                                    <input type="text" name="nombre_reserva_vida" value="{{ $urna->nombre_reserva_vida }}" placeholder="Nombre Titular" class="rounded-xl border-slate-300">
                                                </div>
                                            </div>

                                            <div class="sm:col-span-2 flex justify-end gap-2 pt-2">
                                                <button type="button" @click="editUrna = false" class="px-4 py-2 bg-slate-200 rounded-xl font-semibold">Cancelar</button>
                                                <button type="submit" class="px-5 py-2 bg-[#34287d] text-white rounded-xl font-bold">Guardar Cambios</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">No se encontraron urnas con los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <div class="mt-4">
        {{ $urnas->links() }}
    </div>

    <script>
        function copiarLinkUrna(url) {
            navigator.clipboard.writeText(url);
            const toast = document.getElementById('toast-wsp');
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3200);
        }
    </script>
</x-app-layout>