<?php
$post = $post ?? [];
$hasLiked = $hasLiked ?? false;
$likeCount = $likeCount ?? 0;
$comments = $comments ?? [];

require __DIR__ . '/../layouts/header.php';
?>

<div class="content-layout">
    <main class="content-main">
        <div class="post-card">
            <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>
            <div class="post-meta">
                Posted on <?= date('F j, Y', strtotime($post['created_at'])) ?>
            </div>

            <div class="post-content">
                <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
            </div>

            <div class="like-section">
                <form method="POST">
                    <input type="hidden" name="action" value="like">
                    <button type="submit" class="btn <?= $hasLiked ? 'btn-danger' : '' ?>">
                        <?= $hasLiked ? '❤️ Unlike' : '🤍 Like' ?> (<?= $likeCount ?>)
                    </button>
                </form>
            </div>

            <div class="comments-section">
                <h3>Comments (<?= count($comments) ?>)</h3>
                <br>

                <?php if (!empty($commentSuccess)): ?>
                    <div class="alert">Comment added successfully!</div>
                <?php endif; ?>

                <form method="POST">
                    <label for="author">Your Name:</label>
                    <input type="text" id="author" name="author" required placeholder="Jane Doe">

                    <label for="comment">Your Comment:</label>
                    <textarea id="comment" name="comment" required placeholder="Write a comment..."></textarea>

                    <button type="submit" name="submit_comment" class="btn">Submit Comment</button>
                </form>

                <br><br>

                <?php if (empty($comments)): ?>
                    <p>No comments yet. Be the first to comment!</p>
                <?php else: ?>
                    <?php foreach ($comments as $c): ?>
                        <div class="comment-box">
                            <strong><?= htmlspecialchars($c['author']) ?></strong>
                            <span style="font-size: 0.8rem; color: #888;"> • <?= date('M d, Y H:i', strtotime($c['created_at'])) ?></span>
                            <p><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php require __DIR__ . '/../partials/sidebar.php'; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>