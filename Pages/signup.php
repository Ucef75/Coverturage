<?php
session_start();
require_once '../classes/database.php';
$db = new Database();

// Load language
$lang = [];
$selectedLang = $_SESSION['lang'] ?? 'en';
$langFile = "../lang/{$selectedLang}.php";
$lang = file_exists($langFile) ? include $langFile : include "../lang/en.php";

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $region = trim($_POST['region'] ?? 'TN'); // ✅ Get region from POST, default Tunisia

    // Validation
    if (empty($username)) {
        $errors[] = $lang['auth']['username_required'] ?? 'Username is required';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = $lang['auth']['valid_email_required'] ?? 'Valid email is required';
    }
    if (empty($password)) {
        $errors[] = $lang['auth']['password_required'] ?? 'Password is required';
    }
    if ($password !== $confirm_password) {
        $errors[] = $lang['auth']['passwords_must_match'] ?? 'Passwords must match';
    }

    if (empty($errors)) {
        // Check if email or username already exists
        $checkSql = "SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1";
        $checkResult = $db->query($checkSql, [$email, $username]);

        if ($checkResult && ($existingUser = $checkResult->fetch(PDO::FETCH_ASSOC))) {
            $errors[] = $lang['auth']['email_exists'] ?? 'Email or Username already exists';
        } else {
            // Insert the new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // ✅ Insert username, email, password, bio, and region
            $insertSql = "INSERT INTO users (username, email, password, bio, region) VALUES (?, ?, ?, ?, ?)";
            $insertResult = $db->query($insertSql, [$username, $email, $hashedPassword, ' ', $region]);

            if ($insertResult) {
                header('Location: login.php');
                exit();
            } else {
                $errors[] = $lang['auth']['registration_failed'] ?? 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($selectedLang); ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($lang['auth']['signup_title'] ?? 'Sign Up'); ?></title>
    <link rel="stylesheet" href="../Css/signup.css">
</head>
<body>

<div class="login-container">
    <a href="../index.php" class="back-btn">←</a>

    <h1><?php echo htmlspecialchars($lang['auth']['signup_heading'] ?? 'Create an Account'); ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="text" name="username" placeholder="<?php echo htmlspecialchars($lang['auth']['username'] ?? 'Username'); ?>" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">

        <input type="email" name="email" placeholder="<?php echo htmlspecialchars($lang['auth']['email'] ?? 'Email'); ?>" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">

        <input type="password" name="password" placeholder="<?php echo htmlspecialchars($lang['auth']['password'] ?? 'Password'); ?>" required>

        <input type="password" name="confirm_password" placeholder="<?php echo htmlspecialchars($lang['auth']['confirm_password'] ?? 'Confirm Password'); ?>" required>

        <!-- ✅ Hidden input for region -->
        <input type="hidden" name="region" value="<?php echo htmlspecialchars($_SESSION['country'] ?? 'TN'); ?>">

        <button type="submit" class="btn"><?php echo htmlspecialchars($lang['auth']['signup_button'] ?? 'Sign Up'); ?></button>

        <p class="signup-link">
            <?php echo htmlspecialchars($lang['auth']['already_account'] ?? 'Already have an account?'); ?>
            <a href="login.php"><?php echo htmlspecialchars($lang['auth']['login_here'] ?? 'Login here'); ?></a>
        </p>
    </form>
</div>

</body>
</html>
