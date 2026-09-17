<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="post-card" style="max-width: 450px; margin: 40px auto;">
    <h2>Sign In</h2>
    <br>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert" style="background-color: #e74c3c; color: white;"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= htmlspecialchars($publicUrl) ?>/index.php?page=login">
        <label for="email">Email Address:</label>
        <input type="email" id="email" name="email" required placeholder="user@example.com" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:4px;">

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required placeholder="••••••••" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:4px;">

        <button type="submit" name="submit_login" class="btn" style="width: 100%;">Sign In</button>
    </form>

    <br>
    <p style="font-size: 0.9rem; text-align: center;">
        Don't have an account? <a href="<?= htmlspecialchars($publicUrl) ?>/index.php?page=register">Sign up here</a>.
    </p>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>