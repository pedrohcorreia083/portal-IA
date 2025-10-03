<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['code' => 'AN001', 'name' => 'Anel Solitário', 'category' => 'AN', 'coating' => 'DO'],
            ['code' => 'AN002', 'name' => 'Anel Pavê', 'category' => 'AN', 'coating' => 'RH'],
            ['code' => 'BR001', 'name' => 'Brinco Argola', 'category' => 'BR', 'coating' => 'DO'],
            ['code' => 'BR002', 'name' => 'Brinco Pedra', 'category' => 'BR', 'coating' => 'RH'],
            ['code' => 'C001', 'name' => 'Colar Corrente', 'category' => 'C', 'coating' => 'DO'],
            ['code' => 'PU001', 'name' => 'Pulseira Tennis', 'category' => 'PU', 'coating' => 'RH'],
            ['code' => 'PI001', 'name' => 'Pingente Coração', 'category' => 'PI', 'coating' => 'DO'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}