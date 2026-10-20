<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../models/Like.php';
require_once __DIR__ . '/../models/Ad.php';
require_once __DIR__ . '/../models/Ad.php';

class PostController {
    private Post $postModel;
    private Comment $commentModel;
    private Like $likeModel;
    private Ad $adModel;

    public function __construct() {
        $this->postModel = new Post();
        $this->commentModel = new Comment();
        $this->likeModel = new Like();
        $this->adModel = new Ad();
    }

    public function index(): void {
        $errorMessage = '';

        $posts = $this->postModel->getPublished();
        $featuredPosts = $this->postModel->getFeatured();
        $sidebarPosts = $this->postModel->getSidebarList();

        // Fetch Advertisements
        $headerAd = $this->adModel->getActiveByLocation('header');
        $sidebarAd = $this->adModel->getActiveByLocation('sidebar');
        $inFeedAd = $this->adModel->getActiveByLocation('in_feed');

        require_once __DIR__ . '/../views/posts/index.php';
    }

    public function classified(): void {
        $posts = $this->postModel->getPremiumPublished();
        $isSubscribed = false;
        $userRole = $_SESSION['user_role'] ?? 'user';

        if (in_array($userRole, ['admin', 'editor'], true)) {
            $isSubscribed = true;
        } elseif (isset($_SESSION['user_id'])) {
            require_once __DIR__ . '/../models/Subscription.php';
            $subscriptionModel = new Subscription();
            $isSubscribed = $subscriptionModel->hasActiveSubscription((int) $_SESSION['user_id']);
        }

        require __DIR__ . '/../views/posts/classified.php';
    }

    public function show(): void {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
        if (!$id) {
            header("Location: index.php");
            exit;
        }

        $sidebarPosts = $this->postModel->getSidebarList();
        $sidebarAd = $this->adModel->getActiveByLocation('sidebar');

        try {
            $post = $this->postModel->getById($id);
            if (!$post || ($post['status'] !== 'published' && (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'))) {
                throw new Exception("The requested blog post is not available.");
            }

            // Check if post is classified (Premium)
            if (!empty($post['is_premium'])) {
                $userId = $_SESSION['user_id'] ?? null;
                $userRole = $_SESSION['user_role'] ?? 'user';

                // Admins & Editors bypass paywall
                $isAuthorized = (in_array($userRole, ['admin', 'editor']));

                if (!$isAuthorized && $userId) {
                    require_once __DIR__ . '/../models/Subscription.php';
                    $subModel = new Subscription();
                    $isAuthorized = $subModel->hasActiveSubscription((int)$userId);
                }

                if (!$isAuthorized) {
                    // Render Paywall View if unauthorized
                    require_once __DIR__ . '/../views/posts/paywall.php';
                    return;
                }
            }

            $isPublished = $post['status'] === 'published'
                && strtotime((string) ($post['published_at'] ?? 'now')) <= time();
            $canInteract = isset($_SESSION['user_id']) && $isPublished;
            $interactionError = '';
            $commentSuccess = ($_GET['comment'] ?? '') === 'success';

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['action']) || isset($_POST['submit_comment']))) {
                if (!isset($_SESSION['user_id'])) {
                    header('Location: index.php?page=login');
                    exit;
                }

                if (!$isPublished) {
                    http_response_code(403);
                    $interactionError = 'Only published posts can be liked or commented on.';
                } else {
                    try {
                        if (($_POST['action'] ?? '') === 'like') {
                            $this->likeModel->toggle($id, (string) $_SERVER['REMOTE_ADDR']);
                            header('Location: index.php?page=post&id=' . $id);
                            exit;
                        }

                        if (isset($_POST['submit_comment'])) {
                            $comment = trim((string) ($_POST['comment'] ?? ''));
                            if ($comment === '') {
                                throw new Exception('Please enter a comment.');
                            }

                            $this->commentModel->create($id, (string) $_SESSION['username'], $comment);
                            header('Location: index.php?page=post&id=' . $id . '&comment=success');
                            exit;
                        }
                    } catch (Exception $e) {
                        $interactionError = $e->getMessage();
                    }
                }
            }

            $likeCount = $this->likeModel->getCount($id);
            $hasLiked = $canInteract && $this->likeModel->hasLiked($id, (string) $_SERVER['REMOTE_ADDR']);
            $comments = $this->commentModel->getByPostId($id);

            require_once __DIR__ . '/../views/posts/show.php';
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            require_once __DIR__ . '/../views/errors/500.php';
        }
    }
}