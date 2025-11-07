<?php
declare(strict_types=1);
$nav = [
    ['href' => '/', 'label' => 'Dashboard'],
    ['href' => '/inventory', 'label' => 'Inventory'],
    ['href' => '/tasks', 'label' => 'Tasks'],
    ['href' => '/notes', 'label' => 'Notes'],
    ['href' => '/users', 'label' => 'Users'],
    ['href' => '/profile', 'label' => 'Profile'],
];
ob_start();
?>
<section class="card" data-animate>
    <div class="flex flex-between">
        <h2 style="margin:0;">Tasks</h2>
        <div>
            <a class="badge" href="/exports/tasks.pdf">PDF</a>
            <a class="badge" href="/tasks/export/csv" style="margin-left:0.5rem;">CSV</a>
            <a class="badge" href="/tasks/export/xlsx" style="margin-left:0.5rem;">XLSX</a>
        </div>
    </div>
    <form method="get" style="display:flex;gap:0.75rem;margin-top:1rem;flex-wrap:wrap;">
        <select name="building_id" style="padding:0.75rem;border-radius:0.75rem;border:1px solid rgba(15,23,42,0.15);">
            <option value="">All buildings</option>
            <?php foreach ($buildings as $building): ?>
                <option value="<?= (int)$building['id'] ?>" <?= ($filters['building_id'] ?? '') == $building['id'] ? 'selected' : '' ?>><?= htmlspecialchars($building['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status" style="padding:0.75rem;border-radius:0.75rem;border:1px solid rgba(15,23,42,0.15);">
            <option value="">All status</option>
            <option value="open" <?= ($filters['status'] ?? '') === 'open' ? 'selected' : '' ?>>Open</option>
            <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In progress</option>
            <option value="done" <?= ($filters['status'] ?? '') === 'done' ? 'selected' : '' ?>>Done</option>
        </select>
        <input type="text" name="assigned_to" placeholder="Assignee" value="<?= htmlspecialchars($filters['assigned_to'] ?? '') ?>" style="padding:0.75rem;border-radius:0.75rem;border:1px solid rgba(15,23,42,0.15);flex:1;min-width:160px;">
        <button class="primary">Filter</button>
    </form>
</section>
<section class="card" data-animate>
    <table class="table">
        <thead>
        <tr><th>Title</th><th>Building</th><th>Room</th><th>Assigned</th><th>Status</th><th>Due</th></tr>
        </thead>
        <tbody>
        <?php foreach ($tasks['data'] as $task): ?>
            <tr data-context="tasks-menu">
                <td><?= htmlspecialchars($task['title']) ?></td>
                <td><?= htmlspecialchars($task['building_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($task['room_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($task['assigned_to'] ?? '—') ?></td>
                <td><?= htmlspecialchars($task['status']) ?></td>
                <td><?= htmlspecialchars($task['due_date'] ?? '—') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<div id="tasks-menu" class="context-menu">
    <button data-modal="task-modal">Edit</button>
    <button>Assign</button>
    <button>Mark complete</button>
</div>
<div id="task-modal" class="modal-backdrop">
    <div class="modal">
        <div class="flex flex-between">
            <h3 style="margin-top:0;">Task editor</h3>
            <button data-close>✕</button>
        </div>
        <p>Inline task editing is coming soon. For now, manage tasks via exports.</p>
        <button class="primary" data-close>Close</button>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
