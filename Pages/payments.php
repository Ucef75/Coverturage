<?php
require_once '../server/session.php';
require_once '../include/sidebar.php';
require_once '../classes/database.php';
require_once '../classes/payments.php';
require_once '../classes/rides.php';

// Initialize database connection
$db = new Database();
$pdo = $db->getConnection();

// Create instances
$payments = new Payments($pdo);
$rides = new Ride($pdo); // Assuming you have a Rides class

// Get user data
$paymentHistory = [];
$balance = 0.00;
$currency = 'TND'; // Default currency
$isDriver = false;

if (isLoggedIn() && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $isDriver = $_SESSION['user_data']['is_driver'] ?? false;
    
    // Get all financial transactions
    $paymentHistory = $payments->getPaymentHistory($userId);
    
    // Get ride earnings if driver
    if ($isDriver) {
        $rideEarnings = $rides->getDriverEarnings($userId);
        $paymentHistory = array_merge($paymentHistory, $rideEarnings);
    }
    
    // Sort all transactions by date
    usort($paymentHistory, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    $balance = $payments->getUserBalance($userId);
    
    // Set currency based on user's region
    if (isset($_SESSION['user_data']['region'])) {
        $region = $_SESSION['user_data']['region'];
        $currency = match($region) {
            'DZ' => 'DZD', // Algerian Dinar
            'MA' => 'MAD', // Moroccan Dirham
            'LY' => 'LYD', // Libyan Dinar
            'EG' => 'EGP', // Egyptian Pound
            'MR' => 'MRU', // Mauritanian Ouguiya
            default => 'TND' // Tunisian Dinar (default)
        };
    }
}

// Handle add funds request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_funds'])) {
    $amount = (float)$_POST['amount'];
    if ($amount > 0) {
        $success = $payments->addPayment($userId, $amount, 'Funds added');
        if ($success) {
            header("Location: payments.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($selectedLang) ?>" dir="<?= $languages[$selectedLang]['dir'] ?? 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('payments.title', 'Payments')) ?> - Coverturage</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/payments.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php if (($languages[$selectedLang]['dir'] ?? 'ltr') === 'rtl'): ?>
    <link rel="stylesheet" href="../css/rtl.css">
    <?php endif; ?>
</head>
<body>
    <?php include '../include/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <h1 class="page-title"><?= htmlspecialchars(t('payments.title', 'Payments')) ?></h1>
            
            <!-- Balance Card -->
            <div class="balance-card">
                <div class="balance-header">
                    <i class="fas fa-wallet"></i>
                    <h2><?= htmlspecialchars(t('payments.your_balance', 'Your Balance')) ?></h2>
                </div>
                <div class="balance-amount">
                    <span class="amount"><?= number_format($balance, 2) ?></span>
                    <span class="currency"><?= htmlspecialchars($currency) ?></span>
                </div>
                
                <!-- Add Funds Form -->
                <form method="POST" class="add-funds-form">
                    <div class="form-group">
                        <label for="amount"><?= htmlspecialchars(t('payments.amount', 'Amount')) ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><?= htmlspecialchars($currency) ?></span>
                            <input type="number" id="amount" name="amount" min="1" step="0.01" required 
                                   placeholder="<?= htmlspecialchars(t('payments.amount_placeholder', 'Enter amount')) ?>">
                        </div>
                    </div>
                    <button type="submit" name="add_funds" class="btn btn-primary">
                        <i class="fas fa-plus"></i> <?= htmlspecialchars(t('payments.add_funds', 'Add Funds')) ?>
                    </button>
                </form>
            </div>
            
            <!-- Transaction History -->
            <div class="payment-history">
                <h2><?= htmlspecialchars(t('payments.transaction_history', 'Transaction History')) ?></h2>
                
                <?php if (empty($paymentHistory)): ?>
                    <div class="empty-state">
                        <i class="fas fa-exchange-alt"></i>
                        <p><?= htmlspecialchars(t('payments.no_transactions', 'No transactions yet')) ?></p>
                    </div>
                <?php else: ?>
                    <div class="transactions-list">
                        <?php foreach ($paymentHistory as $transaction): ?>
                            <div class="transaction-item <?= $transaction['amount'] > 0 ? 'incoming' : 'outgoing' ?>">
                                <div class="transaction-icon">
                                    <?php if ($transaction['type'] === 'ride'): ?>
                                        <i class="fas fa-car"></i>
                                    <?php elseif ($transaction['amount'] > 0): ?>
                                        <i class="fas fa-plus-circle"></i>
                                    <?php else: ?>
                                        <i class="fas fa-minus-circle"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="transaction-details">
                                    <h3><?= htmlspecialchars($transaction['description']) ?></h3>
                                    <p class="transaction-date">
                                        <?= date('d M Y, H:i', strtotime($transaction['created_at'])) ?>
                                        <?php if ($transaction['type'] === 'ride'): ?>
                                            <span class="ride-badge">Ride</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="transaction-amount">
                                    <?= number_format($transaction['amount'], 2) ?> <?= htmlspecialchars($currency) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="../js/payments.js"></script>
</body>
</html>