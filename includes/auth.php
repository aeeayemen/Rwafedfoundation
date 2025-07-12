<?php
/**
 * Authentication File
 * Rawafed Yemen Website Admin Panel
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if admin is logged in
 */
function checkAdminLogin() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Login admin user
 */

 
function loginAdmin($username, $password, $pdo) {
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = executeQuery($pdo, $sql, [$username]);
    
    if ($stmt && $user = $stmt->fetch()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            return true;
        }
    }
    return false;
}

/**
 * Logout admin user
 */
function logoutAdmin() {
    session_destroy();
    header('Location: login.php');
    exit;
}

/**
 * Get current admin info
 */
function getCurrentAdmin($pdo) {
    if (isset($_SESSION['admin_id'])) {
        $sql = "SELECT * FROM users WHERE id = ?";
        return getSingleRecord($pdo, $sql, [$_SESSION['admin_id']]);
    }
    return false;
}

/**
 * Hash password for storage
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
?>

