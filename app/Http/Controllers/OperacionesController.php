<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Expediente;
use App\Models\Fallecido;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OperacionesController extends Controller
{
    public function calendario(Request $request)
    {
        $vista = $request->get('vista', 'semana');
        $fechaBase = $request->filled('fecha') ? Carbon::parse($request->fecha) : now();

        if ($vista === 'dia') {
            $inicio = $fechaBase->copy()->startOfDay();
            $fin = $fechaBase->copy()->endOfDay();
        } elseif ($vista === 'mes') {
            $inicio = $fechaBase->copy()->startOfMonth()->startOfWeek();
            $fin = $fechaBase->copy()->endOfMonth()->endOfWeek();
        } else {
            $vista = 'semana';
            $inicio = $fechaBase->copy()->startOfWeek();
            $fin = $fechaBase->copy()->endOfWeek();
        }

        $expedientes = Expediente::with(['fallecido', 'cliente', 'vehiculo', 'chofer', 'ayudante'])
            ->where('estado_expediente', '!=', 'ANULADO_INACTIVO')
            ->where(function ($q) use ($inicio, $fin): void {
                $q->whereBetween('fecha_instalacion', [$inicio, $fin])
                    ->orWhereBetween('fecha_inicio_velatorio', [$inicio, $fin])
                    ->orWhereBetween('fecha_misa', [$inicio, $fin])
                    ->orWhereBetween('fecha_cortejo', [$inicio, $fin])
                    ->orWhereBetween('fecha_retiro_carroza', [$inicio, $fin]);
            })
            ->get();

        // Construir lista cronológica de todos los eventos operativos (Instalación, Velatorio, Misa, Funeral/Cremación, Retiro)
        $eventos = collect();
        foreach ($expedientes as $exp) {
            if ($exp->fecha_instalacion) {
                $eventos->push([
                    'fecha' => $exp->fecha_instalacion,
                    'tipo' => 'INSTALACION',
                    'color' => 'bg-violet-100 text-violet-900 border-violet-300',
                    'titulo' => "Instalación — {$exp->fallecido->nombre_completo}",
                    'lugar' => $exp->lugar_velatorio,
                    'folio' => $exp->folio,
                    'expediente_id' => $exp->id,
                    'vehiculo' => $exp->vehiculo->patente ?? 'Sin asignar',
                    'chofer' => $exp->chofer->name ?? 'Pendiente',
                ]);
            }

            if ($exp->fecha_inicio_velatorio) {
                $eventos->push([
                    'fecha' => $exp->fecha_inicio_velatorio,
                    'tipo' => 'VELATORIO',
                    'color' => 'bg-indigo-100 text-indigo-900 border-indigo-300',
                    'titulo' => "Inicio Velatorio — {$exp->fallecido->nombre_completo}",
                    'lugar' => $exp->lugar_velatorio,
                    'folio' => $exp->folio,
                    'expediente_id' => $exp->id,
                    'vehiculo' => $exp->vehiculo->patente ?? 'Sin asignar',
                    'chofer' => $exp->chofer->name ?? 'Pendiente',
                ]);
            }

            if ($exp->fecha_misa) {
                $eventos->push([
                    'fecha' => $exp->fecha_misa,
                    'tipo' => 'MISA',
                    'color' => 'bg-sky-100 text-sky-900 border-sky-300',
                    'titulo' => "Misa / Responso — {$exp->fallecido->nombre_completo}",
                    'lugar' => $exp->lugar_misa ?: $exp->lugar_velatorio,
                    'folio' => $exp->folio,
                    'expediente_id' => $exp->id,
                    'vehiculo' => $exp->vehiculo->patente ?? 'Sin asignar',
                    'chofer' => $exp->chofer->name ?? 'Pendiente',
                ]);
            }

            if ($exp->fecha_cortejo) {
                $esCremacion = $exp->tipo_servicio === 'CREMACION';
                $eventos->push([
                    'fecha' => $exp->fecha_cortejo,
                    'tipo' => $esCremacion ? 'CREMACION' : 'FUNERAL',
                    'color' => $esCremacion ? 'bg-amber-100 text-amber-900 border-amber-300' : 'bg-rose-100 text-rose-900 border-rose-300',
                    'titulo' => ($esCremacion ? 'Cremación' : 'Cortejo y Funeral')." — {$exp->fallecido->nombre_completo}",
                    'lugar' => $exp->cementerio_destino,
                    'folio' => $exp->folio,
                    'expediente_id' => $exp->id,
                    'vehiculo' => $exp->vehiculo->patente ?? 'Sin asignar',
                    'chofer' => $exp->chofer->name ?? 'Pendiente',
                ]);
            }

            if ($exp->fecha_retiro_carroza) {
                $eventos->push([
                    'fecha' => $exp->fecha_retiro_carroza,
                    'tipo' => 'TRASLADO',
                    'color' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                    'titulo' => "Retiro / Traslado — {$exp->fallecido->nombre_completo}",
                    'lugar' => $exp->cementerio_destino,
                    'folio' => $exp->folio,
                    'expediente_id' => $exp->id,
                    'vehiculo' => $exp->vehiculo->patente ?? 'Sin asignar',
                    'chofer' => $exp->chofer->name ?? 'Pendiente',
                ]);
            }
        }

        $eventosFiltrados = $eventos
            ->filter(fn ($ev) => $ev['fecha']->between($inicio, $fin))
            ->sortBy('fecha')
            ->values();

        $tipoFiltro = $request->get('tipo');
        if ($tipoFiltro) {
            $eventosFiltrados = $eventosFiltrados->where('tipo', $tipoFiltro)->values();
        }

        $paginaActual = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $porPagina = 8;
        $eventosPaginados = new \Illuminate\Pagination\LengthAwarePaginator(
            $eventosFiltrados->forPage($paginaActual, $porPagina)->values(),
            $eventosFiltrados->count(),
            $porPagina,
            $paginaActual,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('operaciones.calendario', compact(
            'vista',
            'fechaBase',
            'inicio',
            'fin',
            'eventosFiltrados',
            'eventosPaginados',
            'tipoFiltro',
            'eventos'
        ));
    }


    public function clientes(Request $request): View
    {
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $query = Cliente::with(['expedientes.fallecido', 'expedientes.convenio'])
            ->withCount('expedientes');

        if ($request->filled('buscar')) {
            $term = '%'.trim((string) $request->buscar).'%';
            $query->where(function ($q) use ($term, $like): void {
                $q->where('rut', $like, $term)
                    ->orWhere('nombre_completo', $like, $term)
                    ->orWhere('telefono', $like, $term);
            });
        }

        if ($request->filtro === 'con_saldo') {
            $query->whereHas('expedientes', fn ($q) => $q->where('saldo_pendiente', '>', 0)->where('estado_expediente', '!=', 'ANULADO_INACTIVO'));
        } elseif ($request->filtro === 'recurrentes') {
            $query->having('expedientes_count', '>=', 2);
        } elseif ($request->filtro === 'sin_servicios') {
            $query->doesntHave('expedientes');
        }

        $clientes = $query->latest()->paginate(15)->withQueryString();

        return view('operaciones.clientes', compact('clientes'));
    }

    public function storeCliente(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rut' => ['required', 'string', 'max:15', new \App\Rules\RutChileno],
            'nombre_completo' => ['required', 'string', 'min:5', 'max:150', 'regex:/^[\pL\s\.\-\']+$/u'],
            'direccion' => 'required|string|min:5|max:255',
            'comuna' => ['required', 'string', 'min:3', 'max:80', 'regex:/^[\pL\s\.\-]+$/u'],
            'telefono' => ['required', 'string', 'min:8', 'max:20', 'regex:/^(\+?56)?\s?0?[2-9]\d{7,8}$/'],
            'email' => 'nullable|email|max:120',
        ]);

        $rutFormateado = \App\Rules\RutChileno::formatear($data['rut']);

        Cliente::updateOrCreate(
            ['rut' => $rutFormateado],
            [
                'nombre_completo' => $data['nombre_completo'],
                'direccion' => $data['direccion'],
                'comuna' => $data['comuna'],
                'telefono' => $data['telefono'],
                'email' => $data['email'] ?? null,
                'creado_por_id' => Auth::id(),
            ]
        );

        return back()->with('success', "Ficha de cliente {$data['nombre_completo']} guardada correctamente.");
    }
    public function destroyCliente(Cliente $cliente): RedirectResponse
    {
        $tieneExpedientesActivos = $cliente->expedientes()
            ->whereIn('estado_expediente', ['ABIERTO', 'EN_PROCESO'])
            ->exists();

        if ($tieneExpedientesActivos) {
            return back()->with('error', "No se puede eliminar al cliente {$cliente->nombre_completo} porque tiene expedientes activos asociados.");
        }

        $nombre = $cliente->nombre_completo;
        $cliente->delete();

        return back()->with('success', "Cliente {$nombre} eliminado correctamente (borrado lógico auditable).");
    }

    public function fallecidos(Request $request): View
    {
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $query = Fallecido::with(['expedientes.cliente']);

        if ($request->filled('buscar')) {
            $term = '%'.trim((string) $request->buscar).'%';
            $query->where(function ($q) use ($term, $like): void {
                $q->where('rut', $like, $term)
                    ->orWhere('nombre_completo', $like, $term)
                    ->orWhere('lugar_fallecimiento', $like, $term);
            });
        }

        if ($request->filtro === 'incompletos') {
            $query->where(function ($q): void {
                $q->whereNull('rut')->orWhere('rut', '')
                    ->orWhereNull('estado_civil')->orWhere('estado_civil', '')
                    ->orWhereNull('escolaridad')->orWhere('escolaridad', '')
                    ->orWhereNull('profesion')->orWhere('profesion', '');
            });
        } elseif ($request->filtro === 'sml') {
            $query->where('intervencion_sml', true);
        } elseif ($request->filtro === 'sin_rut') {
            $query->where(fn ($q) => $q->whereNull('rut')->orWhere('rut', ''));
        }

        $fallecidos = $query->latest('fecha_fallecimiento')->paginate(15)->withQueryString();

        return view('operaciones.fallecidos', compact('fallecidos'));
    }

    public function updateFallecido(Request $request, Fallecido $fallecido)
    {
        $data = $request->validate([
            'rut' => ['nullable', 'string', 'max:15', new \App\Rules\RutChileno],
            'nombre_completo' => ['required', 'string', 'min:5', 'max:150', 'regex:/^[\pL\s\.\-\']+$/u'],
            'fecha_fallecimiento' => 'required|date|after_or_equal:2020-01-01|before_or_equal:2035-12-31',
            'lugar_fallecimiento' => 'required|string|min:3|max:180',
            'comuna_defuncion' => ['required', 'string', 'min:3', 'max:80', 'regex:/^[\pL\s\.\-]+$/u'],
            'escolaridad' => 'nullable|string|max:80',
            'profesion' => 'nullable|string|max:100',
            'estado_civil' => 'nullable|string|max:40',
            'causa_muerte_preliminar' => 'nullable|string|max:200',
            'intervencion_sml' => 'nullable|boolean',
            'numero_oficio_fiscalia' => 'required_if:intervencion_sml,1|nullable|string|min:3|max:60',
        ]);

        $fallecido->update([
            'rut' => \App\Rules\RutChileno::formatear($data['rut'] ?? null),
            'nombre_completo' => $data['nombre_completo'],
            'fecha_fallecimiento' => $data['fecha_fallecimiento'],
            'lugar_fallecimiento' => $data['lugar_fallecimiento'],
            'comuna_defuncion' => $data['comuna_defuncion'],
            'escolaridad' => $data['escolaridad'] ?? null,
            'profesion' => $data['profesion'] ?? null,
            'estado_civil' => $data['estado_civil'] ?? null,
            'causa_muerte_preliminar' => $data['causa_muerte_preliminar'] ?? null,
            'intervencion_sml' => $request->boolean('intervencion_sml'),
            'numero_oficio_fiscalia' => $request->boolean('intervencion_sml') ? ($data['numero_oficio_fiscalia'] ?? null) : null,
        ]);

        return back()->with('success', "Ficha de Registro Civil de {$fallecido->nombre_completo} actualizada.");
    }

}
