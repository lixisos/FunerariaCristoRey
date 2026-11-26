<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Memorial — {{ $expediente->fallecido->nombre_completo }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 antialiased">
    <header class="bg-indigo-950 text-white py-8 px-4 text-center shadow-md">
        <a href="{{ route('publico.catalogo') }}" class="text-xs text-violet-300 hover:underline">← Volver al Portal Funeraria Cristo Rey</a>
        <p class="text-xs uppercase tracking-widest text-violet-300 mt-4">En Memoria de</p>
        <h1 class="text-3xl font-extrabold mt-1">{{ $expediente->fallecido->nombre_completo }}</h1>
        <p class="text-sm text-indigo-200 mt-2">
            Velatorio: {{ $expediente->lugar_velatorio }} | Cortejo: {{ $expediente->fecha_cortejo->format('d/m/Y H:i') }} hrs
        </p>
    </header>

    <main class="max-w-3xl mx-auto py-8 px-4 space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-lg bg-emerald-50 border-l-4 border-emerald-600 text-emerald-900 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <!-- Formulario para Firmar Libro de Condolencias -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 space-y-4">
            <h2 class="font-bold text-lg text-indigo-950">Dejar un Mensaje de Condolencias</h2>
            <form method="POST" action="{{ route('publico.condolencia.store', $expediente->folio) }}" class="space-y-3 text-sm">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold">Su Nombre o Familia *</label>
                        <input type="text" name="nombre_remitente" required placeholder="Ej: Familia Pérez Soto" class="w-full mt-1 rounded-lg border-slate-300">
                    </div>
                    <div>
                        <label class="text-xs font-semibold">Vínculo / Relación</label>
                        <input type="text" name="relacion" placeholder="Ej: Amigos, Colegas, Vecinos" class="w-full mt-1 rounded-lg border-slate-300">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold">Mensaje para la Familia *</label>
                    <textarea name="mensaje" rows="3" required placeholder="Escriba sus palabras de apoyo..." class="w-full mt-1 rounded-lg border-slate-300"></textarea>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-indigo-700 hover:bg-indigo-800 text-white font-bold rounded-lg text-xs">
                    Publicar Condolencia
                </button>
            </form>
        </div>

        <!-- Mensajes Publicados -->
        <div class="space-y-3">
            <h3 class="font-bold text-indigo-950">Mensajes Recibidos ({{ $expediente->condolencias->count() }})</h3>
            @forelse($expediente->condolencias as $cond)
                <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-bold text-indigo-900 text-sm">{{ $cond->nombre_remitente }}</span>
                        <span class="text-xs text-slate-400">{{ $cond->relacion }} • {{ $cond->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-slate-700 italic">"{{ $cond->mensaje }}"</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">Aún no hay mensajes registrados. Sea el primero en acompañar a la familia.</p>
            @endforelse
        </div>
    </main>
</body>
</html>