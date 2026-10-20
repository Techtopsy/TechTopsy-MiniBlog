<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="post-card" style="max-width: 600px; margin: 40px auto; text-align: center; padding: 40px;">
    <h2>💎 Premium Classified Access</h2>
    <p style="color: var(--text-muted); margin: 15px 0 25px 0;">
        Unlock exclusive investigative articles, expert guides, and classified blog content with a monthly subscription.
    </p>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert" style="background-color: var(--accent-red); color: white; padding: 10px; border-radius: 6px; margin-bottom: 20px;"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <?php if (!empty($successMessage)): ?>
        <div class="alert" style="background-color: #22c55e; color: white; padding: 10px; border-radius: 6px; margin-bottom: 20px;"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <div style="background: #f1f5f9; padding: 30px; border-radius: var(--radius); margin-bottom: 25px;">
        <h3 style="font-size: 2rem; color: var(--primary); margin-bottom: 5px;">₦3,000 <span style="font-size: 1rem; color: var(--text-muted);">/ month</span></h3>
        <p style="font-size: 0.9rem; color: var(--text-muted);">Billed monthly. Cancel anytime.</p>
    </div>

    <?php if (!empty($isSubscribed)): ?>
        <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; font-weight: 600;">
            ✓ You have an active Premium Subscription!
        </div>
    <?php else: ?>
        <form action="index.php?page=subscribe" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <button type="submit" class="btn" style="padding: 12px 30px; font-size: 1rem; width: 100%;">Pay with Paystack</button>
        </form>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>