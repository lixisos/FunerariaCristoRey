<?php

use App\Http\Controllers\AdministracionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\FinanzaController;
use App\Http\Controllers\OperacionesController;
use App\Http\Controllers\PortalPublicoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UrnaController;
use App\Http\Controllers\VehiculoController;
use Illuminate\Support\Facades\Route;

// Rutas Públicas (Portal de Familias y Libro de Condolencias sin precios)
Route::get('/', [PortalPublicoController::class, 'catalogo'])->name('home');
Route::get('/catalogo', [PortalPublicoController::class, 'catalogo'])->name('publico.catalogo');
Route::get('/memorial/{folio}', [PortalPublicoController::class, 'memorial'])->name('publico.memorial');
Route::post('/memorial/{folio}/condolencia', [PortalPublicoController::class, 'guardarCondolencia'])->name('publico.condolencia.store');
Route::get('/catalogo/urna/{codigo_sku}', [PortalPublicoController::class, 'detalleUrna'])->name('publico.urna');

// Rutas Autenticadas con RBAC
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Delegación y Gestión de Personal (Solo Administrador / Gerente)
    Route::patch('/usuarios/{user}/delegacion', [DashboardController::class, 'actualizarDelegacion'])
        ->middleware('role:admin')
        ->name('usuarios.delegacion');

    // Módulo de Bodega, Inventario de Urnas y Kardex
    Route::middleware('role:admin,coordinador,bodega')->group(function () {
        Route::get('/urnas', [UrnaController::class, 'index'])->name('urnas.index');
        Route::post('/urnas', [UrnaController::class, 'store'])->name('urnas.store');
        Route::put('/urnas/{urna}', [UrnaController::class, 'update'])->name('urnas.update');
        Route::delete('/urnas/{urna}', [UrnaController::class, 'destroy'])
            ->middleware('role:admin,coordinador')
            ->name('urnas.destroy');
        Route::post('/urnas/{urna}/traspasar', [UrnaController::class, 'traspasarSalon'])->name('urnas.traspasar');
        Route::post('/urnas/{urna}/reponer', [UrnaController::class, 'reponerProveedor'])->name('urnas.reponer');
        Route::post('/urnas/{urna}/salida', [UrnaController::class, 'registrarSalidaBodega'])->name('urnas.salida');
        Route::get('/urnas/{urna}/hoja-medidas', [UrnaController::class, 'hojaMedidasPdf'])->name('urnas.hoja-medidas');
        Route::get('/bodega', [UrnaController::class, 'bodega'])->name('bodega.index');
    });

    // Módulo de Flota de Vehículos y Asignación de Personal (Incluye Choferes para ver su ruta/vehículo)
    Route::middleware('role:admin,coordinador,chofer,bodega')->group(function () {
        Route::get('/vehiculos', [VehiculoController::class, 'index'])->name('vehiculos.index');
        Route::post('/vehiculos', [VehiculoController::class, 'store'])
            ->middleware('role:admin,coordinador')
            ->name('vehiculos.store');
        Route::patch('/vehiculos/{vehiculo}/estado', [VehiculoController::class, 'cambiarEstado'])
            ->middleware('role:admin,coordinador,bodega')
            ->name('vehiculos.estado');
        Route::patch('/vehiculos/asignar/{expediente}', [VehiculoController::class, 'asignarServicio'])
            ->middleware('role:admin,coordinador')
            ->name('vehiculos.asignar');
        Route::delete('/vehiculos/{vehiculo}', [VehiculoController::class, 'destroy'])
            ->middleware('role:admin,coordinador')
            ->name('vehiculos.destroy');
    });

    // Módulo de Expedientes, Calendario, Clientes y Fallecidos
    Route::middleware('role:admin,coordinador,chofer,finanzas')->group(function () {
        Route::get('/expedientes', [ExpedienteController::class, 'index'])->name('expedientes.index');
        Route::get('/api/clientes/buscar', [ExpedienteController::class, 'buscarCliente'])->name('clientes.buscar');
        Route::post('/expedientes', [ExpedienteController::class, 'store'])
            ->middleware('role:admin,coordinador')
            ->name('expedientes.store');
        Route::get('/expedientes/{expediente}', [ExpedienteController::class, 'show'])->name('expedientes.show');
        Route::put('/expedientes/{expediente}', [ExpedienteController::class, 'update'])
            ->middleware('role:admin,coordinador')
            ->name('expedientes.update');
        Route::delete('/expedientes/{expediente}', [ExpedienteController::class, 'destroy'])
            ->middleware('role:admin,coordinador')
            ->name('expedientes.destroy');
        Route::post('/expedientes/{expediente}/documentos', [ExpedienteController::class, 'subirDocumento'])->name('expedientes.documentos.store');
        Route::get('/documentos/{documento}/ver', [ExpedienteController::class, 'verDocumento'])->name('documentos.ver');
        Route::patch('/expedientes/{expediente}/estado', [ExpedienteController::class, 'cambiarEstado'])->name('expedientes.estado');
        Route::get('/expedientes/{expediente}/contrato-pdf', [ExpedienteController::class, 'contratoPdf'])->name('expedientes.contrato-pdf');

        // Submódulos de Operaciones (Calendario, Clientes y Fallecidos)
        Route::get('/calendario', [OperacionesController::class, 'calendario'])->name('calendario.index');
        Route::get('/clientes', [OperacionesController::class, 'clientes'])->name('clientes.index');
        Route::post('/clientes', [OperacionesController::class, 'storeCliente'])->name('clientes.store');
        Route::delete('/clientes/{cliente}', [OperacionesController::class, 'destroyCliente'])
            ->middleware('role:admin,coordinador')
            ->name('clientes.destroy');
        Route::get('/fallecidos', [OperacionesController::class, 'fallecidos'])->name('fallecidos.index');
        Route::put('/fallecidos/{fallecido}', [OperacionesController::class, 'updateFallecido'])->name('fallecidos.update');
    });

    // Módulo Financiero, Cobranzas 10/20/30+ y Convenios IPS / AFP
    Route::middleware('role:admin,finanzas,coordinador')->group(function () {
        Route::get('/finanzas', [FinanzaController::class, 'index'])->name('finanzas.index');
        Route::post('/finanzas/{expediente}/abonos', [FinanzaController::class, 'registrarAbono'])->name('finanzas.abonos.store');
        Route::patch('/finanzas/comprobantes/{abono}/anular', [FinanzaController::class, 'anularAbono'])
            ->middleware('role:admin,finanzas')
            ->name('finanzas.abonos.anular');
        Route::get('/finanzas/comprobantes/{abono}/pdf', [FinanzaController::class, 'comprobantePdf'])->name('finanzas.comprobante-pdf');
        Route::get('/cobranzas', [FinanzaController::class, 'cobranzas'])->name('cobranzas.index');
        Route::get('/convenios', [FinanzaController::class, 'convenios'])->name('convenios.index');
        Route::post('/convenios', [FinanzaController::class, 'storeConvenio'])->name('convenios.store');
        Route::put('/convenios/{convenio}', [FinanzaController::class, 'updateConvenio'])->name('convenios.update');
        Route::delete('/convenios/{convenio}', [FinanzaController::class, 'destroyConvenio'])
            ->middleware('role:admin,finanzas')
            ->name('convenios.destroy');
        Route::get('/convenios/{expediente}/pdf', [FinanzaController::class, 'convenioPdf'])->name('convenios.pdf');
    });

    // Módulo de Documentos Digitales, Centro PDF y Condolencias
    Route::middleware('role:admin,coordinador,finanzas,bodega')->group(function () {
        Route::get('/documentos', [DocumentoController::class, 'index'])->name('documentos.index');
        Route::post('/documentos', [DocumentoController::class, 'store'])
            ->middleware('role:admin,coordinador,finanzas')
            ->name('documentos.store');
        Route::delete('/documentos/{documento}', [DocumentoController::class, 'destroy'])
            ->middleware('role:admin,coordinador')
            ->name('documentos.destroy');
        Route::get('/pdf-centro', [DocumentoController::class, 'centroPdf'])->name('pdf.centro');
        Route::get('/condolencias', [DocumentoController::class, 'condolencias'])->name('condolencias.admin');
        Route::post('/condolencias', [DocumentoController::class, 'storeCondolencia'])
            ->middleware('role:admin,coordinador')
            ->name('condolencias.store');
        Route::patch('/condolencias/{condolencia}/toggle', [DocumentoController::class, 'toggleCondolencia'])->name('condolencias.toggle');
        Route::delete('/condolencias/{condolencia}', [DocumentoController::class, 'destroyCondolencia'])
            ->middleware('role:admin,coordinador')
            ->name('condolencias.destroy');
        Route::get('/condolencias/{expediente}/pdf', [DocumentoController::class, 'condolenciasPdf'])->name('condolencias.pdf');
    
    });

    // Módulo de Administración (Usuarios, Ausencias, Roles RBAC, Bitácora y Notificaciones)
    Route::middleware('role:admin')->group(function () {
        Route::get('/usuarios', [AdministracionController::class, 'usuarios'])->name('usuarios.index');
        Route::post('/usuarios', [AdministracionController::class, 'storeUsuario'])->name('usuarios.store');
        Route::put('/usuarios/{user}', [AdministracionController::class, 'updateUsuario'])->name('usuarios.update');
        Route::patch('/usuarios/{user}/ausencia', [AdministracionController::class, 'actualizarAusencia'])->name('usuarios.ausencia');
        Route::patch('/usuarios/{user}/rol', [AdministracionController::class, 'cambiarRolUsuario'])->name('usuarios.rol');
        Route::patch('/usuarios/{user}/estado', [AdministracionController::class, 'toggleEstadoUsuario'])->name('usuarios.estado');
        Route::get('/roles', [AdministracionController::class, 'roles'])->name('roles.index');
        Route::put('/roles/{rol}', [AdministracionController::class, 'updateRol'])->name('roles.update');
        Route::get('/bitacora', [AdministracionController::class, 'bitacora'])->name('bitacora.index');
        Route::get('/notificaciones', [AdministracionController::class, 'notificaciones'])->name('notificaciones.index');
    });
    
    // Configuración General
    Route::get('/configuracion', [AdministracionController::class, 'configuracion'])->name('configuracion.index');

    // Perfil Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
