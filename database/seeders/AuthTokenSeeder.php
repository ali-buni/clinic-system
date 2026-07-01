<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AuthTokenSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['owner', 'doctor', 'secretary', 'patient'];

        foreach ($roles as $role) {
            $user = User::role($role)->first();

            if (!$user) {
                $this->command->warn("No user found with role '{$role}' — skipping token.");
                continue;
            }

            $user->tokens()->where('name', 'api-token-' . $role)->delete();

            $token = $user->createToken('api-token-' . $role)->plainTextToken;

            $this->command->info('');
            $this->command->info('╔══════════════════════════════════════════════════════╗');
            $this->command->info("║  Role:     {$role}");
            $this->command->info("║  Email:    {$user->email}");
            $this->command->info("║  Phone:    {$user->phone}");
            $this->command->info("║  Password: password");
            $this->command->info('╠══════════════════════════════════════════════════════╣');
            $this->command->info("║  Token:");
            $this->command->info("║  {$token}");
            $this->command->info('╚══════════════════════════════════════════════════════╝');
            $this->command->info('');

            logger('User API token generated\n', [
                'clinic_id' => $user->id,
                'owner_id'  => $user->id,
                'token'     => $token,
            ]);
        }
    }
}
