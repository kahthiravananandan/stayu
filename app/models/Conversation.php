<?php
class Conversation {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = getPDO();
    }

    public function findOrCreate(int $listingId, int $studentId, int $ownerId): array {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM conversations WHERE listing_id = ? AND student_id = ? AND owner_id = ? LIMIT 1'
        );
        $stmt->execute([$listingId, $studentId, $ownerId]);
        $conv = $stmt->fetch();

        if ($conv) return $conv;

        // Generate unique Firebase session ID
        $sessionId = 'chat_' . $listingId . '_' . $studentId . '_' . $ownerId . '_' . bin2hex(random_bytes(4));
        $stmt = $this->pdo->prepare(
            'INSERT INTO conversations (listing_id, student_id, owner_id, firebase_session_id)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$listingId, $studentId, $ownerId, $sessionId]);
        return [
            'conversation_id'     => (int) $this->pdo->lastInsertId(),
            'listing_id'          => $listingId,
            'student_id'          => $studentId,
            'owner_id'            => $ownerId,
            'firebase_session_id' => $sessionId,
        ];
    }

    public function getByStudent(int $studentId): array {
        $stmt = $this->pdo->prepare(
            'SELECT c.*, l.title AS listing_title, u.full_name AS owner_name
             FROM conversations c
             JOIN listings l ON c.listing_id = l.listing_id
             JOIN users u ON c.owner_id = u.user_id
             WHERE c.student_id = ?
             ORDER BY c.created_at DESC'
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public function getByOwner(int $ownerId): array {
        $stmt = $this->pdo->prepare(
            'SELECT c.*, l.title AS listing_title, u.full_name AS student_name, u.matric_number
             FROM conversations c
             JOIN listings l ON c.listing_id = l.listing_id
             JOIN users u ON c.student_id = u.user_id
             WHERE c.owner_id = ?
             ORDER BY c.created_at DESC'
        );
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare(
            'SELECT c.*, l.title AS listing_title,
                    s.full_name AS student_name, s.matric_number,
                    o.full_name AS owner_name
             FROM conversations c
             JOIN listings l ON c.listing_id = l.listing_id
             JOIN users s ON c.student_id = s.user_id
             JOIN users o ON c.owner_id = o.user_id
             WHERE c.conversation_id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
