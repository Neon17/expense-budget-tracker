<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait HasDateScopes
{
    /**
     * Scope a query to filter by month (YYYY-MM format).
     * Works with MySQL, PostgreSQL, and SQLite.
     */
    public function scopeMonth(Builder $query, string $month): Builder
    {
        $dateColumn = $this->getDateColumn();
        
        // Parse month string to get year and month
        [$year, $monthNum] = explode('-', $month);
        
        // Use date range approach - works with all databases
        $startDate = "{$year}-{$monthNum}-01";
        $endDate = date('Y-m-t', strtotime($startDate)); // Last day of month
        
        return $query->whereBetween($dateColumn, [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by year (YYYY format).
     * Works with MySQL, PostgreSQL, and SQLite.
     */
    public function scopeYear(Builder $query, string $year): Builder
    {
        $dateColumn = $this->getDateColumn();
        
        $startDate = "{$year}-01-01";
        $endDate = "{$year}-12-31";
        
        return $query->whereBetween($dateColumn, [$startDate, $endDate]);
    }

    /**
     * Get the date column name for this model.
     */
    protected function getDateColumn(): string
    {
        return property_exists($this, 'dateColumn') ? $this->dateColumn : 'date';
    }

    /**
     * Get database-agnostic year-month expression for grouping.
     */
    public static function yearMonthExpression(string $column = 'date'): string
    {
        $driver = DB::getDriverName();
        
        return match ($driver) {
            'mysql', 'mariadb' => "DATE_FORMAT({$column}, '%Y-%m')",
            'pgsql' => "TO_CHAR({$column}, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', {$column})",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }

    /**
     * Get database-agnostic year expression for grouping.
     */
    public static function yearExpression(string $column = 'date'): string
    {
        $driver = DB::getDriverName();
        
        return match ($driver) {
            'mysql', 'mariadb' => "YEAR({$column})",
            'pgsql' => "EXTRACT(YEAR FROM {$column})",
            'sqlite' => "strftime('%Y', {$column})",
            default => "YEAR({$column})",
        };
    }

    /**
     * Get database-agnostic month expression for grouping.
     */
    public static function monthExpression(string $column = 'date'): string
    {
        $driver = DB::getDriverName();
        
        return match ($driver) {
            'mysql', 'mariadb' => "MONTH({$column})",
            'pgsql' => "EXTRACT(MONTH FROM {$column})",
            'sqlite' => "strftime('%m', {$column})",
            default => "MONTH({$column})",
        };
    }

    /**
     * Get database-agnostic date expression for grouping by day.
     */
    public static function dateExpression(string $column = 'date'): string
    {
        $driver = DB::getDriverName();
        
        return match ($driver) {
            'mysql', 'mariadb' => "DATE({$column})",
            'pgsql' => "DATE({$column})",
            'sqlite' => "date({$column})",
            default => "DATE({$column})",
        };
    }
}
