<?php
$publicUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
if ($publicUrl === '.' || $publicUrl === '') {
    $publicUrl = '/';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Blog MVC</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($publicUrl) ?>/css/style.css">
</head>
<body>
    <header>
        <h1><a href="<?= htmlspecialchars($publicUrl) ?>/index.php">Mini Blog</a></h1>
        <nav>
            <a href="<?= htmlspecialchars($publicUrl) ?>/index.php">Home</a>

            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (in_array($_SESSION['user_role'], ['admin', 'editor'], true)): ?>
                    <a href="<?= htmlspecialchars($publicUrl) ?>/index.php?page=admin">Admin Panel</a>
                <?php endif; ?>

                <a href="<?= htmlspecialchars($publicUrl) ?>/index.php?page=profile" class="profile-link">
                    <?php if (!empty($_SESSION['profile_pic'])): ?>
                        <img src="<?= htmlspecialchars($publicUrl) ?>/uploads/avatars/<?= htmlspecialchars($_SESSION['profile_pic']) ?>" class="avatar-sm" alt="Profile">
                    <?php endif; ?>
                    <span class="profile-name"><?= htmlspecialchars($_SESSION['username']) ?></span>
                </a>
                <a href="<?= htmlspecialchars($publicUrl) ?>/index.php?page=logout" style="color: #e74c3c;">Sign Out</a>
            <?php else: ?>
                <a href="<?= htmlspecialchars($publicUrl) ?>/index.php?page=login">Sign In</a>
                <a href="<?= htmlspecialchars($publicUrl) ?>/index.php?page=register">Sign Up</a>
            <?php endif; ?>
        </nav>
    </header>
    <div class="container">