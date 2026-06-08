<?php
defined('SESSION_TIMEOUT') or define('SESSION_TIMEOUT', 1800); // 30 minutes

function startSession(): void {
    if (session_status() !== PHP_SESSION_NONE) return;

    // PHPSESSID is a session-identifier cookie only — auth data lives server-side.
    // lifetime=0: cookie expires when the browser closes (no persistent auth cookie).
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,   // Set true when serving over HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    // Inactivity timeout: kill authenticated sessions idle for > SESSION_TIMEOUT seconds
    if (!empty($_SESSION['logged_in'])) {
        if (isset($_SESSION['_last_activity']) && (time() - $_SESSION['_last_activity']) > SESSION_TIMEOUT) {
            killSession('timeout');
        }
        $_SESSION['_last_activity'] = time();
    }
}

/**
 * Destroy the current session and redirect to the login page.
 * Passes the reason via a URL parameter so no second session_start() is needed,
 * avoiding PHP session state issues after session_destroy().
 */
function killSession(string $reason): never {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: ' . BASE_URL . '/auth/login?reason=' . urlencode($reason));
    exit;
}

function setUserSession(array $user): void {
    // Regenerate session ID on every login to prevent session-fixation attacks
    session_regenerate_id(true);

    $_SESSION['user_id']         = (int) $user['user_id'];
    $_SESSION['role']            = $user['role'];
    $_SESSION['full_name']       = $user['full_name'];
    $_SESSION['owner_type']      = $user['owner_type'] ?? null;
    $_SESSION['status']          = $user['status'];
    $_SESSION['profile_photo']   = $user['profile_photo'] ?? null;
    $_SESSION['logged_in']       = true;
    $_SESSION['_last_activity']  = time();
    $_SESSION['_status_checked'] = time();
}

function isLoggedIn(): bool {
    return !empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']);
}

function getSessionUser(): array {
    return [
        'user_id'       => $_SESSION['user_id']       ?? null,
        'role'          => $_SESSION['role']          ?? null,
        'full_name'     => $_SESSION['full_name']     ?? null,
        'owner_type'    => $_SESSION['owner_type']    ?? null,
        'status'        => $_SESSION['status']        ?? null,
        'profile_photo' => $_SESSION['profile_photo'] ?? null,
    ];
}

function getSessionRole(): ?string {
    return $_SESSION['role'] ?? null;
}

function getSessionUserId(): ?int {
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function destroySession(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

function setFlash(string $key, string $message): void {
    $_SESSION['flash'][$key] = $message;
}

function getFlash(string $key): ?string {
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function redirect(string $path): never {
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}
