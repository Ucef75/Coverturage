<?php
require_once '../classes/db.php'; // Database connection

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found");
}

// Initialize variables
$errors = [];
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update profile information
        $name = trim($_POST['name']);
        $bio = trim($_POST['bio']);
        $region = trim($_POST['region']);
        
        // Validate inputs
        if (empty($name)) {
            $errors[] = "Name is required";
        }
        
        if (empty($errors)) {
            // Handle profile picture upload
            $picture = $user['picture']; // Keep existing picture by default
            
            if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/profile_pics/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileExt = strtolower(pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION));
                $fileName = uniqid('profile_') . '.' . $fileExt;
                $targetPath = $uploadDir . $fileName;
                
                // Validate image
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                $fileType = strtolower($fileExt);
                
                if (in_array($fileType, $allowedTypes)) {
                    // Create image resource to validate and potentially resize
                    try {
                        if ($fileType === 'jpeg' || $fileType === 'jpg') {
                            $image = imagecreatefromjpeg($_FILES['picture']['tmp_name']);
                        } elseif ($fileType === 'png') {
                            $image = imagecreatefrompng($_FILES['picture']['tmp_name']);
                        } elseif ($fileType === 'gif') {
                            $image = imagecreatefromgif($_FILES['picture']['tmp_name']);
                        }

                        // Resize to 500x500 max while maintaining aspect ratio
                        $width = imagesx($image);
                        $height = imagesy($image);
                        $maxSize = 500;
                        
                        if ($width > $maxSize || $height > $maxSize) {
                            $ratio = $width / $height;
                            if ($ratio > 1) {
                                $newWidth = $maxSize;
                                $newHeight = $maxSize / $ratio;
                            } else {
                                $newWidth = $maxSize * $ratio;
                                $newHeight = $maxSize;
                            }
                            
                            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                            imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                            
                            // Save resized image
                            if ($fileType === 'jpeg' || $fileType === 'jpg') {
                                imagejpeg($resizedImage, $targetPath, 90);
                            } elseif ($fileType === 'png') {
                                imagepng($resizedImage, $targetPath, 9);
                            } elseif ($fileType === 'gif') {
                                imagegif($resizedImage, $targetPath);
                            }
                            
                            imagedestroy($resizedImage);
                        } else {
                            // Save original if no resizing needed
                            move_uploaded_file($_FILES['picture']['tmp_name'], $targetPath);
                        }
                        
                        imagedestroy($image);
                        
                        // Delete old picture if it's not the default
                        if ($user['picture'] && $user['picture'] !== 'default.jpg') {
                            @unlink($uploadDir . $user['picture']);
                        }
                        $picture = $fileName;
                    } catch (Exception $e) {
                        $errors[] = "Image processing failed: " . $e->getMessage();
                    }
                } else {
                    $errors[] = "Invalid file type. Only JPG, PNG, and GIF are allowed";
                }
            }
            
            if (empty($errors)) {
                // Update database
                $stmt = $pdo->prepare("UPDATE users SET name = ?, bio = ?, region = ?, picture = ? WHERE id = ?");
                if ($stmt->execute([$name, $bio, $region, $picture, $user_id])) {
                    $success = "Profile updated successfully!";
                    // Refresh user data
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $errors[] = "Failed to update profile";
                }
            }
        }
    } elseif (isset($_POST['change_password'])) {
        // Change password
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate inputs
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $errors[] = "All password fields are required";
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        } elseif (strlen($new_password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        } elseif (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
            $errors[] = "Password must contain at least one uppercase letter, one lowercase letter, and one number";
        }
        
        if (empty($errors)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashed_password, $user_id])) {
                $success = "Password changed successfully!";
            } else {
                $errors[] = "Failed to change password";
            }
        }
    } elseif (isset($_POST['delete_account'])) {
        // Delete account
        $confirm = $_POST['confirm_delete'] ?? false;
        
        if ($confirm) {
            // Delete user's profile picture if it's not the default
            if ($user['picture'] && $user['picture'] !== 'default.jpg') {
                @unlink('../uploads/profile_pics/' . $user['picture']);
            }
            
            // Delete user from database
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$user_id])) {
                // Logout and redirect
                session_destroy();
                header('Location: ../index.php?account_deleted=1');
                exit();
            } else {
                $errors[] = "Failed to delete account";
            }
        } else {
            $errors[] = "You must confirm account deletion";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --success: #2ecc71;
            --text: #333;
            --text-light: #7f8c8d;
            --sidebar-width: 250px;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light);
            color: var(--text);
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1;
            padding: 30px;
            margin-left: var(--sidebar-width);
        }
        
        .settings-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .settings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .settings-header h1 {
            color: var(--primary);
            font-size: 28px;
            font-weight: 600;
        }
        
        .settings-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
        }
        
        @media (min-width: 992px) {
            .settings-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        .settings-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            padding: 25px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .settings-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }
        
        .settings-card h2 {
            color: var(--primary);
            margin-bottom: 20px;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .settings-card h2 i {
            color: var(--secondary);
        }
        
        .profile-picture-container {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            gap: 20px;
        }
        
        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--secondary);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .picture-upload {
            flex: 1;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--secondary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            background-color: var(--secondary);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn-danger {
            background-color: var(--accent);
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .btn-success {
            background-color: var(--success);
        }
        
        .btn-success:hover {
            background-color: #27ae60;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--secondary);
            color: var(--secondary);
        }
        
        .btn-outline:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert i {
            font-size: 18px;
        }
        
        .delete-confirm {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            gap: 10px;
        }
        
        .delete-confirm input {
            width: 18px;
            height: 18px;
        }
        
        .password-toggle {
            position: relative;
        }
        
        .password-toggle i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-light);
        }
        
        .section-divider {
            border: none;
            height: 1px;
            background-color: rgba(0, 0, 0, 0.1);
            margin: 25px 0;
        }
        
        .danger-zone {
            border-left: 4px solid var(--accent);
            padding-left: 15px;
            margin-top: 30px;
        }
        
        .danger-zone h3 {
            color: var(--accent);
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="settings-container">
            <div class="settings-header">
                <h1><i class="fas fa-cog"></i> Account Settings</h1>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div><?php echo htmlspecialchars($success); ?></div>
                </div>
            <?php endif; ?>
            
            <div class="settings-grid">
                <!-- Profile Information Section -->
                <div class="settings-card">
                    <h2><i class="fas fa-user"></i> Profile Information</h2>
                    
                    <form action="settings.php" method="POST" enctype="multipart/form-data">
                        <div class="profile-picture-container">
                            <img src="../uploads/profile_pics/<?php echo htmlspecialchars($user['picture'] ?? 'default.jpg'); ?>" 
                                 alt="Profile Picture" 
                                 class="profile-picture"
                                 id="profile-picture-preview">
                            <div class="picture-upload">
                                <label for="picture" class="btn btn-outline">
                                    <i class="fas fa-camera"></i> Change Photo
                                </label>
                                <input type="file" id="picture" name="picture" accept="image/*" style="display: none;">
                                <p class="text-muted" style="margin-top: 10px; font-size: 13px; color: var(--text-light);">
                                    JPG, PNG or GIF (Max 5MB)
                                </p>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="bio">About Me</label>
                            <textarea id="bio" name="bio" class="form-control" 
                                      placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="region">Location</label>
                            <input type="text" id="region" name="region" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['region'] ?? ''); ?>" 
                                   placeholder="Your country or city">
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-success">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>
                
                <!-- Security Section -->
                <div class="settings-card">
                    <h2><i class="fas fa-lock"></i> Security</h2>
                    
                    <form action="settings.php" method="POST">
                        <div class="form-group password-toggle">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                            <i class="fas fa-eye" id="toggle-current-password"></i>
                        </div>
                        
                        <div class="form-group password-toggle">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                            <i class="fas fa-eye" id="toggle-new-password"></i>
                        </div>
                        
                        <div class="form-group password-toggle">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            <i class="fas fa-eye" id="toggle-confirm-password"></i>
                        </div>
                        
                        <div class="password-requirements" style="margin-bottom: 20px; font-size: 13px; color: var(--text-light);">
                            <p>Password must contain:</p>
                            <ul style="padding-left: 20px;">
                                <li>At least 8 characters</li>
                                <li>One uppercase letter</li>
                                <li>One lowercase letter</li>
                                <li>One number</li>
                            </ul>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                    
                    <hr class="section-divider">
                    
                    <div class="danger-zone">
                        <h3><i class="fas fa-exclamation-triangle"></i> Danger Zone</h3>
                        <p style="margin-bottom: 15px; color: var(--text-light);">
                            Once you delete your account, there is no going back. Please be certain.
                        </p>
                        
                        <form action="settings.php" method="POST" id="delete-account-form">
                            <div class="delete-confirm">
                                <input type="checkbox" id="confirm_delete" name="confirm_delete" required>
                                <label for="confirm_delete">I understand that all my data will be permanently deleted</label>
                            </div>
                            
                            <button type="submit" name="delete_account" class="btn btn-danger" id="delete-account-btn">
                                <i class="fas fa-trash-alt"></i> Delete My Account
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Profile picture preview
        document.getElementById('picture').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profile-picture-preview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Password toggle functionality
        function setupPasswordToggle(inputId, toggleId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(toggleId);
            
            toggleIcon.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                }
            });
        }
        
        setupPasswordToggle('current_password', 'toggle-current-password');
        setupPasswordToggle('new_password', 'toggle-new-password');
        setupPasswordToggle('confirm_password', 'toggle-confirm-password');
        
        // Delete account confirmation
        document.getElementById('delete-account-btn').addEventListener('click', function(e) {
            if (!document.getElementById('confirm_delete').checked) {
                e.preventDefault();
                alert('Please confirm you understand this action cannot be undone');
            } else if (!confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>