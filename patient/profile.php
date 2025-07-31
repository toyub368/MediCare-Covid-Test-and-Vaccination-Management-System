<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('patient');

$db = new Database();

// Handle profile update
if ($_POST) {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $city = sanitize($_POST['city']);
    $state = sanitize($_POST['state']);
    $pincode = sanitize($_POST['pincode']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get current patient data
    $db->query("SELECT * FROM patients WHERE id = :id");
    $db->bind(':id', $_SESSION['user_id']);
    $patient = $db->single();
    
    if (empty($full_name) || empty($email) || empty($phone) || empty($address) || empty($city) || empty($state) || empty($pincode)) {
        $error = 'Please fill in all required fields';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } elseif (!preg_match('/^[6-9]\d{9}$/', $phone)) {
        $error = 'Please enter a valid 10-digit mobile number';
    } elseif (!preg_match('/^\d{6}$/', $pincode)) {
        $error = 'Please enter a valid 6-digit pincode';
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
                $db->query("UPDATE patients SET full_name = :name, email = :email, phone = :phone, address = :address, city = :city, state = :state, pincode = :pincode, password = :password WHERE id = :id");
                $db->bind(':password', $hashed_password);
            } else {
                $db->query("UPDATE patients SET full_name = :name, email = :email, phone = :phone, address = :address, city = :city, state = :state, pincode = :pincode WHERE id = :id");
            }
            
            $db->bind(':name', $full_name);
            $db->bind(':email', $email);
            $db->bind(':phone', $phone);
            $db->bind(':address', $address);
            $db->bind(':city', $city);
            $db->bind(':state', $state);
            $db->bind(':pincode', $pincode);
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

// Get current patient data
$db->query("SELECT * FROM patients WHERE id = :id");
$db->bind(':id', $_SESSION['user_id']);
$patient = $db->single();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Patient Portal</title>
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
                                <i class="fas fa-user me-2 text-primary"></i>My Profile
                            </h4>
                            <p class="text-muted mb-0">Manage your personal information and account settings</p>
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
                                        <label for="full_name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" name="full_name" 
                                               value="<?php echo htmlspecialchars($patient['full_name']); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" name="email" 
                                               value="<?php echo htmlspecialchars($patient['email']); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control" name="phone" 
                                               value="<?php echo htmlspecialchars($patient['phone']); ?>" 
                                               pattern="[0-9]{10}" maxlength="10" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" 
                                               value="<?php echo $patient['date_of_birth']; ?>" readonly>
                                        <small class="text-muted">Date of birth cannot be changed</small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="gender" class="form-label">Gender</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo ucfirst($patient['gender']); ?>" readonly>
                                        <small class="text-muted">Gender cannot be changed</small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="aadhar_number" class="form-label">CNIC Number</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo htmlspecialchars($patient['aadhar_number']); ?>" readonly>
                                        <small class="text-muted">Aadhar number cannot be changed</small>
                                    </div>
                                    
                                    <div class="col-12 mb-3">
                                        <label for="address" class="form-label">Address *</label>
                                        <textarea class="form-control" name="address" rows="2" required><?php echo htmlspecialchars($patient['address']); ?></textarea>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="city" class="form-label">City *</label>
                                        <input type="text" class="form-control" name="city" 
                                               value="<?php echo htmlspecialchars($patient['city']); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="state" class="form-label">State *</label>
                                        <input type="text" class="form-control" name="state" 
                                               value="<?php echo htmlspecialchars($patient['state']); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="pincode" class="form-label">Pincode *</label>
                                        <input type="text" class="form-control" name="pincode" 
                                               value="<?php echo htmlspecialchars($patient['pincode']); ?>" 
                                               pattern="[0-9]{6}" maxlength="6" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="created_at" class="form-label">Member Since</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo formatDate($patient['created_at']); ?>" readonly>
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
                                <i class="fas fa-chart-bar me-2 text-primary"></i>My Health Records
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="p-3">
                                        <i class="fas fa-vial fa-2x text-primary mb-2"></i>
                                        <h4><?php 
                                            $db->query("SELECT COUNT(*) as total FROM test_bookings WHERE patient_id = :id");
                                            $db->bind(':id', $_SESSION['user_id']);
                                            echo $db->single()['total'];
                                        ?></h4>
                                        <p class="text-muted mb-0">Total Tests</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3">
                                        <i class="fas fa-syringe fa-2x text-success mb-2"></i>
                                        <h4><?php 
                                            $db->query("SELECT COUNT(*) as total FROM vaccination_bookings WHERE patient_id = :id");
                                            $db->bind(':id', $_SESSION['user_id']);
                                            echo $db->single()['total'];
                                        ?></h4>
                                        <p class="text-muted mb-0">Vaccinations</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3">
                                        <i class="fas fa-certificate fa-2x text-warning mb-2"></i>
                                        <h4><?php 
                                            $db->query("SELECT COUNT(*) as total FROM vaccination_bookings WHERE patient_id = :id AND certificate_number IS NOT NULL");
                                            $db->bind(':id', $_SESSION['user_id']);
                                            echo $db->single()['total'];
                                        ?></h4>
                                        <p class="text-muted mb-0">Certificates</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3">
                                        <i class="fas fa-calendar fa-2x text-info mb-2"></i>
                                        <h4><?php echo date_diff(date_create($patient['date_of_birth']), date_create('today'))->y; ?></h4>
                                        <p class="text-muted mb-0">Age (Years)</p>
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
        
        // Phone number validation
        document.querySelector('input[name="phone"]').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
        
        // Pincode validation
        document.querySelector('input[name="pincode"]').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
    </script>
</body>
</html>