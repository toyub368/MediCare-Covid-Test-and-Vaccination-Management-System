<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('hospital');

$db = new Database();
$hospital_id = $_SESSION['user_id'];

// Get data for reports
$db->query("SELECT COUNT(*) as total,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN test_result = 'positive' THEN 1 ELSE 0 END) as positive,
            SUM(CASE WHEN test_result = 'negative' THEN 1 ELSE 0 END) as negative
            FROM test_bookings 
            WHERE hospital_id = :hospital_id");
$db->bind(':hospital_id', $hospital_id);
$testStats = $db->single();

$db->query("SELECT COUNT(*) as total,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            COUNT(DISTINCT patient_id) as patients
            FROM vaccination_bookings 
            WHERE hospital_id = :hospital_id");
$db->bind(':hospital_id', $hospital_id);
$vaccinationStats = $db->single();

$db->query("SELECT vaccine_name, available_doses
            FROM vaccine_inventory 
            WHERE hospital_id = :hospital_id
            ORDER BY available_doses DESC");
$db->bind(':hospital_id', $hospital_id);
$inventory = $db->resultset();

// Get booking trends for the last 30 days
$db->query("SELECT DATE(created_at) as date, 
            COUNT(*) as total_bookings,
            SUM(CASE WHEN type = 'test' THEN 1 ELSE 0 END) as test_bookings,
            SUM(CASE WHEN type = 'vaccination' THEN 1 ELSE 0 END) as vaccination_bookings
            FROM (
                SELECT created_at, 'test' as type FROM test_bookings WHERE hospital_id = :hospital_id
                UNION ALL
                SELECT created_at, 'vaccination' as type FROM vaccination_bookings WHERE hospital_id = :hospital_id2
            ) as bookings
            WHERE created_at >= CURDATE() - INTERVAL 30 DAY
            GROUP BY DATE(created_at)
            ORDER BY date");
$db->bind(':hospital_id', $hospital_id);
$db->bind(':hospital_id2', $hospital_id);
$bookingTrends = $db->resultset();

// Prepare data for charts
$dates = [];
$testCounts = [];
$vaccinationCounts = [];

foreach ($bookingTrends as $trend) {
    $dates[] = date('M d', strtotime($trend['date']));
    $testCounts[] = $trend['test_bookings'];
    $vaccinationCounts[] = $trend['vaccination_bookings'];
}

// Get recent activities
$db->query("SELECT 'test' as type, id, patient_id, status, test_result as result, created_at 
            FROM test_bookings 
            WHERE hospital_id = :hospital_id
            UNION
            SELECT 'vaccination' as type, id, patient_id, status, vaccine_name as result, created_at 
            FROM vaccination_bookings 
            WHERE hospital_id = :hospital_id2
            ORDER BY created_at DESC 
            LIMIT 10");
$db->bind(':hospital_id', $hospital_id);
$db->bind(':hospital_id2', $hospital_id);
$recentActivities = $db->resultset();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Hospital Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        :root {
            --primary: #2c7be5;
            --secondary: #6c757d;
            --success: #00d97e;
            --info: #39afd1;
            --warning: #ffcc00;
            --danger: #e63757;
            --light: #f9fafd;
            --dark: #283252;
            --card-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        body {
            background-color: #f5f7fb;
            color: #3b506c;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .card-report {
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            overflow: hidden;
            background-color: white;
        }
        
        .card-report:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.1);
        }
        
        .stat-card {
            text-align: center;
            padding: 25px 15px;
            border-radius: 12px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: "";
            position: absolute;
            top: -20px;
            right: -20px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 15px 0;
            position: relative;
            z-index: 2;
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }
        
        .stat-icon {
            font-size: 3rem;
            opacity: 0.8;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
        }
        
        .chart-container {
            height: 300px;
            position: relative;
        }
        
        .report-section {
            margin-bottom: 30px;
        }
        
        .report-header {
            border-bottom: 2px solid rgba(0,0,0,0.05);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        
        .inventory-progress {
            height: 12px;
            border-radius: 6px;
        }
        
        .activity-item {
            border-left: 3px solid var(--primary);
            padding-left: 15px;
            margin-bottom: 20px;
            background: rgba(44, 123, 229, 0.03);
            border-radius: 0 8px 8px 0;
            padding: 12px 15px;
            transition: all 0.2s;
        }
        
        .activity-item:hover {
            background: rgba(44, 123, 229, 0.08);
            transform: translateX(5px);
        }
        
        .activity-type {
            font-weight: 600;
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-right: 10px;
        }
        
        .activity-type.test {
            background-color: rgba(44, 123, 229, 0.15);
            color: var(--primary);
        }
        
        .activity-type.vaccination {
            background-color: rgba(0, 217, 126, 0.15);
            color: var(--success);
        }
        
        .badge-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }
        
        .stat-card-primary { background: linear-gradient(135deg, #2c7be5, #1a5fca); }
        .stat-card-success { background: linear-gradient(135deg, #00d97e, #00b86b); }
        .stat-card-info { background: linear-gradient(135deg, #39afd1, #2a8fb0); }
        .stat-card-warning { background: linear-gradient(135deg, #ffcc00, #e6b800); }
        
        .dashboard-header {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: var(--card-shadow);
        }
        
        .progress {
            border-radius: 10px;
        }
        
        .progress-bar {
            border-radius: 10px;
        }
        
        .inventory-row:hover {
            background-color: rgba(44, 123, 229, 0.03);
        }
        
        .export-btn {
            background: linear-gradient(135deg, var(--primary), #1a5fca);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .export-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(44, 123, 229, 0.3);
        }
        
        .summary-card {
            background: linear-gradient(135deg, #f5f7fb, #e6eefd);
            border-radius: 12px;
            padding: 20px;
        }
        
        .summary-value {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .summary-label {
            color: var(--secondary);
            font-size: 1rem;
        }
        
        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            margin-bottom: 25px;
        }
        
        .section-title {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: var(--dark);
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 12px;
            background: rgba(44, 123, 229, 0.1);
            width: 45px;
            height: 45px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: var(--primary);
        }
        
        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-completed {
            background-color: rgba(0, 217, 126, 0.15);
            color: var(--success);
        }
        
        .status-pending {
            background-color: rgba(255, 204, 0, 0.15);
            color: var(--warning);
        }
        
        .status-approved {
            background-color: rgba(57, 175, 209, 0.15);
            color: var(--info);
        }
        
        .status-canceled {
            background-color: rgba(230, 55, 87, 0.15);
            color: var(--danger);
        }
        
        .inventory-header {
            background-color: rgba(44, 123, 229, 0.05);
            font-weight: 600;
        }
        
        .dashboard-container {
            max-width: 1600px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="dashboard-container">
            <div class="dashboard-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Hospital Analytics Dashboard</h1>
                        <p class="text-muted mb-0">Comprehensive insights and analytics for your hospital operations</p>
                    </div>
                    <div>
                        <button class="export-btn" id="export-report">
                            <i class="fas fa-download me-2"></i>Export Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="row mb-4">
                <div class="col-md-3 mb-4">
                    <div class="card-report">
                        <div class="stat-card stat-card-primary">
                            <div class="stat-icon">
                                <i class="fas fa-vial"></i>
                            </div>
                            <div class="stat-number"><?= $testStats['completed'] ?></div>
                            <div class="stat-label">Tests Completed</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card-report">
                        <div class="stat-card stat-card-success">
                            <div class="stat-icon">
                                <i class="fas fa-syringe"></i>
                            </div>
                            <div class="stat-number"><?= $vaccinationStats['completed'] ?></div>
                            <div class="stat-label">Vaccinations Administered</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card-report">
                        <div class="stat-card stat-card-info">
                            <div class="stat-icon">
                                <i class="fas fa-user-injured"></i>
                            </div>
                            <div class="stat-number"><?= $vaccinationStats['patients'] ?></div>
                            <div class="stat-label">Patients Vaccinated</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card-report">
                        <div class="stat-card stat-card-warning">
                            <div class="stat-icon">
                                <i class="fas fa-virus"></i>
                            </div>
                            <div class="stat-number"><?= $testStats['positive'] ?></div>
                            <div class="stat-label">Positive Cases</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Detailed Reports -->
            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Booking Trends -->
                    <div class="chart-card">
                        <div class="section-title">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Booking Trends (Last 30 Days)</span>
                        </div>
                        <div class="chart-container">
                            <canvas id="bookingTrendsChart"></canvas>
                        </div>
                    </div>

                    <!-- Test Results -->
                    <div class="chart-card">
                        <div class="section-title">
                            <i class="fas fa-vial"></i>
                            <span>COVID-19 Test Results</span>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="chart-container">
                                    <canvas id="testResultsChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between mb-4">
                                    <div class="text-center">
                                        <h3 class="text-primary"><?= $testStats['completed'] ?></h3>
                                        <p class="text-muted mb-0">Total Tests</p>
                                    </div>
                                    <div class="text-center">
                                        <h3 class="text-success"><?= $testStats['negative'] ?></h3>
                                        <p class="text-muted mb-0">Negative</p>
                                    </div>
                                    <div class="text-center">
                                        <h3 class="text-danger"><?= $testStats['positive'] ?></h3>
                                        <p class="text-muted mb-0">Positive</p>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Positive Rate</span>
                                        <span><?= $testStats['total'] > 0 ? round(($testStats['positive'] / $testStats['total']) * 100, 1) : 0 ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 14px;">
                                        <div class="progress-bar bg-danger" role="progressbar" 
                                             style="width: <?= $testStats['total'] > 0 ? ($testStats['positive'] / $testStats['total']) * 100 : 0 ?>%" 
                                             aria-valuenow="<?= $testStats['positive'] ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="<?= $testStats['total'] ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Completion Rate</span>
                                        <span><?= $testStats['total'] > 0 ? round(($testStats['completed'] / $testStats['total']) * 100, 1) : 0 ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 14px;">
                                        <div class="progress-bar bg-primary" role="progressbar" 
                                             style="width: <?= $testStats['total'] > 0 ? ($testStats['completed'] / $testStats['total']) * 100 : 0 ?>%" 
                                             aria-valuenow="<?= $testStats['completed'] ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="<?= $testStats['total'] ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Inventory Status -->
                    <div class="chart-card">
                        <div class="section-title">
                            <i class="fas fa-warehouse"></i>
                            <span>Vaccine Inventory</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="inventory-header">
                                    <tr>
                                        <th>Vaccine</th>
                                        <th>Available</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inventory as $item): 
                                        // Calculate status based on available doses
                                        $status = 'Good';
                                        $statusClass = 'success';
                                        
                                        if ($item['available_doses'] < 10) {
                                            $status = 'Low';
                                            $statusClass = 'danger';
                                        } elseif ($item['available_doses'] < 25) {
                                            $status = 'Medium';
                                            $statusClass = 'warning';
                                        }
                                    ?>
                                    <tr class="inventory-row">
                                        <td><?= $item['vaccine_name'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="fw-bold me-2"><?= $item['available_doses'] ?></span>
                                                <div class="progress w-100 inventory-progress">
                                                    <div class="progress-bar bg-<?= $statusClass ?>" 
                                                         role="progressbar" 
                                                         style="width: <?= min($item['available_doses'], 100) ?>%" 
                                                         aria-valuenow="<?= $item['available_doses'] ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $statusClass ?> status-badge"><?= $status ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="chart-card">
                        <div class="section-title">
                            <i class="fas fa-history"></i>
                            <span>Recent Activities</span>
                        </div>
                        <div class="mt-3">
                            <?php foreach ($recentActivities as $activity): 
                                $typeClass = ($activity['type'] == 'test') ? 'test' : 'vaccination';
                                
                                // Status classes
                                $statusClass = 'status-pending';
                                if ($activity['status'] == 'completed') $statusClass = 'status-completed';
                                elseif ($activity['status'] == 'approved') $statusClass = 'status-approved';
                                elseif ($activity['status'] == 'canceled') $statusClass = 'status-canceled';
                            ?>
                            <div class="activity-item">
                                <div class="d-flex justify-content-between">
                                    <span class="activity-type <?= $typeClass ?>">
                                        <?= ucfirst($activity['type']) ?> #<?= $activity['id'] ?>
                                    </span>
                                    <small class="text-muted"><?= formatDate($activity['created_at']) ?></small>
                                </div>
                                <p class="mb-1">Patient ID: <strong><?= $activity['patient_id'] ?></strong></p>
                                <div class="d-flex justify-content-between mt-2">
                                    <span>
                                        <?php if ($activity['type'] == 'test'): ?>
                                            Result: <span class="fw-bold <?= $activity['result'] == 'positive' ? 'text-danger' : 'text-success' ?>">
                                                <?= ucfirst($activity['result']) ?>
                                            </span>
                                        <?php else: ?>
                                            Vaccine: <span class="fw-bold"><?= $activity['result'] ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="badge <?= $statusClass ?>">
                                        <?= ucfirst($activity['status']) ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Summary Section -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="summary-card">
                        <div class="section-title">
                            <i class="fas fa-file-alt"></i>
                            <span>Monthly Summary</span>
                        </div>
                        <div class="row text-center">
                            <div class="col-md-3 mb-4">
                                <div class="summary-value text-primary"><?= $testStats['completed'] ?></div>
                                <div class="summary-label">Tests Completed</div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <div class="summary-value text-success"><?= $vaccinationStats['completed'] ?></div>
                                <div class="summary-label">Vaccinations Given</div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <div class="summary-value text-info"><?= $vaccinationStats['patients'] ?></div>
                                <div class="summary-label">Patients Served</div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <div class="summary-value text-warning"><?= $testStats['positive'] ?></div>
                                <div class="summary-label">Positive Cases</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Booking Trends Chart
        const trendsCtx = document.getElementById('bookingTrendsChart').getContext('2d');
        const trendsChart = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($dates) ?>,
                datasets: [{
                    label: 'Test Bookings',
                    data: <?= json_encode($testCounts) ?>,
                    borderColor: '#2c7be5',
                    backgroundColor: 'rgba(44, 123, 229, 0.1)',
                    tension: 0.3,
                    fill: true,
                    borderWidth: 3
                }, {
                    label: 'Vaccination Bookings',
                    data: <?= json_encode($vaccinationCounts) ?>,
                    borderColor: '#00d97e',
                    backgroundColor: 'rgba(0, 217, 126, 0.1)',
                    tension: 0.3,
                    fill: true,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(40, 50, 82, 0.9)',
                        padding: 15,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });

        // Test Results Chart
        const resultsCtx = document.getElementById('testResultsChart').getContext('2d');
        const resultsChart = new Chart(resultsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Positive', 'Negative'],
                datasets: [{
                    data: [<?= $testStats['positive'] ?>, <?= $testStats['negative'] ?>],
                    backgroundColor: ['#e63757', '#00d97e'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 13
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(40, 50, 82, 0.9)',
                        padding: 12,
                        bodyFont: {
                            size: 13
                        }
                    }
                }
            }
        });

        // Export functionality
        document.getElementById('export-report').addEventListener('click', function() {
            const button = this;
            const originalText = button.innerHTML;
            
            // Show loading indicator
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating Report...';
            button.disabled = true;
            
            // Use html2canvas to capture the dashboard-container
            html2canvas(document.querySelector('.dashboard-container')).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jspdf.jsPDF('p', 'mm', 'a4');
                const imgProps = pdf.getImageProperties(imgData);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                
                pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                pdf.save('hospital-report-<?= date("Y-m-d") ?>.pdf');
                
                // Restore button
                button.innerHTML = originalText;
                button.disabled = false;
            });
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>