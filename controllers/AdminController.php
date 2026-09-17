<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Post.php';

class AdminController {
    private Post $postModel;

    public function __construct() {
        // Authorization Guard: Require Admin Role
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header("Location: index.php?page=login");
            exit;
        }

        $this->postModel = new Post();
    }

    public function index(): void {
        $message = '';
        $errorMessage = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {
            try {
                $title = trim((string) $_POST['title']);
                $content = trim((string) $_POST['content']);

                if (empty($title) || empty($content)) {
                    throw new Exception("Please fill in all required fields.");
                }

                $this->postModel->create($title, $content);
                $message = "Post created successfully!";
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        if (isset($_GET['delete'])) {
            try {
                $this->postModel->delete((int) $_GET['delete']);
                header("Location: index.php?page=admin&msg=deleted");
                exit;
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        try {
            $posts = $this->postModel->getAll();
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $posts = [];
        }

        require_once __DIR__ . '/../views/admin/index.php';
    }
}