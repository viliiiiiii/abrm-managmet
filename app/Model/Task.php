<?php
declare(strict_types=1);

namespace App\Model;

use App\Util\Paginator;

final class Task
{
    public static function paginate(int $limit = 20, ?string $cursor = null, array $filters = []): array
    {
        $sql = 'SELECT t.*, s.name AS sector_name, u.name AS assignee FROM tasks t LEFT JOIN sectors s ON s.id = t.sector_id LEFT JOIN users u ON u.id = t.assigned_to WHERE 1=1';
        $params = [];
        if (!empty($filters['type'])) {
            $sql .= ' AND t.type = :type';
            $params['type'] = $filters['type'];
        }
        if (!empty($filters['sector_id'])) {
            $sql .= ' AND t.sector_id = :sector_id';
            $params['sector_id'] = (int)$filters['sector_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= ' AND t.status = :status';
            $params['status'] = $filters['status'];
        }
        $sql .= ' ORDER BY t.id DESC';
        return Paginator::cursor(DB::ops(), $sql, $params, $limit, $cursor, 'id');
    }

    public static function countsByStatus(): array
    {
        $stmt = DB::ops()->query('SELECT status, COUNT(*) AS total FROM tasks GROUP BY status');
        $rows = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['status']] = (int)$row['total'];
        }
        return $result;
    }
}
