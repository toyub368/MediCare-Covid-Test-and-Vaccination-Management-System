<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('admin');

$db = new Database();

// Handle profile update
if ($_POST) {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get current admin data
    $db->query("SELECT * FROM admins WHERE id = :id");
    $db->bind(':id', $_SESSION['user_id']);
    $admin = $db->single();
    
    if (empty($full_name) || empty($email)) {
        $error = 'Please fill in all required fields';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } elseif (!empty($new_password) && !verifyPassword($current_password, $admin['password'])) {
        $error = 'Current password is incorrect';
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } elseif (!empty($new_password) && strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long';
    } else {
        // Check if email is already taken by another admin
        $db->query("SELECT id FROM admins WHERE email = :email AND id != :id");
        $db->bind(':email', $email);
        $db->bind(':id', $_SESSION['user_id']);
        
        if ($db->single()) {
            $error = 'Email address is already in use';
        } else {
            // Update profile
            if (!empty($new_password)) {
                $hashed_password = hashPassword($new_password);
                $db->query("UPDATE admins SET full_name = :name, email = :email, password = :password WHERE id = :id");
                $db->bind(':password', $hashed_password);
            } else {
                $db->query("UPDATE admins SET full_name = :name, email = :email WHERE id = :id");
            }
            
            $db->bind(':name', $full_name);
            $db->bind(':email', $email);
            $db->bind(':id', $_SESSION['user_id']);
            
            if ($db->execute()) {
                $_SESSION['name'] = $full_name;
                $_SESSION['email'] = $email;
                $success = 'Profile updated successfully';
            } else {
                $error = 'Failed to update profile';
            }
        }
    }
}

// Get current admin data
$db->query("SELECT * FROM admins WHERE id = :id");
$db->bind(':id', $_SESSION['user_id']);
$admin = $db->single();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 pb-0">
                            <h4 class="mb-0">
                                <i class="fas fa-user me-2 text-primary"></i>Admin Profile
                            </h4>
                            <p class="text-muted mb-0">Manage your account settings and preferences</p>
                        </div>
                        <div class="card-body">
                            <?php if (isset($success)): ?>
                                <div class="alert alert-success alert-custom">
                                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger alert-custom">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($admin['username']); ?>" readonly>
                                        <small class="text-muted">Username cannot be changed</small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="full_name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" name="full_name" 
                                               value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" name="email" 
                                               value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="created_at" class="form-label">Member Since</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo formatDate($admin['created_at']); ?>" readonly>
                                    </div>
                                </div>
                                
                                <hr class="my-4">
                                
                                <h6 class="mb-3">Change Password</h6>
                                <p class="text-muted mb-3">Leave password fields empty if you don't want to change your password.</p>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" name="current_password">
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" name="new_password" minlength="6">
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" name="confirm_password" minlength="6">
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <a href="dashboard.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Account Statistics -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar me-2 text-primary"></i>Account Statistics
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="p-3">
                                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                        <h4><?php 
                                            $db->query("SELECT COUNT(*) as total FROM patients");
                                            echo $db->single()['total'];
                                        ?></h4>
                                        <p class="text-muted mb-0">Total Patients</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3">
                                        <i class="fas fa-hospital fa-2x text-info mb-2"></i>
                                        <h4><?php 
                                            $db->query("SELECT COUNT(*) as total FROM hospitals WHERE status = 'approved'");
                                            echo $db->single()['total'];
                                        ?></h4>
                                        <p class="text-muted mb-0">Active Hospitals</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3">
                                        <i class="fas fa-vial fa-2x text-success mb-2"></i>
                                        <h4><?php 
                                            $db->query("SELECT COUNT(*) as total FROM test_bookings");
                                            echo $db->single()['total'];
                                        ?></h4>
                                        <p class="text-muted mb-0">Total Tests</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3">
                                        <i class="fas fa-syringe fa-2x text-warning mb-2"></i>
                                        <h4><?php 
                                            $db->query("SELECT COUNT(*) as total FROM vaccination_bookings");
                                            echo $db->single()['total'];
                                        ?></h4>
                                        <p class="text-muted mb-0">Total Vaccinations</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Password confirmation validation
        document.querySelector('input[name="confirm_password"]').addEventListener('input', function() {
            const newPassword = document.querySelector('input[name="new_password"]').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Require current password if new password is entered
        document.querySelector('input[name="new_password"]').addEventListener('input', function() {
            const currentPasswordField = document.querySelector('input[name="current_password"]');
            if (this.value) {
                currentPasswordField.required = true;
            } else {
                currentPasswordField.required = false;
            }
        });
    </script>
</body>
</html>