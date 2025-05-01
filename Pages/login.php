<?php
require_once '../server/session.php';
require_once '../classes/database.php';
require_once '../config.php';
require_once '../server/language.php';
require_once '../classes/users.php';

$db = new Database();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = t('auth.valid_email_required', 'Valid email is required');
    }
    if (empty($password)) {
        $errors[] = t('auth.password_required', 'Password is required');
    }

    if (empty($errors)) {
        try {
            // First get just the ID and password for verification
            $stmt = $db->query("SELECT id, password, username, email, is_driver, region FROM users WHERE email = ?", [$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                loginUser([
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'is_driver' => (bool)$user['is_driver'],
                    'region' => $user['region']
                ]);
                
                header("Location: interface.php");
                exit();
            }
            
            $errors[] = t('auth.incorrect_credentials', 'Invalid email or password');
            
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $errors[] = t('auth.login_error', 'Login failed. Please try again.');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($selectedLang) ?>" dir="<?= $languages[$selectedLang]['dir'] ?? 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('auth.login_title', 'Login')) ?></title>
    <link rel="stylesheet" href="../Css/login.css">
    <link rel="stylesheet" href="../Css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php if (($languages[$selectedLang]['dir'] ?? 'ltr') === 'rtl'): ?>
    <link rel="stylesheet" href="../Css/rtl.css">
    <?php endif; ?>
</head>
<body>

<div class="login-container">
    <div class="back-inside">
        <a href="../index.php" class="back-link">←</a>
    </div>

    <h1><?= htmlspecialchars(t('auth.login_heading', 'Welcome Back')) ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">        
        <input type="email" name="email" placeholder="<?= htmlspecialchars(t('auth.email', 'Email')) ?>" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        
        <input type="password" name="password" placeholder="<?= htmlspecialchars(t('auth.password', 'Password')) ?>" required>

        <button type="submit" class="btn"><?= htmlspecialchars(t('auth.login_button', 'Login')) ?></button>

        <p class="signup-link">
            <?= htmlspecialchars(t('auth.no_account', "Don't have an account?")) ?>
            <a href="signup.php"><?= htmlspecialchars(t('auth.signup_here', 'Sign up here')) ?></a>
        </p>
    </form>
</div>

</body>
</html>