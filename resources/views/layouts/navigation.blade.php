<!-- BARRA LATERAL OSCURA ÍNDIGO (SIDEBAR) -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       class="fixed inset-y-0 left-0 z-40 w-64 bg-gradient-to-b from-[#231b56] via-[#281f63] to-[#1b1445] text-white flex flex-col justify-between transition-transform duration-200 shadow-2xl border-r border-indigo-900/60">

    <!-- Logo Superior del Sidebar -->
    <div class="px-5 pt-5 pb-4 border-b border-white/10 flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white shrink-0">
                <svg class="w-8 h-8" viewBox="0 0 64 64" fill="none">
                    <path d="M22 50C13 44 9 33 12 22C13.5 16.5 17 12 21 9" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                    <path d="M42 50C51 44 55 33 52 22C50.5 16.5 47 12 43 9" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
                    <path d="M32 13V51M21 25H43" stroke="currentColor" stroke-width="3.6" stroke-linecap="round"/>
                </svg>
            </div>
            <div>
                <span class="font-bold text-base tracking-tight block leading-none">Funeraria</span>
                <span class="font-bold text-base tracking-tight block leading-tight text-violet-200">Cristo Rey</span>
                <span class="text-[10px] text-indigo-300 block mt-0.5">Acompañamos cada despedida</span>
            </div>
        </a>
        <button @click="sidebarOpen = false" class="lg:hidden text-indigo-200 hover:text-white">✕</button>
    </div>

    <!-- Navegación Colapsable por Módulos -->
