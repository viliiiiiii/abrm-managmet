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
        <h2 style="margin:0;">Users</h2>
        <button class="primary" data-modal="user-modal">Invite</button>
    </div>
    <table class="table" style="margin-top:1rem;">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th></tr></thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr data-context="users-menu">
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['role_name'] ?? '—') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<div id="users-menu" class="context-menu">
    <button data-modal="user-modal">Edit</button>
    <button>Reset password</button>
</div>
<div id="user-modal" class="modal-backdrop">
    <div class="modal">
        <div class="flex flex-between">
            <h3 style="margin-top:0;">User management</h3>
            <button data-close>✕</button>
        </div>
        <p>Role management will arrive in a subsequent milestone.</p>
        <button class="primary" data-close>Close</button>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
