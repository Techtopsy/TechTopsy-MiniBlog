<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

/**
 * @method void updatePassword(int $userId, string $password)
 */
class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
        $this->ensureProfilePicColumnExists();
    }

    private function ensureProfilePicColumnExists(): void {
        try {
            $check = $this->db->query("SHOW COLUMNS FROM users LIKE 'profile_pic'");
            if ($check->rowCount() === 0) {
                $this->db->exec("ALTER TABLE users ADD COLUMN profile_pic VARCHAR(255) DEFAULT NULL");
            }
        } catch (PDOException $e) {
            error_log("Database Error [User::ensureProfilePicColumnExists]: " . $e->getMessage());
        }
    }

    public function create(string $username, string $email, string $password, string $role = 'user'): bool {
        try {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password_hash, role) 
                VALUES (:username, :email, :password_hash, :role)
            ");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password_hash', $passwordHash, PDO::PARAM_STR);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database Error [User::create]: " . $e->getMessage());
            if ($e->getCode() === '23000') {
                throw new Exception("Username or Email already exists.");
            }
            throw new Exception("Registration failed. Please try again.");
        }
    }

    public function findByEmail(string $email): array|false {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Database Error [User::findByEmail]: " . $e->getMessage());
            throw new Exception("Error checking user records.");
        }
    }

    public function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    public function updatePassword(int $userId, string $password): void {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
        $stmt->execute([
            ':password_hash' => $passwordHash,
            ':id' => $userId,
        ]);
    }

    public function findById(int $id): array|false {
        try {
            $stmt = $this->db->prepare("SELECT id, username, email, role, profile_pic FROM users WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $user = $stmt->fetch();
            if ($user !== false && !array_key_exists('profile_pic', $user)) {
                $user['profile_pic'] = null;
            }

            return $user;
        } catch (PDOException $e) {
            if ($e->getCode() === '42S22') {
                try {
                    $fallback = $this->db->prepare("SELECT id, username, email, role FROM users WHERE id = :id");
                    $fallback->bindParam(':id', $id, PDO::PARAM_INT);
                    $fallback->execute();
                    $user = $fallback->fetch();
                    if ($user !== false) {
                        $user['profile_pic'] = null;
                    }
                    return $user;
                } catch (PDOException $fallbackException) {
                    error_log("Database Error [User::findById fallback]: " . $fallbackException->getMessage());
                }
            }

            error_log("Database Error [User::findById]: " . $e->getMessage());
            throw new Exception("Error retrieving user details.");
        }
    }

    public function updateProfilePic(int $userId, string $filename): bool {
        try {
            $stmt = $this->db->prepare("UPDATE users SET profile_pic = :profile_pic WHERE id = :id");
            $stmt->bindParam(':profile_pic', $filename, PDO::PARAM_STR);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database Error [User::updateProfilePic]: " . $e->getMessage());
            throw new Exception("Failed to update profile picture.");
        }
    }
}