<div class="flex-1 overflow-y-auto px-3 py-4 space-y-1.5 text-sm"
         x-data="{
             openOp: JSON.parse(localStorage.getItem('menu_op') ?? 'false'),
             openInv: JSON.parse(localStorage.getItem('menu_inv') ?? 'false'),
             openFin: JSON.parse(localStorage.getItem('menu_fin') ?? 'false'),
             openDoc: JSON.parse(localStorage.getItem('menu_doc') ?? 'false'),
             openAdm: JSON.parse(localStorage.getItem('menu_adm') ?? 'false'),
             toggle(key, prop) {
                 this[prop] = !this[prop];
                 localStorage.setItem(key, JSON.stringify(this[prop]));
             }
         }">

        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white shadow-sm' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }}">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>
            <span>Dashboard</span>
        </a>

        <!-- Grupo 1: Operaciones -->
        @if(Auth::user()->tieneRol(['admin', 'coordinador', 'chofer', 'finanzas']))
            <div class="pt-1">
                <button type="button" @click="openOp = !openOp" class="w-full flex items-center justify-between px-3.5 py-2 text-xs font-bold uppercase tracking-wider text-indigo-300 hover:text-white">
                    <span class="flex items-center gap-2">
                        <span>📋</span> Operaciones
                    </span>
                    <span x-text="openOp ? '⌄' : '›'"></span>
                </button>
                <div x-show="openOp" class="mt-1 space-y-1 pl-4 border-l border-white/10 ml-4">
                    <a href="{{ route('expedientes.index') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('expedientes.*') ? 'bg-violet-600 text-white font-bold' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }}">
                        Servicios (Expedientes)
                    </a>
                    <a href="{{ route('calendario.index') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('calendario.*') ? 'bg-violet-600 text-white font-bold' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }}">
                        Calendario
                    </a>
                    <a href="{{ route('clientes.index') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('clientes.*') ? 'bg-violet-600 text-white font-bold' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }}">
                        Clientes
                    </a>
                    <a href="{{ route('fallecidos.index') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('fallecidos.*') ? 'bg-violet-600 text-white font-bold' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }}">
                        Fallecidos
                    </a>
                </div>
            </div>
        @endif

        <!-- Grupo 2: Inventario y Flota -->
        @if(Auth::user()->tieneRol(['admin', 'coordinador', 'bodega', 'chofer']))
            <div class="pt-1">
                <button type="button" @click="openInv = !openInv" class="w-full flex items-center justify-between px-3.5 py-2 text-xs font-bold uppercase tracking-wider text-indigo-300 hover:text-white">
                    <span class="flex items-center gap-2">
                        <span>📦</span> Inventario y Flota
                    </span>
                    <span x-text="openInv ? '⌄' : '›'"></span>
                </button>
                <div x-show="openInv" class="mt-1 space-y-1 pl-4 border-l border-white/10 ml-4">
                    @if(Auth::user()->tieneRol(['admin', 'coordinador', 'bodega']))
                        <a href="{{ route('urnas.index') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('urnas.*') ? 'bg-violet-600 text-white font-bold' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }}">
                            Urnas
                        </a>
                        <a href="{{ route('bodega.index') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('bodega.*') ? 'bg-violet-600 text-white font-bold' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }}">
                            Bodega (Kardex)
                        </a>
                    @endif
                    <a href="{{ route('vehiculos.index') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('vehiculos.*') ? 'bg-violet-600 text-white font-bold' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }}">
                        Vehículos
                    </a>
                </div>
            </div>
        @endif

        <!-- Grupo 3: Finanzas -->
        @if(Auth::user()->tieneRol(['admin', 'finanzas', 'coordinador']))
            <div class="pt-1">
                <button type="button" @click="openFin = !openFin" class="w-full flex items-center justify-between px-3.5 py-2 text-xs font-bold uppercase tracking-wider text-indigo-300 hover:text-white">
                    <span class="flex items-center gap-2">
                        <span>💲</span> Finanzas
                    </span>
                    <span x-text="openFin ? '⌄' : '›'"></span>
                </button>
                <div x-show="openFin" class="mt-1 space-y-1 pl-4 border-l border-white/10 ml-4">
                    <a href="{{ route('finanzas.index') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('finanzas.*') ? 'bg-violet-600 text-white font-bold' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }}">
                        Pagos
                    </a>
                    <a href="{{ route('cobranzas.index') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('cobranzas.*') ? 'bg-violet-600 text-white font-bold' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }}">
                        Cobranzas
                    </a>
                    <a href="{{ route('convenios.index') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('convenios.*') ? 'bg-violet-600 text-white font-bold' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }}">
                        Convenios
                    </a>
                </div>
            </div>
        @endif

        <!-- Grupo 4: Documentos -->
        @if(Auth::user()->tieneRol(['admin', 'coordinador', 'finanzas', 'bodega']))
            <div class="pt-1">
                <button type="button" @click="openDoc = !openDoc" class="w-full flex items-center justify-between px-3.5 py-2 text-xs font-bold uppercase tracking-wider text-indigo-300 hover:text-white">
                    <span class="flex items-center gap-2">
                        <span>📄</span> Documentos
                    </span>
                    <span x-text="openDoc ? '⌄' : '›'"></span>
                </button>
                <div x-show="openDoc" class="mt-1 space-y-1 pl-4 border-l border-white/10 ml-4">
                    <a href="{{ route('documentos.index') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('documentos.*') ? 'bg-violet-600 text-white font-bold' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }}">
                        Documentos digitales
                    </a>
                    <a href="{{ route('pdf.centro') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('pdf.*') ? 'bg-violet-600 text-white font-bold' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }}">
                        Generación de PDF
                    </a>
                    <a href="{{ route('condolencias.admin') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('condolencias.*') ? 'bg-violet-600 text-white font-bold' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }}">
                        Condolencias
                    </a>
                </div>
            </div>
        @endif

        <!-- Grupo 5: Administración -->
        @if(Auth::user()->tieneRol(['admin']))
            <div class="pt-1">
                <button type="button" @click="openAdm = !openAdm" class="w-full flex items-center justify-between px-3.5 py-2 text-xs font-bold uppercase tracking-wider text-indigo-300 hover:text-white">
                    <span class="flex items-center gap-2">
                        <span>⚙️</span> Administración
                    </span>
                    <span x-text="openAdm ? '⌄' : '›'"></span>
                </button>
                <div x-show="openAdm" class="mt-1 space-y-1 pl-4 border-l border-white/10 ml-4">
                    <a href="{{ route('usuarios.index') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('usuarios.*') ? 'bg-violet-600 text-white font-bold' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }}">
                        Usuarios y Personal
                    </a>
                    <a href="{{ route('roles.index') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('roles.*') ? 'bg-violet-600 text-white font-bold' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }}">
                        Roles y permisos
                    </a>
                    <a href="{{ route('bitacora.index') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('bitacora.*') ? 'bg-violet-600 text-white font-bold' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }}">
                        Bitácora de auditoría
                    </a>
                    <a href="{{ route('notificaciones.index') }}" class="block px-3 py-1.5 rounded-lg text-xs {{ request()->routeIs('notificaciones.*') ? 'bg-violet-600 text-white font-bold' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }}">
                        Notificaciones
                    </a>
                </div>
            </div>
        @endif

        <a href="{{ route('configuracion.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs text-indigo-200 hover:bg-white/10 hover:text-white mt-2">
            <span>🔧</span>
            <span>Configuración</span>
        </a>

        <a href="{{ route('publico.catalogo') }}" target="_blank" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs text-emerald-300 hover:bg-white/10 mt-1">
            <span>🌐</span>
            <span>Portal Familias ↗</span>
        </a>
    </div>

    <!-- Pie del Sidebar -->
    <div class="p-4 border-t border-white/10 text-xs text-indigo-300/80 space-y-2">
        <div class="leading-tight">
            <p class="font-semibold text-white/90">Funeraria Cristo Rey</p>
            <p class="text-[11px]">Temuco - Chile</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full py-1.5 px-3 rounded-lg bg-white/10 hover:bg-rose-600 text-white text-xs font-semibold transition text-center">
                Cerrar Sesión
            </button>
        </form>
    </div>
</aside>