<?php
$post = $post ?? [];
$hasLiked = $hasLiked ?? false;
$likeCount = $likeCount ?? 0;
$comments = $comments ?? [];

require __DIR__ . '/../layouts/header.php';
?>

<div class="content-layout">
    <main class="content-main">
        <article class="post-card detail-card">
            <?php if (!empty($post['image'])): ?>
                <img src="uploads/posts/<?= htmlspecialchars($post['image']) ?>" class="post-card-img" alt="<?= htmlspecialchars($post['title']) ?>">
            <?php endif; ?>
            <div class="detail-card-body">
                <h1 class="detail-title"><?= htmlspecialchars($post['title']) ?></h1>
                <div class="post-meta">
                    <?php if (!empty($post['show_author']) && !empty($post['author_name'])): ?>
                        <span>By <strong><?= htmlspecialchars($post['author_name']) ?></strong></span>
                    <?php endif; ?>
                    <span>Posted on <?= date('F j, Y', strtotime($post['published_at'] ?? $post['created_at'])) ?></span>
                </div>

                <div class="post-content">
                    <?php if ($post['content'] === strip_tags($post['content'])): ?>
                        <?= nl2br(htmlspecialchars($post['content'])) ?>
                    <?php else: ?>
                        <?= $post['content'] ?>
                    <?php endif; ?>
                </div>

                <div class="like-section">
                    <form method="POST">
                        <input type="hidden" name="action" value="like">
                        <button type="submit" class="btn <?= $hasLiked ? 'btn-danger' : '' ?>">
                            <?= $hasLiked ? '❤️ Unlike' : '🤍 Like' ?> (<?= $likeCount ?>)
                        </button>
                    </form>
                </div>

                <section class="comments-section">
                    <h3>Comments (<?= count($comments) ?>)</h3>

                    <?php if (!empty($commentSuccess)): ?>
                        <div class="alert alert-success">Comment added successfully!</div>
                    <?php endif; ?>

                    <form class="comment-form" method="POST">
                        <div class="comment-field">
                            <label for="author">Your Name</label>
                            <input type="text" id="author" name="author" required placeholder="Jane Doe">
                        </div>

                        <div class="comment-field">
                            <label for="comment">Your Comment</label>
                            <textarea id="comment" name="comment" required placeholder="Write a comment..."></textarea>
                        </div>

                        <button type="submit" name="submit_comment" class="btn">Submit Comment</button>
                    </form>

                    <div class="comment-list">
                        <?php if (empty($comments)): ?>
                            <p class="empty-comments">No comments yet. Be the first to comment!</p>
                        <?php else: ?>
                            <?php foreach ($comments as $c): ?>
                                <div class="comment-box">
                                    <div class="comment-author">
                                        <strong><?= htmlspecialchars($c['author']) ?></strong>
                                        <span><?= date('M d, Y H:i', strtotime($c['created_at'])) ?></span>
                                    </div>
                                    <p><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </article>
    </main>

    <aside class="sidebar">
        <?php $ad = $sidebarAd ?? false; require __DIR__ . '/../partials/ad_banner.php'; ?>
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>
    </aside>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>