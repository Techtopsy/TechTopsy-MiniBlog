<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Database.php';

class Subscription {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function hasActiveSubscription(int $userId): bool {
        try {
            $stmt = $this->db->prepare("
                SELECT id FROM subscriptions 
                WHERE user_id = :user_id 
                AND status = 'success' 
                AND expires_at > NOW() 
                LIMIT 1
            ");
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Database Error [Subscription::hasActiveSubscription]: " . $e->getMessage());
            return false;
        }
    }

    public function createPending(int $userId, string $reference, float $amount): bool {
        try {
            $stmt = $this->db->prepare("INSERT INTO subscriptions (user_id, reference, amount, status, expires_at) VALUES (:user_id, :reference, :amount, 'pending', CURRENT_TIMESTAMP)");
            return $stmt->execute([
                ':user_id' => $userId,
                ':reference' => $reference,
                ':amount' => number_format($amount, 2, '.', ''),
            ]);
        } catch (PDOException $e) {
            error_log("Database Error [Subscription::createPending]: " . $e->getMessage());
            return false;
        }
    }

    public function findByReference(string $reference): array|false {
        $stmt = $this->db->prepare('SELECT * FROM subscriptions WHERE reference = :reference LIMIT 1');
        $stmt->execute([':reference' => $reference]);
        return $stmt->fetch();
    }

    public function failPending(int $userId, string $reference): void {
        $stmt = $this->db->prepare("UPDATE subscriptions SET status = 'failed' WHERE user_id = :user_id AND reference = :reference AND status = 'pending'");
        $stmt->execute([':user_id' => $userId, ':reference' => $reference]);
    }

    public function activatePayment(int $userId, string $reference): bool {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare('SELECT status FROM subscriptions WHERE user_id = :user_id AND reference = :reference FOR UPDATE');
            $stmt->execute([':user_id' => $userId, ':reference' => $reference]);
            $payment = $stmt->fetch();

            if (!$payment) {
                $this->db->rollBack();
                return false;
            }

            if ($payment['status'] === 'success') {
                $this->db->commit();
                return true;
            }

            if ($payment['status'] !== 'pending') {
                $this->db->rollBack();
                return false;
            }

            $active = $this->db->prepare("SELECT MAX(expires_at) FROM subscriptions WHERE user_id = :user_id AND status = 'success' AND expires_at > NOW()");
            $active->execute([':user_id' => $userId]);
            $activeUntil = $active->fetchColumn();
            $baseTime = max(time(), $activeUntil ? strtotime((string) $activeUntil) : 0);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days', $baseTime));

            $update = $this->db->prepare("UPDATE subscriptions SET status = 'success', expires_at = :expires_at WHERE user_id = :user_id AND reference = :reference AND status = 'pending'");
            $update->execute([
                ':expires_at' => $expiresAt,
                ':user_id' => $userId,
                ':reference' => $reference,
            ]);
            $this->db->commit();
            return $update->rowCount() === 1;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Database Error [Subscription::activatePayment]: " . $e->getMessage());
            return false;
        }
    }
}