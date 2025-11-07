<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controller;
use App\Auth\Permissions;
use App\Model\Task;

final class ExportsController extends Controller
{
    public function tasksPdf(): void
    {
        Permissions::authorize('exports.tasks');
        $rows = Task::paginate(1000)['data'];
        if (class_exists('TCPDF')) {
            $pdf = new \TCPDF();
            $pdf->SetTitle('Tasks Export');
            $pdf->AddPage();
            $html = '<h1>Tasks</h1><table border="1" cellpadding="4"><tr><th>Title</th><th>Status</th><th>Sector</th></tr>';
            foreach ($rows as $row) {
                $html .= '<tr><td>' . htmlspecialchars($row['title']) . '</td><td>' . htmlspecialchars($row['status']) . '</td><td>' . htmlspecialchars($row['sector_name']) . '</td></tr>';
            }
            $html .= '</table>';
            $pdf->writeHTML($html);
            $pdf->Output('tasks.pdf', 'I');
            return;
        }
        header('Content-Type: text/html');
        echo '<h1>Tasks</h1>';
        foreach ($rows as $row) {
            echo '<div><strong>' . htmlspecialchars($row['title']) . '</strong> - ' . htmlspecialchars($row['status']) . '</div>';
        }
    }
}
