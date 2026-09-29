<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Ad.php';

class AdminController {
    private Post $postModel;
    private User $userModel;
    private Ad $adModel;

    public function __construct() {
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'editor'])) {
            header("Location: index.php?page=login");
            exit;
        }

        $this->postModel = new Post();
        $this->userModel = new User();
        $this->adModel = new Ad();
    }

    public function index(): void {
        $message = '';
        $errorMessage = '';
        $isAdmin = $this->isAdmin();
        $editingPost = null;

        if (isset($_GET['edit'])) {
            $editingPost = $this->postModel->getById((int) $_GET['edit']);
            if (!$editingPost) {
                $errorMessage = "The selected post could not be found.";
            }
        }

        // Update an existing post. Editors may edit posts, but cannot delete them.
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_post'])) {
            try {
                $postId = (int) ($_POST['post_id'] ?? 0);
                $title = trim((string) ($_POST['title'] ?? ''));
                $content = $this->sanitizeArticleContent((string) ($_POST['content'] ?? ''));
                $status = (string) ($_POST['status'] ?? 'published');
                $showAuthor = isset($_POST['show_author']) ? 1 : 0;
                $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
                $publishedAt = !empty($_POST['published_at']) ? (string) $_POST['published_at'] : null;

                if ($postId < 1 || $title === '' || $content === '') {
                    throw new Exception("Post title and content are required.");
                }

                if (!in_array($status, ['published', 'draft', 'hidden'], true)) {
                    throw new Exception("Invalid post visibility status.");
                }

                $imageFilename = null;
                if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
                    $imageFilename = $this->uploadPostImage($_FILES['post_image']);
                }

                $this->postModel->update($postId, $title, $content, $imageFilename, $isFeatured, $status, $showAuthor, $publishedAt);
                header("Location: index.php?page=admin&updated=1");
                exit;
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
                $editingPost = $this->postModel->getById((int) ($_POST['post_id'] ?? 0));
            }
        }

        // 1. Handle Post Creation with Image, Scheduling, Visibility & Author Settings
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {
            try {
                $title = trim((string) $_POST['title']);
                $content = $this->sanitizeArticleContent((string) ($_POST['content'] ?? ''));
                $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
                $status = (string) ($_POST['status'] ?? 'published');
                $showAuthor = isset($_POST['show_author']) ? 1 : 0;
                $publishedAt = !empty($_POST['published_at']) ? (string) $_POST['published_at'] : null;
                $authorId = (int) $_SESSION['user_id'];

                if (empty($title) || empty($content)) {
                    throw new Exception("Title and content are required.");
                }

                // Handle Featured Image Upload
                $imageFilename = null;
                if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
                    $imageFilename = $this->uploadPostImage($_FILES['post_image']);
                }

                $this->postModel->create(
                    $title, 
                    $content, 
                    $authorId, 
                    $imageFilename, 
                    $isFeatured, 
                    $status, 
                    $showAuthor, 
                    $publishedAt
                );

                $message = "Post created successfully!";
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        // 2. Add Team Member
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_team_member'])) {
            try {
                if (!$isAdmin) {
                    throw new Exception("Only administrators can add team members.");
                }

                $username = trim((string) $_POST['team_username']);
                $email = trim((string) $_POST['team_email']);
                $password = (string) $_POST['team_password'];
                $role = (string) ($_POST['team_role'] ?? 'editor');

                if (!in_array($role, ['admin', 'editor'], true)) {
                    throw new Exception("Invalid team member role.");
                }

                if (empty($username) || empty($email) || empty($password)) {
                    throw new Exception("All team member fields are required.");
                }

                $this->userModel->create($username, $email, $password, $role);
                $message = "Team member '{$username}' added successfully!";
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        // 3. Add Advertisement
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_ad'])) {
            try {
                $adTitle = trim((string) $_POST['ad_title']);
                $location = (string) $_POST['ad_location'];
                $targetUrl = trim((string) $_POST['ad_target_url']);

                if (empty($adTitle) || empty($targetUrl) || !isset($_FILES['ad_image'])) {
                    throw new Exception("Ad title, target URL, and image are required.");
                }

                $adImage = $this->uploadPostImage($_FILES['ad_image']);
                $this->adModel->create($adTitle, $location, 'uploads/posts/' . $adImage, $targetUrl);
                $message = "Advertisement created successfully!";
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        // Delete Post Action
        if (isset($_GET['delete'])) {
            if (!$isAdmin) {
                $errorMessage = "Only administrators can delete posts.";
            } else {
                $this->postModel->delete((int) $_GET['delete']);
                header("Location: index.php?page=admin&msg=deleted");
                exit;
            }
        }

        // Toggle Ad Active Action
        if (isset($_GET['toggle_ad'])) {
            $this->adModel->toggleActive((int) $_GET['toggle_ad']);
            header("Location: index.php?page=admin");
            exit;
        }

        $posts = $this->postModel->getAllAdmin();
        $ads = $this->adModel->getAll();

        require_once __DIR__ . '/../views/admin/index.php';
    }

    private function isAdmin(): bool {
        return ($_SESSION['user_role'] ?? '') === 'admin';
    }

    private function sanitizeArticleContent(string $content): string {
        $content = trim($content);
        $content = preg_replace('/<(?!\/?(?:strong|b|em|i|br|p|div)\b)[^>]*>/i', '', $content) ?? '';
        return preg_replace('/<(strong|b|em|i|br|p|div)\b[^>]*>/i', '<$1>', $content) ?? '';
    }

    private function uploadPostImage(array $file): string {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

        if (!array_key_exists($mimeType, $allowed)) {
            throw new Exception("Invalid image format. Only JPG, PNG, and WebP allowed.");
        }

        $filename = 'post_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mimeType];
        $uploadDir = __DIR__ . '/../public/uploads/posts/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            throw new Exception("Failed to save image.");
        }

        return $filename;
    }
}