<?php
$errorMessage = $errorMessage ?? '';
$successMessage = $successMessage ?? '';
require __DIR__ . '/../layouts/header.php';
?>

<main class="auth-card">
    <div class="auth-card-header">
        <h2>Create an Account</h2>
        <p>Join the Mini Blog community</p>
    </div>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <form class="auth-form" method="POST" action="<?= htmlspecialchars($publicUrl) ?>/index.php?page=register">
        <div class="auth-field">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required placeholder="johndoe">
        </div>

        <div class="auth-field">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required placeholder="user@example.com">
        </div>

        <div class="auth-field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="At least 6 characters">
        </div>

        <div class="auth-field">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repeat password">
        </div>

        <button type="submit" name="submit_register" class="btn">Sign Up</button>
    </form>

    <p class="auth-switch">
        Already have an account? <a href="<?= htmlspecialchars($publicUrl) ?>/index.php?page=login">Log in here</a>.
    </p>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>