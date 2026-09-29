<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class Ad {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function create(string $title, string $location, string $imageUrl, string $targetUrl): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO ads (title, location, image_url, target_url, is_active) 
                VALUES (:title, :location, :image_url, :target_url, 1)
            ");
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':location', $location, PDO::PARAM_STR);
            $stmt->bindParam(':image_url', $imageUrl, PDO::PARAM_STR);
            $stmt->bindParam(':target_url', $targetUrl, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database Error [Ad::create]: " . $e->getMessage());
            throw new Exception("Failed to create advertisement.");
        }
    }

    public function getActiveByLocation(string $location): array|false {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM ads 
                WHERE location = :location AND is_active = 1 
                ORDER BY RAND() LIMIT 1
            ");
            $stmt->bindParam(':location', $location, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Database Error [Ad::getActiveByLocation]: " . $e->getMessage());
            return false;
        }
    }

    public function getAll(): array {
        try {
            $stmt = $this->db->query("SELECT * FROM ads ORDER BY created_at DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function toggleActive(int $id): bool {
        try {
            $stmt = $this->db->prepare("UPDATE ads SET is_active = NOT is_active WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}