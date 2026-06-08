<?php
class User {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = getPDO();
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function findByMatric(string $matric): ?array {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE matric_number = ? LIMIT 1');
        $stmt->execute([$matric]);
        return $stmt->fetch() ?: null;
    }

    public function findByIC(string $ic): ?array {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE ic_number = ? LIMIT 1');
        $stmt->execute([$ic]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE user_id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function createStudent(array $data): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (role, full_name, matric_number, email, phone_number, password_hash)
             VALUES (\'pelajar\', :full_name, :matric, :email, :phone, :hash)'
        );
        $stmt->execute([
            ':full_name' => $data['full_name'],
            ':matric'    => $data['matric_number'],
            ':email'     => $data['email'] ?? null,   // email is optional for pelajar
            ':phone'     => $data['phone_number'],
            ':hash'      => password_hash($data['password'], PASSWORD_DEFAULT),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function createOwner(array $data): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (role, owner_type, full_name, ic_number, email, phone_number, password_hash)
             VALUES (\'pemilik\', :owner_type, :full_name, :ic, :email, :phone, :hash)'
        );
        $stmt->execute([
            ':owner_type' => $data['owner_type'],
            ':full_name'  => $data['full_name'],
            ':ic'         => $data['ic_number'],
            ':email'      => $data['email'] ?? null,   // email is optional for pemilik
            ':phone'      => $data['phone_number'],
            ':hash'       => password_hash($data['password'], PASSWORD_DEFAULT),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function createCorporate(array $data): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (role, owner_type, full_name, ic_number, email, phone_number, password_hash)
             VALUES (\'pemilik\', \'korporat\', :full_name, :ic, :email, :phone, :hash)'
        );
        $stmt->execute([
            ':full_name' => $data['full_name'],
            ':ic'        => $data['ic_number'],
            ':email'     => $data['email'],
            ':phone'     => $data['phone_number'],
            ':hash'      => password_hash($data['password'], PASSWORD_DEFAULT),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateStatus(int $userId, string $status): void {
        $stmt = $this->pdo->prepare('UPDATE users SET status = ? WHERE user_id = ?');
        $stmt->execute([$status, $userId]);
    }

    public function updateProfile(int $userId, array $data): void {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET full_name = :name, phone_number = :phone, profile_photo = :photo WHERE user_id = :id'
        );
        $stmt->execute([
            ':name'  => $data['full_name'],
            ':phone' => $data['phone_number'],
            ':photo' => $data['profile_photo'] ?? null,
            ':id'    => $userId,
        ]);
    }

    public function updatePassword(int $userId, string $newPassword): void {
        $stmt = $this->pdo->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?');
        $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
    }

    public function getAllOwners(): array {
        $stmt = $this->pdo->query("SELECT * FROM users WHERE role = 'pemilik' ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function getAllStudents(): array {
        $stmt = $this->pdo->query("SELECT * FROM users WHERE role = 'pelajar' ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function countByRole(string $role): int {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE role = ?');
        $stmt->execute([$role]);
        return (int) $stmt->fetchColumn();
    }

    public function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
}
