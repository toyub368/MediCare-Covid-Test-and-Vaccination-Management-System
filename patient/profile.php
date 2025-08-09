<?php

require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('patient');

$db = new Database();

// Initialize variables
$full_name = $email = $created_at = '';
$success = $error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Get current patient data
    $db->query("SELECT * FROM patients WHERE id = :id");
    $db->bind(':id', $_SESSION['user_id']);
    $patient = $db->single();

    if (empty($full_name) || empty($email)) {
        $error = 'Please fill in all required fields';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } elseif (!empty($new_password) && !verifyPassword($current_password, $patient['password'])) {
        $error = 'Current password is incorrect';
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } elseif (!empty($new_password) && strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long';
    } else {
        // Check if email is already taken by another patient
        $db->query("SELECT id FROM patients WHERE email = :email AND id != :id");
        $db->bind(':email', $email);
        $db->bind(':id', $_SESSION['user_id']);

        if ($db->single()) {
            $error = 'Email address is already in use';
        } else {
            // Update profile
            if (!empty($new_password)) {
                $hashed_password = hashPassword($new_password);
                $db->query("UPDATE patients SET full_name = :full_name, email = :email, password = :password WHERE id = :id");
                $db->bind(':password', $hashed_password);
            } else {
                $db->query("UPDATE patients SET full_name = :full_name, email = :email WHERE id = :id");
            }

            $db->bind(':full_name', $full_name);
            $db->bind(':email', $email);
            $db->bind(':id', $_SESSION['user_id']);

            if ($db->execute()) {
                $_SESSION['name'] = $full_name;
                $_SESSION['email'] = $email;
                $success = 'Profile updated successfully';
                
                // Refresh patient data after update
                $db->query("SELECT * FROM patients WHERE id = :id");
                $db->bind(':id', $_SESSION['user_id']);
                $patient = $db->single();
            } else {
                $error = 'Failed to update profile';
            }
        }
    }
}

// Get current patient data
if (!isset($patient)) {
    $db->query("SELECT * FROM patients WHERE id = :id");
    $db->bind(':id', $_SESSION['user_id']);
    $patient = $db->single();
}

// Set variables from patient data
if ($patient) {
    $full_name = $patient['full_name'] ?? '';
    $email = $patient['email'] ?? '';
    $created_at = $patient['created_at'] ?? '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Profile</title>
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
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0 pb-0">
                            <h4><i class="fas fa-user me-2 text-primary"></i>Patient Profile</h4>
                            <p class="text-muted">Manage your personal information and security settings</p>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($success)): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" name="full_name"
                                           value="<?php echo htmlspecialchars($full_name); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" name="email"
                                           value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="created_at" class="form-label">Registered On</label>
                                    <input type="text" class="form-control"
                                           value="<?php echo formatDate($created_at); ?>" readonly>
                                </div>

                                <hr class="my-4">

                                <h6 class="mb-3">Change Password</h6>
                                <p class="text-muted mb-3">Leave blank to keep current password.</p>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" name="current_password" class="form-control">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password" class="form-control" minlength="6">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" name="confirm_password" class="form-control" minlength="6">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="dashboard.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Back
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    // Validate confirm password
    document.querySelector('input[name="confirm_password"]').addEventListener('input', function() {
        const newPassword = document.querySelector('input[name="new_password"]').value;
        if (newPassword !== this.value) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });

    // Require current password if new password is entered
    document.querySelector('input[name="new_password"]').addEventListener('input', function() {
        const currentPassword = document.querySelector('input[name="current_password"]');
        if (this.value !== '') {
            currentPassword.required = true;
        } else {
            currentPassword.required = false;
        }
    });
</script>
</body>
</html>