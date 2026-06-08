<?php
class ViewingRequest {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = getPDO();
    }

    public function create(int $listingId, int $studentId, string $date, string $time): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO viewing_requests (listing_id, student_id, proposed_date, proposed_time)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$listingId, $studentId, $date, $time]);
        return (int) $this->pdo->lastInsertId();
    }

    public function getByStudent(int $studentId): array {
        $stmt = $this->pdo->prepare(
            'SELECT vr.*, l.title AS listing_title, l.address,
                    u.full_name AS owner_name, u.phone_number AS owner_phone
             FROM viewing_requests vr
             JOIN listings l ON vr.listing_id = l.listing_id
             JOIN users u ON l.owner_id = u.user_id
             WHERE vr.student_id = ?
             ORDER BY vr.created_at DESC'
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public function getByOwner(int $ownerId): array {
        $stmt = $this->pdo->prepare(
            'SELECT vr.*, l.title AS listing_title,
                    u.full_name AS student_name, u.phone_number AS student_phone,
                    u.matric_number
             FROM viewing_requests vr
             JOIN listings l ON vr.listing_id = l.listing_id
             JOIN users u ON vr.student_id = u.user_id
             WHERE l.owner_id = ?
             ORDER BY vr.proposed_date ASC, vr.proposed_time ASC'
        );
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll();
    }

    public function getById(int $requestId): ?array {
        $stmt = $this->pdo->prepare('SELECT * FROM viewing_requests WHERE request_id = ? LIMIT 1');
        $stmt->execute([$requestId]);
        return $stmt->fetch() ?: null;
    }

    public function updateStatus(int $requestId, string $status): void {
        $stmt = $this->pdo->prepare('UPDATE viewing_requests SET status = ? WHERE request_id = ?');
        $stmt->execute([$status, $requestId]);
    }

    public function existsPending(int $listingId, int $studentId): bool {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM viewing_requests
             WHERE listing_id = ? AND student_id = ? AND status = 'pending'"
        );
        $stmt->execute([$listingId, $studentId]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
