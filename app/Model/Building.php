<?php
declare(strict_types=1);

namespace App\Model;

final class Building
{
    public static function all(): array
    {
        $stmt = DB::ops()->query('SELECT id, name, created_at FROM buildings ORDER BY name');
        return $stmt->fetchAll();
    }
}
