<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['code' => 'AN001', 'name' => 'Anel Solitário', 'category' => 'A', 'coating' => 'DO'],
            ['code' => 'AN002', 'name' => 'Anel Pavê', 'category' => 'A', 'coating' => 'RH'],
            ['code' => 'AN003', 'name' => 'Anel Aparador', 'category' => 'A', 'coating' => 'RO'],
            ['code' => 'BR001', 'name' => 'Brinco Argola', 'category' => 'B', 'coating' => 'DO'],
            ['code' => 'BR002', 'name' => 'Brinco Pedra', 'category' => 'B', 'coating' => 'RH'],
            ['code' => 'BR003', 'name' => 'Brinco Ear Cuff', 'category' => 'B', 'coating' => 'NE'],
            ['code' => 'C001', 'name' => 'Colar Corrente', 'category' => 'C', 'coating' => 'DO'],
            ['code' => 'CJ001', 'name' => 'Conjunto Riviera', 'category' => 'CJ', 'coating' => 'RH'],
            ['code' => 'PU001', 'name' => 'Pulseira Tennis', 'category' => 'PU', 'coating' => 'RH'],
            ['code' => 'PU002', 'name' => 'Pulseira Elo', 'category' => 'PU', 'coating' => 'DO'],
            ['code' => 'PI001', 'name' => 'Pingente Coração', 'category' => 'PI', 'coating' => 'DO'],
            ['code' => 'PI002', 'name' => 'Pingente Inicial', 'category' => 'PI', 'coating' => 'MIX'],
        ];

        foreach ($products as $product) {
            // updateOrCreate permite rodar o seeder mais de uma vez.
            Product::updateOrCreate(['code' => $product['code']], $product);
        }
    }
}
