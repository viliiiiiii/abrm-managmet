<?php
declare(strict_types=1);
/** @var array $nav */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="stylesheet" href="/assets/css/app.css">
    <title>ABRM-Managment</title>
</head>
<body>
<div class="toast-container"></div>
<div class="layout">
    <aside class="nav" data-animate>
        <div class="flex flex-between">
            <h1>ABRM</h1>
            <button data-theme-toggle title="Toggle theme">🌓</button>
        </div>
        <nav>
            <ul style="list-style:none;padding:0;margin-top:1.5rem;display:grid;gap:0.5rem;">
                <?php foreach ($nav as $item): ?>
                    <li><a href="<?= htmlspecialchars($item['href']) ?>" class="nav-link"><?= htmlspecialchars($item['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <form method="post" action="/logout" style="margin-top:auto;">
            <input type="hidden" name="<?= htmlspecialchars($csrfField) ?>" value="<?= htmlspecialchars($csrfToken) ?>">
            <button class="primary" style="width:100%;margin-top:1.5rem;">Logout</button>
        </form>
    </aside>
    <main>
        <?= $content ?>
    </main>
</div>
<script type="module" src="/assets/js/app.js"></script>
</body>
</html>
