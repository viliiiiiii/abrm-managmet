<?php
declare(strict_types=1);

namespace App\Util;

use PDO;

final class Paginator
{
    public static function cursor(PDO $pdo, string $sql, array $params, int $limit, ?string $cursor, string $cursorColumn): array
    {
        if ($cursor) {
            $sql .= (stripos($sql, 'WHERE') === false ? ' WHERE ' : ' AND ');
            $sql .= sprintf('%s < :cursor', $cursorColumn);
            $params['cursor'] = $cursor;
        }
        $sql .= ' LIMIT :limit';
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $next = null;
        if (count($rows) === $limit) {
            $last = end($rows);
            $next = $last[$cursorColumn] ?? null;
        }
        return [
            'data' => $rows,
            'next_cursor' => $next,
        ];
    }
}
