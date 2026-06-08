<?php
class Complaint {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = getPDO();
    }

    public function create(array $data): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO complaints (complainant_id, reported_owner_id, listing_id, category, description)
             VALUES (:cid, :oid, :lid, :cat, :desc)'
        );
        $stmt->execute([
            ':cid'  => $data['complainant_id'],
            ':oid'  => $data['reported_owner_id'],
            ':lid'  => $data['listing_id'] ?? null,
            ':cat'  => $data['category'],
            ':desc' => $data['description'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function getByComplainant(int $userId): array {
        $stmt = $this->pdo->prepare(
            'SELECT c.*, u.full_name AS owner_name, l.title AS listing_title
             FROM complaints c
             JOIN users u ON c.reported_owner_id = u.user_id
             LEFT JOIN listings l ON c.listing_id = l.listing_id
             WHERE c.complainant_id = ?
             ORDER BY c.created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getByOwner(int $ownerId): array {
        $stmt = $this->pdo->prepare(
            'SELECT c.*, u.full_name AS student_name, l.title AS listing_title
             FROM complaints c
             JOIN users u ON c.complainant_id = u.user_id
             LEFT JOIN listings l ON c.listing_id = l.listing_id
             WHERE c.reported_owner_id = ?
             ORDER BY c.created_at DESC'
        );
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare(
            'SELECT c.*, s.full_name AS student_name, s.matric_number,
                    o.full_name AS owner_name, l.title AS listing_title
             FROM complaints c
             JOIN users s ON c.complainant_id = s.user_id
             JOIN users o ON c.reported_owner_id = o.user_id
             LEFT JOIN listings l ON c.listing_id = l.listing_id
             WHERE c.complaint_id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getAll(): array {
        $stmt = $this->pdo->query(
            'SELECT c.*, s.full_name AS student_name, o.full_name AS owner_name, l.title AS listing_title
             FROM complaints c
             JOIN users s ON c.complainant_id = s.user_id
             JOIN users o ON c.reported_owner_id = o.user_id
             LEFT JOIN listings l ON c.listing_id = l.listing_id
             ORDER BY c.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    public function submitDefense(int $id, int $ownerId, string $defense, ?string $evidence): void {
        $stmt = $this->pdo->prepare(
            "UPDATE complaints SET owner_defense = ?, defense_evidence = ?, status = 'under_review'
             WHERE complaint_id = ? AND reported_owner_id = ?"
        );
        $stmt->execute([$defense, $evidence, $id, $ownerId]);
    }

    public function resolve(int $id, int $adminId, string $action): void {
        $stmt = $this->pdo->prepare(
            "UPDATE complaints SET status = 'resolved', action_taken = ?,
             handled_by = ?, resolved_at = NOW()
             WHERE complaint_id = ?"
        );
        $stmt->execute([$action, $adminId, $id]);
    }

    public function getAllActive(): array {
        $stmt = $this->pdo->query(
            "SELECT c.*, s.full_name AS student_name, o.full_name AS owner_name, l.title AS listing_title
             FROM complaints c
             JOIN users s ON c.complainant_id = s.user_id
             JOIN users o ON c.reported_owner_id = o.user_id
             LEFT JOIN listings l ON c.listing_id = l.listing_id
             WHERE c.status IN ('open', 'under_review')
             ORDER BY c.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    public function countOpen(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'open'");
        return (int) $stmt->fetchColumn();
    }
}
