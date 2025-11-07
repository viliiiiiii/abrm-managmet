<?php
declare(strict_types=1);
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/app.css">
    <title>Login — ABRM</title>
</head>
<body class="login-body">
<div class="login-stars" aria-hidden="true"></div>
<div class="login-shell" data-animate>
    <section class="login-brand">
        <span class="brand-mark" aria-hidden="true"></span>
        <h1>ABRM<span>Management</span></h1>
        <p>Secure access to the adaptive building response matrix.</p>
        <button type="button" data-theme-toggle class="ghost-toggle" aria-label="Toggle theme">
            <span class="toggle-icon" data-icon>☾</span>
        </button>
    </section>
    <form method="post" action="/login" class="card login-card">
        <header>
            <h2>Command Console Login</h2>
            <p>Enter your credentials to synchronize with control.</p>
        </header>
        <?php if (!empty($errors)): ?>
            <div class="form-alert" role="alert">
                <?php foreach ($errors as $field => $messages): ?>
                    <div><?= htmlspecialchars(is_array($messages) ? implode(', ', $messages) : $messages) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <label class="field">
            <span>Email</span>
            <input type="email" name="email" autocomplete="username" required>
        </label>
        <label class="field">
            <span>Password</span>
            <input type="password" name="password" autocomplete="current-password" required>
        </label>
        <input type="hidden" name="<?= htmlspecialchars($csrfField) ?>" value="<?= htmlspecialchars($csrfToken) ?>">
        <button class="primary">Initiate Session</button>
    </form>
</div>
<script type="module" src="/assets/js/app.js"></script>
</body>
</html>
