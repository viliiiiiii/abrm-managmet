<?php
declare(strict_types=1);

use App\Auth\Auth;

/** @var array $nav */
$currentPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
$user = Auth::user();
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
<div class="cosmic-grid" aria-hidden="true"></div>
<div class="orb orb-one" aria-hidden="true"></div>
<div class="orb orb-two" aria-hidden="true"></div>
<div class="toast-container"></div>
<div class="layout">
    <aside class="nav" data-animate>
        <div class="nav__header">
            <div>
                <span class="nav__logo">ABRM</span>
                <p class="nav__subtitle">Control Nexus</p>
            </div>
            <button data-theme-toggle class="ghost-toggle" aria-label="Toggle theme">
                <span class="toggle-icon" data-icon>☾</span>
            </button>
        </div>
        <nav class="nav__menu">
            <ul>
                <?php foreach ($nav as $item): ?>
                    <?php
                        $href = $item['href'];
                        $isActive = $currentPath === $href || ($href !== '/' && str_starts_with($currentPath, $href));
                    ?>
                    <li>
                        <a href="<?= htmlspecialchars($href) ?>" class="nav-link<?= $isActive ? ' active' : '' ?>">
                            <span><?= htmlspecialchars($item['label']) ?></span>
                            <?php if ($isActive): ?><span class="pulse" aria-hidden="true"></span><?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <?php if ($user): ?>
            <div class="nav__profile">
                <p class="nav__user"><?= htmlspecialchars($user->name ?: $user->email) ?></p>
                <span class="nav__role">Role: <?= htmlspecialchars(ucwords(str_replace('-', ' ', $user->role_slug))) ?></span>
            </div>
        <?php endif; ?>
        <form method="post" action="/logout" class="nav__logout">
            <input type="hidden" name="<?= htmlspecialchars($csrfField) ?>" value="<?= htmlspecialchars($csrfToken) ?>">
            <button class="primary">Logout</button>
        </form>
    </aside>
    <main class="main" data-animate>
        <?= $content ?>
    </main>
</div>
<script type="module" src="/assets/js/app.js"></script>
</body>
</html>
