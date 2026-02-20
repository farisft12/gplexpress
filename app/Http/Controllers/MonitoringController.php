<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Models\PaymentTransaction;
use App\Models\Shipment;

class MonitoringController extends Controller
{
    /**
     * Comprehensive health check endpoint
     * Returns 200 if healthy, 503 if unhealthy
     */
    public function health(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'queue' => $this->checkQueue(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        $healthy = collect($checks)->every(fn($check) => $check['status'] === 'ok');
        $statusCode = $healthy ? 200 : 503;

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $statusCode);
    }

    /**
     * Detailed system metrics
     * Requires admin authentication
     */
    public function metrics(): JsonResponse
    {
        return response()->json([
            'timestamp' => now()->toIso8601String(),
            'application' => [
                'version' => config('app.version', '1.0.0'),
                'environment' => config('app.env'),
            ],
            'database' => $this->getDatabaseMetrics(),
            'queue' => $this->getQueueMetrics(),
            'payments' => $this->getPaymentMetrics(),
            'shipments' => $this->getShipmentMetrics(),
        ]);
    }

    /**
     * Queue health monitoring
     */
    public function queueHealth(): JsonResponse
    {
        $metrics = $this->getQueueMetrics();
        
        $healthy = $metrics['failed_jobs'] < 100 && 
                   $metrics['pending_jobs'] < 1000 &&
                   $metrics['queue_backlog_seconds'] < 300;

        return response()->json([
            'status' => $healthy ? 'healthy' : 'degraded',
            'metrics' => $metrics,
        ], $healthy ? 200 : 503);
    }

    /**
     * Failed jobs summary
     */
    public function failedJobs(): JsonResponse
    {
        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(50)
            ->get(['id', 'uuid', 'queue', 'failed_at', 'exception']);

        return response()->json([
            'total_failed' => DB::table('failed_jobs')->count(),
            'recent_failures' => $failedJobs->map(function ($job) {
                return [
                    'id' => $job->id,
                    'uuid' => $job->uuid,
                    'queue' => $job->queue,
                    'failed_at' => $job->failed_at,
                    'exception_preview' => substr($job->exception, 0, 200),
                ];
            }),
        ]);
    }

    /**
     * Check database connectivity
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $responseTime = $this->measureTime(function () {
                DB::select('SELECT 1');
            });
            
            return [
                'status' => 'ok',
                'response_time_ms' => round($responseTime * 1000, 2),
            ];
        } catch (\Exception $e) {
            Log::error('Database health check failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'error',
                'error' => 'Database connection failed',
            ];
        }
    }

    /**
     * Check queue system
     */
    private function checkQueue(): array
    {
        try {
            $connection = config('queue.default');
            $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();
            
            return [
                'status' => $pending < 1000 && $failed < 100 ? 'ok' : 'warning',
                'connection' => $connection,
                'pending_jobs' => $pending,
                'failed_jobs' => $failed,
            ];
        } catch (\Exception $e) {
            Log::error('Queue health check failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'error',
                'error' => 'Queue check failed',
            ];
        }
    }

    /**
     * Check cache system
     */
    private function checkCache(): array
    {
        try {
            $key = 'health_check_' . time();
            Cache::put($key, 'test', 10);
            $value = Cache::get($key);
            Cache::forget($key);
            
            return [
                'status' => $value === 'test' ? 'ok' : 'error',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            Log::error('Cache health check failed', ['error' => $e->getMessage()]);
            return [
                'status' => 'error',
                'error' => 'Cache check failed',
            ];
        }
    }

    /**
     * Check storage writability
     */
    private function checkStorage(): array
    {
        $paths = [
            'storage' => storage_path(),
            'logs' => storage_path('logs'),
            'cache' => storage_path('framework/cache'),
        ];

        $writable = collect($paths)->every(function ($path) {
            return is_writable($path);
        });

        return [
            'status' => $writable ? 'ok' : 'error',
            'paths' => $paths,
        ];
    }

    /**
     * Get database metrics
     */
    private function getDatabaseMetrics(): array
    {
        try {
            // Get connection count (PostgreSQL)
            $connections = DB::selectOne("
                SELECT count(*) as count 
                FROM pg_stat_activity 
                WHERE datname = current_database()
            ");

            // Get slow queries (queries taking > 1 second)
            $slowQueries = DB::select("
                SELECT query, mean_exec_time, calls
                FROM pg_stat_statements
                WHERE mean_exec_time > 1000
                ORDER BY mean_exec_time DESC
                LIMIT 10
            ");

            return [
                'active_connections' => $connections->count ?? 0,
                'slow_queries' => count($slowQueries),
                'top_slow_queries' => collect($slowQueries)->map(function ($query) {
                    return [
                        'mean_time_ms' => round($query->mean_exec_time, 2),
                        'calls' => $query->calls,
                        'query_preview' => substr($query->query, 0, 100),
                    ];
                }),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Metrics unavailable'];
        }
    }

    /**
     * Get queue metrics
     */
    private function getQueueMetrics(): array
    {
        $pending = DB::table('jobs')->count();
        $failed = DB::table('failed_jobs')->count();
        
        // Calculate queue backlog (oldest pending job)
        $oldestJob = DB::table('jobs')
            ->orderBy('created_at', 'asc')
            ->first();
        
        $backlogSeconds = $oldestJob 
            ? now()->diffInSeconds($oldestJob->created_at) 
            : 0;

        return [
            'pending_jobs' => $pending,
            'failed_jobs' => $failed,
            'queue_backlog_seconds' => $backlogSeconds,
            'queues' => [
                'default' => DB::table('jobs')->where('queue', 'default')->count(),
                'payments' => DB::table('jobs')->where('queue', 'payments')->count(),
            ],
        ];
    }

    /**
     * Get payment metrics
     */
    private function getPaymentMetrics(): array
    {
        $last24h = now()->subDay();
        
        return [
            'total_pending' => PaymentTransaction::where('status', 'pending')->count(),
            'total_failed' => PaymentTransaction::whereIn('status', ['expire', 'deny', 'cancel'])->count(),
            'failed_last_24h' => PaymentTransaction::whereIn('status', ['expire', 'deny', 'cancel'])
                ->where('created_at', '>=', $last24h)
                ->count(),
            'unprocessed_settlements' => PaymentTransaction::where('status', 'settlement')
                ->where('is_processed', false)
                ->count(),
        ];
    }

    /**
     * Get shipment metrics
     */
    private function getShipmentMetrics(): array
    {
        return [
            'total_shipments' => Shipment::count(),
            'in_transit' => Shipment::whereIn('status', ['diproses', 'dalam_pengiriman', 'sampai_di_cabang_tujuan'])->count(),
            'pending_cod' => Shipment::where('type', 'cod')
                ->where('cod_status', 'belum_lunas')
                ->where('status', 'sampai_di_cabang_tujuan')
                ->count(),
        ];
    }

    /**
     * Measure execution time of a callable
     */
    private function measureTime(callable $callback): float
    {
        $start = microtime(true);
        $callback();
        return microtime(true) - $start;
    }
}
