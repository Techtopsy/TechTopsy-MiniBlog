<?php
$publicUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
if ($publicUrl === '.' || $publicUrl === '') {
    $publicUrl = '/';
}
require __DIR__ . '/../layouts/header.php';
?>

<h2>Latest Blog Posts</h2>
<br>

<?php if (empty($posts)): ?>
    <p>No blog posts found. <a href="<?= htmlspecialchars($publicUrl) ?>/index.php?page=admin">Create one in the Admin Panel</a>.</p>
<?php else: ?>
    <div class="content-layout">
        <main class="content-main">
            <?php foreach ($posts as $post): ?>
                <div class="post-card">
                    <h2 class="post-title"><?= htmlspecialchars($post['title']) ?></h2>
                    <div class="post-meta">
                        Published on <?= date('M d, Y', strtotime($post['created_at'])) ?>
                    </div>
                    <p class="post-snippet">
                        <?= htmlspecialchars(substr($post['content'], 0, 150)) ?>...
                    </p>
                    <p>
                        <strong>❤️ <?= $post['like_count'] ?> Likes</strong> | 
                        <strong>💬 <?= $post['comment_count'] ?> Comments</strong>
                    </p>
                    <br>
                    <a href="<?= htmlspecialchars($publicUrl) ?>/index.php?page=post&id=<?= $post['id'] ?>" class="btn">Read Full Post</a>
                </div>
            <?php endforeach; ?>
        </main>

        <?php require __DIR__ . '/../partials/sidebar.php'; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>