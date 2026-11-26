<?php

namespace App\Http\Controllers;

use App\Models\BitacoraAuditoria;
use App\Models\Expediente;
use App\Models\MovimientoInventario;
use App\Models\Urna;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UrnaController extends Controller
{
    public function index(Request $request)
    {
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $vista = $request->get('vista', 'inventario');
        $query = Urna::where('estado_urna', 'HABILITADO');

        if ($request->filled('buscar')) {
            $term = '%'.trim((string) $request->buscar).'%';
            $query->where(function ($q) use ($term, $like): void {
                $q->where('modelo', $like, $term)
                    ->orWhere('codigo_sku', $like, $term)
                    ->orWhere('color', $like, $term);
            });
        }

        if ($request->filled('categoria_tamano')) {
            $query->where('categoria_tamano', $request->categoria_tamano);
        }

        if ($request->filled('acabado_tallado')) {
            $query->where('acabado_tallado', $request->acabado_tallado);
        }

        if ($request->filled('proveedor_origen')) {
            $query->where('proveedor_origen', $request->proveedor_origen);
        }

        if ($request->ubicacion === 'salon') {
            $query->where('stock_salon', '>', 0);
        } elseif ($request->ubicacion === 'taller') {
            $query->where('stock_taller', '>', 0);
        } elseif ($request->ubicacion === 'reservada') {
            $query->where('reservada_en_vida', true);
        }

        $urnas = $query->orderBy('modelo')->paginate(8)->withQueryString();

        // Alertas de stock bajo (<= 2 unidades totales)
        $alertasStock = Urna::where('estado_urna', 'HABILITADO')
            ->whereRaw('(stock_salon + stock_taller) <= 2')
            ->orderByRaw('(stock_salon + stock_taller) asc')
            ->get();

        $stockTotales = DB::table('urnas')
            ->whereNull('deleted_at')
            ->where('estado_urna', 'HABILITADO')
            ->selectRaw('COALESCE(SUM(stock_salon), 0) AS salon, COALESCE(SUM(stock_taller), 0) AS taller')
            ->first();

        $totalSalon = (int) ($stockTotales->salon ?? 0);
        $totalTaller = (int) ($stockTotales->taller ?? 0);

        return view('urnas.index', compact(
            'urnas',
            'vista',
            'alertasStock',
            'totalSalon',
            'totalTaller'
        ));
    }

    public function bodega(Request $request)
    {
        $urnas = Urna::where('estado_urna', 'HABILITADO')->orderBy('modelo')->get();

        $movQuery = MovimientoInventario::with(['urna', 'registradoPor']);

        if ($request->filled('urna_id')) {
            $movQuery->where('urna_id', $request->urna_id);
        }

        if ($request->filled('tipo_movimiento')) {
            $movQuery->where('tipo_movimiento', $request->tipo_movimiento);
        }

        $movimientos = $movQuery->latest()->paginate(20)->withQueryString();

        $totalSalon = $urnas->sum('stock_salon');
        $totalTaller = $urnas->sum('stock_taller');

        return view('urnas.bodega', compact(
            'urnas',
            'movimientos',
            'totalSalon',
            'totalTaller'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo_sku' => 'required|string|max:30|unique:urnas,codigo_sku',
            'modelo' => 'required|string|max:120',
            'descripcion' => 'nullable|string|max:1000',
            'categoria_tamano' => 'required|in:ESTANDAR,NINO_SEREMI,ESPECIAL_XL',
            'acabado_tallado' => 'required|in:LISA,PALOMA_BIBLIA,MARQUETEADA,PERSONALIZADA',
            'color' => 'required|string|max:50',
            'largo_cm' => 'required|integer|min:30|max:250',
            'ancho_cm' => 'required|integer|min:20|max:120',
            'alto_cm' => 'required|integer|min:20|max:100',
            'precio_compra' => 'nullable|integer|min:0',
            'precio_venta' => 'required|integer|min:0',
            'stock_salon' => 'required|integer|min:0|max:24',
            'stock_taller' => 'required|integer|min:0|max:60',
            'proveedor_origen' => 'required|in:SAN_JAVIER,SANTIAGO',
            'reservada_en_vida' => 'nullable|boolean',
            'rut_reserva_vida' => 'nullable|string|max:15',
            'nombre_reserva_vida' => 'nullable|string|max:120',
            'foto' => 'nullable|image|max:4096',
            'fotos_galeria.*' => 'nullable|image|max:4096',
        ]);

        $salonActual = Urna::where('estado_urna', 'HABILITADO')->sum('stock_salon');
        if (($salonActual + (int) $data['stock_salon']) > 24) {
            return back()->withInput()->with('error', 'Capacidad máxima de Salón Calle Blanco excedida (máximo 24 urnas en exhibición).');
        }

        $fotoUrl = null;
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('urnas', 'public');
            $fotoUrl = '/storage/'.$path;
        }

        $galeria = [];
        if ($request->hasFile('fotos_galeria')) {
            foreach ($request->file('fotos_galeria') as $archivo) {
                $pathExtra = $archivo->store('urnas', 'public');
                $galeria[] = '/storage/'.$pathExtra;
            }
        }

        $urna = DB::transaction(function () use ($data, $request, $fotoUrl, $galeria) {
            $urna = Urna::create([
                'codigo_sku' => strtoupper(trim($data['codigo_sku'])),
                'modelo' => $data['modelo'],
                'descripcion' => $data['descripcion'] ?? null,
                'categoria_tamano' => $data['categoria_tamano'],
                'acabado_tallado' => $data['acabado_tallado'],
                'color' => $data['color'],
                'largo_cm' => $data['largo_cm'],
                'ancho_cm' => $data['ancho_cm'],
                'alto_cm' => $data['alto_cm'],
                'precio_compra' => $data['precio_compra'] ?? 0,
                'precio_venta' => $data['precio_venta'],
                'stock_salon' => $data['stock_salon'],
                'stock_taller' => $data['stock_taller'],
                'proveedor_origen' => $data['proveedor_origen'],
                'reservada_en_vida' => $request->boolean('reservada_en_vida'),
                'rut_reserva_vida' => $request->boolean('reservada_en_vida') ? ($data['rut_reserva_vida'] ?? null) : null,
                'nombre_reserva_vida' => $request->boolean('reservada_en_vida') ? ($data['nombre_reserva_vida'] ?? null) : null,
                'foto_s3_url' => $fotoUrl,
                'galeria_fotos' => $galeria,
                'creado_por_id' => Auth::id(),
            ]);

            $stockInicial = (int) $data['stock_salon'] + (int) $data['stock_taller'];
            if ($stockInicial > 0) {
                MovimientoInventario::create([
                    'urna_id' => $urna->id,
                    'tipo_movimiento' => 'INGRESO_PROVEEDOR',
                    'cantidad' => $stockInicial,
                    'origen' => 'Fábrica '.$urna->proveedor_origen,
                    'destino' => "Salón ({$data['stock_salon']}) / Taller ({$data['stock_taller']})",
                    'observacion' => 'Alta inicial en inventario',
                    'registrado_por_id' => Auth::id(),
                ]);
            }

            return $urna;
        });

        return back()->with('success', "Urna {$urna->modelo} ({$urna->codigo_sku}) registrada correctamente.");
    }

    public function update(Request $request, Urna $urna)
    {
        $data = $request->validate([
            'codigo_sku' => 'required|string|max:30|unique:urnas,codigo_sku,'.$urna->id,
            'modelo' => 'required|string|max:120',
            'descripcion' => 'nullable|string|max:1000',
            'categoria_tamano' => 'required|in:ESTANDAR,NINO_SEREMI,ESPECIAL_XL',
            'acabado_tallado' => 'required|in:LISA,PALOMA_BIBLIA,MARQUETEADA,PERSONALIZADA',
            'color' => 'required|string|max:50',
            'largo_cm' => 'required|integer|min:30|max:250',
            'ancho_cm' => 'required|integer|min:20|max:120',
            'alto_cm' => 'required|integer|min:20|max:100',
            'precio_compra' => 'nullable|integer|min:0',
            'precio_venta' => 'required|integer|min:0',
            'stock_salon' => 'required|integer|min:0|max:24',
            'stock_taller' => 'required|integer|min:0|max:60',
            'proveedor_origen' => 'required|in:SAN_JAVIER,SANTIAGO',
            'reservada_en_vida' => 'nullable|boolean',
            'rut_reserva_vida' => 'nullable|string|max:15',
            'nombre_reserva_vida' => 'nullable|string|max:120',
            'foto' => 'nullable|image|max:4096',
            'fotos_galeria.*' => 'nullable|image|max:4096',
            'eliminar_fotos' => 'nullable|array',
        ]);

        $salonOtros = Urna::where('estado_urna', 'HABILITADO')
            ->where('id', '!=', $urna->id)
            ->sum('stock_salon');

        if (($salonOtros + (int) $data['stock_salon']) > 24) {
            return back()->with('error', 'Capacidad máxima de Salón Calle Blanco excedida (máximo 24 urnas en exhibición).');
        }

        $fotoPrincipal = $urna->foto_s3_url;
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('urnas', 'public');
            $fotoPrincipal = '/storage/'.$path;
        }

        $galeriaActual = is_array($urna->galeria_fotos) ? $urna->galeria_fotos : [];
        $aEliminar = $request->input('eliminar_fotos', []);
        if (! empty($aEliminar)) {
            $galeriaActual = array_values(array_filter($galeriaActual, fn ($url) => ! in_array($url, $aEliminar, true)));
            if ($fotoPrincipal && in_array($fotoPrincipal, $aEliminar, true)) {
                $fotoPrincipal = $galeriaActual[0] ?? null;
            }
        }

        if ($request->hasFile('fotos_galeria')) {
            foreach ($request->file('fotos_galeria') as $archivo) {
                $pathExtra = $archivo->store('urnas', 'public');
                $galeriaActual[] = '/storage/'.$pathExtra;
            }
        }

        $urna->update([
            'codigo_sku' => strtoupper(trim($data['codigo_sku'])),
            'modelo' => $data['modelo'],
            'descripcion' => $data['descripcion'] ?? null,
            'categoria_tamano' => $data['categoria_tamano'],
            'acabado_tallado' => $data['acabado_tallado'],
            'color' => $data['color'],
            'largo_cm' => $data['largo_cm'],
            'ancho_cm' => $data['ancho_cm'],
            'alto_cm' => $data['alto_cm'],
            'precio_compra' => $data['precio_compra'] ?? 0,
            'precio_venta' => $data['precio_venta'],
            'stock_salon' => $data['stock_salon'],
            'stock_taller' => $data['stock_taller'],
            'proveedor_origen' => $data['proveedor_origen'],
            'reservada_en_vida' => $request->boolean('reservada_en_vida'),
            'rut_reserva_vida' => $request->boolean('reservada_en_vida') ? ($data['rut_reserva_vida'] ?? null) : null,
            'nombre_reserva_vida' => $request->boolean('reservada_en_vida') ? ($data['nombre_reserva_vida'] ?? null) : null,
            'foto_s3_url' => $fotoPrincipal,
            'galeria_fotos' => $galeriaActual,
        ]);

        return back()->with('success', "Urna {$urna->modelo} ({$urna->codigo_sku}) actualizada correctamente.");
    }

    public function traspasarSalon(Request $request, Urna $urna)
    {
        $request->validate(['cantidad' => 'required|integer|min:1']);
        $cantidad = (int) $request->cantidad;

        if ($urna->stock_taller < $cantidad) {
            return back()->with('error', 'Stock insuficiente en Taller para realizar el traspaso.');
        }

        $salonActual = Urna::where('estado_urna', 'HABILITADO')->sum('stock_salon');
        if (($salonActual + $cantidad) > 24) {
            return back()->with('error', 'Capacidad máxima de Salón Calle Blanco excedida (máximo 24 urnas en exhibición).');
        }

        DB::transaction(function () use ($urna, $cantidad) {
            $urna->decrement('stock_taller', $cantidad);
            $urna->increment('stock_salon', $cantidad);

            MovimientoInventario::create([
                'urna_id' => $urna->id,
                'tipo_movimiento' => 'TRASPASO_TALLER_SALON',
                'cantidad' => $cantidad,
                'origen' => 'Bodega Taller',
                'destino' => 'Salón Calle Blanco',
                'observacion' => 'Traspaso interno a sala de ventas',
                'registrado_por_id' => Auth::id(),
            ]);
        });

        return back()->with('success', "Se traspasaron {$cantidad} unidad(es) de Taller a Salón Calle Blanco.");
    }

    public function reponerProveedor(Request $request, Urna $urna)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
            'destino' => 'required|in:TALLER,SALON',
            'proveedor' => 'nullable|in:SAN_JAVIER,SANTIAGO',
            'observacion' => 'nullable|string|max:200',
        ]);

        $cantidad = (int) $request->cantidad;
        $proveedor = $request->proveedor ?? $urna->proveedor_origen;

        if ($proveedor === 'SANTIAGO' && $cantidad < 24) {
            return back()->with('error', 'Regla de Proveedor: La fábrica de Santiago exige pedidos por camión completo (mínimo 24 urnas).');
        }

        if ($request->destino === 'SALON') {
            $salonActual = Urna::where('estado_urna', 'HABILITADO')->sum('stock_salon');
            if (($salonActual + $cantidad) > 24) {
                return back()->with('error', 'Capacidad máxima de Salón Calle Blanco excedida (máximo 24 urnas).');
            }
        }

        DB::transaction(function () use ($urna, $cantidad, $request, $proveedor) {
            if ($request->destino === 'SALON') {
                $urna->increment('stock_salon', $cantidad);
            } else {
                $urna->increment('stock_taller', $cantidad);
            }

            MovimientoInventario::create([
                'urna_id' => $urna->id,
                'tipo_movimiento' => 'INGRESO_PROVEEDOR',
                'cantidad' => $cantidad,
                'origen' => 'Fábrica '.$proveedor,
                'destino' => $request->destino === 'SALON' ? 'Salón Calle Blanco' : 'Bodega Taller',
                'observacion' => $request->observacion ?: 'Recepción de lote de proveedor',
                'registrado_por_id' => Auth::id(),
            ]);
        });

        return back()->with('success', "Entrada de {$cantidad} urna(s) registrada desde {$proveedor}.");
    }

    public function registrarSalidaBodega(Request $request, Urna $urna)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
            'ubicacion' => 'required|in:SALON,TALLER',
            'motivo' => 'required|string|max:200',
        ]);

        $cantidad = (int) $request->cantidad;
        $stockDisponible = $request->ubicacion === 'SALON' ? $urna->stock_salon : $urna->stock_taller;

        if ($stockDisponible < $cantidad) {
            return back()->with('error', "Stock insuficiente en {$request->ubicacion} para registrar esa salida.");
        }

        DB::transaction(function () use ($urna, $cantidad, $request) {
            if ($request->ubicacion === 'SALON') {
                $urna->decrement('stock_salon', $cantidad);
            } else {
                $urna->decrement('stock_taller', $cantidad);
            }

            MovimientoInventario::create([
                'urna_id' => $urna->id,
                'tipo_movimiento' => 'AJUSTE_INVENTARIO',
                'cantidad' => $cantidad,
                'origen' => $request->ubicacion === 'SALON' ? 'Salón Calle Blanco' : 'Bodega Taller',
                'destino' => 'Salida / Reposición Externa',
                'observacion' => $request->motivo,
                'registrado_por_id' => Auth::id(),
            ]);
        });

        return back()->with('success', "Salida de {$cantidad} unidad(es) registrada en Kardex.");
    }

    public function hojaMedidasPdf(Urna $urna)
    {
        BitacoraAuditoria::create([
            'modulo' => 'Inventario / PDF',
            'tabla_afectada' => 'urnas',
            'registro_id' => $urna->id,
            'accion' => 'EMISION_PDF',
            'valores_nuevos_json' => ['documento' => 'Hoja de Medidas Cementerio', 'sku' => $urna->codigo_sku],
            'ip_origen' => request()->ip(),
            'usuario_id' => Auth::id(),
        ]);

        $pdf = Pdf::loadView('pdf.hoja_medidas', compact('urna'));

        return $pdf->stream("Hoja_Medidas_{$urna->codigo_sku}.pdf");
    }

    public function destroy(Urna $urna)
    {
        if ($urna->reservada_en_vida) {
            return back()->with('error', "No se puede eliminar la urna {$urna->codigo_sku} porque tiene una reserva en vida vigente.");
        }

        $enExpedienteActivo = Expediente::where('urna_id', $urna->id)
            ->whereIn('estado_expediente', ['ABIERTO', 'EN_PROCESO'])
            ->exists();

        if ($enExpedienteActivo) {
            return back()->with('error', "No se puede eliminar la urna {$urna->codigo_sku} porque está asignada a un expediente activo.");
        }

        $sku = $urna->codigo_sku;
        $urna->update(['estado_urna' => 'INACTIVO']);
        $urna->delete();

        return back()->with('success', "Urna {$sku} dada de baja correctamente del inventario.");
    }
}
