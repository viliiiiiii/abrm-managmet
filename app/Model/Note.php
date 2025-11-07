<?php
declare(strict_types=1);

namespace App\Model;

use App\Util\Paginator;

final class Note
{
    public static function paginate(int $limit = 20, ?string $cursor = null, array $filters = []): array
    {
        $sql = 'SELECT n.*, u.name AS author FROM notes n LEFT JOIN users u ON u.id = n.user_id';
        $params = [];
        if (!empty($filters['tag'])) {
            $sql .= ' WHERE FIND_IN_SET(:tag, n.tags)';
            $params['tag'] = $filters['tag'];
        }
        $sql .= ' ORDER BY n.pinned DESC, n.created_at DESC';
        return Paginator::cursor(DB::ops(), $sql, $params, $limit, $cursor, 'id');
    }
}
