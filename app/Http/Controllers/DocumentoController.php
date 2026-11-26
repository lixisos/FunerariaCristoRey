<?php

namespace App\Http\Controllers;

use App\Models\AbonoPago;
use App\Models\BitacoraAuditoria;
use App\Models\Condolencia;
use App\Models\DocumentoExpediente;
use App\Models\Expediente;
use App\Models\Urna;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentoController extends Controller
{
    public function index(Request $request): View
    {
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $query = DocumentoExpediente::with(['expediente.fallecido', 'subidoPor']);

        if ($request->filled('buscar')) {
            $term = '%'.trim((string) $request->buscar).'%';
            $query->where(function ($q) use ($term, $like): void {
                $q->where('nombre_archivo', $like, $term)
                    ->orWhereHas('expediente', function ($e) use ($term, $like): void {
                        $e->where('folio', $like, $term)
                            ->orWhereHas('fallecido', fn ($f) => $f->where('nombre_completo', $like, $term)->orWhere('rut', $like, $term));
                    });
            });
        }

        if ($request->filled('tipo_documento')) {
            $query->where('tipo_documento', $request->tipo_documento);
        }

        if ($request->filled('expediente_id')) {
            $query->where('expediente_id', $request->expediente_id);
        }

        $documentos = $query->latest()->paginate(15)->withQueryString();
        $expedientes = Expediente::with('fallecido')
            ->where('estado_expediente', '!=', 'ANULADO_INACTIVO')
            ->latest()
            ->get();

        return view('documentos.index', compact('documentos', 'expedientes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'expediente_id' => 'required|exists:expedientes,id',
            'tipo_documento' => 'required|in:CERTIFICADO_DEFUNCION,PASE_SEPULTACION,CARNET_CONTRATANTE,CARNET_FALLECIDO,OFICIO_FISCALIA_SML,RESOLUCION_SEREMI,CONTRATO_FIRMADO,HOJA_MEDIDAS_CEMENTERIO,OTRO',
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $expediente = Expediente::findOrFail($data['expediente_id']);
        $file = $request->file('archivo');
        $ruta = $file->store("expedientes/{$expediente->folio}", 'public');

        DocumentoExpediente::create([
            'expediente_id' => $expediente->id,
            'tipo_documento' => $data['tipo_documento'],
            'nombre_archivo' => $file->getClientOriginalName(),
            'ruta_s3' => $ruta,
            'validado' => true,
            'subido_por_id' => Auth::id(),
        ]);

        return back()->with('success', "Documento cargado y asociado al expediente {$expediente->folio}.");
    }

    public function destroy(DocumentoExpediente $documento): RedirectResponse
    {
        $nombre = $documento->nombre_archivo;
        $ruta = (string) $documento->ruta_s3;

        $documento->delete();

        if ($ruta !== '' && ! str_starts_with($ruta, 's3://') && ! str_contains($ruta, '..')) {
            if (Storage::disk('public')->exists($ruta)) {
                Storage::disk('public')->delete($ruta);
            }
        }

        return back()->with('success', "Documento \"{$nombre}\" eliminado correctamente.");
    }

    public function centroPdf(Request $request): View
    {
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $buscar = trim((string) $request->buscar);

        $expQuery = Expediente::with(['cliente', 'fallecido', 'convenio'])
            ->where('estado_expediente', '!=', 'ANULADO_INACTIVO');

        $urnaQuery = Urna::where('estado_urna', 'HABILITADO');

        $abonoQuery = AbonoPago::with(['expediente.cliente'])
            ->where('estado_pago', 'VALIDO');

        if ($buscar !== '') {
            $term = "%{$buscar}%";

            $expQuery->where(function ($q) use ($term, $like): void {
                $q->where('folio', $like, $term)
                    ->orWhereHas('cliente', fn ($c) => $c->where('nombre_completo', $like, $term)->orWhere('rut', $like, $term))
                    ->orWhereHas('fallecido', fn ($f) => $f->where('nombre_completo', $like, $term)->orWhere('rut', $like, $term));
            });

            $urnaQuery->where(function ($q) use ($term, $like): void {
                $q->where('modelo', $like, $term)->orWhere('codigo_sku', $like, $term);
            });

            $abonoQuery->where(function ($q) use ($term, $like): void {
                $q->where('folio_comprobante', $like, $term)
                    ->orWhereHas('expediente', function ($e) use ($term, $like): void {
                        $e->where('folio', $like, $term)
                            ->orWhereHas('cliente', fn ($c) => $c->where('nombre_completo', $like, $term));
                    });
            });
        }

        $expedientes = $expQuery->latest()->paginate(10, ['*'], 'page_exp')->withQueryString();
        $urnas = $urnaQuery->orderBy('modelo')->get();
        $abonos = $abonoQuery->latest('fecha_pago')->paginate(8, ['*'], 'page_comp')->withQueryString();

        return view('documentos.centro_pdf', compact('expedientes', 'urnas', 'abonos'));
    }

    public function condolencias(Request $request): View
    {
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $query = Condolencia::with(['expediente.fallecido']);

        if ($request->filled('buscar')) {
            $term = '%'.trim((string) $request->buscar).'%';
            $query->where(function ($q) use ($term, $like): void {
                $q->where('nombre_remitente', $like, $term)
                    ->orWhere('mensaje', $like, $term)
                    ->orWhereHas('expediente', function ($e) use ($term, $like): void {
                        $e->where('folio', $like, $term)
                            ->orWhereHas('fallecido', fn ($f) => $f->where('nombre_completo', $like, $term));
                    });
            });
        }

        if ($request->filled('expediente_id')) {
            $query->where('expediente_id', $request->expediente_id);
        }

        if ($request->estado === 'visible') {
            $query->where('aprobado', true);
        } elseif ($request->estado === 'oculto') {
            $query->where('aprobado', false);
        }

        $condolencias = $query->latest()->paginate(15)->withQueryString();

        $expedientes = Expediente::with('fallecido')
            ->where('estado_expediente', '!=', 'ANULADO_INACTIVO')
            ->withCount([
                'condolencias',
                'condolencias as condolencias_aprobadas_count' => fn ($q) => $q->where('aprobado', true),
            ])
            ->latest()
            ->take(30)
            ->get();

        return view('documentos.condolencias', compact('condolencias', 'expedientes'));
    }

    public function storeCondolencia(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'expediente_id' => 'required|exists:expedientes,id',
            'nombre_remitente' => 'required|string|max:120',
            'relacion' => 'nullable|string|max:80',
            'mensaje' => 'required|string|max:600',
        ]);

        Condolencia::create([
            'expediente_id' => $data['expediente_id'],
            'nombre_remitente' => $data['nombre_remitente'],
            'relacion' => $data['relacion'] ?: 'Familiar / Amistad',
            'mensaje' => $data['mensaje'],
            'aprobado' => true,
        ]);

        return back()->with('success', 'Dedicatoria registrada y publicada en el Libro de Condolencias.');
    }

    public function toggleCondolencia(Condolencia $condolencia): RedirectResponse
    {
        $condolencia->update(['aprobado' => ! $condolencia->aprobado]);

        return back()->with('success', $condolencia->aprobado
            ? 'Mensaje aprobado y visible en el portal familiar.'
            : 'Mensaje ocultado del portal público.');
    }

    public function destroyCondolencia(Condolencia $condolencia): RedirectResponse
    {
        $remitente = $condolencia->nombre_remitente;
        $condolencia->delete();

        return back()->with('success', "Condolencia de \"{$remitente}\" eliminada correctamente.");
    }

    public function condolenciasPdf(Expediente $expediente): Response
    {
        $expediente->load(['fallecido', 'cliente', 'condolencias' => fn ($q) => $q->where('aprobado', true)->latest()]);

        BitacoraAuditoria::create([
            'modulo' => 'Documentos / Condolencias',
            'tabla_afectada' => 'expedientes',
            'registro_id' => $expediente->id,
            'accion' => 'EMISION_PDF',
            'valores_nuevos_json' => ['documento' => 'Libro Digital de Condolencias', 'folio' => $expediente->folio],
            'ip_origen' => request()->ip(),
            'usuario_id' => Auth::id(),
        ]);

        $pdf = Pdf::loadView('pdf.libro_condolencias', compact('expediente'));

        return $pdf->stream("Libro_Condolencias_{$expediente->folio}.pdf");
    }
}