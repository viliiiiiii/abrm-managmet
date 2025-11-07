<?php
declare(strict_types=1);

namespace App\Model;

use App\Util\Paginator;

final class Inventory
{
    public static function paginate(int $limit = 20, ?string $cursor = null, array $filters = []): array
    {
        $sql = 'SELECT i.id, i.sku, i.name, i.sector_id, i.quantity, i.min_stock, i.location, i.created_at, i.updated_at, s.name AS sector_name'
            . ' FROM inventory_items i'
            . ' LEFT JOIN sectors s ON s.id = i.sector_id';
        $params = [];
        $conditions = [];
        if (!empty($filters['sector_id'])) {
            $conditions[] = 'i.sector_id = :sector_id';
            $params['sector_id'] = (int)$filters['sector_id'];
        }
        if (!empty($filters['q'])) {
            $conditions[] = '(i.name LIKE :q OR i.sku LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }
        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY i.id DESC';
        return Paginator::cursor(DB::ops(), $sql, $params, $limit, $cursor, 'id');
    }

    public static function lowStock(int $limit = 5): array
    {
        $stmt = DB::ops()->prepare('SELECT id, name, sku, quantity, min_stock FROM inventory_items WHERE quantity <= min_stock ORDER BY quantity ASC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
