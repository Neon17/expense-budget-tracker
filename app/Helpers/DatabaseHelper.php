<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class DatabaseHelper
{
    /**
     * Get database-agnostic year expression.
     */
    public static function yearExpression(string $column): string
    {
        $driver = DB::getDriverName();
        
        return match ($driver) {
            'mysql', 'mariadb' => "YEAR({$column})",
            'pgsql' => "EXTRACT(YEAR FROM {$column})::integer",
            'sqlite' => "CAST(strftime('%Y', {$column}) AS INTEGER)",
            default => "YEAR({$column})",
        };
    }

    /**
     * Get database-agnostic month expression.
     */
    public static function monthExpression(string $column): string
    {
        $driver = DB::getDriverName();
        
        return match ($driver) {
            'mysql', 'mariadb' => "MONTH({$column})",
            'pgsql' => "EXTRACT(MONTH FROM {$column})::integer",
            'sqlite' => "CAST(strftime('%m', {$column}) AS INTEGER)",
            default => "MONTH({$column})",
        };
    }

    /**
     * Get database-agnostic year-month expression.
     */
    public static function yearMonthExpression(string $column): string
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
     * Get database-agnostic date expression.
     */
    public static function dateExpression(string $column): string
    {
        $driver = DB::getDriverName();
        
        return match ($driver) {
            'mysql', 'mariadb' => "DATE({$column})",
            'pgsql' => "{$column}::date",
            'sqlite' => "date({$column})",
            default => "DATE({$column})",
        };
    }

    /**
     * Get database driver name.
     */
    public static function getDriver(): string
    {
        return DB::getDriverName();
    }
}
