<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Funeraria Cristo Rey — Acompañamos en los momentos más difíciles</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .font-serif-display { font-family: 'Playfair Display', Georgia, serif; }
        .font-sans-ui { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans-ui text-[#251f54] bg-[#dcd5f2] antialiased selection:bg-violet-300 selection:text-indigo-950"
      x-data="{
          modalCondolencia: false,
          modalNosotros: false,
          modalCatalogoCompleto: {{ request()->hasAny(['categoria_tamano', 'acabado_tallado']) ? 'true' : 'false' }},
          urnaSeleccionada: null,
          folioSeleccionado: '{{ $serviciosActivos->first()->folio ?? '' }}',
          slideCondolencia: 0,
          totalSlides: {{ max(1, $condolenciasDestacadas->count()) }},
          scrollUrnas(dir) {
              const el = this.$refs.carruselUrnas;
              if (el) el.scrollBy({ left: dir * 280, behavior: 'smooth' });
          }
      }"
      x-init="setInterval(() => { slideCondolencia = (slideCondolencia + 1) % totalSlides }, 6000)">

    <!-- FONDO FIJO A PANTALLA COMPLETA (Rellena 100% sin recortes ni cruz encima) -->
    <div class="fixed inset-0 w-full h-full -z-10 pointer-events-none">
        <img src="{{ asset('images/hero-cristo-rey.jpg') }}"
             alt="Funeraria Cristo Rey"
             class="w-full h-full object-cover object-center">
        <!-- Degradado sutil solo a la izquierda para que el título se lea perfecto sin tapar la cruz ni los lirios -->
        <div class="absolute inset-0 bg-gradient-to-r from-[#ede9f8]/80 via-[#ede9f8]/30 to-transparent"></div>
    </div>

    <!-- Contenedor Principal Fluido (Se expande a todo el ancho en cualquier zoom) -->
    <div class="min-h-screen w-full flex flex-col justify-between">

        <!-- Alerta Flotante si se envió una condolencia -->
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                 class="fixed top-20 right-6 z-50 bg-indigo-950 text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-violet-400/40 text-sm flex items-center gap-3">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- 1. BARRA DE NAVEGACIÓN SUPERIOR (Ancho completo fluido) -->
        <header class="w-full pt-5 pb-3 px-6 sm:px-10 lg:px-16 xl:px-20 z-20">
            <div class="w-full flex items-center justify-between">
                <!-- Logo Izquierda (Corona de Laureles + Cruz + Texto Serif) -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-[#2c2566] transition-transform group-hover:scale-105">
                        <svg class="w-12 h-12" viewBox="0 0 64 64" fill="none">
                            <path d="M22 50C13 44 9 33 12 22C13.5 16.5 17 12 21 9" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                            <path d="M42 50C51 44 55 33 52 22C50.5 16.5 47 12 43 9" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                            <path d="M14 34L9 31M13 26L9 22M16 18L13 14M50 34L55 31M51 26L55 22M48 18L51 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M32 13V51M21 25H43" stroke="currentColor" stroke-width="3.6" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="font-serif-display leading-tight text-[#262058]">
                        <span class="block text-lg font-semibold tracking-tight">Funeraria</span>
                        <span class="block text-xl font-bold tracking-tight -mt-1">Cristo Rey</span>
                    </div>
                </a>

                <!-- Menú Central -->
                <nav class="hidden md:flex items-center gap-9 text-sm font-medium text-[#3b3472]">
                    <a href="#inicio" class="flex items-center gap-1.5 text-[#262058] font-semibold border-b-2 border-[#483d99] pb-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>
                        Inicio
                    </a>
                    <button type="button" @click="modalNosotros = true" class="flex items-center gap-1.5 hover:text-[#262058] transition">
                        <svg class="w-4 h-4 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Nosotros
                    </button>
                    <a href="#servicios" class="flex items-center gap-1.5 hover:text-[#262058] transition">
                        <svg class="w-4 h-4 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Servicios
                    </a>
                    <button type="button" @click="modalCondolencia = true" class="flex items-center gap-1.5 hover:text-[#262058] transition">
                        <svg class="w-4 h-4 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        Condolencias
                    </button>
                </nav>

                <!-- Botón Iniciar Sesión Derecha -->
                <div>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-gradient-to-r from-[#4b3f96] to-[#342a73] hover:from-[#5749ab] hover:to-[#3f338a] text-white text-xs sm:text-sm font-semibold shadow-md transition">
                            <span>Panel Interno</span>
                            <span>→</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-gradient-to-r from-[#4b3f96] to-[#342a73] hover:from-[#5749ab] hover:to-[#3f338a] text-white text-xs sm:text-sm font-semibold shadow-md transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                            <span>Iniciar Sesión</span>
                            <span>→</span>
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <!-- 2. CONTENIDO PRINCIPAL FLUIDO (Se expande a todo el ancho) -->
        <main id="inicio" class="w-full px-6 sm:px-10 lg:px-16 xl:px-20 pt-4 pb-10 space-y-7 z-10">

            <!-- Texto Principal Izquierda -->
            <div class="max-w-xl space-y-4 pt-2">
                <p class="font-serif-display text-lg text-[#3a3270] font-medium">Funeraria Cristo Rey</p>
                <h1 class="font-serif-display text-4xl sm:text-5xl lg:text-[54px] font-bold text-[#251f54] leading-[1.12] tracking-tight">
                    Acompañamos en los momentos más <span class="italic font-normal text-[#352d73]">difíciles...</span>
                </h1>
                <p class="text-sm sm:text-base text-[#3b3469] font-medium leading-relaxed max-w-md">
                    Brindamos un servicio integral y con respeto, para honrar la memoria de quienes más amas.
                </p>

                <!-- Botones Píldora -->
                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    <a href="#servicios" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-gradient-to-r from-[#4b3f96] to-[#362c78] hover:from-[#584aad] hover:to-[#42368f] text-white text-sm font-semibold shadow-lg shadow-indigo-950/20 transition">
                        <span>♥</span>
                        <span>Conocer nuestros servicios</span>
                    </a>
                    <a href="https://wa.me/56912345678?text=Hola%20Funeraria%20Cristo%20Rey,%20necesito%20orientaci%C3%B3n%20sobre%20sus%20servicios." target="_blank"
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-white/75 hover:bg-white/95 backdrop-blur-md border border-[#8e85c4]/50 text-[#2c2566] text-sm font-semibold shadow-sm transition">
                        <svg class="w-4 h-4 text-[#4b3f96]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Contáctanos</span>
                    </a>
                </div>
            </div>

            <!-- 3. TARJETA GLASSMORPHISM: NUESTRAS URNAS (Ancho completo fluido) -->
            <section id="servicios" class="w-full bg-white/78 backdrop-blur-xl rounded-[28px] p-6 sm:p-8 shadow-[0_20px_50px_rgba(38,32,88,0.12)] border border-white/85">
                <div class="flex flex-col lg:flex-row lg:items-center gap-6">

                    <!-- Columna Izquierda con Ícono de Urna Funeraria y Título Ampliado -->
                    <div class="lg:w-[23%] space-y-3 shrink-0">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#4b3f96] to-[#342a73] text-white flex items-center justify-center shadow-md shrink-0">
                                <!-- Ícono de Urna Funeraria Clásica -->
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M8 3h8v2H8V3zm1 3h6l2 4.5c.4.9.5 1.9.3 2.9L16 19H8l-1.3-5.6c-.2-1-.1-2 .3-2.9L9 6zm-2 14h10v2H7v-2z"/>
                                </svg>
                            </div>
                            <h2 class="font-serif-display text-2xl sm:text-[29px] font-bold text-[#251f54] leading-tight">
                                Nuestras Urnas
                            </h2>
                        </div>
                        <p class="text-xs sm:text-sm text-[#544d85] leading-relaxed">
                            Calidad y elegancia para un último adiós con dignidad.
                        </p>
                        <div class="pt-1">
                            <button type="button" @click="modalCatalogoCompleto = !modalCatalogoCompleto"
                                    class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-white hover:bg-indigo-50 text-[#2c2566] text-xs font-bold border border-[#c5bee8] shadow-sm transition">
                                <span x-text="modalCatalogoCompleto ? 'Ocultar filtros' : 'Ver catálogo'">Ver catálogo</span>
                                <span>→</span>
                            </button>
                        </div>
                    </div>

                    <!-- Carrusel Horizontal Fluido con Flechas Laterales -->
                    <div class="lg:w-[77%] relative flex items-center">
                        <button type="button" @click="scrollUrnas(-1)"
                                class="hidden sm:flex w-10 h-10 rounded-full bg-white hover:bg-indigo-50 text-[#3b3472] shadow-md border border-slate-200 items-center justify-center shrink-0 mr-4 transition">
                            ←
                        </button>

                        <div x-ref="carruselUrnas" class="flex gap-5 overflow-x-auto pb-2 snap-x snap-mandatory w-full">
                            @foreach($urnas as $urna)
                                <div @click="urnaSeleccionada = {{ json_encode([
                                        'modelo' => $urna->modelo,
                                        'sku' => $urna->codigo_sku,
                                        'descripcion' => $urna->descripcion,
                                        'acabado' => \App\Support\Etiquetas::texto($urna->acabado_tallado),
                                        'color' => $urna->color,
                                        'categoria' => \App\Support\Etiquetas::texto($urna->categoria_tamano),
                                        'foto' => $urna->foto_s3_url ?: 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=600&q=80',
                                    ]) }}"
                                     class="min-w-[210px] flex-1 snap-start cursor-pointer group text-center space-y-2.5">
                                    <div class="h-40 rounded-2xl bg-gradient-to-b from-slate-100 to-white border border-slate-200/80 p-3 flex items-center justify-center overflow-hidden shadow-sm group-hover:shadow-md group-hover:border-violet-300 transition">
                                        <img src="{{ $urna->foto_s3_url ?: 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=600&q=80' }}"
                                             alt="{{ $urna->modelo }}"
                                             class="w-full h-full object-cover rounded-xl group-hover:scale-105 transition-transform duration-300">
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-sm text-[#251f54] group-hover:text-violet-700 transition line-clamp-1">{{ $urna->modelo }}</h3>
                                        <p class="text-[11px] text-[#635c91] line-clamp-1">{{ \App\Support\Etiquetas::texto($urna->acabado_tallado) }} • {{ $urna->color }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" @click="scrollUrnas(1)"
                                class="hidden sm:flex w-10 h-10 rounded-full bg-white hover:bg-indigo-50 text-[#3b3472] shadow-md border border-slate-200 items-center justify-center shrink-0 ml-4 transition">
                            →
                        </button>
                    </div>
                </div>

                <!-- Panel Desplegable de Filtros del Catálogo -->
                <div x-show="modalCatalogoCompleto" x-cloak class="mt-6 pt-5 border-t border-indigo-100">
                    <form method="GET" action="{{ route('publico.catalogo') }}" class="flex flex-wrap items-center justify-between gap-3">
                        <span class="text-xs font-bold text-[#251f54]">Filtrar modelos disponibles (Consulta sin precios públicos):</span>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <select name="categoria_tamano" class="rounded-full border-slate-300 bg-white px-4 py-1.5 text-xs">
                                <option value="">Todas las categorías</option>
                                <option value="ESTANDAR" {{ request('categoria_tamano') === 'ESTANDAR' ? 'selected' : '' }}>Estándar Adulto</option>
                                <option value="NINO_SEREMI" {{ request('categoria_tamano') === 'NINO_SEREMI' ? 'selected' : '' }}>Infantil / Angelito</option>
                                <option value="ESPECIAL_XL" {{ request('categoria_tamano') === 'ESPECIAL_XL' ? 'selected' : '' }}>Especial XL</option>
                            </select>
                            <select name="acabado_tallado" class="rounded-full border-slate-300 bg-white px-4 py-1.5 text-xs">
                                <option value="">Todos los tallados</option>
                                <option value="LISA" {{ request('acabado_tallado') === 'LISA' ? 'selected' : '' }}>Lisa</option>
                                <option value="PALOMA_BIBLIA" {{ request('acabado_tallado') === 'PALOMA_BIBLIA' ? 'selected' : '' }}>Paloma Biblia</option>
                                <option value="MARQUETEADA" {{ request('acabado_tallado') === 'MARQUETEADA' ? 'selected' : '' }}>Marqueteada</option>
                                <option value="PERSONALIZADA" {{ request('acabado_tallado') === 'PERSONALIZADA' ? 'selected' : '' }}>Personalizada</option>
                            </select>
                            <button type="submit" class="px-4 py-1.5 rounded-full bg-[#3b3086] text-white font-semibold">Aplicar Filtro</button>
                            @if(request()->hasAny(['categoria_tamano', 'acabado_tallado']))
                                <a href="{{ route('home') }}" class="px-3 py-1.5 rounded-full bg-slate-200 text-slate-700 font-semibold">Limpiar</a>
                            @endif
                        </div>
                    </form>
                </div>
            </section>

            <!-- 4. FILA INFERIOR: TARJETA VELA CON LIRIOS + TARJETA NUESTRAS CONDOLENCIAS -->
            <section class="w-full grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Tarjeta Izquierda: Vela Encendida con Lirios + Citas Rotativas -->
                <div class="relative rounded-[26px] overflow-hidden bg-gradient-to-r from-[#1d1747] via-[#2b2263] to-[#3a2f7d] text-white shadow-xl min-h-[185px] flex items-center">
                    <div class="w-2/5 h-full relative self-stretch bg-[#171238]">
                        <img src="https://images.unsplash.com/photo-1543525238-54e3d131f7ca?auto=format&fit=crop&w=700&q=85"
                             alt="Vela encendida en memoria"
                             class="w-full h-full object-cover object-center opacity-90">
                        <!-- Lirios blancos decorativos en la esquina inferior izquierda de la vela -->
                        <svg class="absolute bottom-0 left-0 w-24 h-24 text-white/90 drop-shadow-md pointer-events-none" viewBox="0 0 100 100" fill="currentColor">
                            <path d="M12 95 C15 70, 30 55, 52 50 C42 68, 32 82, 12 95 Z" opacity="0.95"/>
                            <path d="M5 82 C18 62, 35 58, 55 65 C38 76, 22 84, 5 82 Z" opacity="0.85"/>
                            <path d="M25 98 C35 76, 52 68, 70 74 C54 86, 40 94, 25 98 Z" opacity="0.9"/>
                            <circle cx="44" cy="64" r="3" fill="#fde68a"/>
                        </svg>
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-transparent to-[#271f5c]"></div>
                    </div>

                    <div class="w-3/5 p-6 sm:p-7 flex flex-col justify-between">
                        @if($condolenciasDestacadas->count() > 0)
                            @foreach($condolenciasDestacadas as $idx => $cond)
                                <div x-show="slideCondolencia === {{ $idx }}" x-transition.opacity.duration.500ms class="space-y-2">
                                    <p class="font-serif-display italic text-base sm:text-xl leading-snug text-white/95">
                                        “{{ Str::limit($cond->mensaje, 115) }}”
                                    </p>
                                    <p class="text-xs sm:text-sm text-violet-200 font-medium">
                                        — {{ $cond->nombre_remitente }}
                                        @if($cond->expediente && $cond->expediente->fallecido)
                                            <span class="opacity-75">(En memoria de {{ $cond->expediente->fallecido->nombre_completo }})</span>
                                        @endif
                                    </p>
                                </div>
                            @endforeach
                        @else
                            <div class="space-y-2">
                                <p class="font-serif-display italic text-base sm:text-xl leading-snug text-white/95">
                                    “Que en paz descanse y que su luz siga brillando en nuestros corazones.”
                                </p>
                                <p class="text-xs sm:text-sm text-violet-200 font-medium">— Familia González</p>
                            </div>
                        @endif

                        <!-- Indicadores de puntos -->
                        <div class="flex items-center gap-1.5 pt-4">
                            @for($i = 0; $i < max(1, $condolenciasDestacadas->count()); $i++)
                                <button type="button" @click="slideCondolencia = {{ $i }}"
                                        :class="slideCondolencia === {{ $i }} ? 'w-4 bg-white' : 'w-1.5 bg-white/40'"
                                        class="h-1.5 rounded-full transition-all"></button>
                            @endfor
                        </div>
                    </div>
                </div>

                <!-- Tarjeta Derecha: Nuestras Condolencias + Manos Entrelazadas -->
                <div class="relative rounded-[26px] overflow-hidden bg-white/88 backdrop-blur-xl border border-white shadow-xl min-h-[185px] flex items-center justify-between">
                    <div class="p-6 sm:p-8 z-10 max-w-md space-y-3">
                        <div class="flex items-start gap-3.5">
                            <div class="text-[#5849ab] shrink-0 mt-0.5">
                                <svg class="w-10 h-10" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.4">
                                    <path d="M24 8C20 16 14 24 12 34L22 38V14L24 8Z" stroke-linejoin="round"/>
                                    <path d="M24 8C28 16 34 24 36 34L26 38V14L24 8Z" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-serif-display text-2xl sm:text-[26px] font-bold text-[#251f54]">Nuestras Condolencias</h3>
                                <p class="text-xs sm:text-sm text-[#544d85] mt-1 leading-relaxed">
                                    Deja un mensaje de apoyo y cariño para la familia en este difícil momento.
                                </p>
                            </div>
                        </div>

                        <div class="pt-1 pl-13">
                            <button type="button" @click="modalCondolencia = true"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-gradient-to-r from-[#4b3f96] to-[#362c78] hover:from-[#584aad] hover:to-[#42368f] text-white text-xs sm:text-sm font-semibold shadow-md transition">
                                <span>✍</span>
                                <span>Dejar mensaje</span>
                                <span>→</span>
                            </button>
                        </div>
                    </div>

                    <!-- Imagen Manos Entrelazadas a la Derecha -->
                    <div class="hidden sm:block w-2/5 h-full self-stretch relative">
                        <img src="https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?auto=format&fit=crop&w=600&q=80"
                             alt="Acompañamiento familiar"
                             class="w-full h-full object-cover opacity-55">
                        <div class="absolute inset-0 bg-gradient-to-r from-white via-white/50 to-transparent"></div>
                    </div>
                </div>
            </section>
        </main>

        <!-- 5. FOOTER OSCURO ÍNDIGO PROFUNDO (Sin redes sociales y ancho completo fluido) -->
        <footer class="w-full bg-[#1b1642] text-white py-5 px-6 sm:px-10 lg:px-16 xl:px-20 border-t border-indigo-900/60">
            <div class="w-full flex flex-col md:flex-row items-center justify-between gap-4">
                <!-- Logo Blanco Izquierda -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full border border-white/30 flex items-center justify-center">
                        <span class="text-lg">✝</span>
                    </div>
                    <div class="font-serif-display leading-tight">
                        <span class="block text-sm font-semibold">Funeraria</span>
                        <span class="block text-base font-bold -mt-1">Cristo Rey</span>
                    </div>
                </div>

                <!-- Datos de Contacto Centro -->
                <div class="text-center space-y-1">
                    <div class="flex flex-wrap items-center justify-center gap-4 text-xs sm:text-sm text-indigo-100">
                        <span>📍 Av. Manuel Rodríguez 1234, Temuco</span>
                        <span class="hidden sm:inline text-indigo-500">|</span>
                        <span>📞 +56 9 1234 5678</span>
                        <span class="hidden sm:inline text-indigo-500">|</span>
                        <span>✉️ contacto@funerariacristorey.cl</span>
                    </div>
                    <p class="text-[11px] text-indigo-300/75">© {{ date('Y') }} Funeraria Cristo Rey. Todos los derechos reservados.</p>
                </div>

                <!-- Espaciador equilibrado en escritorio -->
                <div class="hidden md:block text-right text-xs text-indigo-300/80">
                    Atención Continua 24/7
                </div>
            </div>
        </footer>
    </div>

    <!-- MODAL 1: NOSOTROS (Se abre desde el menú superior sin recargar la portada) -->
    <div x-show="modalNosotros" x-cloak class="fixed inset-0 z-50 bg-indigo-950/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="modalNosotros = false" class="bg-white rounded-3xl max-w-xl w-full p-7 shadow-2xl space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="font-serif-display text-2xl font-bold text-[#251f54]">Sobre Funeraria Cristo Rey</h3>
                <button type="button" @click="modalNosotros = false" class="text-slate-400 hover:text-slate-700 font-bold">✕</button>
            </div>
            <p class="text-sm text-slate-600 leading-relaxed">
                Acompañamos con respeto, dignidad y calidez humana a las familias de <strong>Temuco y toda la Región de La Araucanía</strong> las 24 horas del día.
            </p>
            <ul class="text-xs text-slate-700 space-y-2 bg-indigo-50/70 p-4 rounded-2xl">
                <li>• Traslados urbanos y rurales las 24 horas con flota propia.</li>
                <li>• Gestión completa ante Registro Civil, SML y Seremi de Salud.</li>
                <li>• Asesoría directa para tramitación de cuota mortuoria <strong>IPS y AFP</strong>.</li>
                <li>• Salón Velatorio en Calle Blanco y Av. Manuel Rodríguez 1234, Temuco.</li>
            </ul>
            <div class="flex justify-end">
                <button type="button" @click="modalNosotros = false" class="px-5 py-2 rounded-full bg-[#3b3086] text-white text-xs font-bold">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- MODAL 2: DETALLE DE URNA + CONSULTA WHATSAPP (SIN PRECIO PÚBLICO) -->
    <div x-show="urnaSeleccionada" x-cloak class="fixed inset-0 z-50 bg-indigo-950/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="urnaSeleccionada = null" class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl">
            <template x-if="urnaSeleccionada">
                <div>
                    <div class="h-60 bg-slate-100 relative">
                        <img :src="urnaSeleccionada.foto" :alt="urnaSeleccionada.modelo" class="w-full h-full object-cover">
                        <button type="button" @click="urnaSeleccionada = null" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/90 text-slate-800 font-bold shadow">✕</button>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <span class="text-xs font-bold text-violet-700 uppercase" x-text="urnaSeleccionada.sku + ' • ' + urnaSeleccionada.acabado"></span>
                            <h3 class="font-serif-display text-2xl font-bold text-[#251f54]" x-text="urnaSeleccionada.modelo"></h3>
                            <p class="text-xs text-slate-600 mt-1" x-text="urnaSeleccionada.descripcion || 'Terminaciones nobles de alta calidad y dignidad.'"></p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2.5 pt-2">
                            <a :href="'https://wa.me/56912345678?text=' + encodeURIComponent('Hola Funeraria Cristo Rey, deseo consultar por el servicio funerario con la urna: ' + urnaSeleccionada.modelo + ' (' + urnaSeleccionada.sku + ')')"
                               target="_blank"
                               class="flex-1 py-3 px-4 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold text-center shadow">
                                Consultar Disponibilidad por WhatsApp
                            </a>
                            <button type="button" @click="urnaSeleccionada = null" class="px-5 py-3 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold">
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- MODAL 3: DEJAR MENSAJE DE CONDOLENCIAS EN VIVO -->
    <div x-show="modalCondolencia" x-cloak class="fixed inset-0 z-50 bg-indigo-950/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="modalCondolencia = false" class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-serif-display text-2xl font-bold text-[#251f54]">Libro Digital de Condolencias</h3>
                    <p class="text-xs text-slate-500">Su mensaje quedará publicado en el memorial de la familia</p>
                </div>
                <button type="button" @click="modalCondolencia = false" class="text-slate-400 hover:text-slate-700 font-bold">✕</button>
            </div>

            @if($serviciosActivos->count() > 0)
                <form :action="'/memorial/' + folioSeleccionado + '/condolencia'" method="POST" class="space-y-3.5 text-sm">
                    @csrf
                    <div>
                        <label class="text-xs font-bold text-[#251f54]">Seleccione el Servicio / Familia *</label>
                        <select x-model="folioSeleccionado" class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                            @foreach($serviciosActivos as $srv)
                                <option value="{{ $srv->folio }}">En memoria de {{ $srv->fallecido->nombre_completo }} ({{ $srv->folio }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-bold text-[#251f54]">Su Nombre o Familia *</label>
                            <input type="text" name="nombre_remitente" required placeholder="Ej: Familia González" class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-[#251f54]">Relación / Vínculo</label>
                            <input type="text" name="relacion" placeholder="Ej: Amigos, Vecinos" class="w-full mt-1 rounded-xl border-slate-300 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-[#251f54]">Mensaje de Apoyo *</label>
                        <textarea name="mensaje" rows="3" required placeholder="Escriba sus palabras de cariño y consuelo..." class="w-full mt-1 rounded-xl border-slate-300 text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="modalCondolencia = false" class="px-5 py-2.5 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold">Cancelar</button>
                        <button type="submit" class="px-6 py-2.5 rounded-full bg-gradient-to-r from-[#4b3f96] to-[#362c78] text-white text-xs font-bold shadow">
                            Publicar Condolencia
                        </button>
                    </div>
                </form>
            @else
                <p class="text-sm text-slate-600">No hay servicios en velatorio activo en este momento.</p>
            @endif
        </div>
    </div>

</body>
</html>