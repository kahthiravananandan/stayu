<?php
class VerificationDocument {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = getPDO();
    }

    public function create(int $listingId, int $ownerId, string $icPath, string $grantPath): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO verification_documents (listing_id, owner_id, ic_doc_path, grant_doc_path)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$listingId, $ownerId, $icPath, $grantPath]);
        return (int) $this->pdo->lastInsertId();
    }

    public function getByListing(int $listingId): ?array {
        $stmt = $this->pdo->prepare('SELECT * FROM verification_documents WHERE listing_id = ? LIMIT 1');
        $stmt->execute([$listingId]);
        return $stmt->fetch() ?: null;
    }

    public function getById(int $docId): ?array {
        $stmt = $this->pdo->prepare(
            'SELECT vd.*, l.title AS listing_title, u.full_name AS owner_name
             FROM verification_documents vd
             JOIN listings l ON vd.listing_id = l.listing_id
             JOIN users u ON vd.owner_id = u.user_id
             WHERE vd.document_id = ? LIMIT 1'
        );
        $stmt->execute([$docId]);
        return $stmt->fetch() ?: null;
    }

    public function getPending(): array {
        $stmt = $this->pdo->query(
            "SELECT vd.*, l.title AS listing_title, u.full_name AS owner_name
             FROM verification_documents vd
             JOIN listings l ON vd.listing_id = l.listing_id
             JOIN users u ON vd.owner_id = u.user_id
             WHERE vd.status = 'pending'
             ORDER BY vd.submitted_at ASC"
        );
        return $stmt->fetchAll();
    }

    public function reupload(int $docId, string $icPath, string $grantPath): void {
        $stmt = $this->pdo->prepare(
            "UPDATE verification_documents
             SET ic_doc_path = ?, grant_doc_path = ?, status = 'pending',
                 rejection_reason = NULL, reviewed_by = NULL, reviewed_at = NULL,
                 submitted_at = NOW()
             WHERE document_id = ?"
        );
        $stmt->execute([$icPath, $grantPath, $docId]);
    }

    public function approve(int $docId, int $reviewerId): void {
        $stmt = $this->pdo->prepare(
            "UPDATE verification_documents
             SET status = 'approved', reviewed_by = ?, reviewed_at = NOW()
             WHERE document_id = ?"
        );
        $stmt->execute([$reviewerId, $docId]);
    }

    public function reject(int $docId, int $reviewerId, string $reason): void {
        $stmt = $this->pdo->prepare(
            "UPDATE verification_documents
             SET status = 'rejected', rejection_reason = ?, reviewed_by = ?, reviewed_at = NOW()
             WHERE document_id = ?"
        );
        $stmt->execute([$reason, $reviewerId, $docId]);
    }

    public function countPending(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM verification_documents WHERE status = 'pending'");
        return (int) $stmt->fetchColumn();
    }
}
