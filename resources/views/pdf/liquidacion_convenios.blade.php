<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 12px; }
        .header { background: #231b56; color: #fff; padding: 14px; border-radius: 6px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
        th { background: #eef2ff; color: #1e1b4b; }
    </style>
</head>
<body>
    <div class="header">
        <strong>FUNERARIA CRISTO REY — LIQUIDACIÓN DE CONVENIO PREVISIONAL</strong><br>
        <span>Expediente: {{ $expediente->folio }} | Fecha: {{ now()->format('d/m/Y') }}</span>
    </div>

    <table>
        <tr><th width="35%">Fallecido(a)</th><td>{{ $expediente->fallecido->nombre_completo }} (RUT: {{ $expediente->fallecido->rut ?? 'S/I' }})</td></tr>
        <tr><th>Contratante / Solicitante</th><td>{{ $expediente->cliente->nombre_completo }} (RUT: {{ $expediente->cliente->rut }})</td></tr>
        <tr><th>Institución / Convenio</th><td>{{ $expediente->convenio->nombre ?? 'Servicio Particular' }}</td></tr>
        <tr><th>Urna Seleccionada</th><td>{{ $expediente->urna->modelo }} ({{ $expediente->urna->codigo_sku }})</td></tr>
        <tr><th>Valor Total Servicio</th><td>${{ number_format($expediente->valor_total_servicio, 0, ',', '.') }} CLP</td></tr>
        <tr><th>Monto Cubierto por Convenio</th><td><strong>${{ number_format($expediente->monto_cobertura_convenio, 0, ',', '.') }} CLP</strong></td></tr>
        <tr><th>Saldo Diferencia Familia</th><td>${{ number_format($expediente->saldo_pendiente, 0, ',', '.') }} CLP</td></tr>
    </table>

    <p style="margin-top: 40px; font-size: 11px;">
        Certificado emitido para tramitación previsional ante IPS / AFP / Caja de Previsión correspondiente.<br>
        <strong>Funeraria Cristo Rey — Calle Blanco #412, Temuco.</strong>
    </p>
</body>
</html>