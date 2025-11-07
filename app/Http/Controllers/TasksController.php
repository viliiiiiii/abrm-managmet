<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controller;
use App\Auth\Permissions;
use App\Model\Task;
use App\Model\Building;
use App\Util\Csv;
use App\Util\Xlsx;

final class TasksController extends Controller
{
    public function index(): string
    {
        Permissions::authorize('tasks.view');
        $filters = [
            'building_id' => $_GET['building_id'] ?? null,
            'status' => $_GET['status'] ?? null,
            'assigned_to' => isset($_GET['assigned_to']) ? trim((string)$_GET['assigned_to']) : null,
        ];
        $cursor = $_GET['cursor'] ?? null;
        $tasks = Task::paginate(20, $cursor, $filters);
        return $this->view('tasks/index', [
            'tasks' => $tasks,
            'filters' => $filters,
            'buildings' => Building::all(),
        ]);
    }

    public function exportCsv(): void
    {
        Permissions::authorize('tasks.export');
        $rows = Task::paginate(1000)['data'];
        Csv::stream(
            'tasks.csv',
            ['Title', 'Building', 'Room', 'Assigned To', 'Status', 'Due Date'],
            array_map(
                fn($row) => [
                    $row['title'],
                    $row['building_name'],
                    $row['room_name'],
                    $row['assigned_to'],
                    $row['status'],
                    $row['due_date'],
                ],
                $rows
            )
        );
    }

    public function exportXlsx(): void
    {
        Permissions::authorize('tasks.export');
        $rows = Task::paginate(1000)['data'];
        Xlsx::stream(
            'tasks.xlsx',
            ['Title', 'Building', 'Room', 'Assigned To', 'Status', 'Due Date'],
            array_map(
                fn($row) => [
                    $row['title'],
                    $row['building_name'],
                    $row['room_name'],
                    $row['assigned_to'],
                    $row['status'],
                    $row['due_date'],
                ],
                $rows
            )
        );
    }
}
