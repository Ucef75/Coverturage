<?php
require_once '../server/session.php';
require_once '../server/language.php';

// Valeurs par défaut
$username = 'Guest';
$profilePic = '../src/default.jpg';
$userRole = 'Guest';

// Vérifie la connexion
if (isLoggedIn() && isset($_SESSION['user_data'])) {
    $userData = $_SESSION['user_data'];

    $username = $userData['username'] ?? 'User';
    $profilePic = $userData['profile_picture'] ?? '../src/default.jpg';

    $roles = [];
if (!empty($userData['is_student'])) {
    $roles[] = t('roles.student', 'Student');
}
if (!empty($userData['is_driver'])) {
    $roles[] = t('roles.driver', 'Driver');
}

$roles[] = t('roles.passenger', 'Passenger'); // Default role
$userRole = implode(' & ', $roles);
}
?>

<link rel="stylesheet" href="../css/sidebar.css">
<aside class="sidebar">
    <div class="profile">
        <div class="profile-pic">
            <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture">
        </div>
        <div class="profile-info">
            <h3><?php echo htmlspecialchars($username); ?></h3>
            <p><?php echo htmlspecialchars($userRole); ?></p>
        </div>
    </div>
    <ul class="nav-menu">
        <li><a href="<?php echo addLangAndCountryToUrl('interface.php'); ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == 'interface.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> <span><?php echo t('dashboard', 'DASHBOARD'); ?></span>
        </a></li>
        <li><a href="<?php echo addLangAndCountryToUrl('my_rides.php'); ?>">
            <i class="fas fa-car"></i> <span><?php echo t('my_rides', 'My Rides'); ?></span>
        </a></li>
        <li><a href="<?php echo addLangAndCountryToUrl('book_ride.php'); ?>">
            <i class="fas fa-calendar-alt"></i> <span><?php echo t('book_ride', 'Book a Ride'); ?></span>
        </a></li>
        <li><a href="<?php echo addLangAndCountryToUrl('payments.php'); ?>">
            <i class="fas fa-wallet"></i> <span><?php echo t('payments', 'Payments'); ?></span>
        </a></li>
        <li><a href="<?php echo addLangAndCountryToUrl('ratings.php'); ?>">
            <i class="fas fa-star"></i> <span><?php echo t('ratings', 'Ratings'); ?></span>
        </a></li>
        <li><a href="<?php echo addLangAndCountryToUrl('settings.php'); ?>">
            <i class="fas fa-cog"></i> <span><?php echo t('settings', 'Settings'); ?></span>
        </a></li>
        <li><a href="<?php echo addLangAndCountryToUrl('../index.php'); ?>">
            <i class="fas fa-sign-out-alt"></i> <span><?php echo t('logout', 'Logout'); ?></span>
        </a></li>
    </ul>
</aside>