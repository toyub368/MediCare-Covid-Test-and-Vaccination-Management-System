<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';

$role = $_GET['role'] ?? 'patient';
$success = '';
$error = '';

if ($_POST) {
    $db = new Database();
    
    // Common fields
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Check if email already exists
        $table = ($role === 'hospital') ? 'hospitals' : 'patients';
        $db->query("SELECT id FROM $table WHERE email = :email");
        $db->bind(':email', $email);
        
        if ($db->single()) {
            $error = 'Email address already registered';
        } else {
            $hashed_password = hashPassword($password);
            
            if ($role === 'hospital') {
                // Hospital registration
                $hospital_name = sanitize($_POST['hospital_name']);
                $phone = sanitize($_POST['phone']);
                $address = sanitize($_POST['address']);
                $city = sanitize($_POST['city']);
                $state = sanitize($_POST['state']);
                $pincode = sanitize($_POST['pincode']);
                $license_number = sanitize($_POST['license_number']);
                
                $db->query("INSERT INTO hospitals (hospital_name, email, password, phone, address, city, state, pincode, license_number) 
                           VALUES (:hospital_name, :email, :password, :phone, :address, :city, :state, :pincode, :license_number)");
                $db->bind(':hospital_name', $hospital_name);
                $db->bind(':email', $email);
                $db->bind(':password', $hashed_password);
                $db->bind(':phone', $phone);
                $db->bind(':address', $address);
                $db->bind(':city', $city);
                $db->bind(':state', $state);
                $db->bind(':pincode', $pincode);
                $db->bind(':license_number', $license_number);
                
                if ($db->execute()) {
                    $success = 'Hospital registration successful! Please wait for admin approval.';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            } else {
                // Patient registration
                $full_name = sanitize($_POST['full_name']);
                $phone = sanitize($_POST['phone']);
                $date_of_birth = $_POST['date_of_birth'];
                $gender = $_POST['gender'];
                $address = sanitize($_POST['address']);
                $city = sanitize($_POST['city']);
                $state = sanitize($_POST['state']);
                $pincode = sanitize($_POST['pincode']);
                $aadhar_number = sanitize($_POST['aadhar_number']);
                
                $db->query("INSERT INTO patients (full_name, email, password, phone, date_of_birth, gender, address, city, state, pincode, aadhar_number) 
                           VALUES (:full_name, :email, :password, :phone, :date_of_birth, :gender, :address, :city, :state, :pincode, :aadhar_number)");
                $db->bind(':full_name', $full_name);
                $db->bind(':email', $email);
                $db->bind(':password', $hashed_password);
                $db->bind(':phone', $phone);
                $db->bind(':date_of_birth', $date_of_birth);
                $db->bind(':gender', $gender);
                $db->bind(':address', $address);
                $db->bind(':city', $city);
                $db->bind(':state', $state);
                $db->bind(':pincode', $pincode);
                $db->bind(':aadhar_number', $aadhar_number);
                
                if ($db->execute()) {
                    $success = 'Registration successful! You can now login.';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo ucfirst($role); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-7">
                    <div class="auth-card fade-in">
                        <div class="auth-logo">
                            <i class="<?php echo ($role === 'hospital') ? 'fas fa-hospital' : 'fas fa-user-injured'; ?>"></i>
                        </div>
                        
                        <h2 class="text-center mb-4"><?php echo ucfirst($role); ?> Registration</h2>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-custom">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                <div class="mt-2">
                                    <a href="login.php?role=<?php echo $role; ?>" class="btn btn-sm btn-outline-success">Login Now</a>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-custom">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <?php if ($role === 'hospital'): ?>
                                    <div class="col-12 mb-3">
                                        <label for="hospital_name" class="form-label">Hospital Name *</label>
                                        <input type="text" class="form-control" id="hospital_name" name="hospital_name" 
                                               value="<?php echo htmlspecialchars($_POST['hospital_name'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="license_number" class="form-label">License Number *</label>
                                        <input type="text" class="form-control" id="license_number" name="license_number" 
                                               value="<?php echo htmlspecialchars($_POST['license_number'] ?? ''); ?>" required>
                                    </div>
                                <?php else: ?>
                                    <div class="col-12 mb-3">
                                        <label for="full_name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" 
                                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="date_of_birth" class="form-label">Date of Birth *</label>
                                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                               value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="gender" class="form-label">Gender *</label>
                                        <select class="form-select" id="gender" name="gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="male" <?php echo ($_POST['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                                            <option value="female" <?php echo ($_POST['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                                            <option value="other" <?php echo ($_POST['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="aadhar_number" class="form-label">CNIC Number *</label>
                                        <input type="text" class="form-control" id="aadhar_number" name="aadhar_number" 
                                               value="<?php echo htmlspecialchars($_POST['aadhar_number'] ?? ''); ?>" 
                                               pattern="[0-9]{12}" maxlength="12" required>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                                           pattern="[0-9]{10}" maxlength="10" required>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label for="address" class="form-label">Address *</label>
                                    <textarea class="form-control" id="address" name="address" rows="2" required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="city" class="form-label">City *</label>
                                    <input type="text" class="form-control" id="city" name="city" 
                                           value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="state" class="form-label">State *</label>
                                    <input type="text" class="form-control" id="state" name="state" 
                                           value="<?php echo htmlspecialchars($_POST['state'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="pincode" class="form-label">Pincode *</label>
                                    <input type="text" class="form-control" id="pincode" name="pincode" 
                                           value="<?php echo htmlspecialchars($_POST['pincode'] ?? ''); ?>" 
                                           pattern="[0-9]{6}" maxlength="6" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           minlength="6" required>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           minlength="6" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-gradient w-100 mb-3">
                                <i class="fas fa-user-plus me-2"></i>Register
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-2">Already have an account? 
                                <a href="login.php?role=<?php echo $role; ?>" class="text-decoration-none">Login here</a>
                            </p>
                            <a href="../index.php" class="text-muted text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Back to Home
                            </a>
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
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Aadhar number validation
        document.getElementById('aadhar_number')?.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
        
        // Phone number validation
        document.getElementById('phone').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
        
        // Pincode validation
        document.getElementById('pincode').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
    </script>
</body>
</html>