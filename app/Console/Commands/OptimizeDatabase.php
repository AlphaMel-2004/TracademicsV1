<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseOptimizationService;
use App\Services\ComplianceService;

class OptimizeDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracademics:optimize-db {--clear-cache : Clear optimization caches} {--maintenance : Run database maintenance} {--metrics : Show performance metrics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize TracAdemics database performance and clear caches';

    protected $databaseOptimizationService;
    protected $complianceService;

    /**
     * Create a new command instance.
     */
    public function __construct(DatabaseOptimizationService $databaseOptimizationService, ComplianceService $complianceService)
    {
        parent::__construct();
        $this->databaseOptimizationService = $databaseOptimizationService;
        $this->complianceService = $complianceService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('TracAdemics Database Optimization');
        $this->info('=====================================');

        if ($this->option('clear-cache')) {
            $this->clearCaches();
        }

        if ($this->option('maintenance')) {
            $this->runMaintenance();
        }

        if ($this->option('metrics')) {
            $this->showMetrics();
        }

        if (!$this->option('clear-cache') && !$this->option('maintenance') && !$this->option('metrics')) {
            $this->showHelp();
        }

        $this->info('Operation completed successfully!');
    }

    /**
     * Clear all optimization caches
     */
    protected function clearCaches()
    {
        $this->info('Clearing optimization caches...');
        
        $this->databaseOptimizationService->clearOptimizationCaches();
        $this->complianceService->clearAllCache();
        
        $this->line('✅ All optimization caches cleared');
    }

    /**
     * Run database maintenance
     */
    protected function runMaintenance()
    {
        $this->info('Running database maintenance...');
        
        $success = $this->databaseOptimizationService->performDatabaseMaintenance();
        
        if ($success) {
            $this->line('✅ Database maintenance completed successfully');
        } else {
            $this->error('❌ Database maintenance failed. Check logs for details.');
        }
    }

    /**
     * Show performance metrics
     */
    protected function showMetrics()
    {
        $this->info('Database Performance Metrics');
        $this->info('============================');

        $metrics = $this->databaseOptimizationService->getDatabasePerformanceMetrics();

        if ($metrics) {
            if (isset($metrics['table_stats'])) {
                $this->info('Table Statistics:');
                $headers = ['Table', 'Rows', 'Size (MB)', 'Index Size (MB)'];
                $rows = [];

                foreach ($metrics['table_stats'] as $stat) {
                    $rows[] = [
                        $stat->table_name,
                        number_format($stat->table_rows),
                        $stat->size_mb,
                        $stat->index_size_mb
                    ];
                }

                $this->table($headers, $rows);
            }

            $this->info('Performance Indicators:');
            $this->line("Cache Hit Rate: {$metrics['cache_hit_rate']}%");
            $this->line("Average Query Time: {$metrics['average_query_time']}ms");
        } else {
            $this->error('Failed to retrieve performance metrics');
        }
    }

    /**
     * Show command help
     */
    protected function showHelp()
    {
        $this->info('Available options:');
        $this->line('--clear-cache   Clear all optimization caches');
        $this->line('--maintenance   Run database maintenance (analyze tables, cleanup logs)');
        $this->line('--metrics       Show current database performance metrics');
        $this->line('');
        $this->info('Examples:');
        $this->line('php artisan tracademics:optimize-db --clear-cache');
        $this->line('php artisan tracademics:optimize-db --maintenance');
        $this->line('php artisan tracademics:optimize-db --metrics');
        $this->line('php artisan tracademics:optimize-db --clear-cache --maintenance');
    }
}
