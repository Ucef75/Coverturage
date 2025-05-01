<?php
// server/session.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize empty user if not set
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = null;
}

// Initialize database connection if not exists
if (!isset($GLOBALS['db'])) {
    $GLOBALS['db'] = new Database();
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return $_SESSION['user'] !== null;
}

/**
 * Get current user data
 */
function getCurrentUser(): ?array {
    return $_SESSION['user'];
}

/**
 * Login user and set session data
 */
function loginUser(array $userData): void {
    $_SESSION['user'] = [
        'id' => $userData['id'],
        'username' => $userData['username'],
        'email' => $userData['email'],
        'is_driver' => (bool)($userData['is_driver'] ?? false),
        'is_student' => (bool)($userData['is_student'] ?? false),
        'region' => $userData['region'] ?? 'TN'
    ];
}

/**
 * Logout user and destroy session
 */
function logoutUser(): void {
    $_SESSION['user'] = null;
    session_destroy();
}

/**
 * Get database connection
 */
function getDB(): Database {
    return $GLOBALS['db'];
}