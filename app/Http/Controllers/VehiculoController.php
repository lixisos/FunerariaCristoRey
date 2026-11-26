<?php

namespace App\Http\Controllers;

use App\Models\BitacoraAuditoria;
use App\Models\Expediente;
use App\Models\User;
use App\Models\Vehiculo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VehiculoController extends Controller
{
    public function index(Request $request)
    {
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $vehQuery = Vehiculo::with(['expedientes' => function ($q) {
            $q->with('fallecido')->whereIn('estado_expediente', ['ABIERTO', 'EN_PROCESO'])->orderBy('fecha_cortejo');
        }]);

        if ($request->filled('buscar')) {
            $term = '%'.trim((string) $request->buscar).'%';
            $vehQuery->where(function ($q) use ($term, $like): void {
                $q->where('patente', $like, $term)
                    ->orWhere('marca_modelo', $like, $term);
            });
        }

        if ($request->filled('tipo_vehiculo')) {
            $vehQuery->where('tipo_vehiculo', $request->tipo_vehiculo);
        }

        if ($request->filled('estado_vehiculo')) {
            $vehQuery->where('estado_vehiculo', $request->estado_vehiculo);
        }

        $vehiculos = $vehQuery->orderBy('patente')->paginate(6, ['*'], 'page_vehiculos')->withQueryString();
        $vehiculosOperativos = Vehiculo::where('estado_vehiculo', '!=', 'MANTENCION')->orderBy('patente')->get();

        $expedientesActivos = Expediente::with(['fallecido', 'vehiculo', 'chofer', 'ayudante'])
            ->whereIn('estado_expediente', ['ABIERTO', 'EN_PROCESO'])
            ->orderBy('fecha_cortejo')
            ->paginate(8, ['*'], 'page_servicios')
            ->withQueryString();

        $personalActivo = User::with('rol')
            ->where('estado_usuario', 'HABILITADO')
            ->where('en_vacaciones', false)
            ->orderBy('name')
            ->get();

        $personalAusente = User::with('rol')
            ->where('en_vacaciones', true)
            ->orderBy('name')
            ->get();

        return view('vehiculos.index', compact(
            'vehiculos',
            'vehiculosOperativos',
            'expedientesActivos',
            'personalActivo',
            'personalAusente'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patente' => 'required|string|max:10|unique:vehiculos,patente',
            'marca_modelo' => 'required|string|max:100',
            'tipo_vehiculo' => 'required|in:CARROZA_PANORAMICA,FURGON_TRASLADO,VEHICULO_ACOMPANAMIENTO',
            'anio' => 'nullable|integer|min:1995|max:2030',
            'kilometraje' => 'nullable|integer|min:0',
            'estado_vehiculo' => 'required|in:DISPONIBLE,EN_SERVICIO,MANTENCION,INACTIVO',
        ]);

        $vehiculo = Vehiculo::create([
            'patente' => strtoupper(trim($data['patente'])),
            'marca_modelo' => $data['marca_modelo'],
            'tipo_vehiculo' => $data['tipo_vehiculo'],
            'anio' => $data['anio'] ?? date('Y'),
            'kilometraje' => $data['kilometraje'] ?? 0,
            'estado_vehiculo' => $data['estado_vehiculo'],
            'creado_por_id' => Auth::id(),
        ]);

        return back()->with('success', "Vehículo {$vehiculo->patente} ({$vehiculo->marca_modelo}) incorporado a la flota.");
    }

    public function cambiarEstado(Request $request, Vehiculo $vehiculo)
    {
        $request->validate([
            'estado_vehiculo' => 'required|in:DISPONIBLE,EN_SERVICIO,MANTENCION,INACTIVO',
            'kilometraje' => 'nullable|integer|min:0',
        ]);

        $anterior = $vehiculo->estado_vehiculo;
        $vehiculo->update([
            'estado_vehiculo' => $request->estado_vehiculo,
            'kilometraje' => $request->filled('kilometraje') ? $request->kilometraje : $vehiculo->kilometraje,
        ]);

        BitacoraAuditoria::create([
            'modulo' => 'Flota / Vehículos',
            'tabla_afectada' => 'vehiculos',
            'registro_id' => $vehiculo->id,
            'accion' => 'CAMBIO_ESTADO_FLOTA',
            'valores_anteriores_json' => ['patente' => $vehiculo->patente, 'estado' => $anterior],
            'valores_nuevos_json' => ['patente' => $vehiculo->patente, 'estado' => $request->estado_vehiculo],
            'ip_origen' => request()->ip(),
            'usuario_id' => Auth::id(),
        ]);

        return back()->with('success', "Estado de {$vehiculo->patente} actualizado a {$request->estado_vehiculo}.");
    }

    public function asignarServicio(Request $request, Expediente $expediente)
    {
        $data = $request->validate([
            'vehiculo_id' => 'nullable|exists:vehiculos,id',
            'chofer_id' => 'nullable|exists:users,id',
            'ayudante_id' => 'nullable|exists:users,id',
        ]);

        // Validar disponibilidad de Chofer y Ayudante
        foreach (['chofer_id', 'ayudante_id'] as $campo) {
            if (! empty($data[$campo])) {
                $persona = User::find($data[$campo]);
                if ($persona && $persona->en_vacaciones) {
                    return back()->with('error', "Asignación bloqueada: {$persona->name} está ausente ({$persona->tipo_ausencia}).");
                }
            }
        }

        // Validar que el vehículo no esté en mantención ni solapado
        if (! empty($data['vehiculo_id'])) {
            $vehiculo = Vehiculo::findOrFail($data['vehiculo_id']);
            if ($vehiculo->estado_vehiculo === 'MANTENCION') {
                return back()->with('error', "El vehículo {$vehiculo->patente} se encuentra en MANTENCIÓN y no puede asignarse.");
            }

            $cortejo = Carbon::parse($expediente->fecha_cortejo);
            $conflicto = Expediente::where('id', '!=', $expediente->id)
                ->where('vehiculo_id', $vehiculo->id)
                ->whereIn('estado_expediente', ['ABIERTO', 'EN_PROCESO'])
                ->whereBetween('fecha_cortejo', [$cortejo->copy()->subHours(3), $cortejo->copy()->addHours(3)])
                ->first();

            if ($conflicto) {
                return back()->with('error', "Conflicto horario: {$vehiculo->patente} ya está asignada a {$conflicto->folio} en ese horario.");
            }

            $vehiculo->update(['estado_vehiculo' => 'EN_SERVICIO']);
        }

        $expediente->update([
            'vehiculo_id' => $data['vehiculo_id'] ?? null,
            'chofer_id' => $data['chofer_id'] ?? null,
            'ayudante_id' => $data['ayudante_id'] ?? null,
        ]);

        BitacoraAuditoria::create([
            'modulo' => 'Flota / Despacho Personal',
            'tabla_afectada' => 'expedientes',
            'registro_id' => $expediente->id,
            'accion' => 'ASIGNACION_FLOTA_PERSONAL',
            'valores_nuevos_json' => [
                'folio' => $expediente->folio,
                'vehiculo_id' => $data['vehiculo_id'] ?? null,
                'chofer_id' => $data['chofer_id'] ?? null,
                'ayudante_id' => $data['ayudante_id'] ?? null,
            ],
            'ip_origen' => request()->ip(),
            'usuario_id' => Auth::id(),
        ]);

        return back()->with('success', "Flota y personal asignados correctamente al expediente {$expediente->folio}.");
    }

    public function destroy(Vehiculo $vehiculo)
    {
        $enExpedienteActivo = Expediente::where('vehiculo_id', $vehiculo->id)
            ->whereIn('estado_expediente', ['ABIERTO', 'EN_PROCESO'])
            ->exists();

        if ($vehiculo->estado_vehiculo === 'EN_SERVICIO' || $enExpedienteActivo) {
            return back()->with('error', "No se puede eliminar el vehículo {$vehiculo->patente} porque está en servicio o asignado a un expediente activo.");
        }

        $patente = $vehiculo->patente;
        $vehiculo->update(['estado_vehiculo' => 'INACTIVO']);
        $vehiculo->delete();

        return back()->with('success', "Vehículo {$patente} retirado correctamente de la flota.");
    }
}
