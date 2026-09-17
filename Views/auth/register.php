<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="post-card" style="max-width: 450px; margin: 40px auto;">
    <h2>Create an Account</h2>
    <br>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert" style="background-color: #e74c3c; color: white;"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div class="alert" style="background-color: #2ecc71; color: white;"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= htmlspecialchars($publicUrl) ?>/index.php?page=register">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required placeholder="johndoe" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:4px;">

        <label for="email">Email Address:</label>
        <input type="email" id="email" name="email" required placeholder="user@example.com" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:4px;">

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required placeholder="At least 6 characters" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:4px;">

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repeat password" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:4px;">

        <button type="submit" name="submit_register" class="btn" style="width: 100%;">Sign Up</button>
    </form>

    <br>
    <p style="font-size: 0.9rem; text-align: center;">
        Already have an account? <a href="<?= htmlspecialchars($publicUrl) ?>/index.php?page=login">Log in here</a>.
    </p>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>