<?php

require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('patient');

// Define missing functions if needed
if (!function_exists('formatDate')) {
    function formatDate($date) {
        if (empty($date) || $date === '0000-00-00') return 'N/A';
        return date('M d, Y', strtotime($date));
    }
}

if (!function_exists('formatTime')) {
    function formatTime($time) {
        if (empty($time)) return 'N/A';
        return date('h:i A', strtotime($time));
    }
}

// Export functionality - moved to top
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $db = new Database();
    $patient_id = $_SESSION['user_id'];

    // Get test bookings
    $db->query("SELECT tb.*, h.hospital_name 
                FROM test_bookings tb 
                JOIN hospitals h ON tb.hospital_id = h.id 
                WHERE tb.patient_id = :patient_id");
    $db->bind(':patient_id', $patient_id);
    $test_bookings = $db->resultset();

    // Get vaccination bookings
    $db->query("SELECT vb.*, h.hospital_name 
                FROM vaccination_bookings vb 
                JOIN hospitals h ON vb.hospital_id = h.id 
                WHERE vb.patient_id = :patient_id");
    $db->bind(':patient_id', $patient_id);
    $vaccination_bookings = $db->resultset();

    // Prepare data
    $all_appointments = [];
    
    foreach ($test_bookings as $booking) {
        $all_appointments[] = [
            'Type' => 'Test',
            'Hospital' => $booking['hospital_name'],
            'Service' => $booking['test_type'],
            'Date' => formatDate($booking['booking_date']),
            'Time' => formatTime($booking['booking_time']),
            'Status' => $booking['status'],
            'Result' => $booking['test_result'] ?? 'N/A',
            'Price' => $booking['price'] ?? 'N/A',
            'Booked On' => formatDate($booking['created_at'])
        ];
    }
    
    foreach ($vaccination_bookings as $booking) {
        $all_appointments[] = [
            'Type' => 'Vaccination',
            'Hospital' => $booking['hospital_name'],
            'Service' => ($booking['vaccine_name'] ?? 'N/A') . ' - Dose ' . ($booking['dose_number'] ?? 'N/A'),
            'Date' => formatDate($booking['booking_date']),
            'Time' => formatTime($booking['booking_time']),
            'Status' => $booking['status'],
            'Result' => $booking['certificate_number'] ?? 'N/A',
            'Price' => 'N/A',
            'Booked On' => formatDate($booking['created_at'])
        ];
    }

    // CSV headers
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=my_appointments.csv');
    
    $output = fopen('php://output', 'w');
    
    // Header row
    if (!empty($all_appointments)) {
        fputcsv($output, array_keys($all_appointments[0]));
        
        // Data rows
        foreach ($all_appointments as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit;
}

// Normal page processing
$db = new Database();
$patient_id = $_SESSION['user_id'];

// Get test bookings
$db->query("SELECT tb.*, h.hospital_name, h.phone as hospital_phone 
            FROM test_bookings tb 
            JOIN hospitals h ON tb.hospital_id = h.id 
            WHERE tb.patient_id = :patient_id 
            ORDER BY tb.created_at DESC");
$db->bind(':patient_id', $patient_id);
$test_bookings = $db->resultset();

// Get vaccination bookings
$db->query("SELECT vb.*, h.hospital_name, h.phone as hospital_phone 
            FROM vaccination_bookings vb 
            JOIN hospitals h ON vb.hospital_id = h.id 
            WHERE vb.patient_id = :patient_id 
            ORDER BY vb.created_at DESC");
$db->bind(':patient_id', $patient_id);
$vaccination_bookings = $db->resultset();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - Patient Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        /* ... existing styles ... */
        
        /* Modal styles */
        .modal-details dt {
            font-weight: 600;
            width: 150px;
        }
        .modal-details dd {
            margin-left: 170px;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="fas fa-calendar-alt me-2 text-primary"></i>My Appointments
                            </h4>
                            <p class="text-muted mb-0">View and manage your COVID-19 test and vaccination appointments</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="book-test.php" class="btn btn-outline-primary">
                                <i class="fas fa-vial me-2"></i>Book Test
                            </a>
                            <a href="book-vaccination.php" class="btn btn-outline-success">
                                <i class="fas fa-syringe me-2"></i>Book Vaccination
                            </a>
                            <a href="?export=excel" class="btn btn-success">
                                <i class="fas fa-file-excel me-2"></i>Export
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Appointments -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0">
                                <i class="fas fa-vial me-2 text-primary"></i>COVID-19 Test Appointments
                                <span class="badge bg-primary ms-2"><?php echo count($test_bookings); ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($test_bookings)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-vial fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">No test appointments</h6>
                                    <p class="text-muted">You haven't booked any COVID-19 tests yet.</p>
                                    <a href="book-test.php" class="btn btn-primary">Book Your First Test</a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Test Type</th>
                                                <th>Hospital</th>
                                                <th>Appointment</th>
                                                <th>Status</th>
                                                <th>Test Result</th>
                                                <th>Price</th>
                                                <th>Booked On</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($test_bookings as $booking): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo htmlspecialchars($booking['test_type']); ?></span>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($booking['hospital_name']); ?></strong><br>
                                                            <small class="text-muted">
                                                                <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($booking['hospital_phone']); ?>
                                                            </small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong><?php echo formatDate($booking['booking_date']); ?></strong><br>
                                                            <small class="text-muted"><?php echo formatTime($booking['booking_time']); ?></small>
                                                        </div>
                                                    </td>
                                                    <td><?php echo getStatusBadge($booking['status']); ?></td>
                                                    <td>
                                                        <?php if (($booking['test_result'] ?? 'pending') === 'pending'): ?>
                                                            <span class="badge bg-warning">Pending</span>
                                                        <?php else: ?>
                                                            <?php echo getStatusBadge($booking['test_result']); ?>
                                                            <?php if (!empty($booking['result_date'])): ?>
                                                                <br><small class="text-muted"><?php echo formatDate($booking['result_date']); ?></small>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>PKR <?php echo number_format($booking['price'] ?? 0, 2); ?></td>
                                                    <td><?php echo formatDate($booking['created_at']); ?></td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <?php if (($booking['test_result'] ?? '') !== 'pending' && ($booking['status'] ?? '') === 'completed'): ?>
                                                                <a href="download_test_report.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-download"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                            <button type="button" class="btn btn-sm btn-outline-info view-details" data-type="test" data-id="<?php echo $booking['id']; ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vaccination Appointments -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0">
                                <i class="fas fa-syringe me-2 text-success"></i>Vaccination Appointments
                                <span class="badge bg-success ms-2"><?php echo count($vaccination_bookings); ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($vaccination_bookings)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-syringe fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">No vaccination appointments</h6>
                                    <p class="text-muted">You haven't booked any vaccinations yet.</p>
                                    <a href="book-vaccination.php" class="btn btn-success">Book Vaccination</a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Vaccine & Dose</th>
                                                <th>Hospital</th>
                                                <th>Appointment</th>
                                                <th>Status</th>
                                                <th>Certificate</th>
                                                <th>Booked On</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($vaccination_bookings as $booking): ?>
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <span class="badge bg-success"><?php echo htmlspecialchars($booking['vaccine_name']); ?></span><br>
                                                            <span class="badge bg-info mt-1">Dose <?php echo htmlspecialchars($booking['dose_number']); ?></span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($booking['hospital_name']); ?></strong><br>
                                                            <small class="text-muted">
                                                                <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($booking['hospital_phone']); ?>
                                                            </small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong><?php echo formatDate($booking['booking_date']); ?></strong><br>
                                                            <small class="text-muted"><?php echo formatTime($booking['booking_time']); ?></small>
                                                        </div>
                                                    </td>
                                                    <td><?php echo getStatusBadge($booking['status']); ?></td>
                                                    <td>
                                                        <?php if (!empty($booking['certificate_number'])): ?>
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($booking['certificate_number']); ?></strong><br>
                                                                <small class="text-muted"><?php echo formatDate($booking['vaccination_date']); ?></small>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">Not issued</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo formatDate($booking['created_at']); ?></td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <?php if (!empty($booking['certificate_number'])): ?>
                                                                <a href="download_certificate.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-success">
                                                                    <i class="fas fa-certificate"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                            <button type="button" class="btn btn-sm btn-outline-info view-details" data-type="vaccination" data-id="<?php echo $booking['id']; ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Appointment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Details will be loaded here via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // View details with modal
        document.querySelectorAll('.view-details').forEach(button => {
            button.addEventListener('click', function() {
                const type = this.dataset.type;
                const id = this.dataset.id;
                
                fetch(`get_appointment_details.php?type=${type}&id=${id}`)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('modalBody').innerHTML = data;
                        document.getElementById('modalTitle').textContent = `${type.charAt(0).toUpperCase() + type.slice(1)} Appointment Details`;
                        const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
                        modal.show();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('modalBody').innerHTML = `<div class="alert alert-danger">Failed to load details</div>`;
                        const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
                        modal.show();
                    });
            });
        });
    </script>
</body>
</html>