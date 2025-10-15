<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;
use App\Models\ServiceOrder;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerCustomValidators();
        $this->registerGates();
    }

    /**
     * Registrar validadores customizados
     */
    private function registerCustomValidators(): void
    {
        // Seu código atual de validação CNPJ...
        Validator::extend('cnpj', function ($attribute, $value, $parameters, $validator) {
            $cnpj = preg_replace('/[^0-9]/', '', $value);
            if (strlen($cnpj) != 14) {
                return false;
            }
            return $this->validateCnpj($cnpj);
        });

        Validator::replacer('cnpj', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, 'O CNPJ informado é inválido.');
        });
    }


    private function registerGates(): void
    {
        // Gate para análise técnica - CORRIGIDO
        Gate::define('technical-analysis', function (User $user, ServiceOrder $serviceOrder) {
            return ($user->isAdmin() || $user->isTechnical()) && $serviceOrder->canBeAnalyzed();
        });

        // ... outros gates permanecem iguais
        Gate::define('update-service-order-status', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('edit-service-order', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('delete-service-order', function (User $user) {
            return $user->isAdmin();
        });
    }
    /**
     * Validar CNPJ (seu código atual)
     */
    private function validateCnpj($cnpj): bool
    {
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
