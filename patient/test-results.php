<?php

require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('patient');

$db = new Database();
$patient_id = $_SESSION['user_id'];

// Get all test results
$db->query("SELECT tb.*, h.hospital_name, h.phone as hospital_phone, h.address as hospital_address 
           FROM test_bookings tb 
           JOIN hospitals h ON tb.hospital_id = h.id 
           WHERE tb.patient_id = :patient_id AND tb.test_result != 'pending'
           ORDER BY tb.result_date DESC, tb.created_at DESC");
$db->bind(':patient_id', $patient_id);
$test_results = $db->resultset();

// Get patient info for reports
$db->query("SELECT * FROM patients WHERE id = :id");
$db->bind(':id', $patient_id);
$patient_info = $db->single();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Results - Patient Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 pb-0">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h4 class="mb-0">
                                        <i class="fas fa-file-medical me-2 text-primary"></i>COVID-19 Test Results
                                    </h4>
                                    <p class="text-muted mb-0">View and download your test reports</p>
                                </div>
                                <div class="col-auto">
                                    <a href="book-test.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Book New Test
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($test_results)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-file-medical fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No test results available</h5>
                                    <p class="text-muted">You don't have any completed test results yet. Book a test to get started.</p>
                                    <a href="book-test.php" class="btn btn-primary">Book COVID Test</a>
                                </div>
                            <?php else: ?>
                                <div class="row g-4">
                                    <?php foreach ($test_results as $result): ?>
                                        <div class="col-lg-6">
                                            <div class="card border-0 shadow-sm h-100 test-result-card">
                                                <div class="card-header bg-light border-0">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h6 class="mb-0"><?php echo $result['test_type']; ?> Test</h6>
                                                            <small class="text-muted">Test ID: #<?php echo $result['id']; ?></small>
                                                        </div>
                                                        <div class="text-end">
                                                            <?php echo getStatusBadge($result['test_result']); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row mb-3">
                                                        <div class="col-6">
                                                            <small class="text-muted">Test Date</small>
                                                            <div class="fw-bold"><?php echo formatDate($result['booking_date']); ?></div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted">Result Date</small>
                                                            <div class="fw-bold"><?php echo formatDate($result['result_date']); ?></div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <small class="text-muted">Hospital</small>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($result['hospital_name']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($result['hospital_phone']); ?></small>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <small class="text-muted">Test Result</small>
                                                        <div class="d-flex align-items-center">
                                                            <?php if ($result['test_result'] === 'negative'): ?>
                                                                <i class="fas fa-check-circle text-success me-2 fa-lg"></i>
                                                                <span class="fw-bold text-success">NEGATIVE</span>
                                                            <?php else: ?>
                                                                <i class="fas fa-exclamation-triangle text-danger me-2 fa-lg"></i>
                                                                <span class="fw-bold text-danger">POSITIVE</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if ($result['test_result'] === 'positive'): ?>
                                                        <div class="alert alert-warning">
                                                            <i class="fas fa-info-circle me-2"></i>
                                                            <strong>Important:</strong> Please follow isolation guidelines and consult with healthcare professionals.
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="alert alert-success">
                                                            <i class="fas fa-check-circle me-2"></i>
                                                            <strong>Good news:</strong> Your test result is negative. Continue following safety protocols.
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="card-footer bg-white border-0">
                                                    <div class="d-grid gap-2 d-md-flex">
                                                        <button type="button" class="btn btn-outline-primary flex-fill" onclick="downloadReport(<?php echo $result['id']; ?>)">
                                                            <i class="fas fa-download me-2"></i>Download Report
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info flex-fill" onclick="shareReport(<?php echo $result['id']; ?>)">
                                                            <i class="fas fa-share me-2"></i>Share
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Test Summary -->
            <?php if (!empty($test_results)): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 pb-0">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-pie me-2 text-primary"></i>Test Summary
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="p-3">
                                            <i class="fas fa-vial fa-2x text-primary mb-2"></i>
                                            <h4><?php echo count($test_results); ?></h4>
                                            <p class="text-muted mb-0">Total Tests</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3">
                                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                            <h4><?php echo count(array_filter($test_results, function($r) { return $r['test_result'] === 'negative'; })); ?></h4>
                                            <p class="text-muted mb-0">Negative Results</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3">
                                            <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                                            <h4><?php echo count(array_filter($test_results, function($r) { return $r['test_result'] === 'positive'; })); ?></h4>
                                            <p class="text-muted mb-0">Positive Results</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3">
                                            <i class="fas fa-calendar fa-2x text-info mb-2"></i>
                                            <h4><?php echo !empty($test_results) ? formatDate($test_results[0]['result_date']) : 'N/A'; ?></h4>
                                            <p class="text-muted mb-0">Latest Test</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header gradient-bg text-white">
                    <h5 class="modal-title">COVID-19 Test Report</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="reportContent">
                    <!-- Report content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="printReport()">
                        <i class="fas fa-print me-2"></i>Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        function downloadReport(testId) {
            // Generate and download PDF report
            window.open(`generate_test_report.php?id=${testId}`, '_blank');
        }

        function shareReport(testId) {
            // Show share options
            if (navigator.share) {
                navigator.share({
                    title: 'COVID-19 Test Report',
                    text: 'My COVID-19 test report',
                    url: `${window.location.origin}/patient/generate_test_report.php?id=${testId}`
                });
            } else {
                // Fallback for browsers that don't support Web Share API
                const url = `${window.location.origin}/patient/generate_test_report.php?id=${testId}`;
                navigator.clipboard.writeText(url).then(() => {
                    showAlert('Report link copied to clipboard!', 'success');
                });
            }
        }

        function printReport() {
            window.print();
        }
    </script>
    
    <style>
        .test-result-card {
            transition: transform 0.2s ease-in-out;
        }
        
        .test-result-card:hover {
            transform: translateY(-5px);
        }
        
        @media print {
            .sidebar, .navbar, .btn, .no-print {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</body>
</html>