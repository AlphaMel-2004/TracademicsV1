<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrgSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'ASBME Department',
            'ALLIED HEALTH Department',
            'Nursing Department',
        ];
        foreach ($departments as $d) {
            DB::table('departments')->updateOrInsert(['name' => $d], ['name' => $d]);
        }

        $depIds = DB::table('departments')->pluck('id','name');
        $programs = [
            // ASBME programs
            ['name' => 'BSIT','department' => 'ASBME Department'],
            ['name' => 'BSPsych','department' => 'ASBME Department'],
            ['name' => 'BEED','department' => 'ASBME Department'],
            ['name' => 'BATheo','department' => 'ASBME Department'],
            ['name' => 'BSBusinessAd','department' => 'ASBME Department'],
            ['name' => 'BSHM','department' => 'ASBME Department'],
            // Allied Health umbrella (Pharmacy and MLS)
            ['name' => 'BS Pharmacy','department' => 'ALLIED HEALTH Department'],
            ['name' => 'BS Medical Laboratory Science','department' => 'ALLIED HEALTH Department'],
            // Nursing separate
            ['name' => 'BS Nursing','department' => 'Nursing Department'],
        ];
        foreach ($programs as $p) {
            DB::table('programs')->updateOrInsert(
                ['name' => $p['name']],
                ['name' => $p['name'], 'department_id' => $depIds[$p['department']] ?? null]
            );
        }

        $progIds = DB::table('programs')->pluck('id','name');
        $subjects = [
            ['code' => 'IT101','title' => 'Intro to IT','program' => 'BSIT'],
            ['code' => 'PSY101','title' => 'General Psychology','program' => 'BSPsych'],
            ['code' => 'EDU101','title' => 'Foundations of Education','program' => 'BEED'],
            ['code' => 'THEO101','title' => 'Introduction to Theology','program' => 'BATheo'],
            ['code' => 'BA101','title' => 'Business Fundamentals','program' => 'BSBusinessAd'],
            ['code' => 'HM101','title' => 'Hospitality Basics','program' => 'BSHM'],
            ['code' => 'PHAR101','title' => 'Pharmaceutical Chemistry','program' => 'BS Pharmacy'],
            ['code' => 'MLS101','title' => 'Clinical Microscopy','program' => 'BS Medical Laboratory Science'],
            ['code' => 'NURS101','title' => 'Foundations of Nursing','program' => 'BS Nursing'],
        ];
        foreach ($subjects as $s) {
            DB::table('subjects')->updateOrInsert(
                ['code' => $s['code']],
                ['code' => $s['code'],'title' => $s['title'],'program_id' => $progIds[$s['program']] ?? null]
            );
        }

        $terms = [
            ['name' => '1st Sem','year' => '2025-2026', 'is_active' => true],
            ['name' => '2nd Sem','year' => '2025-2026', 'is_active' => false],
        ];
        foreach ($terms as $t) {
            DB::table('semesters')->updateOrInsert(
                ['name' => $t['name'], 'year' => $t['year']], 
                $t
            );
        }

        $docTypes = [
            'Information Sheet','TOR/Diploma','Certificates of Trainings (past 5 years)','Faculty Load',
            'Syllabus (all courses)','Prelim Test Questions','Prelim Class Record','Midterm Test Questions',
            'Midterm Table of Specifications','Midterm Class Record','Pre-final Test Questions','Pre-final Class Record',
            'Final Test Questions','Final Table of Specifications','Final Class Record','Final Grading Sheet',
        ];
        $i=1; foreach ($docTypes as $name) {
            DB::table('document_types')->updateOrInsert(['name' => $name], ['name' => $name, 'order' => $i++]);
        }
    }
}


