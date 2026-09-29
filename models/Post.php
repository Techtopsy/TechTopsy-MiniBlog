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
            $stmt = $this->db->query("SELECT id, title, created_at FROM posts WHERE status = 'published' AND published_at <= NOW() ORDER BY created_at DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Database Error [Post::getSidebarList]: " . $e->getMessage());
            return [];
        }
    }

    public function getPublished(): array {
        try {
            $stmt = $this->db->query("SELECT p.*, u.username AS author_name FROM posts p LEFT JOIN users u ON u.id = p.author_id WHERE p.status = 'published' AND p.published_at <= NOW() ORDER BY p.published_at DESC, p.created_at DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Database Error [Post::getPublished]: " . $e->getMessage());
            return [];
        }
    }

    public function getFeatured(): array {
        try {
            $stmt = $this->db->query("SELECT p.*, u.username AS author_name FROM posts p LEFT JOIN users u ON u.id = p.author_id WHERE p.status = 'published' AND p.published_at <= NOW() AND p.is_featured = 1 ORDER BY p.published_at DESC, p.created_at DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Database Error [Post::getFeatured]: " . $e->getMessage());
            return [];
        }
    }

    public function getAllAdmin(): array {
        try {
            $stmt = $this->db->query("SELECT p.*, u.username AS author_name FROM posts p LEFT JOIN users u ON u.id = p.author_id ORDER BY p.created_at DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Database Error [Post::getAllAdmin]: " . $e->getMessage());
            return [];
        }
    }

    public function getById(int $id): array|false {
        try {
            $stmt = $this->db->prepare("SELECT p.*, u.username AS author_name FROM posts p LEFT JOIN users u ON u.id = p.author_id WHERE p.id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Database Error [Post::getById]: " . $e->getMessage());
            throw new Exception("Failed to retrieve the requested post.");
        }
    }

    public function create(string $title, string $content, int $authorId, ?string $image, int $isFeatured, string $status, int $showAuthor, ?string $publishedAt): bool {
        try {
            $stmt = $this->db->prepare("INSERT INTO posts (title, content, author_id, image, is_featured, status, show_author, published_at) VALUES (:title, :content, :author_id, :image, :is_featured, :status, :show_author, :published_at)");
            return $stmt->execute([
                ':title' => $title,
                ':content' => $content,
                ':author_id' => $authorId,
                ':image' => $image,
                ':is_featured' => $isFeatured,
                ':status' => $status,
                ':show_author' => $showAuthor,
                ':published_at' => $publishedAt ?: date('Y-m-d H:i:s'),
            ]);
        } catch (PDOException $e) {
            error_log("Database Error [Post::create]: " . $e->getMessage());
            throw new Exception("Failed to create the post.");
        }
    }

    public function update(int $id, string $title, string $content, ?string $image, int $isFeatured, string $status, int $showAuthor, ?string $publishedAt): bool {
        try {
            $sql = "UPDATE posts SET title = :title, content = :content, image = COALESCE(:image, image), is_featured = :is_featured, status = :status, show_author = :show_author, published_at = :published_at WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':title' => $title,
                ':content' => $content,
                ':image' => $image,
                ':is_featured' => $isFeatured,
                ':status' => $status,
                ':show_author' => $showAuthor,
                ':published_at' => $publishedAt ?: date('Y-m-d H:i:s'),
            ]);
        } catch (PDOException $e) {
            error_log("Database Error [Post::update]: " . $e->getMessage());
            throw new Exception("Failed to update the post.");
        }
    }

    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM posts WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Database Error [Post::delete]: " . $e->getMessage());
            throw new Exception("Failed to delete the post.");
        }
    }
}