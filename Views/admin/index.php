<?php
$message = $message ?? '';
$errorMessage = $errorMessage ?? '';
$posts = $posts ?? [];
$ads = $ads ?? [];
$isAdmin = $isAdmin ?? (($_SESSION['user_role'] ?? '') === 'admin');
$editingPost = $editingPost ?? null;

require __DIR__ . '/../layouts/header.php';
?>

<main class="main-content admin-page">
    <h2><?= $isAdmin ? 'Admin Dashboard' : 'Editor Dashboard' ?></h2>
    <br>

    <?php if (!empty($message)): ?>
        <div class="alert" style="background-color: #22c55e; color: white; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert" style="background-color: #ef4444; color: white; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <?php if ($editingPost): ?>
    <div class="sidebar-card admin-edit-card">
        <div class="admin-section-heading">
            <div>
                <h3>Edit Post</h3>
                <p>Update this post's content and publishing settings.</p>
            </div>
            <a href="index.php?page=admin" class="text-muted">Cancel</a>
        </div>
        <form class="admin-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="post_id" value="<?= (int) $editingPost['id'] ?>">
            <div class="form-group">
                <label for="edit_title">Title:</label>
                <input type="text" id="edit_title" name="title" value="<?= htmlspecialchars($editingPost['title']) ?>" required>
            </div>
            <div class="form-group">
                <label for="edit_content">Content:</label>
                <div class="rich-editor-wrapper">
                    <div class="rich-toolbar" role="toolbar" aria-label="Text formatting">
                        <button type="button" data-command="bold" title="Bold"><strong>B</strong></button>
                        <button type="button" data-command="italic" title="Italic"><em>I</em></button>
                    </div>
                    <div class="rich-editor" contenteditable="true" data-editor-for="edit_content"><?= $editingPost['content'] ?></div>
                    <textarea id="edit_content" name="content" hidden></textarea>
                </div>
            </div>
            <div class="form-group">
                <label for="edit_post_image">Replace Image (optional):</label>
                <input type="file" id="edit_post_image" name="post_image" accept="image/jpeg,image/png,image/webp">
            </div>
            <div class="form-group">
                <label for="edit_published_at">Schedule Post:</label>
                <input type="datetime-local" id="edit_published_at" name="published_at" value="<?= htmlspecialchars(str_replace(' ', 'T', substr((string) $editingPost['published_at'], 0, 16))) ?>">
            </div>
            <div class="form-group">
                <label for="edit_status">Post Visibility Status:</label>
                <select id="edit_status" name="status">
                    <option value="published" <?= $editingPost['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="hidden" <?= $editingPost['status'] === 'hidden' ? 'selected' : '' ?>>Hidden (Draft)</option>
                </select>
            </div>
            <div class="form-group checkbox-group">
                <input type="checkbox" id="edit_show_author" name="show_author" value="1" <?= !empty($editingPost['show_author']) ? 'checked' : '' ?>>
                <label for="edit_show_author">Show Author Name publicly</label>
            </div>
            <div class="form-group checkbox-group">
                <input type="checkbox" id="edit_is_featured" name="is_featured" value="1" <?= !empty($editingPost['is_featured']) ? 'checked' : '' ?>>
                <label for="edit_is_featured">Feature this post on home banner</label>
            </div>
            <button type="submit" name="update_post" class="btn">Save Changes</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- 1. Post Creation Form -->
    <div class="sidebar-card" style="margin-bottom: 30px;">
        <h3>Create New Post</h3>
        <br>
        <form class="admin-form" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="content">Content:</label>
                <div class="rich-editor-wrapper">
                    <div class="rich-toolbar" role="toolbar" aria-label="Text formatting">
                        <button type="button" data-command="bold" title="Bold"><strong>B</strong></button>
                        <button type="button" data-command="italic" title="Italic"><em>I</em></button>
                    </div>
                    <div class="rich-editor" contenteditable="true" data-editor-for="content"></div>
                    <textarea id="content" name="content" hidden></textarea>
                </div>
            </div>

            <div class="form-group">
                <label for="post_image">Post Image:</label>
                <input type="file" id="post_image" name="post_image" accept="image/jpeg,image/png,image/webp">
            </div>

            <div class="form-group">
                <label for="published_at">Schedule Post (Leave blank to publish now):</label>
                <input type="datetime-local" id="published_at" name="published_at">
            </div>

            <div class="form-group">
                <label for="status">Post Visibility Status:</label>
                <select id="status" name="status">
                    <option value="published">Published</option>
                    <option value="hidden">Hidden (Draft)</option>
                </select>
            </div>

            <div class="form-group checkbox-group">
                <input type="checkbox" id="show_author" name="show_author" value="1" checked>
                <label for="show_author">Show Author Name publicly</label>
            </div>

            <div class="form-group checkbox-group">
                <input type="checkbox" id="is_featured" name="is_featured" value="1">
                <label for="is_featured">Feature this post on home banner</label>
            </div>

            <button type="submit" name="create_post" class="btn">Publish / Schedule Post</button>
        </form>
    </div>

    <!-- 2. Add Team Member Form -->
    <?php if ($isAdmin): ?>
    <div class="sidebar-card" style="margin-bottom: 30px;">
        <h3>Add Team Member</h3>
        <br>
        <form class="admin-form" method="POST">
            <div class="form-group">
                <label for="team_username">Username:</label>
                <input type="text" id="team_username" name="team_username" required>
            </div>
            <div class="form-group">
                <label for="team_email">Email Address:</label>
                <input type="email" id="team_email" name="team_email" required>
            </div>
            <div class="form-group">
                <label for="team_password">Password:</label>
                <input type="password" id="team_password" name="team_password" required>
            </div>
            <div class="form-group">
                <label for="team_role">Role:</label>
                <select id="team_role" name="team_role">
                    <option value="editor">Editor</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" name="add_team_member" class="btn">Add Team Member</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- 3. Ad Management Form -->
    <div class="sidebar-card" style="margin-bottom: 30px;">
        <h3>Set Up Advertisements</h3>
        <br>
        <form class="admin-form" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="ad_title">Ad Campaign Title:</label>
                <input type="text" id="ad_title" name="ad_title" required>
            </div>
            <div class="form-group">
                <label for="ad_location">Placement Location:</label>
                <select id="ad_location" name="ad_location">
                    <option value="header">Header Banner</option>
                    <option value="sidebar">Sidebar Card</option>
                    <option value="in_feed">In-Feed (Between Posts)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="ad_target_url">Target Redirect URL:</label>
                <input type="url" id="ad_target_url" name="ad_target_url" placeholder="https://example.com" required>
            </div>
            <div class="form-group">
                <label for="ad_image">Ad Image Banner:</label>
                <input type="file" id="ad_image" name="ad_image" accept="image/*" required>
            </div>
            <button type="submit" name="add_ad" class="btn">Save Advertisement</button>
        </form>
    </div>

    <!-- Manage Existing Posts -->
    <div class="sidebar-card">
        <h3>Manage Posts</h3>
        <br>
        <table class="admin-table">
            <thead>
                <tr style="text-align:left; border-bottom: 2px solid var(--border-color);">
                    <th style="padding: 10px;">Title</th>
                    <th>Status</th>
                    <th>Scheduled Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $p): ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 12px 10px;">
                        <strong><?= htmlspecialchars($p['title']) ?></strong>
                        <?php if ($p['is_featured']): ?>
                            <span class="badge badge-featured">Featured</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge badge-status"><?= ucfirst($p['status']) ?></span></td>
                    <td style="font-size: 0.85rem; color: var(--text-muted);"><?= $p['published_at'] ?></td>
                    <td class="admin-actions">
                        <a href="index.php?page=admin&edit=<?= $p['id'] ?>" class="btn" style="padding: 4px 10px; font-size: 0.8rem;">Edit</a>
                        <?php if ($isAdmin): ?>
                            <a href="index.php?page=admin&delete=<?= $p['id'] ?>" class="btn btn-danger" style="padding: 4px 10px; font-size: 0.8rem;" onclick="return confirm('Delete this post?')">Delete</a>
                        <?php else: ?>
                            <span class="text-muted">Admin only</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="sidebar-card" style="margin-top: 30px;">
        <h3>Manage Advertisements</h3>
        <br>
        <table style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align:left; border-bottom: 2px solid var(--border-color);">
                    <th style="padding: 10px;">Campaign</th>
                    <th>Placement</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ads as $ad): ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 12px 10px;"><?= htmlspecialchars($ad['title']) ?></td>
                    <td><?= htmlspecialchars($ad['location']) ?></td>
                    <td><?= $ad['is_active'] ? 'Active' : 'Inactive' ?></td>
                    <td><a href="index.php?page=admin&toggle_ad=<?= $ad['id'] ?>" class="btn" style="padding: 4px 10px; font-size: 0.8rem;"><?= $ad['is_active'] ? 'Disable' : 'Enable' ?></a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
document.querySelectorAll('.rich-editor-wrapper').forEach((wrapper) => {
    const editor = wrapper.querySelector('.rich-editor');
    const output = document.getElementById(editor.dataset.editorFor);
    const form = editor.closest('form');

    wrapper.querySelectorAll('[data-command]').forEach((button) => {
        button.addEventListener('mousedown', (event) => event.preventDefault());
        button.addEventListener('click', () => {
            editor.focus();
            document.execCommand(button.dataset.command, false);
        });
    });

    form.addEventListener('submit', () => {
        output.value = editor.innerHTML.trim();
    });
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>