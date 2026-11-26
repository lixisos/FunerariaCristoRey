<?php

namespace Database\Seeders;

use App\Models\AbonoPago;
use App\Models\Cliente;
use App\Models\Condolencia;
use App\Models\ConvenioPrevisional;
use App\Models\DocumentoExpediente;
use App\Models\Expediente;
use App\Models\Fallecido;
use App\Models\Rol;
use App\Models\Urna;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Los 5 Roles estrictos (RBAC)
        $rolAdmin = Rol::create(['nombre' => 'Administrador General', 'slug' => 'admin', 'descripcion' => 'Acceso total, auditoría y delegación de permisos']);
        $rolCoord = Rol::create(['nombre' => 'Coordinador de Servicios', 'slug' => 'coordinador', 'descripcion' => 'Apertura de expedientes, flota y documentos legales']);
        $rolBodega = Rol::create(['nombre' => 'Encargado de Bodega', 'slug' => 'bodega', 'descripcion' => 'Control Salón Calle Blanco, Taller, proveedores y medidas']);
        $rolChofer = Rol::create(['nombre' => 'Chofer / Terreno', 'slug' => 'chofer', 'descripcion' => 'Bitácora móvil de traslados, cortejo y entregas']);
        $rolFinanzas = Rol::create(['nombre' => 'Contador / Finanzas', 'slug' => 'finanzas', 'descripcion' => 'Cálculo IPS/AFP, abonos y emisión de comprobantes foliados']);

        // 2. Usuarios del equipo de la funeraria (Contraseña para todos: 12345678)
        $admin = User::create([
            'rut' => '12.345.678-9', 'name' => 'Renato Osses (Gerencia)',
            'email' => 'admin@cristorey.cl', 'telefono' => '+56 9 8765 4321',
            'password' => Hash::make('12345678'), 'rol_id' => $rolAdmin->id,
        ]);
        $coord = User::create([
            'rut' => '15.234.567-8', 'name' => 'Carolina Méndez (Coordinación)',
            'email' => 'coordinador@cristorey.cl', 'telefono' => '+56 9 7654 3210',
            'password' => Hash::make('12345678'), 'rol_id' => $rolCoord->id,
        ]);
        $bodega = User::create([
            'rut' => '16.345.678-0', 'name' => 'Héctor Paredes (Bodega y Taller)',
            'email' => 'bodega@cristorey.cl', 'telefono' => '+56 9 6543 2109',
            'password' => Hash::make('12345678'), 'rol_id' => $rolBodega->id,
        ]);
        $chofer = User::create([
            'rut' => '14.987.654-3', 'name' => 'Marcelo Villagrán (Chofer)',
            'email' => 'chofer@cristorey.cl', 'telefono' => '+56 9 5432 1098',
            'password' => Hash::make('12345678'), 'rol_id' => $rolChofer->id,
        ]);
        $finanzas = User::create([
            'rut' => '17.654.321-K', 'name' => 'Patricia Soto (Finanzas)',
            'email' => 'finanzas@cristorey.cl', 'telefono' => '+56 9 4321 0987',
            'password' => Hash::make('12345678'), 'rol_id' => $rolFinanzas->id,
        ]);

        // 3. Convenios Previsionales reales en Chile
        $convIps = ConvenioPrevisional::create([
            'nombre' => 'IPS - Asignación por Muerte ($1.070.000)',
            'tipo' => 'IPS', 'monto_tope_clp' => 1070000, 'monto_tope_uf' => 0,
            'requiere_resolucion' => true, 'creado_por_id' => $admin->id,
        ]);
        $convAfp = ConvenioPrevisional::create([
            'nombre' => 'AFP - Cuota Mortuoria Legal (15 UF)',
            'tipo' => 'AFP', 'monto_tope_clp' => 570000, 'monto_tope_uf' => 15.00,
            'requiere_resolucion' => true, 'creado_por_id' => $admin->id,
        ]);
        ConvenioPrevisional::create([
            'nombre' => 'DIPRECA / CAPREDENA',
            'tipo' => 'FFAA_ORDEN', 'monto_tope_clp' => 950000, 'monto_tope_uf' => 0,
            'requiere_resolucion' => true, 'creado_por_id' => $admin->id,
        ]);
        ConvenioPrevisional::create([
            'nombre' => 'Servicio Particular (Sin Convenio)',
            'tipo' => 'PARTICULAR', 'monto_tope_clp' => 0, 'monto_tope_uf' => 0,
            'requiere_resolucion' => false, 'creado_por_id' => $admin->id,
        ]);

        // 4. Flota de Vehículos
        $carroza1 = Vehiculo::create([
            'patente' => 'RK-BL-42', 'marca_modelo' => 'Mercedes-Benz E-Class Carroza Panorámica',
            'tipo_vehiculo' => 'CARROZA_PANORAMICA', 'anio' => 2023, 'kilometraje' => 34200,
            'estado_vehiculo' => 'EN_SERVICIO', 'creado_por_id' => $admin->id,
        ]);
        $carroza2 = Vehiculo::create([
            'patente' => 'PL-RT-88', 'marca_modelo' => 'Chrysler 300C Carroza Clásica Negra',
            'tipo_vehiculo' => 'CARROZA_PANORAMICA', 'anio' => 2021, 'kilometraje' => 58900,
            'estado_vehiculo' => 'DISPONIBLE', 'creado_por_id' => $admin->id,
        ]);
        Vehiculo::create([
            'patente' => 'ST-GH-19', 'marca_modelo' => 'Hyundai H1 Furgón de Traslado SML',
            'tipo_vehiculo' => 'FURGON_TRASLADO', 'anio' => 2022, 'kilometraje' => 71500,
            'estado_vehiculo' => 'DISPONIBLE', 'creado_por_id' => $admin->id,
        ]);

        // 5. Catálogo e Inventario Real de Urnas (Salón vs Taller, Seremi Niño, XL, Reserva en Vida)
        $urna1 = Urna::create([
            'codigo_sku' => 'URN-SJ-001', 'modelo' => 'Caoba Imperial Paloma Biblia',
            'descripcion' => 'Madera nativa barnizada alto brillo con tallado de paloma y biblia en relieve.',
            'categoria_tamano' => 'ESTANDAR', 'acabado_tallado' => 'PALOMA_BIBLIA', 'color' => 'Caoba Oscuro',
            'largo_cm' => 195, 'ancho_cm' => 64, 'alto_cm' => 52, 'precio_venta' => 1150000,
            'stock_salon' => 6, 'stock_taller' => 8, 'proveedor_origen' => 'SAN_JAVIER',
            'foto_s3_url' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=600&q=80',
            'creado_por_id' => $bodega->id,
        ]);
        $urna2 = Urna::create([
            'codigo_sku' => 'URN-ST-002', 'modelo' => 'Roble Europeo Marqueteada',
            'descripcion' => 'Urna ejecutiva fabricada en Santiago con incrustaciones marqueteadas y interior capitoné.',
            'categoria_tamano' => 'ESTANDAR', 'acabado_tallado' => 'MARQUETEADA', 'color' => 'Nogal Miel',
            'largo_cm' => 198, 'ancho_cm' => 66, 'alto_cm' => 54, 'precio_venta' => 1490000,
            'stock_salon' => 4, 'stock_taller' => 5, 'proveedor_origen' => 'SANTIAGO',
            'foto_s3_url' => 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?auto=format&fit=crop&w=600&q=80',
            'creado_por_id' => $bodega->id,
        ]);
        Urna::create([
            'codigo_sku' => 'URN-NIN-003', 'modelo' => 'Angelito Blanco Perlado (Norma Seremi)',
            'descripcion' => 'Urna infantil blanca con detalles dorados. Sujeta a fiscalización estricta de medidas Seremi.',
            'categoria_tamano' => 'NINO_SEREMI', 'acabado_tallado' => 'LISA', 'color' => 'Blanco Perlado',
            'largo_cm' => 110, 'ancho_cm' => 42, 'alto_cm' => 36, 'precio_venta' => 580000,
            'stock_salon' => 3, 'stock_taller' => 4, 'proveedor_origen' => 'SAN_JAVIER',
            'foto_s3_url' => 'https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&fit=crop&w=600&q=80',
            'creado_por_id' => $bodega->id,
        ]);
        Urna::create([
            'codigo_sku' => 'URN-XL-004', 'modelo' => 'Presidencial Sobredimensional XL',
            'descripcion' => 'Estructura reforzada de ancho especial para nichos amplios o sepultura en tierra.',
            'categoria_tamano' => 'ESPECIAL_XL', 'acabado_tallado' => 'LISA', 'color' => 'Cerezo',
            'largo_cm' => 210, 'ancho_cm' => 78, 'alto_cm' => 60, 'precio_venta' => 1650000,
            'stock_salon' => 2, 'stock_taller' => 3, 'proveedor_origen' => 'SANTIAGO',
            'foto_s3_url' => 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=600&q=80',
            'creado_por_id' => $bodega->id,
        ]);
        Urna::create([
            'codigo_sku' => 'URN-PER-005', 'modelo' => 'Edición Especial Kultrún / Deportes Temuco',
            'descripcion' => 'Urna personalizada a pedido con tallado cultural mapuche y emblema albiverde.',
            'categoria_tamano' => 'ESTANDAR', 'acabado_tallado' => 'PERSONALIZADA', 'color' => 'Verde Bosque / Madera',
            'largo_cm' => 196, 'ancho_cm' => 65, 'alto_cm' => 53, 'precio_venta' => 1890000,
            'stock_salon' => 1, 'stock_taller' => 0, 'proveedor_origen' => 'SAN_JAVIER',
            'reservada_en_vida' => true, 'rut_reserva_vida' => '6.543.210-4',
            'nombre_reserva_vida' => 'Don Eugenio Huaiquilao (Reserva en Vida Pagada)',
            'foto_s3_url' => 'https://images.unsplash.com/photo-1507652313519-d4e9174996dd?auto=format&fit=crop&w=600&q=80',
            'creado_por_id' => $admin->id,
        ]);

        // 6. Clientes, Fallecidos y Expedientes Activos de muestra
        $cliente1 = Cliente::create([
            'rut' => '11.456.789-2', 'nombre_completo' => 'María Elena Riquelme Castro',
            'telefono' => '+56 9 9123 4567', 'email' => 'mriquelme@gmail.com',
            'direccion' => 'Av. Alemania 0845', 'comuna' => 'Temuco', 'creado_por_id' => $coord->id,
        ]);
        $fallecido1 = Fallecido::create([
            'rut' => '5.678.901-3', 'nombre_completo' => 'José Luis Riquelme Muñoz',
            'fecha_nacimiento' => '1945-04-12', 'fecha_fallecimiento' => now()->subHours(5),
            'lugar_fallecimiento' => 'Hospital Dr. Hernán Henríquez Aravena', 'comuna_defuncion' => 'Temuco',
            'causa_muerte_preliminar' => 'Insuficiencia Cardíaca', 'intervencion_sml' => true,
            'numero_oficio_fiscalia' => 'FISC-2026-8841', 'creado_por_id' => $coord->id,
        ]);

        $exp1 = Expediente::create([
            'folio' => 'EXP-2026-0001', 'cliente_id' => $cliente1->id, 'fallecido_id' => $fallecido1->id,
            'urna_id' => $urna1->id, 'vehiculo_id' => $carroza1->id, 'chofer_id' => $chofer->id,
            'convenio_id' => $convIps->id, 'tipo_servicio' => 'VELATORIO_Y_SEPULTACION',
            'lugar_velatorio' => 'Capilla Salón Calle Blanco #412, Temuco',
            'fecha_inicio_velatorio' => now()->addHours(1),
            'fecha_cortejo' => now()->addDay()->setTime(15, 0),
            'cementerio_destino' => 'Cementerio Parque del Sendero Temuco',
            'metadatos_ruta_json' => [
                'sector_rural' => false,
                'requiere_escolta' => true,
                'observaciones_chofer' => 'Retiro desde SML Temuco a las 18:30 hrs',
            ],
            'limite_legal_seremi_at' => now()->addHours(2), // Activa Semáforo Rojo (< 3 hrs)
            'valor_total_servicio' => 1450000, 'monto_cobertura_convenio' => 1070000,
            'saldo_pendiente' => 180000, 'estado_expediente' => 'EN_PROCESO',
            'creado_por_id' => $coord->id,
        ]);

        DocumentoExpediente::create([
            'expediente_id' => $exp1->id, 'tipo_documento' => 'CERTIFICADO_DEFUNCION',
            'nombre_archivo' => 'cert_defuncion_riquelme.pdf',
            'ruta_s3' => 's3://cristorey-expedientes/2026/EXP-2026-0001/cert_defuncion.pdf',
            'validado' => true, 'subido_por_id' => $coord->id,
        ]);

        AbonoPago::create([
            'folio_comprobante' => 'COMP-2026-0001', 'expediente_id' => $exp1->id,
            'monto_abonado' => 200000, 'metodo_pago' => 'TRANSFERENCIA',
            'fecha_pago' => now()->subHour(), 'observacion' => 'Pie inicial contrato funerario',
            'registrado_por_id' => $finanzas->id,
        ]);

        Condolencia::create([
            'expediente_id' => $exp1->id, 'nombre_remitente' => 'Familia González Riquelme',
            'relacion' => 'Sobrinos',
            'mensaje' => 'Nuestras más sinceras condolencias para toda la familia. Que descanse en paz tío José.',
        ]);
    }
}