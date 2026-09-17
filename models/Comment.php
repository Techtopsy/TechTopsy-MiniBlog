<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class Comment {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getByPostId(int $postId): array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM comments WHERE post_id = :post_id ORDER BY created_at DESC");
            $stmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Database Error [Comment::getByPostId]: " . $e->getMessage());
            throw new Exception("Failed to retrieve comments.");
        }
    }

    public function create(int $postId, string $author, string $comment): bool {
        try {
            $stmt = $this->db->prepare("INSERT INTO comments (post_id, author, comment) VALUES (:post_id, :author, :comment)");
            $stmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
            $stmt->bindParam(':author', $author, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database Error [Comment::create]: " . $e->getMessage());
            throw new Exception("Failed to submit your comment.");
        }
    }
}