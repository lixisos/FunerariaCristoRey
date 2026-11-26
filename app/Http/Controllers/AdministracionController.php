<?php

namespace App\Http\Controllers;

use App\Models\BitacoraAuditoria;
use App\Models\Condolencia;
use App\Models\Expediente;
use App\Models\Rol;
use App\Models\Urna;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdministracionController extends Controller
{
    public function usuarios(Request $request): View
    {
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $query = User::with(['rol', 'delegadoA']);

        if ($request->filled('buscar')) {
            $term = '%'.trim((string) $request->buscar).'%';
            $query->where(function ($q) use ($term, $like): void {
                $q->where('name', $like, $term)
                    ->orWhere('rut', $like, $term)
                    ->orWhere('email', $like, $term);
            });
        }

        if ($request->filled('rol_id')) {
            $query->where('rol_id', $request->rol_id);
        }

        if ($request->estado === 'ausente') {
            $query->where('en_vacaciones', true);
        } elseif ($request->estado === 'inactivo') {
            $query->where('estado_usuario', 'INACTIVO');
        }

        $usuarios = $query->orderBy('name')->get();
        $todosUsuarios = User::where('estado_usuario', 'HABILITADO')->orderBy('name')->get();
        $roles = Rol::where('estado_rol', 'HABILITADO')->orderBy('id')->get();

        return view('admin.usuarios', compact('usuarios', 'todosUsuarios', 'roles'));
    }

    public function storeUsuario(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rut' => 'required|string|max:15|unique:users,rut',
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'telefono' => 'nullable|string|max:25',
            'rol_id' => 'required|exists:roles,id',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'rut' => trim($data['rut']),
            'name' => $data['name'],
            'email' => strtolower(trim($data['email'])),
            'telefono' => $data['telefono'] ?? null,
            'rol_id' => $data['rol_id'],
            'password' => Hash::make($data['password']),
            'estado_usuario' => 'HABILITADO',
            'tipo_ausencia' => 'ACTIVO',
        ]);

        return back()->with('success', "Colaborador {$data['name']} creado correctamente.");
    }

    public function updateUsuario(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'rut' => ['required', 'string', 'max:15', Rule::unique('users', 'rut')->ignore($user->id)],
            'name' => 'required|string|max:120',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'telefono' => 'nullable|string|max:25',
            'rol_id' => 'required|exists:roles,id',
            'password' => 'nullable|string|min:6',
        ]);

        $payload = [
            'rut' => trim($data['rut']),
            'name' => $data['name'],
            'email' => strtolower(trim($data['email'])),
            'telefono' => $data['telefono'] ?? null,
            'rol_id' => $data['rol_id'],
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        return back()->with('success', "Ficha de {$user->name} actualizada correctamente.");
    }

    public function actualizarAusencia(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'tipo_ausencia' => 'required|in:ACTIVO,VACACIONES,PERMISO,LICENCIA_MEDICA',
            'ausencia_hasta' => 'nullable|date',
            'delegado_a_id' => 'nullable|exists:users,id',
        ]);

        $ausente = $data['tipo_ausencia'] !== 'ACTIVO';

        $user->update([
            'en_vacaciones' => $ausente,
            'tipo_ausencia' => $data['tipo_ausencia'],
            'ausencia_hasta' => $ausente ? ($data['ausencia_hasta'] ?? null) : null,
            'delegado_a_id' => $ausente ? ($data['delegado_a_id'] ?? null) : null,
        ]);

        return back()->with('success', "Disponibilidad de {$user->name} actualizada.");
    }

    public function cambiarRolUsuario(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'rol_id' => 'required|exists:roles,id',
        ]);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Por seguridad, no puedes cambiar tu propio rol de Administrador en sesión.');
        }

        $user->update(['rol_id' => $data['rol_id']]);
        $user->load('rol');

        return back()->with('success', "Rol de {$user->name} actualizado a {$user->rol->nombre}.");
    }

    public function toggleEstadoUsuario(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes deshabilitar tu propio usuario en sesión.');
        }

        $nuevoEstado = $user->estado_usuario === 'HABILITADO' ? 'INACTIVO' : 'HABILITADO';
        $user->update(['estado_usuario' => $nuevoEstado]);

        return back()->with('success', "Usuario {$user->name} cambiado a estado {$nuevoEstado}.");
    }

    public function roles(): View
    {
        $roles = Rol::withCount('usuarios')->orderBy('id')->get();
        $usuarios = User::with('rol')->orderBy('name')->get();

        return view('admin.roles', compact('roles', 'usuarios'));
    }

    public function updateRol(Request $request, Rol $rol): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:80',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $rol->update($data);

        return back()->with('success', "Perfil \"{$rol->nombre}\" actualizado.");
    }

    public function bitacora(Request $request): View
    {
        $like = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $query = BitacoraAuditoria::with('usuario');

        if ($request->filled('buscar')) {
            $term = '%'.trim((string) $request->buscar).'%';
            $query->where(function ($q) use ($term, $like): void {
                $q->where('modulo', $like, $term)
                    ->orWhereRaw('CAST(registro_id AS TEXT) LIKE ?', [$term])
                    ->orWhereRaw('CAST(valores_nuevos_json AS TEXT) '.$like.' ?', [$term])
                    ->orWhereRaw('CAST(valores_anteriores_json AS TEXT) '.$like.' ?', [$term]);
            });
        }

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->filled('accion')) {
            $query->where('accion', $request->accion);
        }

        if ($request->filled('tabla')) {
            $query->where('tabla_afectada', $request->tabla);
        }

        if ($request->filled('fecha')) {
            $query->whereDate('created_at', $request->fecha);
        }

        $registros = $query->latest('created_at')->paginate(20)->withQueryString();
        $usuariosFiltro = User::orderBy('name')->get(['id', 'name']);

        return view('admin.bitacora', compact('registros', 'usuariosFiltro'));
    }

    public function notificaciones(): View
    {
        $alertasSeremi = Expediente::with('fallecido')
            ->whereIn('estado_expediente', ['ABIERTO', 'EN_PROCESO'])
            ->where('limite_legal_seremi_at', '<=', now()->addHours(6))
            ->orderBy('limite_legal_seremi_at')
            ->get();

        $serviciosProximos = Expediente::with(['fallecido', 'vehiculo', 'chofer'])
            ->whereIn('estado_expediente', ['ABIERTO', 'EN_PROCESO'])
            ->whereBetween('fecha_cortejo', [now()->startOfDay(), now()->addDays(2)->endOfDay()])
            ->orderBy('fecha_cortejo')
            ->get();

        $sinFlotaAsignada = Expediente::with(['fallecido'])
            ->whereIn('estado_expediente', ['ABIERTO', 'EN_PROCESO'])
            ->where(function ($q): void {
                $q->whereNull('vehiculo_id')->orWhereNull('chofer_id');
            })
            ->orderBy('fecha_cortejo')
            ->get();

        $alertasStock = Urna::where('estado_urna', 'HABILITADO')
            ->whereRaw('(stock_salon + stock_taller) <= 2')
            ->orderByRaw('(stock_salon + stock_taller) asc')
            ->get();

        $alertasCobranza = Expediente::with(['cliente', 'fallecido'])
            ->where('estado_expediente', '!=', 'ANULADO_INACTIVO')
            ->where('saldo_pendiente', '>', 0)
            ->where('created_at', '<=', now()->subDays(10))
            ->orderBy('created_at')
            ->get();

        $personalAusente = User::with(['rol', 'delegadoA'])
            ->where('en_vacaciones', true)
            ->get();

        $condolenciasOcultas = Condolencia::where('aprobado', false)->count();

        return view('admin.notificaciones', compact(
            'alertasSeremi',
            'serviciosProximos',
            'sinFlotaAsignada',
            'alertasStock',
            'alertasCobranza',
            'personalAusente',
            'condolenciasOcultas'
        ));
    }

    public function configuracion(): View
    {
        return view('admin.configuracion');
    }
}