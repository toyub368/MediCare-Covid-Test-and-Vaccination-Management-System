<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';

$role = $_GET['role'] ?? 'patient';
$error = '';

if ($_POST) {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $db = new Database();
        
        // Determine table based on role
        $table = '';
        switch ($role) {
            case 'admin':
                $table = 'admins';
                break;
            case 'hospital':
                $table = 'hospitals';
                break;
            case 'patient':
                $table = 'patients';
                break;
            default:
                $error = 'Invalid role';
        }
        
        if (!$error) {
            $db->query("SELECT * FROM $table WHERE email = :email");
            $db->bind(':email', $email);
            $user = $db->single();
            
            if ($user && verifyPassword($password, $user['password'])) {
                // Check if hospital is approved
                if ($role === 'hospital' && $user['status'] !== 'approved') {
                    $error = 'Your hospital registration is pending approval';
                } else {
                    $name = $user['full_name'] ?? $user['hospital_name'] ?? $user['username'];
                    setUserSession($user['id'], $role, $name, $user['email']);
                    header("Location: ../$role/dashboard.php");
                    exit();
                }
            } else {
                $error = 'Invalid email or password';
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
    <title>Login - <?php echo ucfirst($role); ?> Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="auth-card fade-in">
                        <div class="auth-logo">
                            <?php
                            $icons = [
                                'admin' => 'fas fa-user-shield',
                                'hospital' => 'fas fa-hospital',
                                'patient' => 'fas fa-user-injured'
                            ];
                            ?>
                            <i class="<?php echo $icons[$role]; ?>"></i>
                        </div>
                        
                        <h2 class="text-center mb-4"><?php echo ucfirst($role); ?> Login</h2>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-custom">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-gradient w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <?php if ($role !== 'admin'): ?>
                                <p class="mb-2">Don't have an account? 
                                    <a href="register.php?role=<?php echo $role; ?>" class="text-decoration-none">Register here</a>
                                </p>
                            <?php endif; ?>
                            <a href="../index.php" class="text-muted text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Back to Home
                            </a>
                        </div>
                        
                        <?php if ($role === 'admin'): ?>
                            <div class="mt-4 p-3 bg-light rounded">
                               
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>