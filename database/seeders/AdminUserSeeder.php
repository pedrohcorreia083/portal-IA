<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador LBF',
            'email' => 'admin@lbfportal.com',
            'password' => Hash::make('C@adead0'),
            'email_verified_at' => now(),
        ]);

        $this->command->info('Usuário administrador criado!');
        $this->command->info('Email: admin@lbfportal.com');
        $this->command->info('Senha: C@adead0');
    }
}