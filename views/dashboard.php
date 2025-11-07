<?php
declare(strict_types=1);
use App\Auth\Auth;
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
        <div>
            <h2 style="margin:0;font-size:2rem;">Welcome back, <?= htmlspecialchars($user->name ?? 'Guest') ?></h2>
            <p style="margin:0.25rem 0 0;opacity:0.7;">Here is an overview of today.</p>
        </div>
        <div class="badge">Notifications <?= htmlspecialchars((string)count($notifications)) ?></div>
    </div>
</section>
<section class="grid grid-3">
    <?php foreach ($tasksByStatus as $status => $count): ?>
        <div class="card" data-animate>
            <h3 style="margin:0 0 0.5rem;">Status: <?= htmlspecialchars(ucfirst($status)) ?></h3>
            <p style="font-size:2rem;font-weight:700;"><?= htmlspecialchars((string)$count) ?></p>
        </div>
    <?php endforeach; ?>
    <div class="card" data-animate>
        <h3 style="margin:0 0 0.5rem;">Low stock alerts</h3>
        <?php if (!$lowStock): ?>
            <p>No low stock items 🎉</p>
        <?php else: ?>
            <ul style="margin:0;padding:0;list-style:none;display:grid;gap:0.35rem;">
                <?php foreach ($lowStock as $item): ?>
                    <li><?= htmlspecialchars($item['name']) ?> <span class="badge">Qty <?= htmlspecialchars((string)$item['quantity']) ?></span></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>
<section class="card" data-animate>
    <h3 style="margin-top:0;">Recent notifications</h3>
    <ul style="margin:0;padding:0;list-style:none;display:grid;gap:0.5rem;">
        <?php foreach ($notifications as $note): ?>
            <li style="padding:0.75rem 1rem;border-radius:0.75rem;background:rgba(37,99,235,0.08);">
                <strong><?= htmlspecialchars($note['category'] ?? 'system') ?></strong> — <?= htmlspecialchars($note['message'] ?? '') ?>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
