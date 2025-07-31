<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('admin');

$db = new Database();

// Get statistics
$db->query("SELECT COUNT(*) as total FROM patients");
$total_patients = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM hospitals WHERE status = 'approved'");
$total_hospitals = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM test_bookings");
$total_tests = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM vaccination_bookings");
$total_vaccinations = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM hospitals WHERE status = 'pending'");
$pending_approvals = $db->single()['total'];

// Recent activities
$db->query("SELECT 'test' as type, tb.id, p.full_name as patient_name, h.hospital_name, tb.test_type, tb.created_at 
           FROM test_bookings tb 
           JOIN patients p ON tb.patient_id = p.id 
           JOIN hospitals h ON tb.hospital_id = h.id 
           ORDER BY tb.created_at DESC LIMIT 5");
$recent_tests = $db->resultset();

$db->query("SELECT 'vaccination' as type, vb.id, p.full_name as patient_name, h.hospital_name, vb.vaccine_name, vb.created_at 
           FROM vaccination_bookings vb 
           JOIN patients p ON vb.patient_id = p.id 
           JOIN hospitals h ON vb.hospital_id = h.id 
           ORDER BY vb.created_at DESC LIMIT 5");
$recent_vaccinations = $db->resultset();

// Merge and sort recent activities
$recent_activities = array_merge($recent_tests, $recent_vaccinations);
usort($recent_activities, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
$recent_activities = array_slice($recent_activities, 0, 10);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - COVID-19 Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            position: relative;
            height: 300px; /* Fixed height for all charts */
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="dashboard-header fade-in">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="welcome-text">Welcome back, <?php echo $_SESSION['name']; ?>!</h1>
                    <p class="welcome-subtitle">Here's what's happening with your COVID-19 booking system today.</p>
                </div>
                <div class="col-auto">
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                            <i class="fas fa-sync-alt me-2"></i>Refresh
                        </button>
                        <button class="btn btn-gradient" onclick="exportReport()">
                            <i class="fas fa-download me-2"></i>Export Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stats-card slide-up">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="mb-1" data-stat="patients"><?php echo number_format($total_patients); ?></h3>
                    <p class="text-muted mb-0">Total Patients</p>
                    <small class="text-success">
                        <i class="fas fa-arrow-up me-1"></i>12% from last month
                    </small>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stats-card slide-up" style="animation-delay: 0.1s;">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-hospital"></i>
                    </div>
                    <h3 class="mb-1" data-stat="hospitals"><?php echo number_format($total_hospitals); ?></h3>
                    <p class="text-muted mb-0">Active Hospitals</p>
                    <small class="text-info">
                        <i class="fas fa-clock me-1"></i><?php echo $pending_approvals; ?> pending approval
                    </small>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stats-card slide-up" style="animation-delay: 0.2s;">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-vial"></i>
                    </div>
                    <h3 class="mb-1" data-stat="tests"><?php echo number_format($total_tests); ?></h3>
                    <p class="text-muted mb-0">COVID Tests</p>
                    <small class="text-success">
                        <i class="fas fa-arrow-up me-1"></i>8% from last week
                    </small>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="stats-card slide-up" style="animation-delay: 0.3s;">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-syringe"></i>
                    </div>
                    <h3 class="mb-1" data-stat="vaccinations"><?php echo number_format($total_vaccinations); ?></h3>
                    <p class="text-muted mb-0">Vaccinations</p>
                    <small class="text-success">
                        <i class="fas fa-arrow-up me-1"></i>15% from last week
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Charts Row -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="chart-container fade-in">
                    <h5 class="mb-4">
                        <i class="fas fa-chart-line me-2 text-primary"></i>Booking Trends
                    </h5>
                    <canvas id="bookingTrendsChart"></canvas>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="chart-container fade-in">
                    <h5 class="mb-4">
                        <i class="fas fa-chart-pie me-2 text-primary"></i>Test Results
                    </h5>
                    <canvas id="testResultsChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm fade-in">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2 text-primary"></i>Recent Activities
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>Patient</th>
                                        <th>Hospital</th>
                                        <th>Details</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_activities as $activity): ?>
                                        <tr>
                                            <td>
                                                <?php if ($activity['type'] === 'test'): ?>
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-vial me-1"></i>Test
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-syringe me-1"></i>Vaccine
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($activity['patient_name']); ?></td>
                                            <td><?php echo htmlspecialchars($activity['hospital_name']); ?></td>
                                            <td>
                                                <?php 
                                                echo htmlspecialchars($activity['test_type'] ?? $activity['vaccine_name']); 
                                                ?>
                                            </td>
                                            <td><?php echo formatDateTime($activity['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm fade-in">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-2 text-warning"></i>Pending Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if ($pending_approvals > 0): ?>
                                <a href="hospitals.php" class="btn btn-outline-warning">
                                    <i class="fas fa-hospital me-2"></i>
                                    <?php echo $pending_approvals; ?> Hospital<?php echo $pending_approvals > 1 ? 's' : ''; ?> Pending Approval
                                </a>
                            <?php endif; ?>
                            
                            <a href="reports.php" class="btn btn-outline-primary">
                                <i class="fas fa-chart-bar me-2"></i>Generate Reports
                            </a>
                            
                            <a href="vaccine-inventory.php" class="btn btn-outline-info">
                                <i class="fas fa-boxes me-2"></i>Manage Vaccine Inventory
                            </a>
                            
                            <a href="patients.php" class="btn btn-outline-success">
                                <i class="fas fa-users me-2"></i>View All Patients
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
        // Global chart variables
        let bookingChart = null;
        let testResultsChart = null;

        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize charts after a small delay to ensure layout is ready
            setTimeout(initCharts, 100);
            
            // Reinitialize charts on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    initCharts();
                }
            });
        });

        function initCharts() {
            // Destroy existing charts if they exist
            if (bookingChart) bookingChart.destroy();
            if (testResultsChart) testResultsChart.destroy();
            
            // Booking Trends Chart
            const bookingCtx = document.getElementById('bookingTrendsChart').getContext('2d');
            bookingChart = new Chart(bookingCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Tests',
                        data: [120, 190, 300, 500, 200, 300],
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Vaccinations',
                        data: [80, 150, 250, 400, 300, 450],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Test Results Chart
            const testCtx = document.getElementById('testResultsChart').getContext('2d');
            testResultsChart = new Chart(testCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Negative', 'Positive', 'Pending'],
                    datasets: [{
                        data: [65, 25, 10],
                        backgroundColor: ['#10b981', '#ef4444', '#f59e0b'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '65%'
                }
            });
        }

        function refreshDashboard() {
            location.reload();
        }

        function exportReport() {
            window.open('reports.php?export=dashboard', '_blank');
        }
    </script>
</body>
</html>