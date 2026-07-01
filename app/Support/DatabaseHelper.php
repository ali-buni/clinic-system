<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class DatabaseHelper
{
    public static function dateFormat(string $column, string $format): string
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            return "strftime('{$format}', {$column})";
        }
        return "DATE_FORMAT({$column}, '{$format}')";
    }

    public static function hour(string $column): string
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            return "CAST(strftime('%H', {$column}) AS INTEGER)";
        }
        return "HOUR({$column})";
    }

    public static function year(string $column): string
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            return "CAST(strftime('%Y', {$column}) AS INTEGER)";
        }
        return "YEAR({$column})";
    }

    public static function age(string $dobColumn): string
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            return "CAST(strftime('%Y', 'now') AS INTEGER) - CAST(strftime('%Y', {$dobColumn}) AS INTEGER)";
        }
        return "TIMESTAMPDIFF(YEAR, {$dobColumn}, CURDATE())";
    }

    public static function curdate(): string
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            return "DATE('now')";
        }
        return "CURDATE()";
    }

    public static function yearWeek(string $column, int $mode = 1): string
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            return "CAST(strftime('%Y', {$column}) AS INTEGER) * 100 + CAST(strftime('%W', {$column}) AS INTEGER)";
        }
        return "YEARWEEK({$column}, {$mode})";
    }
}
