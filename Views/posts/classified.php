<?php
$posts = $posts ?? [];
$isSubscribed = $isSubscribed ?? false;
require __DIR__ . '/../layouts/header.php';
?>

<main class="classified-page">
    <div class="classified-heading">
        <div>
            <p class="classified-kicker">MEMBERS ONLY</p>
            <h2>Classified Reports</h2>
            <p>Investigations and guides reserved for Premium subscribers.</p>
        </div>
        <?php if (!$isSubscribed): ?>
            <a class="btn" href="index.php?page=pricing">Unlock access</a>
        <?php endif; ?>
    </div>

    <?php if (empty($posts)): ?>
        <p class="classified-empty">No classified articles are published yet.</p>
    <?php else: ?>
        <div class="classified-list">
            <?php foreach ($posts as $post): ?>
                <article class="classified-item">
                    <div class="classified-item-meta">
                        <span><?= htmlspecialchars(date('M j, Y', strtotime((string) $post['published_at']))) ?></span>
                        <span><?= $isSubscribed ? 'PREMIUM ACCESS' : 'LOCKED' ?></span>
                    </div>
                    <h3><a href="index.php?page=post&id=<?= (int) $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h3>
                    <?php if ($isSubscribed): ?>
                        <p><?= htmlspecialchars(substr(trim(strip_tags((string) $post['content'])), 0, 180)) ?>...</p>
                        <a class="classified-read-link" href="index.php?page=post&id=<?= (int) $post['id'] ?>">Read report</a>
                    <?php else: ?>
                        <p>Subscriber-only article. Upgrade to Premium to read the full report.</p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>