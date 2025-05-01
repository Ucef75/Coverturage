<?php
// Include the centralized session file
require_once '../server/session.php';
require_once '../server/language.php';

// Initialize with default values
$username = 'Guest';
$profilePic = '../src/default.jpg';
$userRole = 'Guest';

// Use the currentUser from our session system
if (isLoggedIn()) {
    try {
        $user = getCurrentUser();

        $username = $user['username'] ?? 'User';
        $profilePic = $user['profile_picture'] ?? '../src/default.jpg';

        // Handle roles safely
        $roles = [];
        if (isset($user['is_student']) && $user['is_student']) {
            $roles[] = 'Student';
        }
        if (isset($user['is_driver']) && $user['is_driver']) {
            $roles[] = 'Driver';
        }
        $roles[] = 'Passenger'; // Default role for all users

        // Safely implode roles
        $userRole = !empty($roles) ? implode(' & ', $roles) : 'Passenger';

    } catch (Exception $e) {
        error_log("Error loading user in sidebar: " . $e->getMessage());
        // Fallback values
        $username = 'User';
        $profilePic = '../src/default.jpg';
        $userRole = 'Passenger';
    }
}
?>

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
        <li><a href="<?php echo addLangAndCountryToUrl('logout.php'); ?>">
            <i class="fas fa-sign-out-alt"></i> <span><?php echo t('logout', 'Logout'); ?></span>
        </a></li>
    </ul>
</aside>