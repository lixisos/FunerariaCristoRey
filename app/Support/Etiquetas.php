<?php

namespace App\Support;

final class Etiquetas
{
    /** @var array<string, string> */
    private const MAPA = [
        // Estados generales y de expediente
        'ABIERTO' => 'Abierto',
        'EN_PROCESO' => 'En proceso',
        'CERRADO' => 'Cerrado',
        'ANULADO_INACTIVO' => 'Anulado / Inactivo',
        'HABILITADO' => 'Habilitado',
        'INACTIVO' => 'Inactivo',
        'VALIDO' => 'Válido',
        'ANULADO' => 'Anulado',

        // Vehículos y flota
        'DISPONIBLE' => 'Disponible',
        'EN_SERVICIO' => 'En servicio',
        'MANTENCION' => 'En mantención',
        'CARROZA' => 'Carroza',
        'CARROZA_PANORAMICA' => 'Carroza panorámica',
        'FURGON_TRASLADO' => 'Furgón de traslado',
        'VEHICULO_ACOMPANAMIENTO' => 'Vehículo de apoyo',

        // Tipos de servicio funerario
        'VELATORIO_Y_SEPULTACION' => 'Velatorio y sepultación',
        'CREMACION' => 'Cremación',
        'TRASLADO_REGIONAL' => 'Traslado regional',
        'TRASLADO_NACIONAL' => 'Traslado nacional',

        // Urnas e inventario
        'ESTANDAR' => 'Estándar adulto',
        'NINO_SEREMI' => 'Niño (Norma Seremi)',
        'ESPECIAL_XL' => 'Especial XL',
        'LISA' => 'Lisa',
        'PALOMA_BIBLIA' => 'Paloma y Biblia',
        'MARQUETEADA' => 'Marqueteada',
        'PERSONALIZADA' => 'Personalizada',
        'SAN_JAVIER' => 'San Javier',
        'SANTIAGO' => 'Santiago',

        // Movimientos de bodega (Kardex)
        'INGRESO_PROVEEDOR' => 'Ingreso de proveedor',
        'TRASPASO_TALLER_SALON' => 'Traspaso Taller → Salón',
        'REBAJA_VENTA' => 'Salida por servicio',
        'AJUSTE_INVENTARIO' => 'Ajuste / Salida bodega',

        // Finanzas y convenios
        'IPS' => 'IPS',
        'AFP' => 'AFP',
        'FFAA_ORDEN' => 'FF.AA. y Orden',
        'SINDICATO' => 'Sindicato / Empresa',
        'PARTICULAR' => 'Particular',
        'EFECTIVO' => 'Efectivo',
        'TRANSFERENCIA' => 'Transferencia',
        'TARJETA_DEBITO' => 'Tarjeta de débito',
        'TARJETA_CREDITO' => 'Tarjeta de crédito',
        'CONVENIO_DIRECTO' => 'Convenio directo',

        // Documentos legales
        'CERTIFICADO_DEFUNCION' => 'Certificado de defunción',
        'PASE_SEPULTACION' => 'Pase de sepultación',
        'CARNET_CONTRATANTE' => 'Cédula contratante',
        'CARNET_FALLECIDO' => 'Cédula fallecido',
        'OFICIO_FISCALIA_SML' => 'Oficio Fiscalía / SML',
        'RESOLUCION_SEREMI' => 'Resolución Seremi',
        'CONTRATO_FIRMADO' => 'Contrato firmado',
        'HOJA_MEDIDAS_CEMENTERIO' => 'Hoja de medidas',
        'OTRO' => 'Otro documento',

        // Disponibilidad de personal y roles
        'ACTIVO' => 'Disponible',
        'VACACIONES' => 'Vacaciones',
        'PERMISO' => 'Permiso administrativo',
        'LICENCIA_MEDICA' => 'Licencia médica',
        'admin' => 'Administración',
        'coordinador' => 'Coordinación',
        'bodega' => 'Bodega y taller',
        'chofer' => 'Flota y terreno',
        'finanzas' => 'Finanzas y caja',

        // Eventos calendario
        'INSTALACION' => 'Instalación',
        'VELATORIO' => 'Velatorio',
        'MISA' => 'Misa / Responso',
        'FUNERAL' => 'Funeral',
        'TRASLADO' => 'Traslado',
    ];

    public static function texto(?string $codigo, string $porDefecto = 'Sin definir'): string
    {
        if ($codigo === null || trim($codigo) === '') {
            return $porDefecto;
        }

        if (isset(self::MAPA[$codigo])) {
            return self::MAPA[$codigo];
        }

        return ucfirst(strtolower(str_replace('_', ' ', $codigo)));
    }
}
