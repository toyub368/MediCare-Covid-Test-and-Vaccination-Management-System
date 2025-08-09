<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';

$role = $_GET['role'] ?? 'patient';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    
    // Common fields
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    $validationErrors = [];
    
    if (empty($email)) {
        $validationErrors[] = 'Email is required';
    } elseif (!validateEmail($email)) {
        $validationErrors[] = 'Please enter a valid email address';
    }
    
    if (empty($password)) {
        $validationErrors[] = 'Password is required';
    } else {
        // Strong password validation
        if (strlen($password) < 8) {
            $validationErrors[] = 'Password must be at least 8 characters long';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $validationErrors[] = 'Password must contain at least one uppercase letter';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $validationErrors[] = 'Password must contain at least one lowercase letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $validationErrors[] = 'Password must contain at least one number';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $validationErrors[] = 'Password must contain at least one special character';
        }
    }
    
    if ($password !== $confirm_password) {
        $validationErrors[] = 'Passwords do not match';
    }
    
    // If we have validation errors, skip further processing
    if (!empty($validationErrors)) {
        $error = implode('<br>', $validationErrors);
    } else {
        // Check if email already exists
        $table = ($role === 'hospital') ? 'hospitals' : 'patients';
        $db->query("SELECT id FROM $table WHERE email = :email");
        $db->bind(':email', $email);
        
        if ($db->single()) {
            $error = 'Email address already registered';
        } else {
            $hashed_password = hashPassword($password);
            
            // Phone validation based on role
            $phone = sanitize($_POST['phone']);
            $validPhone = false;
            
            if ($role === 'hospital') {
                // PTCL (landline) or mobile validation for hospitals
                $validPhone = preg_match('/^0(2[1-9]|3[0-9]|4[1-9]|6[0-9]|8[1-9]|9[0-9])[0-9]{7,9}$/', $phone);
                if (!$validPhone) {
                    $error = 'Please enter a valid Pakistani phone number (landline or mobile).<br>'
                           . 'Landline: 0211234567 (Karachi), 0511234567 (Islamabad)<br>'
                           . 'Mobile: 03123456789';
                }
            } else {
                // Mobile validation for patients
                $validPhone = preg_match('/^03[0-9]{9}$/', $phone);
                if (!$validPhone) {
                    $error = 'Please enter a valid 11-digit Pakistani mobile number (starting with 03)';
                }
            }

            if ($validPhone) {
                if ($role === 'hospital') {
                    // Hospital registration
                    $hospital_name = sanitize($_POST['hospital_name']);
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
                    $date_of_birth = $_POST['date_of_birth'];
                    $gender = $_POST['gender'];
                    $address = sanitize($_POST['address']);
                    $city = sanitize($_POST['city']);
                    $state = sanitize($_POST['state']);
                    $pincode = sanitize($_POST['pincode']);
                    $cnic = sanitize($_POST['cnic']);
                    
                    // CNIC validation
                    if (!preg_match('/^[0-9]{13}$/', $cnic)) {
                        $validationErrors[] = 'Please enter a valid 13-digit Pakistani CNIC';
                    } else {
                        $db->query("INSERT INTO patients (full_name, email, password, phone, date_of_birth, gender, address, city, state, pincode, cnic) 
                                   VALUES (:full_name, :email, :password, :phone, :date_of_birth, :gender, :address, :city, :state, :pincode, :cnic)");
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
                        $db->bind(':cnic', $cnic);
                        
                        if ($db->execute()) {
                            $success = 'Registration successful! You can now login.';
                        } else {
                            $error = 'Registration failed. Please try again.';
                        }
                    }
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
    <style>
        .password-toggle {
            cursor: pointer;
            background: #f8f9fa;
            border: 1px solid #ced4da;
            border-left: none;
            display: flex;
            align-items: center;
            padding: 0 12px;
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
        }
        .input-group > .form-control {
            border-right: none;
        }
        .input-group > .password-toggle {
            transition: all 0.3s ease;
        }
        .input-group > .password-toggle:hover {
            background: #e9ecef;
        }
        .password-requirements {
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 2px;
        }
        .requirement i {
            margin-right: 5px;
            font-size: 0.75rem;
        }
        .requirement.valid {
            color: #28a745;
        }
        .requirement.invalid {
            color: #6c757d;
        }
    </style>
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
                                        <label for="cnic" class="form-label">CNIC Number *</label>
                                        <input type="text" class="form-control" id="cnic" name="cnic" 
                                               value="<?php echo htmlspecialchars($_POST['cnic'] ?? ''); ?>" 
                                               pattern="[0-9]{13}" maxlength="13" required
                                               placeholder="13-digit CNIC without dashes">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="text" inputmode="numeric" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                                           <?php if ($role === 'hospital'): ?>
                                               pattern="^0(2[1-9]|3[0-9]|4[1-9]|6[0-9]|8[1-9]|9[0-9])[0-9]{7,9}$"
                                               title="Valid formats: 0211234567 (Karachi), 0511234567 (Islamabad), 03123456789"
                                               placeholder="0211234567 or 03123456789"
                                           <?php else: ?>
                                               pattern="03[0-9]{9}" 
                                               title="Enter a valid 11-digit Pakistani mobile number (e.g., 03123456789)"
                                               placeholder="03123456789"
                                           <?php endif; ?>
                                           required>
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
                                           pattern="[0-9]{6}" maxlength="6" required
                                           placeholder="6-digit pincode">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" 
                                               minlength="8" required>
                                        <div class="password-toggle" onclick="togglePassword('password')">
                                            <i class="fas fa-eye"></i>
                                        </div>
                                    </div>
                                    <div class="password-requirements">
                                        <div class="requirement invalid" id="length-req">
                                            <i class="fas fa-circle"></i> At least 8 characters
                                        </div>
                                        <div class="requirement invalid" id="upper-req">
                                            <i class="fas fa-circle"></i> At least one uppercase letter
                                        </div>
                                        <div class="requirement invalid" id="lower-req">
                                            <i class="fas fa-circle"></i> At least one lowercase letter
                                        </div>
                                        <div class="requirement invalid" id="number-req">
                                            <i class="fas fa-circle"></i> At least one number
                                        </div>
                                        <div class="requirement invalid" id="special-req">
                                            <i class="fas fa-circle"></i> At least one special character
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                               minlength="8" required>
                                        <div class="password-toggle" onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye"></i>
                                        </div>
                                    </div>
                                    <div id="password-match" class="form-text"></div>
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
        // Password visibility toggle function
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleIcon = passwordField.nextElementSibling.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Password strength validation
        const passwordInput = document.getElementById('password');
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            
            // Check requirements
            const isLengthValid = password.length >= 8;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[^A-Za-z0-9]/.test(password);
            
            // Update requirement indicators
            updateRequirement('length-req', isLengthValid);
            updateRequirement('upper-req', hasUppercase);
            updateRequirement('lower-req', hasLowercase);
            updateRequirement('number-req', hasNumber);
            updateRequirement('special-req', hasSpecial);
            
            // Check confirm password
            checkPasswordMatch();
        });
        
        function updateRequirement(elementId, isValid) {
            const element = document.getElementById(elementId);
            element.classList.toggle('valid', isValid);
            element.classList.toggle('invalid', !isValid);
            
            const icon = element.querySelector('i');
            if (isValid) {
                icon.classList.replace('fa-circle', 'fa-check');
                icon.style.color = '#28a745';
            } else {
                icon.classList.replace('fa-check', 'fa-circle');
                icon.style.color = '';
            }
        }
        
        // Password match validation
        document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);
        
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchElement = document.getElementById('password-match');
            
            if (confirmPassword === '') {
                matchElement.textContent = '';
                return;
            }
            
            if (password === confirmPassword) {
                matchElement.textContent = 'Passwords match!';
                matchElement.style.color = '#28a745';
            } else {
                matchElement.textContent = 'Passwords do not match';
                matchElement.style.color = '#dc3545';
            }
        }

        document.getElementById('cnic')?.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length !== 13) {
                this.setCustomValidity('CNIC must be exactly 13 digits');
            } else {
                this.setCustomValidity('');
            }
        });

        const phoneInput = document.getElementById('phone');
        const role = "<?php echo $role; ?>";
        
        phoneInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
            
            if (role === 'patient') {
                // For patients: Force mobile format (03XXXXXXXXX)
                if (!this.value.startsWith('03') && this.value.length > 0) {
                    this.value = '03' + this.value.substring(2);
                }
                if (this.value.length > 11) {
                    this.value = this.value.substring(0, 11);
                }
            } else {
                // For hospitals: Allow both landline and mobile
                if (this.value.length > 12) {
                    this.value = this.value.substring(0, 12);
                }
            }
        });

        document.getElementById('pincode').addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length > 6) {
                this.value = this.value.substring(0, 6);
            }
        });
    </script>
</body>
</html>