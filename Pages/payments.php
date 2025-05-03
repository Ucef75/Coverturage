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
    $db = new Database();
    $pdo = $db->getConnection();    
    $user = new User($pdo);
    $rides = new Ride($pdo);
    $payments = new Payments($pdo);
    
    $paymentHistory = [];
    $balance = 0.00;
    $currency = 'TND';
    $isDriver = false;
    
    if (isLoggedIn() && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];        
        if ($user->load($userId)) {
            $isDriver = $user->isDriver();
            
            $balance = $payments->getUserBalance($userId);
            
            $paymentHistory = $payments->getPaymentHistory($userId);
            
            if ($isDriver) {
                $rideEarnings = $rides->getDriverEarnings($userId);
                $paymentHistory = array_merge($paymentHistory, $rideEarnings);
            }
            
            usort($paymentHistory, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
            
            $region = $user->getRegion();
            $currency = match($region) {
                'DZ' => 'DZD', 'MA' => 'MAD', 'LY' => 'LYD',
                'EG' => 'EGP', 'MR' => 'MRU', default => 'TND'
            };
        } else {
        }
    } else {
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_funds'])) {
        try {
            $amount = (float)($_POST['amount'] ?? 0);
            
            // Enhanced validation
            if ($amount <= 0) {
                throw new InvalidArgumentException("Amount must be greater than 0");
            }
            
            if ($payments->addPayment($userId, $amount, "Manual top-up")) {
                $_SESSION['payment_message'] = [
                    'success' => true, 
                    'key' => 'add_success', 
                    'default' => 'Funds added successfully'
                ];
                header("Location: payments.php");
                exit();
            } else {
                throw new RuntimeException("Payment processing failed");
            }
            
        } catch (InvalidArgumentException $e) {
            $_SESSION['payment_message'] = [
                'success' => false,
                'key' => 'add_error',
                'default' => $e->getMessage(),
                'debug' => 'Validation error'
            ];
        } catch (PDOException $e) {
            $_SESSION['payment_message'] = [
                'success' => false,
                'key' => 'add_error',
                'default' => 'Database error occurred',
                'debug' => $e->getMessage()
            ];
        } catch (Exception $e) {
            $_SESSION['payment_message'] = [
                'success' => false,
                'key' => 'add_error',
                'default' => 'Error processing payment',
                'debug' => $e->getMessage()
            ];
        }
        
        error_log("Payment Form Error: " . $_SESSION['payment_message']['debug']);
        // header("Location: payments.php");
        // exit();
    }

} catch (Exception $e) {
    error_log("Main script error: " . $e->getMessage());
    $_SESSION['payment_message'] = [
        'success' => false,
        'key' => 'system_error',
        'default' => 'A system error occurred'
    ];
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($selectedLang) ?>" dir="<?= $languages[$selectedLang]['dir'] ?? 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('payments.title', 'Payments')) ?> - Coverturage</title>
    <link rel="stylesheet" href="../css/payments.css">
    <?php if (($languages[$selectedLang]['dir'] ?? 'ltr') === 'rtl'): ?>
    <?php endif; ?>
</head>
<body>
    <?php include '../include/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <h1 class="page-title"><?= htmlspecialchars(t('payments.title', 'Payments')) ?></h1>
            
            <!-- Message display container (will be updated via JavaScript) -->
            <div id="payment-message" class="alert" style="display: none;"></div>
            
            <!-- Balance Card -->
            <div class="balance-card">
                <div class="balance-header">
                    <i class="fas fa-wallet"></i>
                    <h2><?= htmlspecialchars(t('payments.your_balance', 'Your Balance')) ?></h2>
                </div>
                <div class="balance-amount">
                    <span class="amount" id="current-balance"><?= number_format($balance, 2) ?></span>
                    <span class="currency"><?= htmlspecialchars($currency) ?></span>
                </div>
                
                <!-- Add Funds Form -->
                <form method="POST" class="add-funds-form" id="add-funds-form" action="javascript:void(0);">
                    <div class="form-group">
                        <label for="amount"><?= htmlspecialchars(t('payments.amount', 'Amount')) ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><?= htmlspecialchars($currency) ?></span>
                            <input type="number" id="amount" name="amount" min="1" step="0.01" required 
                                   placeholder="<?= htmlspecialchars(t('payments.amount_placeholder', 'Enter amount')) ?>">
                        </div>
                        <div id="amount-error" class="error-message" style="color: red; display: none;"></div>
                    </div>
                    <button type="submit" name="add_funds" class="btn btn-primary" id="add-funds-btn">
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
                    <div class="transactions-list" id="transactions-list">
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
                                    <h3><?= htmlspecialchars($transaction['description'] ?? 'Transaction') ?></h3>
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