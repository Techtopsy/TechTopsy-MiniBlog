<?php
$currentUser = $user ?? [
    'username' => $_SESSION['username'] ?? 'User',
    'profile_pic' => $_SESSION['profile_pic'] ?? null,
];
require __DIR__ . '/../layouts/header.php';
?>

<div class="post-card" style="max-width: 500px; margin: 40px auto;">
    <h2>Profile</h2>
    <br>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert" style="background-color: #e74c3c; color: white;">
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div class="alert" style="background-color: #2ecc71; color: white;">
            <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php endif; ?>

    <div style="text-align: center; margin-bottom: 20px;">
        <?php if (!empty($currentUser['profile_pic'])): ?>
            <img src="<?= htmlspecialchars($publicUrl) ?>/uploads/avatars/<?= htmlspecialchars($currentUser['profile_pic']) ?>"
                 class="avatar-lg" alt="Profile picture">
        <?php else: ?>
            <div class="avatar-lg avatar-placeholder" style="margin: 0 auto;">
                <?= strtoupper(substr($currentUser['username'], 0, 1)) ?>
            </div>
        <?php endif; ?>
    </div>

    <form method="POST" enctype="multipart/form-data" action="<?= htmlspecialchars($publicUrl) ?>/index.php?page=profile">
        <label for="avatar">Choose profile picture:</label>
        <input type="file" id="avatar" name="avatar" accept="image/png,image/jpeg,image/webp" required style="width: 100%; margin-bottom: 15px;">

        <button type="submit" name="upload_avatar" class="btn" style="width: 100%;">Upload Picture</button>
    </form>

    <br>
    <p style="font-size: 0.9rem; text-align: center;">
        <a href="<?= htmlspecialchars($publicUrl) ?>/index.php">Back to Home</a>
    </p>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
