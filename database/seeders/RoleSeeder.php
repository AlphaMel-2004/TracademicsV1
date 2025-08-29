<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'MIS', 'scope' => 'system'],
            ['name' => 'VPAA', 'scope' => 'institution'],
            ['name' => 'Dean', 'scope' => 'department'],
            ['name' => 'Program Head', 'scope' => 'program'],
            ['name' => 'Faculty Member', 'scope' => 'self'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(['name' => $role['name']], $role);
        }
    }
}



