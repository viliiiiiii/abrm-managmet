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
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:linear-gradient(135deg,#0f172a,#2563eb);">
    <form method="post" action="/login" class="card" style="width:min(360px,94vw);">
        <h1 style="margin-top:0;">Sign in</h1>
        <?php if (!empty($errors)): ?>
            <div style="background:rgba(220,38,38,0.15);padding:0.75rem 1rem;border-radius:0.75rem;color:#ef4444;">
                <?php foreach ($errors as $field => $messages): ?>
                    <div><?= htmlspecialchars(is_array($messages) ? implode(', ', $messages) : $messages) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <label>Email
            <input type="email" name="email" required style="width:100%;padding:0.75rem;border-radius:0.75rem;border:1px solid rgba(15,23,42,0.15);margin-top:0.35rem;">
        </label>
        <label style="display:block;margin-top:1rem;">Password
            <input type="password" name="password" required style="width:100%;padding:0.75rem;border-radius:0.75rem;border:1px solid rgba(15,23,42,0.15);margin-top:0.35rem;">
        </label>
        <label style="display:block;margin-top:1rem;">TOTP Code (if enabled)
            <input type="text" name="totp" pattern="[0-9]{6}" style="width:100%;padding:0.75rem;border-radius:0.75rem;border:1px solid rgba(15,23,42,0.15);margin-top:0.35rem;">
        </label>
        <input type="hidden" name="<?= htmlspecialchars($csrfField) ?>" value="<?= htmlspecialchars($csrfToken) ?>">
        <button class="primary" style="width:100%;margin-top:1.5rem;">Login</button>
    </form>
<script type="module" src="/assets/js/app.js"></script>
</body>
</html>
