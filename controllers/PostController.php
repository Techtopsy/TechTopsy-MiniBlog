<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../models/Like.php';

class PostController {
    private Post $postModel;
    private Comment $commentModel;
    private Like $likeModel;

    public function __construct() {
        $this->postModel = new Post();
        $this->commentModel = new Comment();
        $this->likeModel = new Like();
    }

    public function index(): void {
        $errorMessage = '';
        $posts = [];
        $sidebarPosts = [];

        try {
            $posts = $this->postModel->getAll();
            $sidebarPosts = $this->postModel->getSidebarList();
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }

        require_once __DIR__ . '/../views/posts/index.php';
    }

    public function show(): void {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : null;
        if (!$id) {
            header("Location: index.php");
            exit;
        }

        $userIp = (string) $_SERVER['REMOTE_ADDR'];
        $errorMessage = '';
        $commentSuccess = false;
        $post = [];
        $likeCount = 0;
        $hasLiked = false;
        $comments = [];
        $sidebarPosts = [];

        // Action: Toggle Like
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'like') {
            try {
                $this->likeModel->toggle($id, $userIp);
                header("Location: index.php?page=post&id=" . $id);
                exit;
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        // Action: Submit Comment
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
            try {
                $author = trim((string) $_POST['author']);
                $comment = trim((string) $_POST['comment']);

                if (empty($author) || empty($comment)) {
                    throw new Exception("Name and comment fields cannot be empty.");
                }

                $this->commentModel->create($id, $author, $comment);
                $commentSuccess = true;
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        try {
            $post = $this->postModel->getById($id);
            if (!$post) {
                throw new Exception("Post not found.");
            }

            $likeCount = $this->likeModel->getCount($id);
            $hasLiked = $this->likeModel->hasLiked($id, $userIp);
            $comments = $this->commentModel->getByPostId($id);
            $sidebarPosts = $this->postModel->getSidebarList();

            require_once __DIR__ . '/../views/posts/show.php';
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            require_once __DIR__ . '/../views/errors/500.php';
        }
    }
}