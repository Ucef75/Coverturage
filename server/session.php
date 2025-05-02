<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/database.php';
require_once __DIR__ . '/../classes/users.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Init PDO et User une fois pour toutes
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $db = new Database(); // Ce fichier doit retourner un PDO
        $pdo = $db->getConnection(); // À adapter selon ta classe Database
    }
    return $pdo;
}

function getCurrentUser(): ?User {
    if (!isset($_SESSION['user_id'])) return null;

    $user = new User(getDB());
    return $user->load($_SESSION['user_id']) ? $user : null;
}

function loginUser(User $user): void {
    $_SESSION['user_id'] = $user->getId();
    $_SESSION['user_data'] = $user->toArray();
}

function logoutUser(): void {
    $_SESSION = [];
    session_destroy();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}
function refreshUserInSession(User $currentUser): void {
    if (!isLoggedIn()) {
        return;
    }
    
    // Verify the user in session matches the provided user object
    if ($_SESSION['user_id'] !== $currentUser->getId()) {
        return;
    }
    
    // Update the user data in session
    $_SESSION['user_data'] = $currentUser->toArray();
}