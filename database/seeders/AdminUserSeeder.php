<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@lbfportal.com');
        $password = env('ADMIN_PASSWORD');
        $generated = false;

        if (empty($password)) {
            // Sem senha no ambiente, gera uma aleatória e exibe uma única vez.
            $password = Str::password(16);
            $generated = true;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrador LBF',
                'last_name' => 'Sistema',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'type' => 'admin',
                'is_active' => true,
                'permissions' => ['all'],
            ]
        );

        $this->command->info('Usuário administrador criado/atualizado!');
        $this->command->info("Email: {$user->email}");

        if ($generated) {
            $this->command->warn("Senha gerada (guarde agora, não será exibida novamente): {$password}");
        } else {
            $this->command->info('Senha definida pela variável de ambiente ADMIN_PASSWORD.');
        }
    }
}
