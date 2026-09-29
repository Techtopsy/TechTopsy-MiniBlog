<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../models/Like.php';
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

    public function show(): void {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
        if (!$id) {
            header("Location: index.php");
            exit;
        }

        $userIp = (string) $_SERVER['REMOTE_ADDR'];
        $sidebarPosts = $this->postModel->getSidebarList();
        $sidebarAd = $this->adModel->getActiveByLocation('sidebar');

        try {
            $post = $this->postModel->getById($id);
            if (!$post || ($post['status'] !== 'published' && (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'))) {
                throw new Exception("The requested blog post is not available.");
            }

            $likeCount = $this->likeModel->getCount($id);
            $hasLiked = $this->likeModel->hasLiked($id, $userIp);
            $comments = $this->commentModel->getByPostId($id);

            require_once __DIR__ . '/../views/posts/show.php';
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            require_once __DIR__ . '/../views/errors/500.php';
        }
    }
}