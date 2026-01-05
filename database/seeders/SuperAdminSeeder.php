<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'superadmin@internalos.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('SuperAdmin@2026'),
                'is_super_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Super Admin created successfully!');
        $this->command->info('📧 Email: superadmin@internalos.com');
        $this->command->info('🔑 Password: SuperAdmin@2026');
        $this->command->warn('⚠️  IMPORTANT: Change this password after first login!');
    }
}
