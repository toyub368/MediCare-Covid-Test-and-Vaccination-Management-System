<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('hospital');

$db = new Database();

// Get hospital statistics
$hospital_id = $_SESSION['user_id'];

$db->query("SELECT COUNT(*) as total FROM test_bookings WHERE hospital_id = :id");
$db->bind(':id', $hospital_id);
$total_tests = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM vaccination_bookings WHERE hospital_id = :id");
$db->bind(':id', $hospital_id);
$total_vaccinations = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM test_bookings WHERE hospital_id = :id AND status = 'pending'");
$db->bind(':id', $hospital_id);
$pending_tests = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM vaccination_bookings WHERE hospital_id = :id AND status = 'pending'");
$db->bind(':id', $hospital_id);
$pending_vaccinations = $db->single()['total'];

// Recent test bookings
$db->query("SELECT tb.*, p.full_name as patient_name, p.phone as patient_phone 
           FROM test_bookings tb 
           JOIN patients p ON tb.patient_id = p.id 
           WHERE tb.hospital_id = :id 
           ORDER BY tb.created_at DESC LIMIT 5");
$db->bind(':id', $hospital_id);
$recent_tests = $db->resultset();

// Recent vaccination bookings
$db->query("SELECT vb.*, p.full_name as patient_name, p.phone as patient_phone 
           FROM vaccination_bookings vb 
           JOIN patients p ON vb.patient_id = p.id 
           WHERE vb.hospital_id = :id 
           ORDER BY vb.created_at DESC LIMIT 5");
$db->bind(':id', $hospital_id);
$recent_vaccinations = $db->resultset();

// Get hospital info
$db->query("SELECT * FROM hospitals WHERE id = :id");
$db->bind(':id', $hospital_id);
$hospital_info = $db->single();

// Define date/time formatting functions if they don't exist
if (!function_exists('formatDate')) {
    function formatDate($dateString) {
        return date('M j, Y', strtotime($dateString));
    }
}

if (!function_exists('formatTime')) {
    function formatTime($timeString) {
        return date('g:i a', strtotime($timeString));
    }
}

// Status badge function
if (!function_exists('getStatusBadge')) {
    function getStatusBadge($status) {
        switch ($status) {
            case 'pending':
                return '<span class="badge bg-warning">Pending</span>';
            case 'confirmed':
                return '<span class="badge bg-primary">Confirmed</span>';
            case 'completed':
                return '<span class="badge bg-success">Completed</span>';
            case 'cancelled':
                return '<span class="badge bg-danger">Cancelled</span>';
            default:
                return '<span class="badge bg-secondary">' . ucfirst($status) . '</span>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Dashboard - COVID-19 Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="dashboard-header fade-in">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="welcome-text">Welcome back, <?php echo htmlspecialchars($hospital_info['hospital_name']); ?>!</h1>
                    <p class="welcome-subtitle">Manage your COVID-19 test and vaccination services efficiently.</p>
                </div>
                <div class="col-auto">
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                            <i class="fas fa-sync-alt me-2"></i>Refresh
                        </button>
                        <a href="reports.php" class="btn btn-gradient">
                            <i class="fas fa-chart-bar me-2"></i>View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stats-card slide-up">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-vial"></i>
                    </div>
                    <h3 class="mb-1"><?php echo number_format($total_tests); ?></h3>
                    <p class="text-muted mb-0">Total Tests</p>
                    <small class="text-info">
                        <i class="fas fa-clock me-1"></i><?php echo $pending_tests; ?> pending
                    </small>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stats-card slide-up" style="animation-delay: 0.1s;">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-syringe"></i>
                    </div>
                    <h3 class="mb-1"><?php echo number_format($total_vaccinations); ?></h3>
                    <p class="text-muted mb-0">Total Vaccinations</p>
                    <small class="text-info">
                        <i class="fas fa-clock me-1"></i><?php echo $pending_vaccinations; ?> pending
                    </small>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stats-card slide-up" style="animation-delay: 0.2s;">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3 class="mb-1"><?php echo number_format($pending_tests + $pending_vaccinations); ?></h3>
                    <p class="text-muted mb-0">Pending Approvals</p>
                    <small class="text-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>Requires attention
                    </small>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stats-card slide-up" style="animation-delay: 0.3s;">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3 class="mb-1"><?php echo $hospital_info['status'] === 'approved' ? 'Active' : ucfirst($hospital_info['status']); ?></h3>
                    <p class="text-muted mb-0">Hospital Status</p>
                    <small class="text-success">
                        <i class="fas fa-check-circle me-1"></i>Verified
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt me-2 text-primary"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <a href="test-requests.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                    <i class="fas fa-vial fa-2x mb-2"></i>
                                    <span>Manage Test Requests</span>
                                    <?php if ($pending_tests > 0): ?>
                                        <span class="badge bg-danger mt-1"><?php echo $pending_tests; ?> pending</span>
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="vaccination-requests.php" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                    <i class="fas fa-syringe fa-2x mb-2"></i>
                                    <span>Manage Vaccinations</span>
                                    <?php if ($pending_vaccinations > 0): ?>
                                        <span class="badge bg-danger mt-1"><?php echo $pending_vaccinations; ?> pending</span>
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="patients.php" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <span>View Patients</span>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="inventory.php" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                    <i class="fas fa-boxes fa-2x mb-2"></i>
                                    <span>Manage Inventory</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm fade-in">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-0">
                            <i class="fas fa-vial me-2 text-primary"></i>Recent Test Bookings
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_tests)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-vial fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No test bookings yet</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recent_tests as $test): ?>
                                    <div class="list-group-item border-0 px-0">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($test['patient_name']); ?></h6>
                                                <p class="mb-1 text-muted"><?php echo htmlspecialchars($test['test_type']); ?></p>
                                                <small class="text-muted">
                                                    <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($test['patient_phone']); ?>
                                                </small><br>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i><?php echo formatDate($test['booking_date']); ?>
                                                    at <?php echo formatTime($test['booking_time']); ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <?php echo getStatusBadge($test['status']); ?>
                                                <br>
                                                <small class="text-muted"><?php echo formatDate($test['created_at']); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="test-requests.php" class="btn btn-outline-primary">View All Test Requests</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm fade-in">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-0">
                            <i class="fas fa-syringe me-2 text-success"></i>Recent Vaccination Bookings
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_vaccinations)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-syringe fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No vaccination bookings yet</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recent_vaccinations as $vaccination): ?>
                                    <div class="list-group-item border-0 px-0">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($vaccination['patient_name']); ?></h6>
                                                <p class="mb-1 text-muted"><?php echo htmlspecialchars($vaccination['vaccine_name']); ?> - Dose <?php echo htmlspecialchars($vaccination['dose_number']); ?></p>
                                                <small class="text-muted">
                                                    <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($vaccination['patient_phone']); ?>
                                                </small><br>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i><?php echo formatDate($vaccination['booking_date']); ?>
                                                    at <?php echo formatTime($vaccination['booking_time']); ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <?php echo getStatusBadge($vaccination['status']); ?>
                                                <br>
                                                <small class="text-muted"><?php echo formatDate($vaccination['created_at']); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="vaccination-requests.php" class="btn btn-outline-success">View All Vaccination Requests</a>
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
        function refreshDashboard() {
            location.reload();
        }
    </script>
</body>
</html>