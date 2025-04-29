<?php
require_once '../server/session.php';

// 1. Include database connection
require_once '../classes/database.php';
$db = new Database();
$pdo = $db->getConnection();

// 2. Handle login
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
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                // Set all user session data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['is_driver'] = (bool)$user['is_driver'];
                $_SESSION['is_student'] = (bool)$user['is_student'];
                $_SESSION['region'] = $user['region'];
                
                // Redirect to interface (no need for lang/country in URL - they're in session)
                header("Location: interface.php");
                exit();
            } else {
                $errors[] = t('auth.incorrect_password', 'Incorrect password');
            }
        } else {
            $errors[] = t('auth.email_not_found', 'Email not found');
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