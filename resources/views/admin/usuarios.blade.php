<x-app-layout>
    <div x-data="{ openUser: false }" class="space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl text-indigo-950">Usuarios, Personal y Control de Ausencias</h2>
                <p class="text-xs text-slate-500">Gestiona colaboradores, vacaciones/licencias y delegación temporal de permisos</p>
            </div>
            <button type="button" @click="openUser = true" class="px-4 py-2.5 bg-[#34287d] hover:bg-[#231b59] text-white text-xs font-bold rounded-xl shadow">
                + Nuevo Colaborador
            </button>
        </div>

        <!-- Buscador y Filtros -->
        <form method="GET" action="{{ route('usuarios.index') }}" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre, RUT o correo..." class="sm:col-span-5 rounded-xl border-slate-300">

            <select name="rol_id" class="sm:col-span-3 rounded-xl border-slate-300">
                <option value="">Todos los cargos / roles</option>
                @foreach($roles as $rol)
                    <option value="{{ $rol->id }}" {{ request('rol_id') == $rol->id ? 'selected' : '' }}>{{ $rol->nombre }}</option>
                @endforeach
            </select>

            <select name="estado" class="sm:col-span-2 rounded-xl border-slate-300">
                <option value="">Todos los estados</option>
                <option value="ausente" {{ request('estado') === 'ausente' ? 'selected' : '' }}>Con ausencia / delegación</option>
                <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Cuentas deshabilitadas</option>
            </select>

            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 bg-[#34287d] text-white font-bold rounded-xl px-3 py-2">Filtrar</button>
                @if(request('buscar') || request('rol_id') || request('estado'))
                    <a href="{{ route('usuarios.index') }}" class="px-3 py-2 bg-slate-200 rounded-xl font-semibold flex items-center">Limpiar</a>
                @endif
            </div>
        </form>

        <!-- Tabla de Colaboradores -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-[#231b56] text-white uppercase">
                    <tr>
                        <th class="px-4 py-3">Colaborador / Cargo</th>
                        <th class="px-4 py-3">Cuenta</th>
                        <th class="px-4 py-3">Disponibilidad y Delegación Temporal</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($usuarios as $u)
                        <tr x-data="{ ausencia: '{{ $u->tipo_ausencia ?? 'ACTIVO' }}', editUser: false }" class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <span class="font-bold text-indigo-950 block">{{ $u->name }}</span>
                                <span class="text-slate-500">{{ $u->rut }} • {{ $u->email }}</span>
                                <span class="mt-1 inline-block px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-900 font-bold text-[11px]">
                                    {{ $u->rol->nombre ?? 'Sin Rol' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-0.5 rounded-full font-bold {{ $u->estado_usuario === 'HABILITADO' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ \App\Support\Etiquetas::texto($u->estado_usuario) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('usuarios.ausencia', $u) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="tipo_ausencia" x-model="ausencia" class="rounded-xl border-slate-300 text-xs py-1.5">
                                        <option value="ACTIVO">✅ Disponible en turno</option>
                                        <option value="VACACIONES">🏖️ Vacaciones</option>
                                        <option value="PERMISO">📋 Permiso administrativo</option>
                                        <option value="LICENCIA_MEDICA">🏥 Licencia médica</option>
                                    </select>

                                    <template x-if="ausencia !== 'ACTIVO'">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <input type="date" name="ausencia_hasta" value="{{ $u->ausencia_hasta?->format('Y-m-d') }}" class="rounded-xl border-slate-300 text-xs py-1.5" title="Regresa el día">
                                            <select name="delegado_a_id" class="rounded-xl border-slate-300 text-xs py-1.5" title="Prestar permisos del cargo a:">
                                                <option value="">-- Sin delegación --</option>
                                                @foreach($todosUsuarios->where('id', '!=', $u->id) as $otro)
                                                    <option value="{{ $otro->id }}" {{ $u->delegado_a_id == $otro->id ? 'selected' : '' }}>Delegar permisos a: {{ $otro->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </template>

                                    <button type="submit" class="px-3 py-1.5 bg-[#34287d] hover:bg-indigo-900 text-white font-bold rounded-xl">
                                        Guardar estado
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" @click="editUser = true" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-500 text-amber-800 hover:text-white rounded-lg font-bold transition">
                                        Editar
                                    </button>
                                    @if($u->id !== Auth::id())
                                        <form method="POST" action="{{ route('usuarios.estado', $u) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-2.5 py-1 bg-slate-200 hover:bg-slate-300 rounded-lg font-bold">
                                                {{ $u->estado_usuario === 'HABILITADO' ? 'Deshabilitar' : 'Habilitar' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                <!-- Modal Editar Colaborador -->
                                <div x-show="editUser" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4 text-left">
                                    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
                                        <div class="flex justify-between items-center border-b pb-3">
                                            <h3 class="font-bold text-base text-indigo-950">Editar Colaborador</h3>
                                            <button type="button" @click="editUser = false" class="text-slate-400 font-bold">✕</button>
                                        </div>
                                        <form method="POST" action="{{ route('usuarios.update', $u) }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                            @csrf
                                            @method('PUT')
                                            <div>
                                                <label class="font-semibold">RUT *</label>
                                                <input type="text" name="rut" value="{{ $u->rut }}" required class="w-full mt-1 rounded-xl border-slate-300">
                                            </div>
                                            <div>
                                                <label class="font-semibold">Rol / Privilegios *</label>
                                                <select name="rol_id" class="w-full mt-1 rounded-xl border-slate-300">
                                                    @foreach($roles as $rol)
                                                        <option value="{{ $rol->id }}" {{ $u->rol_id == $rol->id ? 'selected' : '' }}>{{ $rol->nombre }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="font-semibold">Nombre Completo *</label>
                                                <input type="text" name="name" value="{{ $u->name }}" required class="w-full mt-1 rounded-xl border-slate-300">
                                            </div>
                                            <div>
                                                <label class="font-semibold">Correo Acceso *</label>
                                                <input type="email" name="email" value="{{ $u->email }}" required class="w-full mt-1 rounded-xl border-slate-300">
                                            </div>
                                            <div>
                                                <label class="font-semibold">Teléfono</label>
                                                <input type="text" name="telefono" value="{{ $u->telefono }}" class="w-full mt-1 rounded-xl border-slate-300">
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="font-semibold">Nueva Contraseña (dejar vacío para mantener la actual)</label>
                                                <input type="password" name="password" placeholder="••••••••" class="w-full mt-1 rounded-xl border-slate-300">
                                            </div>
                                            <div class="sm:col-span-2 flex justify-end gap-2 pt-2">
                                                <button type="button" @click="editUser = false" class="px-4 py-2 bg-slate-200 rounded-xl font-semibold">Cancelar</button>
                                                <button type="submit" class="px-5 py-2 bg-[#34287d] text-white rounded-xl font-bold">Guardar Cambios</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">No se encontraron colaboradores con los filtros aplicados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Modal Nuevo Usuario -->
        <div x-show="openUser" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
                <div class="flex justify-between items-center border-b pb-3">
                    <h3 class="font-bold text-lg text-indigo-950">Registrar Nuevo Colaborador</h3>
                    <button type="button" @click="openUser = false" class="text-slate-400 font-bold">✕</button>
                </div>
                <form method="POST" action="{{ route('usuarios.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold">RUT *</label>
                        <input type="text" name="rut" required placeholder="18.234.567-8" class="w-full mt-1 rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="text-xs font-semibold">Rol / Perfil *</label>
                        <select name="rol_id" class="w-full mt-1 rounded-xl border-slate-300">
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-xs font-semibold">Nombre Completo *</label>
                        <input type="text" name="name" required class="w-full mt-1 rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="text-xs font-semibold">Correo Acceso *</label>
                        <input type="email" name="email" required class="w-full mt-1 rounded-xl border-slate-300">
                    </div>
                    <div>
                        <label class="text-xs font-semibold">Teléfono</label>
                        <input type="text" name="telefono" placeholder="+56 9..." class="w-full mt-1 rounded-xl border-slate-300">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-xs font-semibold">Contraseña Inicial *</label>
                        <input type="password" name="password" value="12345678" required class="w-full mt-1 rounded-xl border-slate-300">
                    </div>
                    <div class="sm:col-span-2 flex justify-end gap-2 pt-2">
                        <button type="button" @click="openUser = false" class="px-4 py-2 bg-slate-200 rounded-xl text-xs font-semibold">Cancelar</button>
                        <button type="submit" class="px-5 py-2 bg-[#34287d] text-white rounded-xl text-xs font-bold">Guardar Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>