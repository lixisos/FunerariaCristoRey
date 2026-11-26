<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-indigo-950">Roles y Permisos del Equipo</h2>
            <p class="text-xs text-slate-500">Asigna cargos a los colaboradores y revisa los permisos de acceso por módulo</p>
        </div>
    </x-slot>

    <!-- 1. Tarjetas de Perfiles -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($roles as $rol)
            <div x-data="{ editRol: false }" class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-900 text-xs font-extrabold">{{ \App\Support\Etiquetas::texto($rol->slug) }}</span>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-500">{{ $rol->usuarios_count }} usuario(s)</span>
                        <button type="button" @click="editRol = true" class="text-[11px] font-bold text-amber-700 hover:underline">Editar</button>
                    </div>
                </div>
                <h3 class="font-bold text-base text-indigo-950">{{ $rol->nombre }}</h3>
                <p class="text-xs text-slate-600">{{ $rol->descripcion }}</p>

                <!-- Modal Editar Descripción del Rol -->
                <div x-show="editRol" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4">
                    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
                        <div class="flex justify-between items-center border-b pb-3">
                            <h3 class="font-bold text-base text-indigo-950">Editar Perfil de Rol</h3>
                            <button type="button" @click="editRol = false" class="text-slate-400 font-bold">✕</button>
                        </div>
                        <form method="POST" action="{{ route('roles.update', $rol) }}" class="space-y-3 text-xs">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="font-semibold">Nombre del Cargo *</label>
                                <input type="text" name="nombre" value="{{ $rol->nombre }}" required class="w-full mt-1 rounded-xl border-slate-300">
                            </div>
                            <div>
                                <label class="font-semibold">Descripción de Funciones</label>
                                <textarea name="descripcion" rows="3" class="w-full mt-1 rounded-xl border-slate-300">{{ $rol->descripcion }}</textarea>
                            </div>
                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" @click="editRol = false" class="px-4 py-2 bg-slate-200 rounded-xl font-semibold">Cancelar</button>
                                <button type="submit" class="px-5 py-2 bg-[#34287d] text-white rounded-xl font-bold">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- 2. Reasignación Directa de Privilegios por Usuario -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
        <div class="p-4 border-b border-slate-100">
            <h3 class="font-bold text-sm text-indigo-950">Asignación de Rol y Privilegios por Colaborador</h3>
            <p class="text-[11px] text-slate-500">Cambia el perfil operativo de un usuario para otorgarle o restringirle acceso a los módulos</p>
        </div>
        <table class="w-full text-xs text-left">
            <thead class="bg-[#231b56] text-white uppercase">
                <tr>
                    <th class="px-4 py-3">Colaborador</th>
                    <th class="px-4 py-3">Rol Actual</th>
                    <th class="px-4 py-3">Cambiar Privilegios a</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($usuarios as $u)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <span class="font-bold text-indigo-950 block">{{ $u->name }}</span>
                            <span class="text-slate-500">{{ $u->email }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-900 font-bold">
                                {{ $u->rol->nombre ?? 'Sin Rol' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($u->id === Auth::id())
                                <span class="text-slate-400 italic">Sesión actual protegida</span>
                            @else
                                <form method="POST" action="{{ route('usuarios.rol', $u) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="rol_id" class="rounded-xl border-slate-300 text-xs py-1.5">
                                        @foreach($roles as $rol)
                                            <option value="{{ $rol->id }}" {{ $u->rol_id == $rol->id ? 'selected' : '' }}>{{ $rol->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="px-3 py-1.5 bg-[#34287d] hover:bg-indigo-900 text-white font-bold rounded-xl">
                                        Cambiar rol
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- 3. Matriz de Permisos por Módulo -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
        <div class="p-4 border-b border-slate-100">
            <h3 class="font-bold text-sm text-indigo-950">Matriz de Accesos por Módulo del Sistema</h3>
        </div>
        <table class="w-full text-xs text-center">
            <thead class="bg-slate-100 text-slate-700 uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Módulo / Función</th>
                    <th class="px-3 py-3">Admin</th>
                    <th class="px-3 py-3">Coordinación</th>
                    <th class="px-3 py-3">Bodega</th>
                    <th class="px-3 py-3">Chofer</th>
                    <th class="px-3 py-3">Finanzas</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                <tr>
                    <td class="px-4 py-2.5 text-left font-semibold">Expedientes, Clientes, Fallecidos y Calendario</td>
                    <td>✅ Total</td><td>✅ Total</td><td>—</td><td>👁️ Lectura</td><td>👁️ Lectura</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5 text-left font-semibold">Catálogo de Urnas y Kardex de Bodega</td>
                    <td>✅ Total</td><td>✅ Total</td><td>✅ Gestión</td><td>—</td><td>—</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5 text-left font-semibold">Flota de Vehículos y Asignación de Cortejos</td>
                    <td>✅ Total</td><td>✅ Total</td><td>🔄 Estado</td><td>👁️ Ruta</td><td>—</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5 text-left font-semibold">Pagos, Abonos, Cobranzas y Convenios IPS/AFP</td>
                    <td>✅ Total</td><td>✅ Gestión</td><td>—</td><td>—</td><td>✅ Total</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5 text-left font-semibold">Documentos Legales, Centro PDF y Condolencias</td>
                    <td>✅ Total</td><td>✅ Total</td><td>📄 PDF</td><td>—</td><td>📄 PDF</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5 text-left font-semibold">Usuarios, Roles, Bitácora y Notificaciones</td>
                    <td>✅ Total</td><td>—</td><td>—</td><td>—</td><td>—</td>
                </tr>
            </tbody>
        </table>
    </div>
</x-app-layout>