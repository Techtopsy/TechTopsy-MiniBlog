<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="post-card">
    <h2>Publish New Blog Post</h2>
    <br>

    <?php if (!empty($message)): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required placeholder="Enter post title">

        <label for="content">Content:</label>
        <textarea id="content" name="content" required placeholder="Write post content here..."></textarea>

        <button type="submit" name="create_post" class="btn">Publish Post</button>
    </form>
</div>

<div class="post-card">
    <h2>Manage Existing Posts</h2>
    <br>
    <?php if (empty($posts)): ?>
        <p>No posts available.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding: 10px 0;">
                <div>
                    <strong><?= htmlspecialchars($post['title']) ?></strong><br>
                    <small><?= date('M d, Y', strtotime($post['created_at'])) ?></small>
                </div>
                <div>
                    <a href="<?= htmlspecialchars($publicUrl) ?>/index.php?page=post&id=<?= $post['id'] ?>" class="btn">View</a>
                    <a href="<?= htmlspecialchars($publicUrl) ?>/index.php?page=admin&delete=<?= $post['id'] ?>" class="btn btn-danger" onclick="return confirm('Delete this post?')">Delete</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>