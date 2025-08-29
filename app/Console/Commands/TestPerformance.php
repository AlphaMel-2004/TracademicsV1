<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ComplianceService;
use App\Models\User;
use App\Models\Department;
use App\Models\Program;
use App\Models\ComplianceDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TestPerformance extends Command
{
    protected $signature = 'test:performance';
    protected $description = 'Test system performance optimizations';

    public function handle()
    {
        $this->info('Testing TracAdemics Performance Optimizations...');
        
        // Test 1: Dashboard Query Performance
        $this->testDashboardPerformance();
        
        // Test 2: Compliance Service Performance  
        $this->testComplianceServicePerformance();
        
        // Test 3: Database Index Performance
        $this->testIndexPerformance();
        
        // Test 4: Cache Performance
        $this->testCachePerformance();
        
        $this->info('Performance tests completed!');
    }

    private function testDashboardPerformance()
    {
        $this->info('Testing Dashboard Query Performance...');
        
        // Test VPAA dashboard data
        $startTime = microtime(true);
        
        $departments = Department::orderBy('name', 'asc')
            ->leftJoin('users', 'departments.id', '=', 'users.department_id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->where('roles.name', 'Faculty Member')
            ->select('departments.*')
            ->selectRaw('COUNT(users.id) as total_faculty')
            ->selectRaw('SUM(CASE WHEN users.faculty_type = "part-time" THEN 1 ELSE 0 END) as part_time')
            ->selectRaw('SUM(CASE WHEN users.faculty_type = "full-time" THEN 1 ELSE 0 END) as full_time')
            ->groupBy('departments.id', 'departments.name', 'departments.created_at', 'departments.updated_at')
            ->get();
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        $this->info("VPAA Dashboard Query: {$executionTime}ms ({$departments->count()} departments)");
        
        // Test individual department stats
        $startTime = microtime(true);
        $facultyCount = User::facultyMembers()->count();
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        $this->info("Faculty Count Query (with scope): {$executionTime}ms ({$facultyCount} faculty)");
    }

    private function testComplianceServicePerformance()
    {
        $this->info('Testing Compliance Service Performance...');
        
        $complianceService = new ComplianceService();
        
        // Test compliance rate calculation
        $startTime = microtime(true);
        $rate = $complianceService->calculateComplianceRate();
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        $this->info("Compliance Rate Calculation: {$executionTime}ms (Rate: {$rate}%)");
        
        // Test compliance chart data
        $startTime = microtime(true);
        $chartData = $complianceService->getComplianceChartData();
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        $this->info("Compliance Chart Data: {$executionTime}ms (Total: {$chartData['total']})");
        
        // Test with department filter
        $department = Department::first();
        if ($department) {
            $startTime = microtime(true);
            $deptRate = $complianceService->calculateComplianceRate($department->id);
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000;
            
            $this->info("Department Compliance Rate: {$executionTime}ms (Dept: {$department->name}, Rate: {$deptRate}%)");
        }
    }

    private function testIndexPerformance()
    {
        $this->info('Testing Database Index Performance...');
        
        // Test queries that should benefit from indexes
        $queries = [
            "SELECT COUNT(*) FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = 'Faculty Member'",
            "SELECT COUNT(*) FROM compliance_documents WHERE status = 'Complied'",
            "SELECT COUNT(*) FROM faculty_assignments fa JOIN users u ON fa.faculty_id = u.id WHERE u.department_id = 1",
        ];
        
        foreach ($queries as $i => $query) {
            $startTime = microtime(true);
            $result = DB::select($query);
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000;
            
            $this->info("Index Test Query " . ($i + 1) . ": {$executionTime}ms");
        }
    }

    private function testCachePerformance()
    {
        $this->info('Testing Cache Performance...');
        
        $complianceService = new ComplianceService();
        
        // Clear cache first
        Cache::flush();
        
        // First call (should hit database)
        $startTime = microtime(true);
        $rate1 = $complianceService->calculateComplianceRate();
        $endTime = microtime(true);
        $firstCall = ($endTime - $startTime) * 1000;
        
        // Second call (should hit cache)
        $startTime = microtime(true);
        $rate2 = $complianceService->calculateComplianceRate();
        $endTime = microtime(true);
        $secondCall = ($endTime - $startTime) * 1000;
        
        $speedup = $firstCall / $secondCall;
        
        $this->info("Cache Test - First call: {$firstCall}ms, Second call: {$secondCall}ms");
        $this->info("Cache speedup: {$speedup}x faster");
    }
}
