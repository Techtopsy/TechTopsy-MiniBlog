<?php
$currentUser = $user ?? [
    'username' => $_SESSION['username'] ?? 'User',
    'profile_pic' => $_SESSION['profile_pic'] ?? null,
];
require __DIR__ . '/../layouts/header.php';
?>

<main class="profile-card">
    <div class="profile-card-header">
        <h2>Profile</h2>
        <p>Update your profile picture</p>
    </div>

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

    <div class="profile-avatar-preview">
        <?php if (!empty($currentUser['profile_pic'])): ?>
            <img src="<?= htmlspecialchars($publicUrl) ?>/uploads/avatars/<?= htmlspecialchars($currentUser['profile_pic']) ?>"
                 class="avatar-lg" alt="Profile picture">
        <?php else: ?>
            <div class="avatar-lg avatar-placeholder">
                <?= strtoupper(substr($currentUser['username'], 0, 1)) ?>
            </div>
        <?php endif; ?>
    </div>

    <form class="profile-form" method="POST" enctype="multipart/form-data" action="<?= htmlspecialchars($publicUrl) ?>/index.php?page=profile">
        <label for="avatar">Choose profile picture</label>
        <input type="file" id="avatar" name="avatar" accept="image/png,image/jpeg,image/webp" required>

        <button type="submit" name="upload_avatar" class="btn">Upload Picture</button>
    </form>

    <p class="profile-back-link">
        <a href="<?= htmlspecialchars($publicUrl) ?>/index.php">Back to Home</a>
    </p>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
