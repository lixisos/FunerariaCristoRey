<x-app-layout>
    <x-slot name="header">
        <div x-data="{ openCli: {{ $errors->any() ? 'true' : 'false' }} }" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-bold text-xl text-indigo-950">Clientes y Familias — Historial por RUT</h2>
                <p class="text-xs text-slate-500">Consulta servicios anteriores, convenios utilizados y estado de pagos cuando vuelve una familia</p>
            </div>
            <button @click="openCli = true" class="px-4 py-2 bg-[#34287d] hover:bg-[#231b59] text-white text-xs sm:text-sm font-bold rounded-xl shadow">
                + Registrar / Actualizar Cliente
            </button>

            <div x-show="openCli" x-cloak class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
                    <div class="flex justify-between items-center border-b pb-3">
                        <h3 class="font-bold text-lg text-indigo-950">Ficha de Cliente Contratante</h3>
                        <button @click="openCli = false" class="text-slate-400 hover:text-slate-700 font-bold">✕</button>
                    </div>

                    @if($errors->any())
                        <div class="p-3 rounded-xl bg-rose-50 border-l-4 border-rose-600 text-rose-900 text-xs space-y-1">
                            <p class="font-bold">Revisa los siguientes datos:</p>
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('clientes.store') }}" class="space-y-3 text-sm">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs font-semibold">RUT *</label>
                                <input type="text" name="rut" value="{{ old('rut') }}" required maxlength="12" placeholder="12.345.678-9"
                                       oninput="formatearRutCliente(this)" onblur="validarRutCliente(this)"
                                       class="w-full mt-1 rounded-xl border-slate-300">
                                <p id="err_rut_cliente" class="hidden text-[11px] text-rose-600 font-semibold mt-1"></p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold">Teléfono *</label>
                                <input type="text" name="telefono" value="{{ old('telefono') }}" required maxlength="16" placeholder="+56 9 8765 4321" class="w-full mt-1 rounded-xl border-slate-300">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-semibold">Nombre Completo *</label>
                            <input type="text" name="nombre_completo" value="{{ old('nombre_completo') }}" required minlength="5" placeholder="Ej: María Elena Riquelme Castro" class="w-full mt-1 rounded-xl border-slate-300">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs font-semibold">Dirección *</label>
                                <input type="text" name="direccion" value="{{ old('direccion') }}" required minlength="5" placeholder="Ej: Av. Alemania 0845" class="w-full mt-1 rounded-xl border-slate-300">
                            </div>
                            <div>
                                <label class="text-xs font-semibold">Comuna *</label>
                                <input type="text" name="comuna" value="{{ old('comuna', 'Temuco') }}" required class="w-full mt-1 rounded-xl border-slate-300">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-semibold">Correo Electrónico</label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="familia@correo.cl" class="w-full mt-1 rounded-xl border-slate-300">
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="openCli = false" class="px-4 py-2 bg-slate-200 rounded-xl text-xs font-semibold">Cancelar</button>
                            <button type="submit" class="px-5 py-2 bg-[#34287d] text-white rounded-xl text-xs font-bold">Guardar Cliente</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>

    <!-- Buscador y Filtros -->
    <form method="GET" action="{{ route('clientes.index') }}" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200/80 grid grid-cols-1 sm:grid-cols-12 gap-3 text-sm">
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar familia por RUT, nombre o teléfono..." class="sm:col-span-6 rounded-xl border-slate-300">

        <select name="filtro" class="sm:col-span-4 rounded-xl border-slate-300">
            <option value="">Todas las familias</option>
            <option value="con_saldo" {{ request('filtro') === 'con_saldo' ? 'selected' : '' }}>Con saldo pendiente por cobrar</option>
            <option value="recurrentes" {{ request('filtro') === 'recurrentes' ? 'selected' : '' }}>Familias recurrentes (2+ servicios)</option>
            <option value="sin_servicios" {{ request('filtro') === 'sin_servicios' ? 'selected' : '' }}>Sin servicios asociados</option>
        </select>

        <div class="sm:col-span-2 flex gap-2">
            <button type="submit" class="flex-1 px-4 py-2 bg-[#34287d] text-white font-bold rounded-xl text-xs">Filtrar</button>
            @if(request('buscar') || request('filtro'))
                <a href="{{ route('clientes.index') }}" class="px-3 py-2 bg-slate-200 rounded-xl font-semibold text-xs flex items-center">Limpiar</a>
            @endif
        </div>
    </form>

    <!-- Listado con Historial Expandido -->
    <div class="space-y-4">
        @forelse($clientes as $cli)
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-base text-indigo-950">{{ $cli->nombre_completo }}</h3>
                            <span class="px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-xs font-bold">{{ $cli->rut }}</span>
                        </div>
                        <p class="text-xs text-slate-500">📞 {{ $cli->telefono }} &nbsp;|&nbsp; 📍 {{ $cli->direccion }}, {{ $cli->comuna }} &nbsp;|&nbsp; ✉️ {{ $cli->email ?? 'Sin correo' }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-xl bg-violet-100 text-violet-900 text-xs font-bold">
                            {{ $cli->expedientes->count() }} servicio(s) en historial
                        </span>
                        @if(Auth::user()->tieneRol(['admin', 'coordinador']))
                            <form method="POST" action="{{ route('clientes.destroy', $cli) }}" onsubmit="return confirm('¿Eliminar ficha de {{ $cli->nombre_completo }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white rounded-xl text-xs font-bold transition">
                                    Eliminar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                @if($cli->expedientes->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="text-slate-400">
                                <tr>
                                    <th class="py-1.5">Expediente</th>
                                    <th class="py-1.5">Fallecido</th>
                                    <th class="py-1.5">Convenio Utilizado</th>
                                    <th class="py-1.5">Total Servicio</th>
                                    <th class="py-1.5">Saldo Pendiente</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($cli->expedientes as $exp)
                                    <tr>
                                        <td class="py-2 font-bold text-indigo-800">
                                            <a href="{{ route('expedientes.show', $exp) }}" class="hover:underline">{{ $exp->folio }}</a>
                                        </td>
                                        <td class="py-2 font-semibold">{{ $exp->fallecido->nombre_completo ?? 'N/A' }}</td>
                                        <td class="py-2">{{ $exp->convenio->nombre ?? 'Particular' }}</td>
                                        <td class="py-2 font-semibold">${{ number_format($exp->valor_total_servicio, 0, ',', '.') }}</td>
                                        <td class="py-2 font-bold {{ $exp->saldo_pendiente > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                                            ${{ number_format($exp->saldo_pendiente, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-2xl p-8 text-center text-sm text-slate-500 border border-slate-200/80">
                No se encontraron clientes con los filtros seleccionados.
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $clientes->links() }}
    </div>

    <script>
        function formatearRutCliente(input) {
            let v = input.value.replace(/[^0-9kK]/g, '').toUpperCase();
            if (v.length <= 1) {
                input.value = v;
                return;
            }
            const cuerpo = v.slice(0, -1).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            const dv = v.slice(-1);
            input.value = `${cuerpo}-${dv}`;
        }

        function validarRutCliente(input) {
            const msg = document.getElementById('err_rut_cliente');
            const limpio = input.value.replace(/[^0-9kK]/g, '').toUpperCase();
            if (limpio.length < 8 || limpio.length > 9 || /^(\d)\1+$/.test(limpio.slice(0, -1))) {
                msg.textContent = 'RUT inválido: debe tener 7 u 8 dígitos y dígito verificador.';
                msg.classList.remove('hidden');
                input.classList.add('border-rose-500');
                return false;
            }
            const cuerpo = limpio.slice(0, -1);
            const dv = limpio.slice(-1);
            let suma = 0, mul = 2;
            for (let i = cuerpo.length - 1; i >= 0; i--) {
                suma += parseInt(cuerpo[i], 10) * mul;
                mul = mul === 7 ? 2 : mul + 1;
            }
            const resto = 11 - (suma % 11);
            const esperado = resto === 11 ? '0' : (resto === 10 ? 'K' : String(resto));
            if (dv !== esperado) {
                msg.textContent = `Dígito verificador incorrecto (debería terminar en -${esperado}).`;
                msg.classList.remove('hidden');
                input.classList.add('border-rose-500');
                return false;
            }
            msg.classList.add('hidden');
            input.classList.remove('border-rose-500');
            return true;
        }
    </script>
</x-app-layout>