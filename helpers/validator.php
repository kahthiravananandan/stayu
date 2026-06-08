<?php
function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

function validateEmail(string $email): bool {
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone(string $phone): bool {
    return (bool) preg_match('/^(\+?60|0)[0-9]{8,10}$/', preg_replace('/\s+/', '', $phone));
}

function validateIC(string $ic): bool {
    return (bool) preg_match('/^\d{12}$/', preg_replace('/-/', '', $ic));
}

function validateMatric(string $matric): bool {
    // UKM pelajar matric: letter A followed by exactly 6 digits (e.g. A202584)
    return (bool) preg_match('/^A\d{6}$/', strtoupper(trim($matric)));
}

function validatePassword(string $password): bool {
    return strlen($password) >= 8;
}

function validateDate(string $date): bool {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date && $d > new DateTime();
}

function validateTime(string $time): bool {
    return (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time);
}

function validateFileUpload(array $file, array $allowedTypes, int $maxBytes = 5242880): array {
    $errors = [];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Muat naik fail gagal. Kod ralat: ' . $file['error'];
        return $errors;
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowedTypes, true)) {
        $errors[] = 'Jenis fail tidak dibenarkan.';
    }
    if ($file['size'] > $maxBytes) {
        $errors[] = 'Saiz fail melebihi had ' . round($maxBytes / 1048576, 1) . ' MB.';
    }
    return $errors;
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('CSRF token mismatch.');
    }
    // Invalidate after use so each form submission requires a fresh token.
    unset($_SESSION['csrf_token']);
}
