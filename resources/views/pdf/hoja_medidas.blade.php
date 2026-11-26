<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 13px; }
        .header { background: #1e1b4b; color: #fff; padding: 18px; border-radius: 6px; }
        .title { font-size: 18px; font-weight: bold; margin: 0; }
        .subtitle { font-size: 11px; color: #c4b5fd; margin-top: 4px; }
        .box { border: 1px solid #cbd5e1; border-radius: 6px; padding: 16px; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #cbd5e1; padding: 10px; text-align: left; }
        th { background: #f1f5f9; font-weight: bold; }
        .medida-destacada { font-size: 16px; font-weight: bold; color: #312e81; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">FUNERARIA CRISTO REY — TEMUCO</p>
        <p class="subtitle">HOJA TÉCNICA DE DIMENSIONES DE URNA PARA ADMINISTRACIÓN DE CEMENTERIO / NICHOS</p>
    </div>

    <div class="box">
        <p><strong>Código SKU Urna:</strong> {{ $urna->codigo_sku }}</p>
        <p><strong>Modelo:</strong> {{ $urna->modelo }}</p>
        <p><strong>Categoría Sanitaria (Seremi):</strong> {{ \App\Support\Etiquetas::texto($urna->categoria_tamano) }}</p>
        <p><strong>Acabado y Color:</strong> {{ \App\Support\Etiquetas::texto($urna->acabado_tallado) }} — {{ $urna->color }}</p>

        <table>
            <thead>
                <tr>
                    <th>Largo Exterior (cm)</th>
                    <th>Ancho Exterior (cm)</th>
                    <th>Alto Exterior (cm)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="medida-destacada">{{ $urna->largo_cm }} cm</td>
                    <td class="medida-destacada">{{ $urna->ancho_cm }} cm</td>
                    <td class="medida-destacada">{{ $urna->alto_cm }} cm</td>
                </tr>
            </tbody>
        </table>

        <p style="margin-top: 20px; font-size: 11px; color: #475569;">
            Documento emitido automáticamente por el Sistema Integral de Funeraria Cristo Rey el {{ now()->format('d/m/Y H:i') }} hrs para verificación de dimensiones en fosa, bóveda o nicho de cementerio.
        </p>
    </div>
</body>
</html>