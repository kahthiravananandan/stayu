<?php
defined('STATUS_RECHECK_INTERVAL') or define('STATUS_RECHECK_INTERVAL', 300); // 5 minutes

function requireLogin(): void {
    if (!isLoggedIn()) {
        setFlash('error', 'Sila log masuk untuk meneruskan.');
        redirect('auth/login');
    }

    // Re-verify account status in DB every 5 minutes.
    // This catches mid-session suspensions applied by an admin after the user logged in.
    $lastCheck = $_SESSION['_status_checked'] ?? 0;
    if ((time() - $lastCheck) >= STATUS_RECHECK_INTERVAL) {
        $userId = getSessionUserId();
        if ($userId) {
            $fresh = (new User())->findById($userId);
            if (!$fresh || $fresh['status'] !== 'active') {
                killSession('suspended');
            }
            $_SESSION['_status_checked'] = time();
            $_SESSION['status']          = $fresh['status'];
        }
    }
}

function requireRole(string ...$roles): void {
    requireLogin();
    if (!in_array(getSessionRole(), $roles, true)) {
        http_response_code(403);
        require BASE_PATH . '/app/views/layouts/403.php';
        exit;
    }
}

function requireStudent(): void { requireRole('pelajar'); }
function requireOwner(): void   { requireRole('pemilik'); }
function requireAdmin(): void   { requireRole('admin');   }

function canAccessListing(int $ownerId): bool {
    if (!isLoggedIn()) return false;
    $user = getSessionUser();
    if ($user['role'] === 'admin') return true;
    return $user['role'] === 'pemilik' && (int) $user['user_id'] === $ownerId;
}
