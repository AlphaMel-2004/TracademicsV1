<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class MisUserSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create MIS role
        $misRole = Role::firstOrCreate(
            ['name' => 'MIS'],
            ['scope' => 'system']
        );

        // Create MIS user if doesn't exist
        $misUser = User::firstOrCreate(
            ['email' => 'mis@brokenshire.edu.ph'],
            [
                'name' => 'MIS Administrator',
                'password' => Hash::make('password123'), // Default password
                'role_id' => $misRole->id,
                'department_id' => null, // MIS doesn't need department
                'program_id' => null, // MIS doesn't need program
                'faculty_type' => null, // MIS is not faculty
            ]
        );

        $this->command->info('MIS user created:');
        $this->command->info('Email: mis@brokenshire.edu.ph');
        $this->command->info('Password: password123');
        $this->command->info('Please change the password after first login.');
    }
}
