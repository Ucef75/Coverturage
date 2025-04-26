<?php
// Define the root directory path
define('ROOT_PATH', dirname(__DIR__));
require_once __DIR__ . '/../config.php';
// Check if files exist before including
$required_files = [
    '../include/header.php',
    '../classes/db.php',
    '../classes/rides.php',
    '../classes/user.php'
];

foreach ($required_files as $file) {
    if (!file_exists($file)) {
        die("Error: Missing required file - " . htmlspecialchars($file));
    }
    require_once $file;
}
// Include files using absolute paths
require_once ROOT_PATH . '/include/header.php';
require_once ROOT_PATH . '/classes/db.php';
require_once ROOT_PATH . '/classes/rides.php';
require_once ROOT_PATH . '/classes/user.php';

// Rest of your code...

// Initialize database connection
try {
    $db = new Database();
    
    // Initialize objects
    $ride = new Ride($db);
    $user = new User($db);
    
    // Verify user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    // Load user data
    if (!$user->load($_SESSION['user_id'])) {
        throw new Exception("User not found");
    }
    
    // Get upcoming rides for the user
    $upcomingRides = $ride->getUpcomingRides($user->getId());
    
    // Get available rides in user's region
    $userRegion = $user->getRegion();
    $availableRides = $ride->getAvailableRides($userRegion);
    
    // Calculate stats (would come from database in real app)
    $completedRides = $user->getCompletedRidesCount();
    $totalEarnings = $user->getTotalEarnings();

} catch (Exception $e) {
    // Log error and show user-friendly message
    error_log("Error in dashboard: " . $e->getMessage());
    $errorMessage = "An error occurred while loading the dashboard. Please try again later.";
}
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
            <?php if ($user->isDriver()): ?>
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
                    $bookedSeats = $ride->getBookedSeats($rideItem['id']);
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
                                <?php if ($user->isStudent()): ?>
                                    <span class="student-badge">Student</span>
                                <?php endif; ?>
                            </h4>
                            <p>
                                <i class="fas fa-calendar-alt"></i> 
                                <?php echo $departureTime->format('D, M j, g:i A'); ?> | 
                                <i class="fas fa-user-friends"></i> 
                                <?php echo $bookedSeats; ?>/<?php echo $rideItem['available_seats']; ?> seats booked
                            </p>
                            <div class="ride-actions">
                                <button class="btn-sm" onclick="viewRideDetails(<?php echo $rideItem['id']; ?>)">
                                    <i class="fas fa-info-circle"></i> Details
                                </button>
                                <button class="btn-sm btn-danger" onclick="cancelRide(<?php echo $rideItem['id']; ?>)">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                        </div>
                        <div class="ride-price">
                            $<?php echo number_format($rideItem['price'], 2); ?>
                            <?php if ($user->isStudent()): ?>
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

<?php 
include '../include/footer.php';
?>