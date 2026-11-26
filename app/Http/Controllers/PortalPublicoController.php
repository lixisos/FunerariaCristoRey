<?php

namespace App\Http\Controllers;

use App\Models\Condolencia;
use App\Models\Expediente;
use App\Models\Urna;
use Illuminate\Http\Request;

class PortalPublicoController extends Controller
{
    public function catalogo(Request $request)
    {
        $query = Urna::where('estado_urna', 'HABILITADO')
            ->where('reservada_en_vida', false);

        if ($request->filled('categoria_tamano')) {
            $query->where('categoria_tamano', $request->categoria_tamano);
        }

        if ($request->filled('acabado_tallado')) {
            $query->where('acabado_tallado', $request->acabado_tallado);
        }

        $urnas = $query->orderBy('id')->get();

        $serviciosActivos = Expediente::with('fallecido')
            ->whereIn('estado_expediente', ['ABIERTO', 'EN_PROCESO'])
            ->latest()
            ->take(6)
            ->get();

        $condolenciasDestacadas = Condolencia::with('expediente.fallecido')
            ->where('aprobado', true)
            ->latest()
            ->take(4)
            ->get();

        return view('publico.catalogo', compact('urnas', 'serviciosActivos', 'condolenciasDestacadas'));
    }

    public function memorial(string $folio)
    {
        $expediente = Expediente::with(['fallecido', 'condolencias' => fn ($q) => $q->where('aprobado', true)->latest()])
            ->where('folio', $folio)
            ->firstOrFail();

        return view('publico.memorial', compact('expediente'));
    }

    public function guardarCondolencia(Request $request, string $folio)
    {
        $expediente = Expediente::where('folio', $folio)->firstOrFail();

        $data = $request->validate([
            'nombre_remitente' => 'required|string|max:120',
            'relacion' => 'nullable|string|max:80',
            'mensaje' => 'required|string|max:1000',
        ]);

        Condolencia::create([
            'expediente_id' => $expediente->id,
            'nombre_remitente' => $data['nombre_remitente'],
            'relacion' => $data['relacion'] ?? 'Acompañante',
            'mensaje' => $data['mensaje'],
            'aprobado' => true,
        ]);

        return back()->with('success', 'Su mensaje de condolencias ha sido publicado para la familia.');
    }

    public function detalleUrna(string $codigo_sku)
    {
        $urna = Urna::where('codigo_sku', strtoupper(trim($codigo_sku)))
            ->where('estado_urna', 'HABILITADO')
            ->firstOrFail();

        return view('publico.urna_detalle', compact('urna'));
    }
}