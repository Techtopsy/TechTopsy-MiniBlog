<?php if (!empty($ad)): ?>
    <div class="ad-container ad-<?= htmlspecialchars($ad['location']) ?>">
        <span class="ad-badge">Sponsored</span>
        <a href="<?= htmlspecialchars($ad['target_url']) ?>" target="_blank" rel="noopener noreferrer">
            <img src="<?= htmlspecialchars($ad['image_url']) ?>" alt="<?= htmlspecialchars($ad['title']) ?>" class="ad-image">
        </a>
    </div>
<?php endif; ?>