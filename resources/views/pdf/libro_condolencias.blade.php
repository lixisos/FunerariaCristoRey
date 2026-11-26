<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 12px; }
        .portada { border: 2px solid #312e81; padding: 18px; border-radius: 8px; text-align: center; background: #f8fafc; margin-bottom: 18px; }
        .dedicatoria { border-left: 3px solid #4f46e5; padding: 8px 12px; margin-bottom: 12px; background: #ffffff; }
    </style>
</head>
<body>
    <div class="portada">
        <p style="font-size: 11px; color: #4338ca; text-transform: uppercase;">Funeraria Cristo Rey — Libro Conmemorativo</p>
        <h2 style="margin: 6px 0; color: #1e1b4b;">En Memoria de {{ $expediente->fallecido->nombre_completo }}</h2>
        <p style="font-size: 11px; color: #475569;">Expediente {{ $expediente->folio }} • Cementerio {{ $expediente->cementerio_destino }}</p>
    </div>

    @forelse($expediente->condolencias as $cond)
        <div class="dedicatoria">
            <p style="margin: 0 0 4px 0;">"{{ $cond->mensaje }}"</p>
            <strong style="font-size: 11px; color: #312e81;">— {{ $cond->nombre_remitente }} ({{ $cond->relacion }})</strong>
        </div>
    @empty
        <p style="text-align: center; color: #64748b;">Aún no se registran mensajes de condolencias aprobados.</p>
    @endforelse
</body>
</html>