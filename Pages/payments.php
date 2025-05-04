<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../server/session.php';
require_once '../include/sidebar.php';
require_once '../classes/database.php';
require_once '../classes/users.php';
require_once '../classes/rides.php';
require_once '../classes/payments.php';
require_once '../server/language.php';

try {
    // Initialize database connection
    $db = new Database();
    $pdo = $db->getConnection();

    // Initialize classes
    $user = new User($pdo);
    $rides = new Ride($pdo);
    $payments = new Payments($pdo);

    // Default values
    $paymentHistory = [];
    $balance = 0.00;
    $currency = 'TND';
    $isDriver = false;

    // Check if the user is logged in
    if (isLoggedIn() && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];

        // Load the user's data
        if ($user->load($userId)) {
            $isDriver = $user->isDriver();
            $balance = $user->getBalance();
            $paymentHistory = $payments->getPaymentHistory($userId);

            // Set the region-based currency
            $region = $user->getRegion();
            $currency = match($region) {
                'DZ' => 'DZD',
                'MA' => 'MAD',
                'LY' => 'LYD',
                'EG' => 'EGP',
                'MR' => 'MRU',
                default => 'TND'
            };
        }
    }

    // Handle form submission for adding funds
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_funds'])) {
        $amount = (float)($_POST['amount'] ?? 0);
    
        if ($amount <= 0) {
            $_SESSION['payment_message'] = 'Amount must be greater than 0';
        } else {
            try {
                if ($user->addBalance($amount)) {
                    $_SESSION['payment_message'] = 'Funds added successfully';
                    // Force reload the user data
                    $user->load($_SESSION['user_id']);
                    $balance = $user->getBalance();
                } else {
                    $_SESSION['payment_message'] = 'Failed to add funds. Please try again.';
                    error_log("Balance addition failed for user: " . $_SESSION['user_id']);
                }
            } catch (Exception $e) {
                $_SESSION['payment_message'] = 'A system error occurred';
                error_log("Payment error: " . $e->getMessage());
            }
        }
        
        // Always redirect after POST
        header("Location: payments.php");
        exit();
    }
} catch (Exception $e) {
    // Log errors and display a generic message
    error_log("Payment error: " . $e->getMessage());
    $_SESSION['payment_message'] = 'A system error occurred';
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($selectedLang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Coverturage</title>
    <link rel="stylesheet" href="../css/payments.css">
</head>
<body>
    <?php include '../include/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <h1 class="page-title">Payments</h1>
            
            <?php if (isset($_SESSION['payment_message'])): ?>
                <div class="alert"><?= htmlspecialchars($_SESSION['payment_message']) ?></div>
                <?php unset($_SESSION['payment_message']); ?>
            <?php endif; ?>
            
            <div class="balance-card">
                <div class="balance-header">
                    <h2>Your Balance</h2>
                </div>
                <div class="balance-amount">
                    <span class="amount"><?= number_format($balance, 2) ?></span>
                    <span class="currency"><?= htmlspecialchars($currency) ?></span>
                </div>
                
                <form method="POST" class="add-funds-form">
                    <div class="form-group">
                        <label for="amount">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text"><?= htmlspecialchars($currency) ?></span>
                            <input type="number" id="amount" name="amount" min="0.01" step="0.01" required>
                        </div>
                    </div>
                    <button type="submit" name="add_funds" class="btn btn-primary">
                        Add Funds
                    </button>
                </form>
            </div>
            
            <div class="payment-history">
                <h2>Transaction History</h2>
                
                <?php if (empty($paymentHistory)): ?>
                    <p>No transactions yet</p>
                <?php else: ?>
                    <div class="transactions-list">
                        <?php foreach ($paymentHistory as $transaction): ?>
                            <div class="transaction-item <?= $transaction['amount'] > 0 ? 'incoming' : 'outgoing' ?>">
                                <div class="transaction-details">
                                    <h3><?= htmlspecialchars($transaction['description'] ?? 'Transaction') ?></h3>
                                    <p class="transaction-date">
                                        <?= date('d M Y, H:i', strtotime($transaction['created_at'])) ?>
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
</body>
</html>