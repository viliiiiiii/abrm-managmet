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
<section class='card hero-card' data-animate>
    <div class='hero-card__grid'>
        <div>
            <h2 class='hero-card__title'>Welcome back, <?= htmlspecialchars($user->name ?? 'Guest') ?></h2>
            <p class='hero-card__subtitle'>Systems are synchronized and ready. Monitor real-time facility telemetry below.</p>
        </div>
        <div class='hero-card__meta'>
            <span class='badge'>Notifications <?= htmlspecialchars((string)count($notifications)) ?></span>
        </div>
    </div>
</section>
<section class='grid grid-3'>
    <?php foreach ($tasksByStatus as $status => $count): ?>
        <div class='stat-card' data-animate>
            <h3><?= htmlspecialchars(strtoupper($status)) ?></h3>
            <div class='value'><?= htmlspecialchars((string)$count) ?></div>
            <p class='stat-description'><?= htmlspecialchars($status === 'completed' ? 'Tasks cleared today' : 'Tasks currently ' . $status) ?></p>
        </div>
    <?php endforeach; ?>
    <div class='card' data-animate>
        <h3 class='section-title'>Low stock alerts</h3>
        <?php if (!$lowStock): ?>
            <div class='empty-state'>Inventory buffers nominal.</div>
        <?php else: ?>
            <ul class='list'>
                <?php foreach ($lowStock as $item): ?>
                    <li class='list-item'>
                        <span><?= htmlspecialchars($item['name']) ?></span>
                        <span class='badge'>Qty <?= htmlspecialchars((string)$item['quantity']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>
<section class='card' data-animate>
    <h3 class='section-title'>Recent notifications</h3>
    <?php if ($notifications): ?>
        <ul class='list'>
            <?php foreach ($notifications as $note): ?>
                <li class='list-item'>
                    <div style='display:flex;flex-direction:column;'>
                        <strong><?= htmlspecialchars(strtoupper($note['type'] ?? 'SYSTEM')) ?></strong>
                        <span><?= htmlspecialchars($note['title'] ?? ($note['body'] ?? '')) ?></span>
                        <small style='opacity:0.6;'><?= htmlspecialchars($note['created_at'] ?? '') ?></small>
                    </div>
                    <?php if (empty($note['is_read'])): ?><span class='badge'>New</span><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class='empty-state'>All caught up — no new signals.</div>
    <?php endif; ?>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
