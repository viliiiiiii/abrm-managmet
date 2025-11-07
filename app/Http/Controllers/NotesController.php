<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controller;
use App\Auth\Permissions;
use App\Model\Note;

final class NotesController extends Controller
{
    public function index(): string
    {
        Permissions::authorize('notes.view');
        $filters = [
            'tag' => $_GET['tag'] ?? null,
        ];
        $cursor = $_GET['cursor'] ?? null;
        $notes = Note::paginate(20, $cursor, $filters);
        return $this->view('notes/index', [
            'notes' => $notes,
            'filters' => $filters,
        ]);
    }
}
