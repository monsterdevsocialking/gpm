<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = trim((string) config('gpm.admin.name'));
        $email = strtolower(trim((string) config('gpm.admin.email')));
        $password = (string) config('gpm.admin.password');

        if ($name === '' && $email === '' && $password === '') {
            $this->command?->warn('Conta inicial não criada: defina GPM_ADMIN_NAME, GPM_ADMIN_EMAIL e GPM_ADMIN_PASSWORD no .env.');
            return;
        }

        if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($password) < 12) {
            throw new RuntimeException('Defina nome, email válido e uma palavra-passe inicial com pelo menos 12 caracteres.');
        }

        if (User::query()->where('email', $email)->exists()) {
            $this->command?->warn('A conta indicada já existe. Nenhuma palavra-passe ou perfil foi alterado.');
            return;
        }

        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $user->forceFill([
            'role' => 'admin',
            'active' => true,
        ])->save();

        $this->command?->info('Administrador inicial criado. Altere a palavra-passe após o primeiro acesso.');
    }
}
