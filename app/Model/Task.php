<?php
declare(strict_types=1);

namespace App\Model;

use App\Util\Paginator;

final class Task
{
    public static function paginate(int $limit = 20, ?string $cursor = null, array $filters = []): array
    {
        $sql = 'SELECT t.id, t.title, t.status, t.priority, t.due_date, t.assigned_to, t.created_at, t.updated_at, '
            . 'b.name AS building_name, '
            . "COALESCE(r.label, r.room_number) AS room_name"
            . ' FROM tasks t'
            . ' INNER JOIN buildings b ON b.id = t.building_id'
            . ' LEFT JOIN rooms r ON r.id = t.room_id'
            . ' WHERE 1=1';
        $params = [];
        if (!empty($filters['building_id'])) {
            $sql .= ' AND t.building_id = :building_id';
            $params['building_id'] = (int)$filters['building_id'];
        }
        if (!empty($filters['assigned_to'])) {
            $sql .= ' AND t.assigned_to LIKE :assigned_to';
            $params['assigned_to'] = '%' . $filters['assigned_to'] . '%';
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
