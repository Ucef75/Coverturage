<?php
session_start();
require_once '../classes/database.php'; 
$db = new Database();
$pdo = $db->getConnection(); // <-- get the PDO connection correctly

// Load language
$lang = [];
$selectedLang = $_SESSION['lang'] ?? 'en';
$langFile = "../lang/{$selectedLang}.php";
$lang = file_exists($langFile) ? include $langFile : include "../lang/en.php";

// Handle login
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = $lang['auth']['valid_email_required'] ?? 'Valid email is required';
    }
    if (empty($password)) {
        $errors[] = $lang['auth']['password_required'] ?? 'Password is required';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: interface.php');
                exit();
            } else {
                $errors[] = $lang['auth']['incorrect_password'] ?? 'Incorrect password';
            }
        } else {
            $errors[] = $lang['auth']['email_not_found'] ?? 'Email not found';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($selectedLang); ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($lang['auth']['login_title'] ?? 'Login'); ?></title>
    <link rel="stylesheet" href="../Css/login.css">
    <link rel="stylesheet" href="../Css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="login-container">

    <div class="back-inside">
        <a href="../index.php" class="back-link">←</a>
    </div>

    <h1><?php echo htmlspecialchars($lang['auth']['login_heading'] ?? 'Welcome Back'); ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="email" name="email" placeholder="<?php echo htmlspecialchars($lang['auth']['email'] ?? 'Email'); ?>" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        
        <input type="password" name="password" placeholder="<?php echo htmlspecialchars($lang['auth']['password'] ?? 'Password'); ?>" required>

        <button type="submit" class="btn"><?php echo htmlspecialchars($lang['auth']['login_button'] ?? 'Login'); ?></button>

        <p class="signup-link">
            <?php echo htmlspecialchars($lang['auth']['no_account'] ?? "Don't have an account?"); ?>
            <a href="signup.php"><?php echo htmlspecialchars($lang['auth']['signup_here'] ?? 'Sign up here'); ?></a>
        </p>
    </form>
</div>

</body>
</html>
