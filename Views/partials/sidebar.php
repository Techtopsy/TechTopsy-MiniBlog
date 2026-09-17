<aside class="sidebar">
    <div class="sidebar-card">
        <h3>All Published Blogs</h3>
        <hr style="margin: 10px 0 15px 0; border: 0; border-top: 1px solid #eee;">
        
        <?php if (empty($sidebarPosts)): ?>
            <p style="font-size: 0.85rem; color: #7f8c8d;">No published blogs yet.</p>
        <?php else: ?>
            <ul class="sidebar-list">
                <?php foreach ($sidebarPosts as $sPost): ?>
                    <?php $isActive = isset($post['id']) && $post['id'] === $sPost['id']; ?>
                    <li class="<?= $isActive ? 'active' : '' ?>">
                        <a href="index.php?page=post&id=<?= $sPost['id'] ?>">
                            <?= htmlspecialchars($sPost['title']) ?>
                        </a>
                        <span class="sidebar-date"><?= date('M d, Y', strtotime($sPost['created_at'])) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</aside>