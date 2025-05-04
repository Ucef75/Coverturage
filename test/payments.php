<?php
// test_payments.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once '../classes/database.php';
require_once '../classes/users.php';

// Fixed DebugUser class with proper inheritance
class DebugUser extends User {
    public function __construct(PDO $db) {
        parent::__construct($db); // Initialize parent class properly
    }

    public function addBalance(float $amount): bool {
        echo "<div style='border:1px solid #ccc; padding:10px; margin:10px 0;'>";
        echo "<h4>Debugging addBalance()</h4>";
        echo "<p>Using database connection: " . get_class($this->db) . "</p>";
        
        try {
            echo "<p>Starting transaction...</p>";
            $this->db->beginTransaction();
            
            // Check user exists
            $stmt = $this->db->prepare("SELECT id, \"Balance\" FROM users WHERE id = ?");
            $stmt->execute([$this->id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                echo "<p style='color:red'>ERROR: User not found</p>";
                $this->db->rollBack();
                return false;
            }
            
            echo "<p>Current balance: {$user['Balance']}</p>";
            $newBalance = $user['Balance'] + $amount;
            
            // Update balance
            $update = $this->db->prepare("UPDATE users SET \"Balance\" = ? WHERE id = ?");
            $success = $update->execute([$newBalance, $this->id]);
            $rowsAffected = $update->rowCount();
            
            if ($success && $rowsAffected > 0) {
                $this->db->commit();
                echo "<p style='color:green'>SUCCESS: Updated balance to $newBalance ($rowsAffected row affected)</p>";
                return true;
            } else {
                echo "<p style='color:red'>ERROR: Update failed (Rows affected: $rowsAffected)</p>";
                $this->db->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            echo "<p style='color:red'>EXCEPTION: " . htmlspecialchars($e->getMessage()) . "</p>";
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
        echo "</div>";
    }
}

// Test database connection
try {
    $db = new Database();
    $pdo = $db->getConnection();
    echo "<p style='color:green'>✓ Database connected successfully</p>";
} catch (PDOException $e) {
    die("<p style='color:red'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>");
}

// Test user
$user = new DebugUser($pdo);
$userId = 1; // CHANGE TO YOUR ACTUAL USER ID
if (!$user->load($userId)) {
    die("<p style='color:red'>User $userId not found in database</p>");
}
echo "<p>Loaded user: <strong>{$user->getUsername()}</strong> (ID: $userId, Balance: {$user->getBalance()})</p>";

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<hr><h2>Transaction Results</h2>";
    $amount = (float)($_POST['amount'] ?? 0);
    
    if ($amount <= 0) {
        echo "<p style='color:red'>❌ Invalid amount: must be greater than 0</p>";
    } else {
        echo "<p>Attempting to add <strong>$amount</strong> to balance...</p>";
        if ($user->addBalance($amount)) {
            // Reload user to verify update
            $user->load($userId);
            echo "<p style='color:green'>✓ Final verified balance: {$user->getBalance()}</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment System Debugger</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .debug { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        form { margin: 20px 0; padding: 20px; background: #eef; border-radius: 5px; }
        pre { background: #333; color: #fff; padding: 10px; border-radius: 3px; }
        table { border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h1>Payment System Debugger</h1>
    
    <div class="debug">
        <h3>Database Structure Verification</h3>
        <?php
        try {
            $stmt = $pdo->query("PRAGMA table_info(users)");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h4>Users Table Columns</h4>";
            echo "<table>";
            echo "<tr><th>Column</th><th>Type</th><th>Nullable</th></tr>";
            foreach ($columns as $col) {
                $nullable = $col['notnull'] ? 'NO' : 'YES';
                echo "<tr><td>{$col['name']}</td><td>{$col['type']}</td><td>$nullable</td></tr>";
            }
            echo "</table>";
        } catch (PDOException $e) {
            echo "<p style='color:red'>Error reading table info: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>
    </div>
    
    <form method="POST">
        <h3>Test Payment Form</h3>
        <p>
            <label>Test Amount:
                <input type="number" name="amount" min="0.01" step="0.01" value="10.00" required>
            </label>
        </p>
        <button type="submit">Process Test Payment</button>
        <p><small>Try different values (positive numbers only)</small></p>
    </form>
    
    <div class="debug">
        <h3>Current User Data</h3>
        <pre><?= htmlspecialchars(print_r($user->toArray(), true)) ?></pre>
    </div>
</body>
</html>