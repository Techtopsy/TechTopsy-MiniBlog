<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class Like {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getCount(int $postId): int {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM likes WHERE post_id = :post_id");
            $stmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Database Error [Like::getCount]: " . $e->getMessage());
            return 0;
        }
    }

    public function hasLiked(int $postId, string $ip): bool {
        try {
            $stmt = $this->db->prepare("SELECT id FROM likes WHERE post_id = :post_id AND ip_address = :ip");
            $stmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
            $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Database Error [Like::hasLiked]: " . $e->getMessage());
            return false;
        }
    }

    public function toggle(int $postId, string $ip): bool {
        try {
            if ($this->hasLiked($postId, $ip)) {
                $stmt = $this->db->prepare("DELETE FROM likes WHERE post_id = :post_id AND ip_address = :ip");
            } else {
                $stmt = $this->db->prepare("INSERT INTO likes (post_id, ip_address) VALUES (:post_id, :ip)");
            }

            $stmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
            $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database Error [Like::toggle]: " . $e->getMessage());
            throw new Exception("Unable to update like status.");
        }
    }
}