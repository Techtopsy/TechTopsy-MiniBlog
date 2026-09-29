<?php
$headerAd = $headerAd ?? false;
$sidebarAd = $sidebarAd ?? false;
$inFeedAd = $inFeedAd ?? false;
$featuredPosts = $featuredPosts ?? [];
$posts = $posts ?? [];

require __DIR__ . '/../layouts/header.php';
?>

<!-- Header Ad Placement -->
<?php $ad = $headerAd; require __DIR__ . '/../partials/ad_banner.php'; ?>

<!-- Featured Posts Carousel / Banner Section -->
<?php if (!empty($featuredPosts)): ?>
<section class="featured-section">
    <h3 class="section-title">⭐ Featured Stories</h3>
    <div class="featured-grid">
        <?php foreach ($featuredPosts as $fPost): ?>
            <div class="featured-card">
                <?php if (!empty($fPost['image'])): ?>
                    <img src="uploads/posts/<?= htmlspecialchars($fPost['image']) ?>" alt="Featured Post">
                <?php endif; ?>
                <div class="featured-card-body">
                    <span class="badge badge-featured" style="width: max-content; margin-bottom: 8px;">Featured</span>
                    <a href="index.php?page=post&id=<?= $fPost['id'] ?>" class="post-title"><?= htmlspecialchars($fPost['title']) ?></a>
                    <div class="post-meta">
                        <?php if ($fPost['show_author'] && !empty($fPost['author_name'])): ?>
                            <span>By <strong><?= htmlspecialchars($fPost['author_name']) ?></strong></span> •
                        <?php endif; ?>
                        <span><?= date('M d, Y', strtotime($fPost['published_at'])) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<div class="layout-wrapper">
    <main class="main-content">
        <h3 class="section-title">Latest Articles</h3>

        <?php if (empty($posts)): ?>
            <p>No articles published yet.</p>
        <?php else: ?>
            <div class="posts-grid">
                <?php $postIndex = 0; ?>
                <?php foreach ($posts as $post): ?>
                    <?php $postIndex++; ?>
                    
                    <article class="post-card">
                        <?php if (!empty($post['image'])): ?>
                            <img src="uploads/posts/<?= htmlspecialchars($post['image']) ?>" class="post-card-img" alt="Post Image">
                        <?php endif; ?>
                        <div class="post-card-body">
                            <a href="index.php?page=post&id=<?= $post['id'] ?>" class="post-title"><?= htmlspecialchars($post['title']) ?></a>
                            <div class="post-meta">
                                <?php if ($post['show_author'] && !empty($post['author_name'])): ?>
                                    <span>By <strong><?= htmlspecialchars($post['author_name']) ?></strong></span> • 
                                <?php endif; ?>
                                <span><?= date('M d, Y', strtotime($post['published_at'])) ?></span>
                            </div>
                            <?php $postSnippet = trim(strip_tags($post['content'])); ?>
                            <p class="post-snippet"><?= htmlspecialchars(substr($postSnippet, 0, 120)) ?>...</p>
                            <a href="index.php?page=post&id=<?= $post['id'] ?>" class="btn">Read Article</a>
                        </div>
                    </article>

                    <!-- In-Feed Ad Banner Placement after 2nd post -->
                    <?php if ($postIndex === 2 && !empty($inFeedAd)): ?>
                        <div style="grid-column: 1 / -1;">
                            <?php $ad = $inFeedAd; require __DIR__ . '/../partials/ad_banner.php'; ?>
                        </div>
                    <?php endif; ?>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Sidebar with Ads and Links -->
    <aside class="sidebar">
        <?php $ad = $sidebarAd; require __DIR__ . '/../partials/ad_banner.php'; ?>
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>
    </aside>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>