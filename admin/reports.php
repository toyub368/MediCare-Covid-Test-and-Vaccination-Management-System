<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('admin');

$db = new Database();

// Handle report generation
$report_type = $_GET['type'] ?? 'overview';
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');

// Get statistics based on date range
$db->query("SELECT COUNT(*) as total FROM test_bookings WHERE created_at BETWEEN :from AND :to");
$db->bind(':from', $date_from);
$db->bind(':to', $date_to . ' 23:59:59');
$test_bookings = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM vaccination_bookings WHERE created_at BETWEEN :from AND :to");
$db->bind(':from', $date_from);
$db->bind(':to', $date_to . ' 23:59:59');
$vaccination_bookings = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM patients WHERE created_at BETWEEN :from AND :to");
$db->bind(':from', $date_from);
$db->bind(':to', $date_to . ' 23:59:59');
$new_patients = $db->single()['total'];

$db->query("SELECT COUNT(*) as total FROM hospitals WHERE created_at BETWEEN :from AND :to AND status = 'approved'");
$db->bind(':from', $date_from);
$db->bind(':to', $date_to . ' 23:59:59');
$new_hospitals = $db->single()['total'];

// Test results breakdown
$db->query("SELECT COALESCE(test_result, 'pending') as test_result, COUNT(*) as count 
            FROM test_bookings 
            WHERE created_at BETWEEN :from AND :to 
            GROUP BY test_result");
$db->bind(':from', $date_from);
$db->bind(':to', $date_to . ' 23:59:59');
$test_results = $db->resultset();

// Vaccination status breakdown
$db->query("SELECT COALESCE(status, 'unknown') as status, COUNT(*) as count 
            FROM vaccination_bookings 
            WHERE created_at BETWEEN :from AND :to 
            GROUP BY status");
$db->bind(':from', $date_from);
$db->bind(':to', $date_to . ' 23:59:59');
$vaccination_status = $db->resultset();

// Top hospitals by bookings
$db->query("SELECT h.hospital_name, h.city, 
                   COUNT(tb.id) as test_count, 
                   COUNT(vb.id) as vaccine_count,
                   (COUNT(tb.id) + COUNT(vb.id)) as total_bookings
            FROM hospitals h 
            LEFT JOIN test_bookings tb ON h.id = tb.hospital_id AND tb.created_at BETWEEN :from AND :to
            LEFT JOIN vaccination_bookings vb ON h.id = vb.hospital_id AND vb.created_at BETWEEN :from2 AND :to2
            WHERE h.status = 'approved'
            GROUP BY h.id 
            ORDER BY total_bookings DESC 
            LIMIT 10");
$db->bind(':from', $date_from);
$db->bind(':to', $date_to . ' 23:59:59');
$db->bind(':from2', $date_from);
$db->bind(':to2', $date_to . ' 23:59:59');
$top_hospitals = $db->resultset();

// Export functionality
if (isset($_GET['export'])) {
    $export_data = [];
    switch ($_GET['export']) {
        case 'overview':
            $export_data = [
                ['Metric', 'Count'],
                ['Test Bookings', $test_bookings],
                ['Vaccination Bookings', $vaccination_bookings],
                ['New Patients', $new_patients],
                ['New Hospitals', $new_hospitals]
            ];
            break;
            
        case 'test_results':
            $export_data = [['Test Result', 'Count']];
            foreach ($test_results as $result) {
                $export_data[] = [$result['test_result'], $result['count']];
            }
            break;
            
        case 'vaccination_status':
            $export_data = [['Status', 'Count']];
            foreach ($vaccination_status as $status) {
                $export_data[] = [$status['status'], $status['count']];
            }
            break;
            
        case 'hospitals':
            $export_data = $top_hospitals;
            break;
    }
    
    if (!empty($export_data)) {
        exportToExcel($export_data, 'report_' . $_GET['export'] . '_' . date('Y-m-d'));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container-fluid">
            <!-- Report Filters -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" class="form-control" name="date_from" value="<?php echo $date_from; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" class="form-control" name="date_to" value="<?php echo $date_to; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="type" class="form-label">Report Type</label>
                            <select class="form-select" name="type">
                                <option value="overview" <?php echo $report_type === 'overview' ? 'selected' : ''; ?>>Overview</option>
                                <option value="detailed" <?php echo $report_type === 'detailed' ? 'selected' : ''; ?>>Detailed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Overview Statistics -->
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-vial"></i>
                        </div>
                        <h3 class="mb-1"><?php echo number_format($test_bookings); ?></h3>
                        <p class="text-muted mb-0">Test Bookings</p>
                        <small class="text-muted">
                            <?php echo formatDate($date_from); ?> to <?php echo formatDate($date_to); ?>
                        </small>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <i class="fas fa-syringe"></i>
                        </div>
                        <h3 class="mb-1"><?php echo number_format($vaccination_bookings); ?></h3>
                        <p class="text-muted mb-0">Vaccination Bookings</p>
                        <small class="text-muted">
                            <?php echo formatDate($date_from); ?> to <?php echo formatDate($date_to); ?>
                        </small>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="mb-1"><?php echo number_format($new_patients); ?></h3>
                        <p class="text-muted mb-0">New Patients</p>
                        <small class="text-muted">
                            <?php echo formatDate($date_from); ?> to <?php echo formatDate($date_to); ?>
                        </small>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                            <i class="fas fa-hospital"></i>
                        </div>
                        <h3 class="mb-1"><?php echo number_format($new_hospitals); ?></h3>
                        <p class="text-muted mb-0">New Hospitals</p>
                        <small class="text-muted">
                            <?php echo formatDate($date_from); ?> to <?php echo formatDate($date_to); ?>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="chart-container">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie me-2 text-primary"></i>Test Results Breakdown
                            </h5>
                            <a href="?export=test_results&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-download me-1"></i>Export
                            </a>
                        </div>
                        <canvas id="testResultsChart"></canvas>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="chart-container">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie me-2 text-primary"></i>Vaccination Status
                            </h5>
                            <a href="?export=vaccination_status&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-download me-1"></i>Export
                            </a>
                        </div>
                        <canvas id="vaccinationStatusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Hospitals Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-trophy me-2 text-primary"></i>Top Performing Hospitals
                                </h5>
                                <a href="?export=hospitals&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>" class="btn btn-success">
                                    <i class="fas fa-file-excel me-2"></i>Export
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Rank</th>
                                            <th>Hospital Name</th>
                                            <th>City</th>
                                            <th>Test Bookings</th>
                                            <th>Vaccination Bookings</th>
                                            <th>Total Bookings</th>
                                            <th>Performance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($top_hospitals as $index => $hospital): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-<?php echo $index < 3 ? 'warning' : 'secondary'; ?>">
                                                        #<?php echo $index + 1; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($hospital['hospital_name']); ?></td>
                                                <td><?php echo htmlspecialchars($hospital['city']); ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo $hospital['test_count']; ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success"><?php echo $hospital['vaccine_count']; ?></span>
                                                </td>
                                                <td>
                                                    <strong><?php echo $hospital['total_bookings']; ?></strong>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar" role="progressbar" 
                                                             style="width: <?php echo $hospital['total_bookings'] > 0 ? ($hospital['total_bookings'] / max(array_column($top_hospitals, 'total_bookings')) * 100) : 0; ?>%">
                                                            <?php echo $hospital['total_bookings']; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Test Results Chart
            const testResultsData = <?php echo json_encode($test_results); ?>;
            const testLabels = testResultsData.map(item => item.test_result.charAt(0).toUpperCase() + item.test_result.slice(1));
            const testCounts = testResultsData.map(item => parseInt(item.count));
            
            const testCtx = document.getElementById('testResultsChart').getContext('2d');
            new Chart(testCtx, {
                type: 'pie',
                data: {
                    labels: testLabels,
                    datasets: [{
                        data: testCounts,
                        backgroundColor: ['#10b981', '#ef4444', '#f59e0b'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Vaccination Status Chart - Pie Chart with percentages
            const vaccinationStatusData = <?php echo json_encode($vaccination_status); ?>;
            const vaccinationLabels = vaccinationStatusData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1));
            const vaccinationCounts = vaccinationStatusData.map(item => parseInt(item.count));
            
            const vaccinationCtx = document.getElementById('vaccinationStatusChart').getContext('2d');
            
            if (vaccinationCounts.length > 0 && vaccinationCounts.some(count => count > 0)) {
                new Chart(vaccinationCtx, {
                    type: 'pie',
                    data: {
                        labels: vaccinationLabels,
                        datasets: [{
                            data: vaccinationCounts,
                            backgroundColor: [
                                '#f59e0b', // Pending/Unknown
                                '#10b981', // Completed
                                '#2563eb', // Scheduled
                                '#ef4444'  // Cancelled
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                vaccinationCtx.fillStyle = '#999';
                vaccinationCtx.font = '16px Arial';
                vaccinationCtx.textAlign = 'center';
                vaccinationCtx.fillText('No vaccination data available', vaccinationCtx.canvas.width / 2, vaccinationCtx.canvas.height / 2);
            }
        });
    </script>
</body>
</html>