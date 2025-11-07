<?php
declare(strict_types=1);

namespace App\Model;

final class Sector
{
    public static function all(): array
    {
        $stmt = DB::ops()->query('SELECT id, name, description FROM sectors ORDER BY name');
        return $stmt->fetchAll();
    }
}
