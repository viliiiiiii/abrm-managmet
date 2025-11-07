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
    <h2 style="margin-top:0;">Profile</h2>
    <p>Name: <?= htmlspecialchars($user->name ?? '') ?></p>
    <p>Email: <?= htmlspecialchars($user->email ?? '') ?></p>
    <p>Last login: <?= htmlspecialchars($user->last_login_at ?? '—') ?></p>
    <button class="primary" data-modal="password-modal">Change password</button>
</section>
<div id="password-modal" class="modal-backdrop">
    <div class="modal">
        <div class="flex flex-between">
            <h3 style="margin-top:0;">Password update</h3>
            <button data-close>✕</button>
        </div>
        <p>Password management is being finalized. Please contact an administrator.</p>
        <button class="primary" data-close>Close</button>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
