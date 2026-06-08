<?php
class Notification {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = getPDO();
    }

    public function create(int $userId, string $type, string $message): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO notifications (user_id, type, message) VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $type, $message]);
    }

    public function getByUser(int $userId, int $limit = 20): array {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?'
        );
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countUnread(int $userId): int {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0'
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function markRead(int $userId): void {
        $stmt = $this->pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
        $stmt->execute([$userId]);
    }

    public function markOneRead(int $notifId): void {
        $stmt = $this->pdo->prepare('UPDATE notifications SET is_read = 1 WHERE notification_id = ?');
        $stmt->execute([$notifId]);
    }

    // Mark a single notification as read, verifying it belongs to the user.
    public function markOneById(int $notifId, int $userId): void {
        $stmt = $this->pdo->prepare(
            'UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?'
        );
        $stmt->execute([$notifId, $userId]);
    }

    // Returns all unread notifications for a user (no limit).
    public function getUnread(int $userId): array {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    // Alias of getByUser — returns the most recent N notifications.
    public function getRecent(int $userId, int $limit = 20): array {
        return $this->getByUser($userId, $limit);
    }

    // Alias of markRead — marks every notification for a user as read.
    public function markAllRead(int $userId): void {
        $this->markRead($userId);
    }
}
