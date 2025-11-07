<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controller;
use App\Auth\Permissions;
use App\Model\Task;
use App\Model\Sector;
use App\Util\Csv;
use App\Util\Xlsx;

final class TasksController extends Controller
{
    public function index(): string
    {
        Permissions::authorize('tasks.view');
        $filters = [
            'type' => $_GET['type'] ?? null,
            'sector_id' => $_GET['sector_id'] ?? null,
            'status' => $_GET['status'] ?? null,
        ];
        $cursor = $_GET['cursor'] ?? null;
        $tasks = Task::paginate(20, $cursor, $filters);
        $sectors = Sector::all();
        return $this->view('tasks/index', [
            'tasks' => $tasks,
            'filters' => $filters,
            'sectors' => $sectors,
        ]);
    }

    public function exportCsv(): void
    {
        Permissions::authorize('tasks.export');
        $rows = Task::paginate(1000)['data'];
        Csv::stream('tasks.csv', ['Title', 'Status', 'Sector'], array_map(fn($row) => [$row['title'], $row['status'], $row['sector_name']], $rows));
    }

    public function exportXlsx(): void
    {
        Permissions::authorize('tasks.export');
        $rows = Task::paginate(1000)['data'];
        Xlsx::stream('tasks.xlsx', ['Title', 'Status', 'Sector'], array_map(fn($row) => [$row['title'], $row['status'], $row['sector_name']], $rows));
    }
}
