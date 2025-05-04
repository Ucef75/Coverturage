<?php
require_once '../server/session.php';
require_once '../classes/users.php';

if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$db = getDB();
$currentUser = getCurrentUser();

if (!$currentUser) {
    // Try to reload user data if session exists but user object failed
    if (isset($_SESSION['user_data'])) {
        $currentUser = new User($db);
        if ($currentUser->load($_SESSION['user_id'])) {
            // Successfully reloaded user
        } else {
            // If still failing, create a basic user object with session data
            $currentUser = new User($db);
            $currentUser->setId($_SESSION['user_id']);
            $currentUser->setUsername($_SESSION['user_data']['username'] ?? '');
            $currentUser->setEmail($_SESSION['user_data']['email'] ?? '');
            $currentUser->setIsDriver($_SESSION['user_data']['is_driver'] ?? false);
            $currentUser->setRegion($_SESSION['user_data']['region'] ?? '');
        }
    } else {
        die("User session invalid - please login again");
    }
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name'] ?? '');
        $region = trim($_POST['region'] ?? '');
        
        if (empty($name)) {
            $errors[] = "Name is required";
        }
        
        if (empty($errors)) {
            if ($currentUser->updateProfile($name, $region)) {
                $success = "Profile updated successfully!";
                refreshUserInSession($currentUser);
            } else {
                $errors[] = "Failed to update profile";
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate inputs
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $errors[] = "All password fields are required";
        } elseif (!$currentUser->verifyPassword($current_password)) {
            $errors[] = "Current password is incorrect";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        } elseif (strlen($new_password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        } elseif (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
            $errors[] = "Password must contain at least one uppercase letter, one lowercase letter, and one number";
        }
        
        if (empty($errors)) {
            if ($currentUser->changePassword($current_password, $new_password)) {
                $success = "Password changed successfully!";
                refreshUserInSession($currentUser);
            } else {
                $errors[] = "Failed to change password";
            }
        }
    } elseif (isset($_POST['delete_account'])) {
        $confirm = $_POST['confirm_delete'] ?? false;
        
        if ($confirm) {
            if ($currentUser->deleteAccount()) {
                // Logout and redirect
                logoutUser();
                header('Location: ../index.php?account_deleted=1');
                exit();
            } else {
                $errors[] = "Failed to delete account";
            }
        } else {
            $errors[] = "You must confirm account deletion";
        }
    } } elseif (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif'
        ];
        
        $fileType = $_FILES['profile_picture']['type'];
        $fileSize = $_FILES['profile_picture']['size'];
        
        if (!isset($allowed[$fileType])) {
            $errors[] = "Invalid file type";
        } elseif ($fileSize > 2 * 1024 * 1024) {
            $errors[] = "File too large (max 2MB)";
        } else {
            $uploadDir = '../src/';
            $filename = 'profile_' . $currentUser->getId() . '.' . $allowed[$fileType];
            $targetPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath)) {
                // Verify file was actually written
                if (!file_exists($targetPath)) {
                    $errors[] = "File upload failed";
                } else {
                    $currentUser->setProfilePicture($filename);
                    if (!$currentUser->saveProfilePicture()) {
                        $errors[] = "Database update failed";
                        unlink($targetPath); // Clean up
                    } else {
                        $success = "Profile picture updated!";
                        refreshUserInSession($currentUser);
                    }
                }
            } else {
                $errors[] = "Error moving uploaded file";
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
    <link rel="stylesheet" href="../css/settings.css">
</head>
<body>
    <?php require_once '../include/sidebar.php'; ?>
    
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
                    
                    <form action="settings.php" method="POST" enctype="multipart/form-data">                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($currentUser->getUsername()); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="region">Location</label>
                            <input type="text" id="region" name="region" class="form-control" 
                                   value="<?php echo htmlspecialchars($currentUser->getRegion()); ?>" 
                                   placeholder="Your country or city">
                        </div>
                        <div class="form-group">
    <label for="profile_picture">Profile Picture</label>
    <div class="form-group">
        <img src="<?= htmlspecialchars($currentUser->getProfilePicture() ?? '../src/default.jpg') ?>" 
             alt="Profile Picture" id="profile-preview" class="profile-pic">
        <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="profile-picture-input">
    </div>
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
                        
                        <div class="password-requirements">
                            <p>Password must contain:</p>
                            <ul>
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
                        <p>
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