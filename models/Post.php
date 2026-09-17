<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class Post {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getSidebarList(): array {
        try {
            $stmt = $this->db->query("SELECT id, title, created_at FROM posts ORDER BY created_at DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Database Error [Post::getSidebarList]: " . $e->getMessage());
            return [];
        }
    }

    public function getAll(): array {
        try {
            $stmt = $this->db->query("
                SELECT p.*, 
                       COUNT(DISTINCT l.id) AS like_count, 
                       COUNT(DISTINCT c.id) AS comment_count
                FROM posts p
                LEFT JOIN likes l ON p.id = l.post_id
                LEFT JOIN comments c ON p.id = c.post_id
                GROUP BY p.id
                ORDER BY p.created_at DESC
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Database Error [Post::getAll]: " . $e->getMessage());
            throw new Exception("Unable to load blog posts at this time.");
        }
    }

    public function getById(int $id): array|false {
        try {
            $stmt = $this->db->prepare("SELECT * FROM posts WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Database Error [Post::getById]: " . $e->getMessage());
            throw new Exception("Failed to retrieve the requested post.");
        }
    }

    public function create(string $title, string $content): bool {
        try {
            $stmt = $this->db->prepare("INSERT INTO posts (title, content) VALUES (:title, :content)");
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':content', $content, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database Error [Post::create]: " . $e->getMessage());
            throw new Exception("Failed to create the post.");
        }
    }

    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM posts WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database Error [Post::delete]: " . $e->getMessage());
            throw new Exception("Failed to delete the post.");
        }
    }
}