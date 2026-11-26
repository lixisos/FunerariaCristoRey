<?php

namespace Tests\Feature;

use App\Models\AbonoPago;
use App\Models\BitacoraAuditoria;
use App\Models\Cliente;
use App\Models\Condolencia;
use App\Models\ConvenioPrevisional;
use App\Models\DocumentoExpediente;
use App\Models\Expediente;
use App\Models\Urna;
use App\Models\User;
use App\Models\Vehiculo;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FunerariaModulosTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $chofer;

    private User $bodega;

    private User $finanzas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@cristorey.cl')->firstOrFail();
        $this->chofer = User::where('email', 'chofer@cristorey.cl')->firstOrFail();
        $this->bodega = User::where('email', 'bodega@cristorey.cl')->firstOrFail();
        $this->finanzas = User::where('email', 'finanzas@cristorey.cl')->firstOrFail();
    }

    public function test_public_and_authenticated_modules_render_their_real_views(): void
    {
        $this->get('/')->assertOk();
        $this->get('/catalogo')->assertOk();
        $this->get('/memorial/EXP-2026-0001')->assertOk();

        $viewsByRoute = [
            '/dashboard' => 'dashboard',
            '/urnas' => 'urnas.index',
            '/bodega' => 'urnas.bodega',
            '/vehiculos' => 'vehiculos.index',
            '/expedientes' => 'expedientes.index',
            '/expedientes/1' => 'expedientes.show',
            '/calendario' => 'operaciones.calendario',
            '/clientes' => 'operaciones.clientes',
            '/fallecidos' => 'operaciones.fallecidos',
            '/finanzas' => 'finanzas.index',
            '/cobranzas' => 'finanzas.cobranzas',
            '/convenios' => 'finanzas.convenios',
            '/documentos' => 'documentos.index',
            '/pdf-centro' => 'documentos.centro_pdf',
            '/condolencias' => 'documentos.condolencias',
            '/usuarios' => 'admin.usuarios',
            '/roles' => 'admin.roles',
            '/bitacora' => 'admin.bitacora',
            '/notificaciones' => 'admin.notificaciones',
            '/configuracion' => 'admin.configuracion',
        ];

        foreach ($viewsByRoute as $uri => $expectedView) {
            $this->actingAs($this->admin)
                ->get($uri)
                ->assertOk()
                ->assertViewIs($expectedView);
        }
    }

    public function test_all_pdf_endpoints_return_valid_pdf_responses(): void
    {
        $expediente = Expediente::firstOrFail();
        $urna = Urna::firstOrFail();
        $abono = AbonoPago::firstOrFail();

        $pdfRoutes = [
            "/urnas/{$urna->id}/hoja-medidas",
            "/expedientes/{$expediente->id}/contrato-pdf",
            "/finanzas/comprobantes/{$abono->id}/pdf",
            "/convenios/{$expediente->id}/pdf",
            "/condolencias/{$expediente->id}/pdf",
        ];

        foreach ($pdfRoutes as $uri) {
            $this->actingAs($this->admin)
                ->get($uri)
                ->assertOk()
                ->assertHeader('content-type', 'application/pdf');
        }
    }

    public function test_rbac_blocks_unauthorized_roles_including_admin_modules(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');

        foreach (['/usuarios', '/roles', '/bitacora', '/notificaciones', '/finanzas', '/urnas', '/documentos'] as $uri) {
            $this->actingAs($this->chofer)->get($uri)->assertForbidden();
        }

        $this->actingAs($this->chofer)->get('/vehiculos')->assertOk()->assertViewIs('vehiculos.index');
        $this->actingAs($this->chofer)->get('/expedientes')->assertOk();

        $this->actingAs($this->bodega)->get('/finanzas')->assertForbidden();
        $this->actingAs($this->bodega)->get('/expedientes')->assertForbidden();
        $this->actingAs($this->bodega)->get('/usuarios')->assertForbidden();
        $this->actingAs($this->bodega)->get('/urnas')->assertOk();

        $this->actingAs($this->finanzas)->get('/urnas')->assertForbidden();
        $this->actingAs($this->finanzas)->get('/usuarios')->assertForbidden();
        $this->actingAs($this->finanzas)->get('/convenios')->assertOk();
    }

    public function test_vehicle_state_change_and_assignment_audit_without_database_errors(): void
    {
        $vehiculo = Vehiculo::where('estado_vehiculo', 'DISPONIBLE')->firstOrFail();
        $expediente = Expediente::firstOrFail();

        $this->actingAs($this->admin)
            ->patch("/vehiculos/{$vehiculo->id}/estado", [
                'estado_vehiculo' => 'MANTENCION',
                'kilometraje' => 60000,
            ])
            ->assertRedirect();

        $this->assertSame('MANTENCION', $vehiculo->fresh()->estado_vehiculo);
        $this->assertDatabaseHas('bitacora_auditoria', [
            'tabla_afectada' => 'vehiculos',
            'registro_id' => $vehiculo->id,
            'accion' => 'CAMBIO_ESTADO_FLOTA',
            'modulo' => 'Flota / Vehículos',
        ]);

        // Vehículo en mantención debe bloquear asignación
        $this->actingAs($this->admin)
            ->patch("/vehiculos/asignar/{$expediente->id}", [
                'vehiculo_id' => $vehiculo->id,
                'chofer_id' => $this->chofer->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $vehiculo->refresh();
        $vehiculo->update(['estado_vehiculo' => 'DISPONIBLE']);

        $this->actingAs($this->admin)
            ->patch("/vehiculos/asignar/{$expediente->id}", [
                'vehiculo_id' => $vehiculo->id,
                'chofer_id' => $this->chofer->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bitacora_auditoria', [
            'tabla_afectada' => 'expedientes',
            'registro_id' => $expediente->id,
            'accion' => 'ASIGNACION_FLOTA_PERSONAL',
            'modulo' => 'Flota / Despacho Personal',
        ]);
    }

    public function test_safe_soft_delete_and_integrity_guards_for_business_entities(): void
    {
        // Cliente sin expedientes activos puede eliminarse
        $this->actingAs($this->admin)->post('/clientes', [
            'rut' => '19.555.444-3',
            'nombre_completo' => 'Cliente Temporal',
            'direccion' => 'Calle 1',
            'comuna' => 'Temuco',
            'telefono' => '+56 9 1234 5678',
        ])->assertRedirect();

        $clienteLibre = Cliente::where('rut', '19.555.444-3')->firstOrFail();
        $this->actingAs($this->admin)->delete("/clientes/{$clienteLibre->id}")->assertRedirect();
        $this->assertSoftDeleted('clientes', ['id' => $clienteLibre->id]);

        // Cliente con expediente activo está protegido
        $clienteActivo = Expediente::firstOrFail()->cliente;
        $this->actingAs($this->admin)->delete("/clientes/{$clienteActivo->id}")->assertSessionHas('error');
        $this->assertNotSoftDeleted('clientes', ['id' => $clienteActivo->id]);

        // Urna sin uso puede darse de baja; urna reservada en vida está protegida
        $urnaReservada = Urna::where('reservada_en_vida', true)->firstOrFail();
        $this->actingAs($this->admin)->delete("/urnas/{$urnaReservada->id}")->assertSessionHas('error');
        $this->assertNotSoftDeleted('urnas', ['id' => $urnaReservada->id]);

        $urnaLibre = Urna::where('codigo_sku', 'URN-XL-004')->firstOrFail();
        $this->actingAs($this->admin)->delete("/urnas/{$urnaLibre->id}")->assertRedirect();
        $this->assertSoftDeleted('urnas', ['id' => $urnaLibre->id]);

        // Vehículo disponible puede retirarse; vehículo EN_SERVICIO está protegido
        $vehEnServicio = Vehiculo::where('estado_vehiculo', 'EN_SERVICIO')->firstOrFail();
        $this->actingAs($this->admin)->delete("/vehiculos/{$vehEnServicio->id}")->assertSessionHas('error');
        $this->assertNotSoftDeleted('vehiculos', ['id' => $vehEnServicio->id]);

        $vehLibre = Vehiculo::where('estado_vehiculo', 'DISPONIBLE')->firstOrFail();
        $this->actingAs($this->admin)->delete("/vehiculos/{$vehLibre->id}")->assertRedirect();
        $this->assertSoftDeleted('vehiculos', ['id' => $vehLibre->id]);

        // Convenio sin expedientes activos puede eliminarse
        $convenioLibre = ConvenioPrevisional::where('tipo', 'PARTICULAR')->firstOrFail();
        $this->actingAs($this->admin)->delete("/convenios/{$convenioLibre->id}")->assertRedirect();
        $this->assertSoftDeleted('convenios_previsionales', ['id' => $convenioLibre->id]);

        // Condolencia y Documento pueden eliminarse
        $condolencia = Condolencia::firstOrFail();
        $this->actingAs($this->admin)->delete("/condolencias/{$condolencia->id}")->assertRedirect();
        $this->assertDatabaseMissing('condolencias', ['id' => $condolencia->id]);

        $documento = DocumentoExpediente::firstOrFail();
        $this->actingAs($this->admin)->delete("/documentos/{$documento->id}")->assertRedirect();
        $this->assertSoftDeleted('documentos_expediente', ['id' => $documento->id]);

        // Expediente se anula, libera vehículo y registra ELIMINACION en bitácora
        $expediente = Expediente::firstOrFail();
        $vehiculoAsignadoId = $expediente->vehiculo_id;

        $this->actingAs($this->admin)->delete("/expedientes/{$expediente->id}")->assertRedirect(route('expedientes.index'));
        $this->assertSoftDeleted('expedientes', ['id' => $expediente->id]);
        $this->assertSame('DISPONIBLE', Vehiculo::find($vehiculoAsignadoId)?->estado_vehiculo);
        $this->assertTrue(
            BitacoraAuditoria::where('tabla_afectada', 'expedientes')
                ->where('registro_id', $expediente->id)
                ->where('accion', 'ELIMINACION')
                ->exists()
        );
    }

    public function test_document_viewer_blocks_path_traversal(): void
    {
        $expediente = Expediente::firstOrFail();
        $docMalicioso = DocumentoExpediente::create([
            'expediente_id' => $expediente->id,
            'tipo_documento' => 'OTRO',
            'nombre_archivo' => 'malicious.txt',
            'ruta_s3' => '../../.env',
            'validado' => false,
            'subido_por_id' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get("/documentos/{$docMalicioso->id}/ver")
            ->assertForbidden();
    }

    public function test_bitacora_displays_human_readable_spanish_details_instead_of_raw_json(): void
    {
        $response = $this->actingAs($this->admin)->get('/bitacora');

        $response->assertOk()
            ->assertSee('Bitácora de Auditoría')
            ->assertSee('Creación')
            ->assertSee('Vehículo')
            ->assertSee('Patente:')
            ->assertDontSee('"marca_modelo":')
            ->assertDontSee('"creado_por_id":');
    }

    public function test_visible_ui_text_contains_no_raw_technical_codes_across_modules(): void
    {
        $forbiddenVisibleTokens = [
            'EN_PROCESO',
            'ANULADO_INACTIVO',
            'CARROZA_PANORAMICA',
            'FURGON_TRASLADO',
            'PALOMA_BIBLIA',
            'NINO_SEREMI',
            'ESPECIAL_XL',
            'SAN_JAVIER',
            'INGRESO_PROVEEDOR',
            'TRASPASO_TALLER_SALON',
            'REBAJA_VENTA',
            'LICENCIA_MEDICA',
            'CERTIFICADO_DEFUNCION',
            'VELATORIO_Y_SEPULTACION',
            'FFAA_ORDEN',
            'RBAC',
            'JSONB',
            'PostgreSQL',
        ];

        $pages = [
            '/',
            '/dashboard',
            '/expedientes',
            '/expedientes/1',
            '/urnas',
            '/bodega',
            '/vehiculos',
            '/finanzas',
            '/convenios',
            '/documentos',
            '/calendario',
            '/usuarios',
            '/roles',
            '/bitacora',
            '/notificaciones',
        ];

        foreach ($pages as $uri) {
            $html = $this->actingAs($this->admin)->get($uri)->assertOk()->getContent();
            $visibleText = strip_tags(preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', '', (string) $html));

            foreach ($forbiddenVisibleTokens as $token) {
                $this->assertStringNotContainsString(
                    $token,
                    $visibleText,
                    "Se encontró texto técnico [{$token}] visible en la ruta {$uri}."
                );
            }
        }

        $this->actingAs($this->admin)->get('/dashboard')->assertSee('Dashboard');

        $this->actingAs($this->admin)->get('/profile')
            ->assertOk()
            ->assertSee('Información del perfil')
            ->assertSee('Actualizar contraseña')
            ->assertDontSee('Profile Information')
            ->assertDontSee('Update Password');

        auth()->logout();

        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('Enviar enlace de recuperación')
            ->assertDontSee('Email Password Reset Link');
    }
}
