<?php
// Start session and include necessary files
require_once '../classes/database.php';
require_once '../classes/users.php'; // Make sure this matches your actual file name

// Initialize with default values
$username = 'Guest';
$profilePic = '../src/default.jpg';
$userRole = 'Guest';

// Only try to load user if session exists
if (isset($_SESSION['user_id'])) {
    try {
        $db = new Database();
        $user = new User($db);
        
        if ($user->load($_SESSION['user_id'])) {
            // Set username with fallback
            $username = $user->getUsername() ?? 'User';
            
            // Set profile picture
            //$profilePic = $user->getProfilePicture();
            
            // Determine user role
            $roles = [];
            if ($user->isStudent()) {
                $roles[] = 'Student';
            }
            if ($user->isDriver()) {
                $roles[] = 'Driver';
            }
            $roles[] = 'Passenger'; // Always a passenger
            
            $userRole = implode(' & ', array_unique($roles));
        }
    } catch (Exception $e) {
        error_log("Error loading user in sidebar: " . $e->getMessage());
        // Keep default values if there's an error
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
        <li><a href="interface.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'interface.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> <span>DASHBOARD</span>
        </a></li>
        <li><a href="my_rides.php"><i class="fas fa-car"></i> <span>My Rides</span></a></li>
        <li><a href="book_ride.php"><i class="fas fa-calendar-alt"></i> <span>Book a Ride</span></a></li>
        <li><a href="payments.php"><i class="fas fa-wallet"></i> <span>Payments</span></a></li>
        <li><a href="ratings.php"><i class="fas fa-star"></i> <span>Ratings</span></a></li>
        <li><a href="settings.php"><i class="fas fa-cog"></i> <span>Settings</span></a></li>
        <li><a href="login.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
    </ul>
</aside>