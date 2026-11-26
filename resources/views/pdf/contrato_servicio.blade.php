<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 12px; line-height: 1.5; }
        .header { background: #1e1b4b; color: #fff; padding: 16px; }
        h1 { font-size: 17px; margin: 0; }
        .section { margin-top: 14px; border: 1px solid #cbd5e1; padding: 12px; }
        .section-title { font-weight: bold; color: #312e81; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 4px 6px; }
        .firmas { margin-top: 50px; width: 100%; }
        .firmas td { width: 50%; text-align: center; border-top: 1px solid #334155; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CONTRATO DE PRESTACIÓN DE SERVICIOS FUNERARIOS</h1>
        <div>Funeraria Cristo Rey — Folio Oficial: <strong>{{ $expediente->folio }}</strong> | Fecha: {{ now()->format('d/m/Y') }}</div>
    </div>

    <div class="section">
        <div class="section-title">1. IDENTIFICACIÓN DEL CONTRATANTE Y FALLECIDO</div>
        <table>
            <tr><td><strong>Contratante:</strong> {{ $expediente->cliente->nombre_completo }}</td><td><strong>RUT:</strong> {{ $expediente->cliente->rut }}</td></tr>
            <tr><td><strong>Domicilio:</strong> {{ $expediente->cliente->direccion }}, {{ $expediente->cliente->comuna }}</td><td><strong>Teléfono:</strong> {{ $expediente->cliente->telefono }}</td></tr>
            <tr><td><strong>Fallecido(a):</strong> {{ $expediente->fallecido->nombre_completo }}</td><td><strong>RUT:</strong> {{ $expediente->fallecido->rut ?? 'No registrado' }}</td></tr>
            <tr><td colspan="2"><strong>Lugar y Fecha Defunción:</strong> {{ $expediente->fallecido->lugar_fallecimiento }} ({{ $expediente->fallecido->fecha_fallecimiento->format('d/m/Y H:i') }})</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">2. DETALLE LOGÍSTICO Y URNA CONTRATADA</div>
        <table>
            <tr><td><strong>Tipo Servicio:</strong> {{ \App\Support\Etiquetas::texto($expediente->tipo_servicio) }}</td><td><strong>Urna:</strong> {{ $expediente->urna->modelo }} ({{ $expediente->urna->codigo_sku }})</td></tr>
            <tr><td><strong>Velatorio:</strong> {{ $expediente->lugar_velatorio }}</td><td><strong>Inicio:</strong> {{ $expediente->fecha_inicio_velatorio->format('d/m/Y H:i') }}</td></tr>
            <tr><td><strong>Cementerio:</strong> {{ $expediente->cementerio_destino }}</td><td><strong>Cortejo:</strong> {{ $expediente->fecha_cortejo->format('d/m/Y H:i') }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">3. LIQUIDACIÓN FINANCIERA Y CONVENIO PREVISIONAL</div>
        <table>
            <tr><td><strong>Convenio Aplicado:</strong></td><td>{{ $expediente->convenio->nombre ?? 'Particular' }}</td></tr>
            <tr><td><strong>Valor Total del Servicio:</strong></td><td>${{ number_format($expediente->valor_total_servicio, 0, ',', '.') }} CLP</td></tr>
            <tr><td><strong>Bonificación Previsional (IPS / AFP):</strong></td><td>- ${{ number_format($expediente->monto_cobertura_convenio, 0, ',', '.') }} CLP</td></tr>
            <tr><td><strong>Saldo Diferencia a Pagar:</strong></td><td><strong>${{ number_format($expediente->saldo_pendiente, 0, ',', '.') }} CLP</strong></td></tr>
        </table>
    </div>

    <table class="firmas">
        <tr>
            <td>Firma Contratante<br>{{ $expediente->cliente->nombre_completo }}<br>RUT: {{ $expediente->cliente->rut }}</td>
            <td>Timbre y Firma<br>Funeraria Cristo Rey<br>Temuco, Región de La Araucanía</td>
        </tr>
    </table>
</body>
</html>