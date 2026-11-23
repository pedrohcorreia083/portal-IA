<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Semeadura padrão de uma instalação nova.
 *
 * Cria apenas o necessário para o sistema operar: um administrador e o
 * catálogo base de produtos. Dados de demonstração ficam no DemoSeeder.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            ProductsSeeder::class,
        ]);
    }
}
