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
        <h2 style="margin:0;">Notes</h2>
        <button class="primary" data-modal="note-modal">New note</button>
    </div>
    <form method="get" style="display:flex;gap:0.75rem;margin-top:1rem;flex-wrap:wrap;">
        <input type="text" name="tag" placeholder="Tag filter" value="<?= htmlspecialchars($filters['tag'] ?? '') ?>" style="padding:0.75rem;border-radius:0.75rem;border:1px solid rgba(15,23,42,0.15);flex:1;">
        <button class="primary">Apply</button>
    </form>
</section>
<section class="grid" data-animate>
    <?php foreach ($notes['data'] as $note): ?>
        <article class="card" data-context="notes-menu">
            <header class="flex flex-between">
                <h3 style="margin:0;"><?= htmlspecialchars($note['title'] ?? 'Note') ?></h3>
                <?php if (!empty($note['tag_list'])): ?><span class="badge"><?= htmlspecialchars($note['tag_list']) ?></span><?php endif; ?>
            </header>
            <p style="opacity:0.8;">
                <?php
                $body = trim((string)($note['body'] ?? ''));
                $plain = strip_tags($body);
                if (function_exists('mb_substr')) {
                    $preview = mb_substr($plain, 0, 200);
                    $truncated = mb_strlen($plain) > 200;
                } else {
                    $preview = substr($plain, 0, 200);
                    $truncated = strlen($plain) > 200;
                }
                ?>
                <?= htmlspecialchars($preview) ?><?= $truncated ? '…' : '' ?>
            </p>
            <footer style="font-size:0.85rem;opacity:0.6;">By <?= htmlspecialchars($note['author_name'] ?? 'Unknown') ?> on <?= htmlspecialchars($note['note_date'] ?? '') ?></footer>
        </article>
    <?php endforeach; ?>
</section>
<div id="notes-menu" class="context-menu">
    <button>Edit</button>
    <button>Pin</button>
    <button>Delete</button>
</div>
<div id="note-modal" class="modal-backdrop">
    <div class="modal">
        <div class="flex flex-between">
            <h3 style="margin-top:0;">Create note</h3>
            <button data-close>✕</button>
        </div>
        <p>Offline-capable note creation is under active development.</p>
        <button class="primary" data-close>Close</button>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
