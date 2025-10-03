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
        $user = User::create([
            'name' => 'Administrador LBF',
            'last_name' => 'Sistema', // ← adicione o last_name
            'email' => 'admin@lbfportal.com',
            'password' => Hash::make('C@adead0'),
            'email_verified_at' => now(),
            'type' => 'admin', // ← use o campo type como 'admin'
            'is_active' => true,
            'permissions' => json_encode(['all']), // ← permissões totais
            // company_name e cnpj podem ficar null para admin
        ]);

        $this->command->info('Usuário administrador criado!');
        $this->command->info('Email: admin@lbfportal.com');
        $this->command->info('Senha: C@adead0');
        $this->command->info('Tipo: Administrador');
        $this->command->info('Permissões: Todas');
    }
}
