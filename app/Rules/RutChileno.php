<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RutChileno implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $rut = strtoupper(preg_replace('/[^0-9kK]/', '', (string) $value));

        if (strlen($rut) < 8 || strlen($rut) > 9) {
            $fail('El RUT debe tener entre 7 y 8 dígitos más su dígito verificador (ej: 12.345.678-9).');

            return;
        }

        $cuerpo = substr($rut, 0, -1);
        $dvIngresado = substr($rut, -1);

        if (! ctype_digit($cuerpo) || preg_match('/^(\d)\1+$/', $cuerpo)) {
            $fail('El RUT ingresado no es válido.');

            return;
        }

        $suma = 0;
        $multiplo = 2;
        for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
            $suma += (int) $cuerpo[$i] * $multiplo;
            $multiplo = $multiplo === 7 ? 2 : $multiplo + 1;
        }

        $resto = 11 - ($suma % 11);
        $dvCalculado = match ($resto) {
            11 => '0',
            10 => 'K',
            default => (string) $resto,
        };

        if ($dvIngresado !== $dvCalculado) {
            $fail("El dígito verificador del RUT es incorrecto (debería terminar en -{$dvCalculado}).");
        }
    }

    public static function formatear(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $limpio = strtoupper(preg_replace('/[^0-9kK]/', '', $value));
        if (strlen($limpio) < 2) {
            return trim($value);
        }

        $cuerpo = substr($limpio, 0, -1);
        $dv = substr($limpio, -1);

        return number_format((int) $cuerpo, 0, '', '.').'-'.$dv;
    }
}