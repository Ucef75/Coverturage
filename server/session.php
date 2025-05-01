<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/database.php';
require_once __DIR__ . '/../classes/users.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize database connection
if (!isset($GLOBALS['db'])) {
    $GLOBALS['db'] = new Database();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function getCurrentUser(): ?User {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    try {
        $db = getDB();
        $user = new User($db);
        if ($user->load($_SESSION['user_id'])) {
            return $user;
        }
    } catch (Exception $e) {
        error_log("Failed to load user: " . $e->getMessage());
    }
    
    return null;
}

function loginUser(array $userData): void {
    $_SESSION['user_id'] = $userData['id'];
    $_SESSION['user_data'] = $userData;
}

function logoutUser(): void {
    $_SESSION = [];
    session_destroy();
}

function getDB(): Database {
    return $GLOBALS['db'];
}