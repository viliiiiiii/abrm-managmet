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
        <h2 style="margin:0;">Inventory</h2>
        <div>
            <a class="badge" href="/exports/inventory.csv">CSV</a>
            <a class="badge" href="/exports/inventory.xlsx" style="margin-left:0.5rem;">XLSX</a>
        </div>
    </div>
    <form method="get" style="display:flex;gap:0.75rem;margin-top:1rem;flex-wrap:wrap;">
        <input type="text" name="q" placeholder="Search..." value="<?= htmlspecialchars($filters['q'] ?? '') ?>" style="padding:0.75rem;border-radius:0.75rem;border:1px solid rgba(15,23,42,0.15);flex:1;">
        <select name="sector_id" style="padding:0.75rem;border-radius:0.75rem;border:1px solid rgba(15,23,42,0.15);">
            <option value="">All sectors</option>
            <?php foreach ($sectors as $sector): ?>
                <option value="<?= (int)$sector['id'] ?>" <?= ($filters['sector_id'] ?? '') == $sector['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sector['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="primary">Filter</button>
    </form>
</section>
<section class="card" data-animate>
    <table class="table">
        <thead>
        <tr><th>SKU</th><th>Name</th><th>Sector</th><th>Quantity</th></tr>
        </thead>
        <tbody>
        <?php foreach ($items['data'] as $item): ?>
            <tr data-context="inventory-menu">
                <td><?= htmlspecialchars($item['sku']) ?></td>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= htmlspecialchars($item['sector_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars((string)$item['quantity']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<div id="inventory-menu" class="context-menu">
    <button data-modal="movement-modal">New movement</button>
    <button>View history</button>
</div>
<div id="movement-modal" class="modal-backdrop">
    <div class="modal">
        <div class="flex flex-between">
            <h3 style="margin-top:0;">Log movement</h3>
            <button data-close>✕</button>
        </div>
        <p>Movement logging is available in the upcoming release.</p>
        <button class="primary" data-close>Close</button>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
