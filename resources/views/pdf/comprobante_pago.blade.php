<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 13px; }
        .card { border: 2px solid #312e81; border-radius: 8px; padding: 20px; }
        .top { background: #312e81; color: #fff; padding: 14px; border-radius: 6px; margin-bottom: 16px; }
        .monto { font-size: 20px; font-weight: bold; color: #047857; background: #ecfdf5; padding: 10px; border-radius: 6px; text-align: center; margin: 16px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 6px; border-bottom: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="top">
            <strong>FUNERARIA CRISTO REY — COMPROBANTE DE INGRESO FOLIADO</strong><br>
            <span>Folio Comprobante: <strong>{{ $abono->folio_comprobante }}</strong></span>
        </div>

        <table>
            <tr><td><strong>Fecha y Hora Emisión:</strong></td><td>{{ $abono->fecha_pago->format('d/m/Y H:i') }} hrs</td></tr>
            <tr><td><strong>Expediente Asociado:</strong></td><td>{{ $abono->expediente->folio }}</td></tr>
            <tr><td><strong>Recibido de (Contratante):</strong></td><td>{{ $abono->expediente->cliente->nombre_completo }} ({{ $abono->expediente->cliente->rut }})</td></tr>
            <tr><td><strong>Servicio Fallecido(a):</strong></td><td>{{ $abono->expediente->fallecido->nombre_completo }}</td></tr>
            <tr><td><strong>Medio de Pago:</strong></td><td>{{ \App\Support\Etiquetas::texto($abono->metodo_pago) }}</td></tr>
            <tr><td><strong>Glosa / Observación:</strong></td><td>{{ $abono->observacion }}</td></tr>
        </table>

        <div class="monto">
            MONTO ABONADO: ${{ number_format($abono->monto_abonado, 0, ',', '.') }} CLP
        </div>

        <p style="font-size: 12px;">
            <strong>Saldo Pendiente Actualizado del Expediente:</strong> ${{ number_format($abono->expediente->saldo_pendiente, 0, ',', '.') }} CLP
        </p>
    </div>
</body>
</html>