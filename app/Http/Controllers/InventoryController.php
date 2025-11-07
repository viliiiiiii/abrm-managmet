<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controller;
use App\Auth\Permissions;
use App\Model\Inventory;
use App\Model\Sector;
use App\Util\Csv;
use App\Util\Xlsx;

final class InventoryController extends Controller
{
    public function index(): string
    {
        Permissions::authorize('inventory.view');
        $filters = [
            'sector_id' => $_GET['sector_id'] ?? null,
            'q' => $_GET['q'] ?? null,
        ];
        $cursor = $_GET['cursor'] ?? null;
        $items = Inventory::paginate(20, $cursor, $filters);
        $sectors = Sector::all();
        return $this->view('inventory/index', [
            'items' => $items,
            'filters' => $filters,
            'sectors' => $sectors,
        ]);
    }

    public function exportCsv(): void
    {
        Permissions::authorize('inventory.export');
        $rows = Inventory::paginate(1000)['data'];
        Csv::stream('inventory.csv', ['SKU', 'Name', 'Quantity'], array_map(fn($row) => [$row['sku'], $row['name'], $row['quantity']], $rows));
    }

    public function exportXlsx(): void
    {
        Permissions::authorize('inventory.export');
        $rows = Inventory::paginate(1000)['data'];
        Xlsx::stream('inventory.xlsx', ['SKU', 'Name', 'Quantity'], array_map(fn($row) => [$row['sku'], $row['name'], $row['quantity']], $rows));
    }
}
