<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $urna->modelo }} — Funeraria Cristo Rey</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .font-serif-display { font-family: 'Playfair Display', Georgia, serif; }
        .font-sans-ui { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="font-sans-ui bg-slate-100 text-slate-800 antialiased"
      x-data="{ fotos: {{ json_encode($urna->todas_las_fotos) }}, activa: 0 }">

    <!-- Barra Superior -->
    <header class="bg-[#231b56] text-white py-4 px-5 shadow">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <span class="text-xl">✝</span>
                <div class="font-serif-display leading-tight">
                    <span class="block text-xs text-violet-200">Funeraria</span>
                    <span class="block text-base font-bold">Cristo Rey</span>
                </div>
            </a>
            <a href="{{ route('home') }}" class="text-xs font-semibold text-violet-200 hover:text-white">
                ← Ver Portal General
            </a>
        </div>
    </header>

    <!-- Ficha de Urna con Galería Multi-Ángulo y Precio -->
    <main class="max-w-5xl mx-auto p-4 sm:p-8">
        <div class="bg-white rounded-3xl shadow-xl border border-slate-200/80 overflow-hidden grid grid-cols-1 lg:grid-cols-12">

            <!-- Galería de Múltiples Ángulos (7 columnas) -->
            <div class="lg:col-span-7 bg-slate-50 p-4 sm:p-6 flex flex-col justify-between space-y-4">
                <div class="relative aspect-[4/3] rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-inner">
                    <img :src="fotos[activa]" alt="{{ $urna->modelo }}" class="w-full h-full object-cover transition-all duration-300">
                    <span class="absolute top-3 left-3 px-3 py-1 rounded-full bg-indigo-950/85 text-white text-xs font-bold">
                        {{ $urna->codigo_sku }}
                    </span>
                    <template x-if="fotos.length > 1">
                        <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-full bg-black/60 text-white text-[11px] font-semibold">
                            Foto <span x-text="activa + 1"></span> de <span x-text="fotos.length"></span>
                        </div>
                    </template>
                </div>

                <!-- Miniaturas de Ángulos -->
                <div class="flex gap-2.5 overflow-x-auto pb-1">
                    <template x-for="(img, idx) in fotos" :key="idx">
                        <button type="button" @click="activa = idx"
                                :class="activa === idx ? 'ring-2 ring-[#34287d] scale-105' : 'opacity-65 hover:opacity-100'"
                                class="w-20 h-16 rounded-xl overflow-hidden border border-slate-300 shrink-0 transition">
                            <img :src="img" alt="Ángulo urna" class="w-full h-full object-cover">
                        </button>
                    </template>
                </div>
            </div>

            <!-- Detalle Técnico y Precio (5 columnas) -->
            <div class="lg:col-span-5 p-6 sm:p-8 flex flex-col justify-between space-y-6">
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-3 py-1 rounded-full bg-violet-100 text-violet-900 text-xs font-bold">
                            {{ \App\Support\Etiquetas::texto($urna->categoria_tamano) }}
                        </span>
                        @if($urna->stock_total > 0 && ! $urna->reservada_en_vida)
                            <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                                ● Disponible entrega inmediata
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold">
                                ● Consultar disponibilidad
                            </span>
                        @endif
                    </div>

                    <div>
                        <h1 class="font-serif-display text-2xl sm:text-3xl font-bold text-indigo-950 leading-tight">
                            {{ $urna->modelo }}
                        </h1>
                        <p class="text-xs text-slate-500 mt-1">Acabado {{ \App\Support\Etiquetas::texto($urna->acabado_tallado) }} • Color {{ $urna->color }}</p>
                    </div>

                    <!-- Valor Referencial / Venta -->
                    <div class="p-4 rounded-2xl bg-indigo-50/80 border border-indigo-100">
                        <p class="text-[11px] font-bold uppercase text-indigo-800 tracking-wide">Valor de Urna</p>
                        <p class="text-3xl font-extrabold text-indigo-950 mt-0.5">
                            ${{ number_format($urna->precio_venta, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-500">CLP</span>
                        </p>
                        <p class="text-[11px] text-slate-500 mt-1">Aplicable con Cuota Mortuoria IPS / AFP o pago particular.</p>
                    </div>

                    @if($urna->descripcion)
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">{{ $urna->descripcion }}</p>
                    @endif

                    <!-- Ficha Técnica -->
                    <div class="border-t border-slate-100 pt-3 grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-slate-400 block">Tallado / Terminación</span>
                            <span class="font-bold text-slate-800">{{ \App\Support\Etiquetas::texto($urna->acabado_tallado) }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Tonalidad</span>
                            <span class="font-bold text-slate-800">{{ $urna->color }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-slate-400 block">Dimensiones Exteriores</span>
                            <span class="font-bold text-slate-800">{{ $urna->largo_cm }} cm (largo) × {{ $urna->ancho_cm }} cm (ancho) × {{ $urna->alto_cm }} cm (alto)</span>
                        </div>
                    </div>
                </div>

                <!-- Botón WhatsApp Directo -->
                <div class="space-y-2 pt-2">
                    <a href="https://wa.me/56912345678?text={{ urlencode("Hola Funeraria Cristo Rey, me interesa la urna {$urna->modelo} (SKU: {$urna->codigo_sku}) valor $" . number_format($urna->precio_venta, 0, ',', '.') . ". ¿Podrían orientarme?") }}"
                       target="_blank"
                       class="block w-full py-3.5 px-5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold text-center shadow-lg transition">
                        📲 Consultar o Solicitar por WhatsApp
                    </a>
                    <p class="text-[11px] text-center text-slate-400">Atención continua 24/7 en Temuco y La Araucanía</p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>