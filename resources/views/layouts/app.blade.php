<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Funeraria Cristo Rey — Sistema Integral</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased bg-[#f3f4f9] text-slate-800" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">

        <!-- Sidebar Oscuro Fijo -->
        @include('layouts.navigation')

        <!-- Contenedor Derecho (Topbar + Contenido) -->
        <div class="flex-1 lg:pl-64 flex flex-col min-w-0">

            <!-- BARRA SUPERIOR CON BUSCADOR GLOBAL Y PERFIL -->
            <header class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-slate-200/80 h-16 px-4 sm:px-6 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 flex-1 max-w-2xl">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                        ☰
                    </button>

                    <!-- Buscador Global -->
                    <form method="GET" action="{{ route('expedientes.index') }}" class="relative w-full">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </span>
                        <input type="text" name="buscar" value="{{ request('buscar') }}"
                               placeholder="Buscar cliente, fallecido, servicio, RUT o número de expediente..."
                               class="w-full pl-10 pr-4 py-2 rounded-full bg-slate-100/90 border-transparent focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200 text-xs sm:text-sm text-slate-700 placeholder-slate-400">
                    </form>
                </div>

                <!-- Derecha: Campana de Alertas + Usuario -->
                <div class="flex items-center gap-4">
                    @php
                        $cntSeremi = \App\Models\Expediente::whereIn('estado_expediente', ['ABIERTO', 'EN_PROCESO'])
                            ->where('limite_legal_seremi_at', '<=', now()->addHours(6))->count();
                        $cntStock = \App\Models\Urna::where('estado_urna', 'HABILITADO')
                            ->whereRaw('(stock_salon + stock_taller) <= 2')->count();
                        $cntCobranza = \App\Models\Expediente::where('estado_expediente', '!=', 'ANULADO_INACTIVO')
                            ->where('saldo_pendiente', '>', 0)
                            ->where('created_at', '<=', now()->subDays(10))->count();
                        $totalAlertasTop = $cntSeremi + $cntStock + $cntCobranza;
                    @endphp
                    <div x-data="{ openNotif: false }" class="relative">
                        <button @click="openNotif = !openNotif" class="relative p-2 rounded-full text-slate-600 hover:bg-slate-100 transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                            </svg>
                            @if($totalAlertasTop > 0)
                                <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-600 text-white text-[10px] font-bold flex items-center justify-center ring-2 ring-white">
                                    {{ $totalAlertasTop }}
                                </span>
                            @endif
                        </button>

                        <div x-show="openNotif" x-cloak @click.away="openNotif = false"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-200 p-4 space-y-2.5 text-xs z-50">
                            <div class="flex items-center justify-between border-b pb-2">
                                <p class="font-bold text-indigo-950">Alertas Operativas ({{ $totalAlertasTop }})</p>
                                @if(Auth::user()->tieneRol(['admin']))
                                    <a href="{{ route('notificaciones.index') }}" class="text-[11px] font-bold text-indigo-700 hover:underline">Ver todas →</a>
                                @endif
                            </div>
                            <a href="{{ route('expedientes.index', ['urgente_seremi' => 1]) }}" class="block p-2 rounded-lg bg-rose-50 text-rose-900 hover:bg-rose-100">
                                <strong>Semáforo Seremi ({{ $cntSeremi }}):</strong> Plazos legales próximos a vencer.
                            </a>
                            <a href="{{ route('urnas.index') }}" class="block p-2 rounded-lg bg-amber-50 text-amber-900 hover:bg-amber-100">
                                <strong>Stock Crítico ({{ $cntStock }}):</strong> Urnas con 2 o menos unidades.
                            </a>
                            <a href="{{ route('cobranzas.index') }}" class="block p-2 rounded-lg bg-indigo-50 text-indigo-900 hover:bg-indigo-100">
                                <strong>Cobranzas Vencidas ({{ $cntCobranza }}):</strong> Saldos con 10 o más días.
                            </a>
                        </div>
                    </div>

                    <!-- Perfil Usuario -->
                    @php
                        $partes = explode(' ', trim(Auth::user()->name));
                        $iniciales = strtoupper(substr($partes[0] ?? 'U', 0, 1) . substr($partes[1] ?? '', 0, 1));
                    @endphp
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full bg-[#2c236b] text-white font-bold text-xs flex items-center justify-center shadow-sm">
                            {{ $iniciales }}
                        </div>
                        <div class="hidden sm:block text-left leading-tight">
                            <p class="text-xs font-bold text-slate-800">{{ Auth::user()->name }}</p>
                            <p class="text-[11px] text-slate-500">{{ Auth::user()->rol->nombre ?? 'Usuario' }}</p>
                        </div>
                    </div>
                </div>
            </header>

            @isset($header)
                <div class="bg-white border-b border-slate-200/70 px-4 sm:px-6 py-4">
                    {{ $header }}
                </div>
            @endisset

            <!-- Contenido Principal -->
            <main class="flex-1 p-4 sm:p-6 space-y-5">
                @if(session('success'))
                    <div class="p-3.5 rounded-xl bg-emerald-50 border-l-4 border-emerald-600 text-emerald-900 text-xs sm:text-sm font-medium shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-3.5 rounded-xl bg-rose-50 border-l-4 border-rose-600 text-rose-900 text-xs sm:text-sm font-semibold shadow-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-3.5 rounded-xl bg-amber-50 border-l-4 border-amber-500 text-amber-900 text-xs sm:text-sm shadow-sm">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>