<?php

require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('patient');




$db = new Database();
$patient_id = $_SESSION['user_id'];

// Get patient statistics
$db->query("SELECT COUNT(*) as total FROM test_bookings WHERE patient_id = :id");
$db->bind(':id', $patient_id);
$total_tests = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM vaccination_bookings WHERE patient_id = :id");
$db->bind(':id', $patient_id);
$total_vaccinations = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM test_bookings WHERE patient_id = :id AND status = 'pending'");
$db->bind(':id', $patient_id);
$pending_tests = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM vaccination_bookings WHERE patient_id = :id AND status = 'pending'");
$db->bind(':id', $patient_id);
$pending_vaccinations = $db->single()['total'];

// Recent test bookings
$db->query("SELECT tb.*, h.hospital_name 
           FROM test_bookings tb 
           JOIN hospitals h ON tb.hospital_id = h.id 
           WHERE tb.patient_id = :id 
           ORDER BY tb.created_at DESC LIMIT 5");
$db->bind(':id', $patient_id);
$recent_tests = $db->resultset();

// Recent vaccination bookings
$db->query("SELECT vb.*, h.hospital_name 
           FROM vaccination_bookings vb 
           JOIN hospitals h ON vb.hospital_id = h.id 
           WHERE vb.patient_id = :id 
           ORDER BY vb.created_at DESC LIMIT 5");
$db->bind(':id', $patient_id);
$recent_vaccinations = $db->resultset();

// Get patient info
$db->query("SELECT * FROM patients WHERE id = :id");
$db->bind(':id', $patient_id);
$patient_info = $db->single();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - COVID-19 Booking System</title>
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
                    <h1 class="welcome-text">Welcome back, <?php echo htmlspecialchars($patient_info['full_name']); ?>!</h1>
                    <p class="welcome-subtitle">Stay safe and manage your COVID-19 health records efficiently.</p>
                </div>
                <div class="col-auto">
                    <div class="d-flex gap-2">
                        <a href="search-hospitals.php" class="btn btn-outline-primary">
                            <i class="fas fa-search me-2"></i>Find Hospitals
                        </a>
                        <a href="book-test.php" class="btn btn-gradient">
                            <i class="fas fa-plus me-2"></i>Book Test
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
                    <p class="text-muted mb-0">Vaccinations</p>
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
                    <p class="text-muted mb-0">Pending Appointments</p>
                    <small class="text-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>Awaiting approval
                    </small>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stats-card slide-up" style="animation-delay: 0.3s;">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <h3 class="mb-1"><?php echo date_diff(date_create($patient_info['date_of_birth']), date_create('today'))->y; ?></h3>
                    <p class="text-muted mb-0">Age (Years)</p>
                    <small class="text-success">
                        <i class="fas fa-check-circle me-1"></i>Verified Profile
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
                                <a href="book-test.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                    <i class="fas fa-vial fa-2x mb-2"></i>
                                    <span>Book COVID Test</span>
                                    <small class="text-muted">RT-PCR, Antigen, Antibody</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="book-vaccination.php" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                    <i class="fas fa-syringe fa-2x mb-2"></i>
                                    <span>Book Vaccination</span>
                                    <small class="text-muted">Covishield, Covaxin, etc.</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="test-results.php" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                    <i class="fas fa-file-medical fa-2x mb-2"></i>
                                    <span>View Test Results</span>
                                    <small class="text-muted">Download reports</small>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="vaccination-certificate.php" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3">
                                    <i class="fas fa-certificate fa-2x mb-2"></i>
                                    <span>Vaccination Certificate</span>
                                    <small class="text-muted">Download certificate</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activities -->
       
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>