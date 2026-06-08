<?php
class Listing {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = getPDO();
    }

    public function getAll(array $filters = [], int $page = 1, int $perPage = 12): array {
        $where  = ["l.status = 'active'"];
        $params = [];

        if (!empty($filters['keyword'])) {
            $where[]  = '(l.title LIKE :kw OR l.address LIKE :kw2)';
            $params[':kw']  = '%' . $filters['keyword'] . '%';
            $params[':kw2'] = '%' . $filters['keyword'] . '%';
        }
        if (!empty($filters['property_type'])) {
            $where[]  = 'l.property_type = :ptype';
            $params[':ptype'] = $filters['property_type'];
        }
        if (!empty($filters['min_rent'])) {
            $where[]  = 'l.monthly_rent >= :minr';
            $params[':minr'] = (float) $filters['min_rent'];
        }
        if (!empty($filters['max_rent'])) {
            $where[]  = 'l.monthly_rent <= :maxr';
            $params[':maxr'] = (float) $filters['max_rent'];
        }
        if (!empty($filters['gender_pref'])) {
            $where[]  = "(l.gender_pref = :gp OR l.gender_pref = 'any')";
            $params[':gp'] = $filters['gender_pref'];
        }
        if (!empty($filters['max_distance'])) {
            $where[]  = '(l.distance_km <= :dist OR l.distance_km IS NULL)';
            $params[':dist'] = (float) $filters['max_distance'];
        }
        if (!empty($filters['amenities'])) {
            foreach ((array)$filters['amenities'] as $i => $aid) {
                $key = ':am' . $i;
                $where[] = "EXISTS (SELECT 1 FROM listing_amenities la WHERE la.listing_id = l.listing_id AND la.amenity_id = $key)";
                $params[$key] = (int)$aid;
            }
        }

        $whereSQL = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $sql = "SELECT l.*, u.full_name AS owner_name, u.owner_type,
                    (SELECT photo_path FROM listing_photos WHERE listing_id = l.listing_id AND is_cover = 1 LIMIT 1) AS cover_photo
                FROM listings l
                JOIN users u ON l.owner_id = u.user_id
                WHERE $whereSQL
                ORDER BY u.owner_type DESC, l.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAll(array $filters = []): int {
        $where  = ["l.status = 'active'"];
        $params = [];

        if (!empty($filters['keyword'])) {
            $where[]  = '(l.title LIKE :kw OR l.address LIKE :kw2)';
            $params[':kw']  = '%' . $filters['keyword'] . '%';
            $params[':kw2'] = '%' . $filters['keyword'] . '%';
        }
        if (!empty($filters['property_type'])) {
            $where[]  = 'l.property_type = :ptype';
            $params[':ptype'] = $filters['property_type'];
        }
        if (!empty($filters['min_rent'])) {
            $where[]  = 'l.monthly_rent >= :minr';
            $params[':minr'] = (float) $filters['min_rent'];
        }
        if (!empty($filters['max_rent'])) {
            $where[]  = 'l.monthly_rent <= :maxr';
            $params[':maxr'] = (float) $filters['max_rent'];
        }

        $whereSQL = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) FROM listings l JOIN users u ON l.owner_id = u.user_id WHERE $whereSQL";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT l.*, u.full_name AS owner_name, u.phone_number AS owner_phone,
                    u.email AS owner_email, u.owner_type
             FROM listings l JOIN users u ON l.owner_id = u.user_id
             WHERE l.listing_id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $listing = $stmt->fetch() ?: null;
        if ($listing) {
            $listing['photos']    = $this->getPhotos($id);
            $listing['amenities'] = $this->getAmenities($id);
        }
        return $listing;
    }

    public function getByOwner(int $ownerId): array {
        $stmt = $this->pdo->prepare(
            "SELECT l.*,
                (SELECT photo_path FROM listing_photos WHERE listing_id = l.listing_id AND is_cover = 1 LIMIT 1) AS cover_photo
             FROM listings l WHERE l.owner_id = ? ORDER BY l.created_at DESC"
        );
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO listings (owner_id, title, description, property_type, monthly_rent, deposit,
             gender_pref, address, latitude, longitude, distance_km, status)
             VALUES (:owner_id,:title,:desc,:ptype,:rent,:deposit,:gp,:addr,:lat,:lng,:dist,:status)'
        );
        $stmt->execute([
            ':owner_id' => $data['owner_id'],
            ':title'    => $data['title'],
            ':desc'     => $data['description'],
            ':ptype'    => $data['property_type'],
            ':rent'     => $data['monthly_rent'],
            ':deposit'  => $data['deposit'] ?? null,
            ':gp'       => $data['gender_pref'],
            ':addr'     => $data['address'],
            ':lat'      => $data['latitude'] ?? null,
            ':lng'      => $data['longitude'] ?? null,
            ':dist'     => $data['distance_km'] ?? null,
            ':status'   => $data['status'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $stmt = $this->pdo->prepare(
            'UPDATE listings SET title=:title, description=:desc, property_type=:ptype,
             monthly_rent=:rent, deposit=:deposit, gender_pref=:gp, address=:addr,
             latitude=:lat, longitude=:lng, distance_km=:dist
             WHERE listing_id=:id'
        );
        $stmt->execute([
            ':title'   => $data['title'],
            ':desc'    => $data['description'],
            ':ptype'   => $data['property_type'],
            ':rent'    => $data['monthly_rent'],
            ':deposit' => $data['deposit'] ?? null,
            ':gp'      => $data['gender_pref'],
            ':addr'    => $data['address'],
            ':lat'     => $data['latitude'] ?? null,
            ':lng'     => $data['longitude'] ?? null,
            ':dist'    => $data['distance_km'] ?? null,
            ':id'      => $id,
        ]);
    }

    public function updateStatus(int $id, string $status): void {
        $stmt = $this->pdo->prepare('UPDATE listings SET status = ? WHERE listing_id = ?');
        $stmt->execute([$status, $id]);
    }

    public function updateDistance(int $id, float $distanceKm): void {
        $stmt = $this->pdo->prepare('UPDATE listings SET distance_km = ? WHERE listing_id = ?');
        $stmt->execute([$distanceKm, $id]);
    }

    public function delete(int $id): void {
        $stmt = $this->pdo->prepare('DELETE FROM listings WHERE listing_id = ?');
        $stmt->execute([$id]);
    }

    public function getPhotos(int $listingId): array {
        $stmt = $this->pdo->prepare('SELECT * FROM listing_photos WHERE listing_id = ? ORDER BY is_cover DESC');
        $stmt->execute([$listingId]);
        return $stmt->fetchAll();
    }

    public function addPhoto(int $listingId, string $path, bool $isCover = false): void {
        if ($isCover) {
            $this->pdo->prepare('UPDATE listing_photos SET is_cover = 0 WHERE listing_id = ?')->execute([$listingId]);
        }
        $stmt = $this->pdo->prepare('INSERT INTO listing_photos (listing_id, photo_path, is_cover) VALUES (?, ?, ?)');
        $stmt->execute([$listingId, $path, $isCover ? 1 : 0]);
    }

    public function deletePhoto(int $photoId): ?string {
        $stmt = $this->pdo->prepare('SELECT photo_path FROM listing_photos WHERE photo_id = ?');
        $stmt->execute([$photoId]);
        $row = $stmt->fetch();
        if ($row) {
            $this->pdo->prepare('DELETE FROM listing_photos WHERE photo_id = ?')->execute([$photoId]);
            return $row['photo_path'];
        }
        return null;
    }

    public function getAmenities(int $listingId): array {
        $stmt = $this->pdo->prepare(
            'SELECT a.amenity_id, a.amenity_name FROM amenities a
             JOIN listing_amenities la ON a.amenity_id = la.amenity_id
             WHERE la.listing_id = ?'
        );
        $stmt->execute([$listingId]);
        return $stmt->fetchAll();
    }

    public function syncAmenities(int $listingId, array $amenityIds): void {
        $this->pdo->prepare('DELETE FROM listing_amenities WHERE listing_id = ?')->execute([$listingId]);
        if (!empty($amenityIds)) {
            $stmt = $this->pdo->prepare('INSERT INTO listing_amenities (listing_id, amenity_id) VALUES (?, ?)');
            foreach ($amenityIds as $aid) {
                $stmt->execute([$listingId, (int)$aid]);
            }
        }
    }

    public function getAllAmenities(): array {
        return $this->pdo->query('SELECT * FROM amenities ORDER BY amenity_name')->fetchAll();
    }

    public function getPendingReview(): array {
        $stmt = $this->pdo->prepare(
            "SELECT l.*, u.full_name AS owner_name FROM listings l
             JOIN users u ON l.owner_id = u.user_id
             WHERE l.status = 'in_review' ORDER BY l.created_at ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countByStatus(string $status): int {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM listings WHERE status = ?');
        $stmt->execute([$status]);
        return (int) $stmt->fetchColumn();
    }

    public function getAllForAdmin(): array {
        $stmt = $this->pdo->query(
            "SELECT l.*, u.full_name AS owner_name, u.user_id AS owner_user_id
             FROM listings l JOIN users u ON l.owner_id = u.user_id
             ORDER BY l.created_at DESC"
        );
        return $stmt->fetchAll();
    }
}
