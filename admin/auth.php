<?php
/**
 * GlobalMed Entry - Admin Authentication & Security Middleware
 */

if (session_status() === PHP_SESSION_NONE) {
    // Harden session cookies
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

require_once __DIR__ . '/../blog/db.php';

function check_auth() {
    if (!isset($_SESSION['admin_user_id'])) {
        $currentUri = urlencode($_SERVER['REQUEST_URI'] ?? 'index.php');
        header("Location: login.php?redirect={$currentUri}");
        exit;
    }
}

function get_current_user_data() {
    if (!isset($_SESSION['admin_user_id'])) {
        return null;
    }
    $db = get_db_connection();
    $stmt = $db->prepare("SELECT id, username, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_user_id']]);
    return $stmt->fetch() ?: null;
}

// CSRF Protection
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Flash messages
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'error', 'info'
        'message' => $message
    ];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Rate Limiting
function get_client_ip() {
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

function is_ip_locked($ip) {
    $db = get_db_connection();
    $fifteenMinsAgo = time() - (15 * 60);
    
    // Clear old attempts
    $db->prepare("DELETE FROM login_attempts WHERE attempt_time < ?")->execute([$fifteenMinsAgo]);
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_time > ?");
    $stmt->execute([$ip, $fifteenMinsAgo]);
    $count = (int)$stmt->fetchColumn();
    
    return $count >= 5; // Lock after 5 failed attempts
}

function record_failed_login($ip) {
    $db = get_db_connection();
    $stmt = $db->prepare("INSERT INTO login_attempts (ip_address, attempt_time) VALUES (?, ?)");
    $stmt->execute([$ip, time()]);
}

function clear_failed_logins($ip) {
    $db = get_db_connection();
    $stmt = $db->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
    $stmt->execute([$ip]);
}
