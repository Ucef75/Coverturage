<?php
require_once '../server/session.php';

// Initialize all variables with default values
$user = null;
$ride = null;
$upcomingRides = [];
$availableRides = [];
$completedRides = 0;
$totalEarnings = 0.00;
$errorMessage = null;

// Include config file first
$configFile = '../config.php';
if (!file_exists($configFile)) {
    die("Error: Missing config.php file at " . htmlspecialchars($configFile));
}
require_once $configFile;

// List of required files with their relative paths from ROOT_PATH
$required_files = [
    '../include/header.php',
    '../include/sidebar.php',
    '../classes/database.php',
    '../classes/users.php',
    '../classes/rides.php',
];

// Check if all required files exist
foreach ($required_files as $file) {
    if (!file_exists($file)) {
        die("Error: Missing required file: " . htmlspecialchars($file));
    }
}

// Include all required files
require_once '../classes/database.php';
require_once '../classes/users.php';  // Changed from users.php to user.php to match your class file
require_once '../classes/rides.php';

try {
    // Initialize database connection
    $db = new Database();
    
    // Initialize objects
    $ride = new Ride($db);
    $user = new User($db);
    
    // Verify user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../pages/login.php");
        exit();
    }

    // Debugging: Output session user_id
    // echo "Session User ID: " . $_SESSION['user_id']; // Uncomment for debugging
    
    // Load the user
    $userId = $_SESSION['user_id'];
    if (!$user->load($userId)) {
        throw new Exception("User with ID $userId not found");
    }
    
    // Debugging: Output user information
    /*
    echo "<pre>";
    echo "User Loaded:\n";
    echo "ID: " . $user->getId() . "\n";
    echo "Username: " . $user->getUsername() . "\n";
    echo "Email: " . $user->getEmail() . "\n";
    echo "Is Driver: " . ($user->isDriver() ? 'Yes' : 'No') . "\n";
    echo "Is Student: " . ($user->isStudent() ? 'Yes' : 'No') . "\n";
    echo "Region: " . $user->getRegion() . "\n";
    echo "Score: " . $user->getScore() . "\n";
    echo "</pre>";
    */    // Get upcoming rides for the user
    //$upcomingRides = $ride->getUpcomingRides($user->getId()) ?: [];
    
    // Get available rides in user's region
    $userRegion = $user->getRegion();
    $availableRides = $ride->getAvailableRides($userRegion) ?: [];
    
    // Calculate stats
    //$completedRides = $user->getCompletedRidesCount();
    //$totalEarnings = $user->getTotalEarnings();
    
} catch (Exception $e) {
    // Log error and show user-friendly message
    error_log("Error in dashboard: " . $e->getMessage());
    $errorMessage = "We encountered an error loading your data. Please try again later.";
}

// Include header after processing to prevent header errors
include '../include/header.php';
include '../include/sidebar.php';
?>

