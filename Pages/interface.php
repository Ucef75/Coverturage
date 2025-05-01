<?php
// Include the centralized session management first
require_once '../server/session.php';

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Initialize variables with default values
$ride = null;
$upcomingRides = [];
$availableRides = [];
$completedRides = 0;
$errorMessage = null;

// Verify required files exist
$requiredFiles = [
    '../config.php',
    '../classes/rides.php',
    '../include/header.php',
    '../include/sidebar.php'
];

foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        die("Error: Missing required file: " . htmlspecialchars($file));
    }
}

// Include all required files
require_once '../config.php';
require_once '../classes/rides.php';

try {
    // Get services from session
    $db = getDB();
    $user = getCurrentUser();
    
    // Redirect if not logged in
    if (!isLoggedIn()) {
        header("Location: ../pages/login.php");
        exit();
    }
    
    // Initialize ride service
    $ride = new Ride($db);
    
    // Get user data from session
    $userData = $_SESSION['user'];
    $userRegion = $userData['region'] ?? 'TN'; // Default to Tunisia
    
    // Get rides data
    //$availableRides = $ride->getAvailableRides($userRegion) ?: [];
    //$completedRides = $ride->getCompletedRidesCount($userData['id']);
    
} catch (PDOException $e) {
    error_log("Database error in dashboard: " . $e->getMessage());
    $errorMessage = t('error.database', 'Database connection error');
} catch (Exception $e) {
    error_log("Error in dashboard: " . $e->getMessage());
    $errorMessage = t('error.general', 'System error occurred');
}

// Include UI components
include '../include/header.php';
include '../include/sidebar.php';
?>

<main class="main-content">
    <div class="header">
        <h1>Forsa<span>Drive</span></h1>
        <div class="search-bar">
            <input type="text" placeholder="<?php echo t('search_rides_placeholder', 'Search rides...'); ?>" id="rideSearch">
            <button onclick="searchRides()"><i class="fas fa-search"></i></button>
        </div>
    </div>
    
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>
    
    <!-- Dashboard Stats -->
    <div class="dashboard-cards">
        <div class="card">
            <h3><?php echo t('upcoming_rides', 'Upcoming Rides'); ?></h3>
            <div class="value"><?php echo count($upcomingRides); ?></div>
        </div>
        <div class="card">
            <h3><?php echo t('completed_rides', 'Completed Rides'); ?></h3>
            <div class="value"><?php echo $completedRides; ?></div>
        </div>
    </div>
    
    <!-- Upcoming Rides Section -->
    <div class="rides-section">
        <div class="section-header">
            <h2><?php echo t('upcoming_rides', 'Upcoming Rides'); ?></h2>
            <?php if ($user && !empty($user['is_driver'])): ?>
                <button class="btn" onclick="location.href='<?php echo addLangAndCountryToUrl('offer_ride.php'); ?>'">
                    <i class="fas fa-plus"></i> <?php echo t('offer_ride', 'Offer a Ride'); ?>
                </button>
            <?php endif; ?>
        </div>
        
        <div class="rides-list">
            <?php if (empty($upcomingRides)): ?>
                <div class="no-rides"><?php echo t('no_upcoming_rides', 'You have no upcoming rides scheduled.'); ?></div>
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
                                <?php echo htmlspecialchars($rideItem['from_location']); ?> <?php echo t('to', 'to'); ?> <?php echo htmlspecialchars($rideItem['to_location']); ?>
                                <?php if ($user && !empty($user['is_student'])): ?>
                                    <span class="student-badge"><?php echo t('student', 'Student'); ?></span>
                                <?php endif; ?>
                            </h4>
                            <p>
                                <i class="fas fa-calendar-alt"></i> 
                                <?php echo $departureTime->format('D, M j, g:i A'); ?> | 
                                <i class="fas fa-user-friends"></i> 
                                <?php echo $bookedSeats; ?>/<?php echo $rideItem['available_seats']; ?> <?php echo t('seats_booked', 'seats booked'); ?>
                            </p>
                            <?php if ($ride): ?>
                            <div class="ride-actions">
                                <button class="btn-sm" onclick="viewRideDetails(<?php echo $rideItem['id']; ?>)">
                                    <i class="fas fa-info-circle"></i> <?php echo t('details', 'Details'); ?>
                                </button>
                                <button class="btn-sm btn-danger" onclick="cancelRide(<?php echo $rideItem['id']; ?>)">
                                    <i class="fas fa-times"></i> <?php echo t('cancel', 'Cancel'); ?>
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
            <h2><?php echo t('available_rides', 'Available Rides Near You'); ?></h2>
            <div>
                <button class="btn" onclick="showFilters()">
                    <i class="fas fa-filter"></i> <?php echo t('filters', 'Filters'); ?>
                </button>
                <button class="btn" onclick="refreshRides()">
                    <i class="fas fa-sync-alt"></i> <?php echo t('refresh', 'Refresh'); ?>
                </button>
            </div>
        </div>
        
        <div class="rides-list">
            <?php if (empty($availableRides)): ?>
                <div class="no-rides"><?php echo t('no_available_rides', 'No available rides in your area at the moment.'); ?></div>
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
                                <?php echo htmlspecialchars($rideItem['from_location']); ?> <?php echo t('to', 'to'); ?> <?php echo htmlspecialchars($rideItem['to_location']); ?>
                                <span class="student-badge"><?php echo t('student_discount', 'Student Discount'); ?></span>
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
                                <?php echo $availableSeats; ?> <?php echo t('seats_left', 'seats left'); ?>
                            </p>
                            <div class="ride-actions">
                                <button class="btn-sm" onclick="viewRideDetails(<?php echo $rideItem['id']; ?>)">
                                    <i class="fas fa-info-circle"></i> <?php echo t('details', 'Details'); ?>
                                </button>
                                <button class="btn-sm btn-success" onclick="bookRide(<?php echo $rideItem['id']; ?>)">
                                    <i class="fas fa-check"></i> <?php echo t('book_now', 'Book Now'); ?>
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
    if (confirm("<?php echo t('book_ride_confirm', 'Book this ride? 50% payment will be required to confirm.'); ?>")) {
        window.location.href = `book_ride.php?id=${rideId}`;
    }
}

function cancelRide(bookingId) {
    if (confirm("<?php echo t('cancel_ride_confirm', 'Are you sure you want to cancel this ride?'); ?>")) {
        window.location.href = `cancel_ride.php?id=${bookingId}`;
    }
}

function refreshRides() {
    window.location.reload();
}

function showFilters() {
    // Implement filter display logic
    alert("<?php echo t('filters_coming_soon', 'Filter functionality coming soon!'); ?>");
}
</script>
</body>
</html>