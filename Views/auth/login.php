<?php
$errorMessage = $errorMessage ?? '';
require __DIR__ . '/../layouts/header.php';
?>

<main class="auth-card">
    <div class="auth-card-header">
        <h2>Sign In</h2>
        <p>Welcome back to Mini Blog</p>
    </div>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <form class="auth-form" method="POST" action="<?= htmlspecialchars($publicUrl) ?>/index.php?page=login">
        <div class="auth-field">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required placeholder="user@example.com">
        </div>

        <div class="auth-field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Enter your password">
        </div>

        <button type="submit" name="submit_login" class="btn">Sign In</button>
    </form>

    <p class="auth-switch">
        Don't have an account? <a href="<?= htmlspecialchars($publicUrl) ?>/index.php?page=register">Sign up here</a>.
    </p>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>