<?php

namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Log all database queries for security compliance
        if (config('app.env') === 'production' || config('database.log_queries', false)) {
            DB::listen(function (QueryExecuted $query) {
                $sql = $query->sql;
                $bindings = $query->bindings;
                $time = $query->time;
                $connection = $query->connectionName;

                // Replace bindings in SQL for complete query
                foreach ($bindings as $binding) {
                    $value = is_numeric($binding) ? $binding : "'" . addslashes($binding) . "'";
                    $sql = preg_replace('/\?/', $value, $sql, 1);
                }

                Log::channel('database')->info('Database Query Executed', [
                    'connection' => $connection,
                    'query' => $sql,
                    'execution_time_ms' => $time,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'url' => request()->fullUrl(),
                    'timestamp' => now()->toISOString(),
                ]);
            });
        }
    }
}