<main class="main-content">
    <div class="header">
        <h1>Forsa<span>Drive</span></h1>
        <div class="search-bar">
            <input type="text" placeholder="Search rides..." id="rideSearch">
            <button onclick="searchRides()"><i class="fas fa-search"></i></button>
        </div>
    </div>
    
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>
    
    <!-- Dashboard Stats -->
    <div class="dashboard-cards">
        <div class="card">
            <h3>Upcoming Rides</h3>
            <div class="value"><?php echo count($upcomingRides); ?></div>
        </div>
        <div class="card">
            <h3>Completed Rides</h3>
            <div class="value"><?php echo $completedRides; ?></div>
        </div>
        <div class="card">
            <h3>Total Earnings</h3>
            <div class="value">$<?php echo number_format($totalEarnings, 2); ?></div>
        </div>
    </div>
    
    <!-- Upcoming Rides Section -->
    <div class="rides-section">
        <div class="section-header">
            <h2>Upcoming Rides</h2>
            <?php if ($user && $user->isDriver()): ?>
                <button class="btn" onclick="location.href='offer_ride.php'">
                    <i class="fas fa-plus"></i> Offer a Ride
                </button>
            <?php endif; ?>
        </div>
        
        <div class="rides-list">
            <?php if (empty($upcomingRides)): ?>
                <div class="no-rides">You have no upcoming rides scheduled.</div>
            <?php else: ?>
                <?php foreach ($upcomingRides as $rideItem): 
                    $bookedSeats = $ride ? $ride->getBookedSeats($rideItem['id']) : 0;
                    try {
                        $departureTime = new DateTime($rideItem['departure_time']);
                    } catch (Exception $e) {
                        $departureTime = new DateTime('now');
                    }
                ?>
                    <div class="ride-card">
                        <div class="ride-info">
                            <h4>
                                <?php echo htmlspecialchars($rideItem['from_location']); ?> to <?php echo htmlspecialchars($rideItem['to_location']); ?>
                                <?php if ($user && $user->isStudent()): ?>
                                    <span class="student-badge">Student</span>
                                <?php endif; ?>
                            </h4>
                            <p>
                                <i class="fas fa-calendar-alt"></i> 
                                <?php echo $departureTime->format('D, M j, g:i A'); ?> | 
                                <i class="fas fa-user-friends"></i> 
                                <?php echo $bookedSeats; ?>/<?php echo $rideItem['available_seats']; ?> seats booked
                            </p>
                            <?php if ($ride): ?>
                            <div class="ride-actions">
                                <button class="btn-sm" onclick="viewRideDetails(<?php echo $rideItem['id']; ?>)">
                                    <i class="fas fa-info-circle"></i> Details
                                </button>
                                <button class="btn-sm btn-danger" onclick="cancelRide(<?php echo $rideItem['id']; ?>)">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="ride-price">
                            $<?php echo number_format($rideItem['price'], 2); ?>
                            <?php if ($user && $user->isStudent()): ?>
                                <div class="student-price">
                                    $<?php echo number_format($rideItem['price'] * 0.5, 2); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Available Rides Section -->
    <?php if ($ride): ?>
    <div class="rides-section" style="margin-top: 30px;">
        <div class="section-header">
            <h2>Available Rides Near You</h2>
            <div>
                <button class="btn" onclick="showFilters()">
                    <i class="fas fa-filter"></i> Filters
                </button>
                <button class="btn" onclick="refreshRides()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
        
        <div class="rides-list">
            <?php if (empty($availableRides)): ?>
                <div class="no-rides">No available rides in your area at the moment.</div>
            <?php else: ?>
                <?php foreach ($availableRides as $rideItem): 
                    $bookedSeats = $ride->getBookedSeats($rideItem['id']);
                    $availableSeats = $rideItem['available_seats'] - $bookedSeats;
                    try {
                        $departureTime = new DateTime($rideItem['departure_time']);
                    } catch (Exception $e) {
                        $departureTime = new DateTime('now');
                    }
                ?>
                    <div class="ride-card">
                        <div class="ride-info">
                            <h4>
                                <?php echo htmlspecialchars($rideItem['from_location']); ?> to <?php echo htmlspecialchars($rideItem['to_location']); ?>
                                <span class="student-badge">Student Discount</span>
                            </h4>
                            <p>
                                <i class="fas fa-calendar-alt"></i> 
                                <?php echo $departureTime->format('D, M j, g:i A'); ?> | 
                                <i class="fas fa-car"></i> 
                                <?php echo htmlspecialchars($rideItem['vehicle_type'] ?? 'Car'); ?>
                            </p>
                            <p>
                                <i class="fas fa-user"></i> 
                                <?php echo htmlspecialchars($rideItem['driver_name']); ?> 
                                ★<?php echo number_format($rideItem['driver_score'], 1); ?> | 
                                <i class="fas fa-user-friends"></i> 
                                <?php echo $availableSeats; ?> seats left
                            </p>
                            <div class="ride-actions">
                                <button class="btn-sm" onclick="viewRideDetails(<?php echo $rideItem['id']; ?>)">
                                    <i class="fas fa-info-circle"></i> Details
                                </button>
                                <button class="btn-sm btn-success" onclick="bookRide(<?php echo $rideItem['id']; ?>)">
                                    <i class="fas fa-check"></i> Book Now
                                </button>
                            </div>
                        </div>
                        <div class="ride-price">
                            <div class="original-price">
                                $<?php echo number_format($rideItem['price'], 2); ?>
                            </div>
                            <div class="discounted-price">
                                $<?php echo number_format($rideItem['price'] * 0.5, 2); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</main>

<script>
function searchRides() {
    const searchTerm = document.getElementById('rideSearch').value;
    // Implement AJAX search functionality
    console.log("Searching for:", searchTerm);
}

function viewRideDetails(rideId) {
    window.location.href = `ride_details.php?id=${rideId}`;
}

function bookRide(rideId) {
    if (confirm("Book this ride? 50% payment will be required to confirm.")) {
        window.location.href = `book_ride.php?id=${rideId}`;
    }
}

function cancelRide(bookingId) {
    if (confirm("Are you sure you want to cancel this ride?")) {
        window.location.href = `cancel_ride.php?id=${bookingId}`;
    }
}

function refreshRides() {
    window.location.reload();
}

function showFilters() {
    // Implement filter display logic
    alert("Filter functionality coming soon!");
}
</script>
</body>
</html>