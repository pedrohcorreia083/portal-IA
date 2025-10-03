<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Validator::extend('cnpj', function ($attribute, $value, $parameters, $validator) {
            // Remover caracteres não numéricos
            $cnpj = preg_replace('/[^0-9]/', '', $value);

            // Verificar se tem 14 dígitos
            if (strlen($cnpj) != 14) {
                return false;
            }

            // Validar CNPJ
            return $this->validateCnpj($cnpj);
        });

        Validator::replacer('cnpj', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, 'O CNPJ informado é inválido.');
        });
    }

    /**
     * Validar CNPJ
     */
    private function validateCnpj($cnpj): bool
    {
        // CNPJs conhecidamente inválidos
        $invalidos = [
            '00000000000000',
            '11111111111111',
            '22222222222222',
            '33333333333333',
            '44444444444444',
            '55555555555555',
            '66666666666666',
            '77777777777777',
            '88888888888888',
            '99999999999999'
        ];

        if (in_array($cnpj, $invalidos)) {
            return false;
        }

        // Validar dígitos verificadores
        for ($t = 12; $t < 14; $t++) {
            for ($d = 0, $p = $t - 7, $c = 0; $c < $t; $c++) {
                $d += $cnpj[$c] * $p;
                $p = ($p < 3) ? 9 : --$p;
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cnpj[$c] != $d) {
                return false;
            }
        }

        return true;
    }
}
