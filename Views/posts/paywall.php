<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="layout-wrapper">
    <main class="main-content">
        <div class="post-card" style="text-align: center; padding: 50px 30px;">
            <div style="font-size: 3rem; margin-bottom: 10px;">🔒</div>
            <h2 style="margin-bottom: 15px;">Classified Premium Content</h2>
            <p style="color: var(--text-muted); max-width: 500px; margin: 0 auto 25px auto;">
                This article is restricted to <strong>Premium Subscribers</strong>. Upgrade your account to unlock full access to this story and all other classified reports.
            </p>
            <a href="index.php?page=pricing" class="btn" style="padding: 12px 25px;">Upgrade to Premium</a>
        </div>
    </main>

    <aside class="sidebar">
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>
    </aside>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>