<?php
declare(strict_types=1);

namespace App\Model;

use App\Util\Paginator;

final class Note
{
    public static function paginate(int $limit = 20, ?string $cursor = null, array $filters = []): array
    {
        $sql = 'SELECT n.id, n.user_id, n.note_date, n.title, n.body, n.created_at, n.updated_at, '
            . 'u.name AS author_name, '
            . 'GROUP_CONCAT(DISTINCT t.name ORDER BY t.name SEPARATOR ", ") AS tag_list'
            . ' FROM notes n'
            . ' LEFT JOIN users u ON u.id = n.user_id'
            . ' LEFT JOIN note_tags nt ON nt.note_id = n.id'
            . ' LEFT JOIN tags t ON t.id = nt.tag_id';
        $params = [];
        if (!empty($filters['tag'])) {
            $sql .= ' WHERE t.name = :tag';
            $params['tag'] = $filters['tag'];
        }
        $sql .= ' GROUP BY n.id ORDER BY n.id DESC';
        return Paginator::cursor(DB::ops(), $sql, $params, $limit, $cursor, 'id');
    }
}
