<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'VPAA User', 'email' => 'vpaa@brokenshire.edu.ph', 'role' => 'VPAA'],
            // Deans per department
            ['name' => 'Dean ASBME', 'email' => 'dean.asbme@brokenshire.edu.ph', 'role' => 'Dean', 'department' => 'ASBME Department'],
            ['name' => 'Dean Allied Health', 'email' => 'dean.allied@brokenshire.edu.ph', 'role' => 'Dean', 'department' => 'ALLIED HEALTH Department'],
            ['name' => 'Dean Nursing', 'email' => 'dean.nursing@brokenshire.edu.ph', 'role' => 'Dean', 'department' => 'Nursing Department'],
            // Program Head samples
            ['name' => 'PH BSIT', 'email' => 'ph.bsit@brokenshire.edu.ph', 'role' => 'Program Head', 'program' => 'BSIT'],
            ['name' => 'PH Nursing', 'email' => 'ph.nursing@brokenshire.edu.ph', 'role' => 'Program Head', 'program' => 'BS Nursing'],
            // Faculty samples
            ['name' => 'Faculty IT', 'email' => 'faculty.it@brokenshire.edu.ph', 'role' => 'Faculty Member', 'program' => 'BSIT'],
            ['name' => 'Faculty Nursing', 'email' => 'faculty.nursing@brokenshire.edu.ph', 'role' => 'Faculty Member', 'program' => 'BS Nursing'],
        ];

        $roleMap = DB::table('roles')->pluck('id', 'name');
        $depMap = DB::table('departments')->pluck('id','name');
        $progMap = DB::table('programs')->pluck('id','name');

        foreach ($users as $u) {
            DB::table('users')->updateOrInsert(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'role_id' => $roleMap[$u['role']] ?? null,
                    'department_id' => isset($u['department']) ? ($depMap[$u['department']] ?? null) : null,
                    'program_id' => isset($u['program']) ? ($progMap[$u['program']] ?? null) : null,
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}



