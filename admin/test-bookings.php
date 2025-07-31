<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('admin');

$db = new Database();

// Handle actions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_result':
                $booking_id = (int)$_POST['booking_id'];
                $test_result = $_POST['test_result'];
                $result_date = date('Y-m-d');
                
                $db->query("UPDATE test_bookings SET test_result = :result, result_date = :date, status = 'completed' WHERE id = :id");
                $db->bind(':result', $test_result);
                $db->bind(':date', $result_date);
                $db->bind(':id', $booking_id);
                
                if ($db->execute()) {
                    $success = "Test result updated successfully";
                } else {
                    $error = "Failed to update test result";
                }
                break;
        }
    }
}

// Get all test bookings with patient and hospital details
$db->query("SELECT tb.*, p.full_name as patient_name, p.phone as patient_phone, 
                   h.hospital_name, h.phone as hospital_phone
            FROM test_bookings tb 
            JOIN patients p ON tb.patient_id = p.id 
            JOIN hospitals h ON tb.hospital_id = h.id 
            ORDER BY tb.created_at DESC");
$bookings = $db->resultset();

// Export functionality
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    exportToExcel($bookings, 'test_bookings');
}

// Time formatting helper function
function formatBookingTime($timeString) {
    return date('h:i A', strtotime($timeString));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Bookings - Admin Dashboard</title>
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
                                        <i class="fas fa-vial me-2 text-primary"></i>Test Bookings Management
                                    </h4>
                                    <p class="text-muted mb-0">Monitor and manage all COVID-19 test bookings</p>
                                </div>
                                <div class="col-auto">
                                    <div class="d-flex gap-2">
                                        <select class="form-select" id="statusFilter">
                                            <option value="">All Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="approved">Approved</option>
                                            <option value="completed">Completed</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                        <input type="text" class="form-control search-input" placeholder="Search bookings..." data-target="#bookingsTable">
                                        <a href="?export=excel" class="btn btn-success">
                                            <i class="fas fa-file-excel me-2"></i>Export
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (isset($success)): ?>
                                <div class="alert alert-success alert-custom">
                                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger alert-custom">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="bookingsTable">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>ID</th>
                                            <th>Patient</th>
                                            <th>Hospital</th>
                                            <th>Test Type</th>
                                            <th>Date & Time</th>
                                            <th>Status</th>
                                            <th>Result</th>
                                            <th>Price</th>
                                            <th>Booked On</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bookings as $booking): ?>
                                            <tr data-status="<?php echo strtolower($booking['status']); ?>" data-booking='<?php echo htmlspecialchars(json_encode($booking)); ?>'>
                                                <td class="align-middle"><?php echo $booking['id']; ?></td>
                                                <td class="align-middle">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($booking['patient_name']); ?></strong><br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($booking['patient_phone']); ?></small>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($booking['hospital_name']); ?></strong><br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($booking['hospital_phone']); ?></small>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="badge bg-info"><?php echo $booking['test_type']; ?></span>
                                                </td>
                                                <td class="align-middle">
                                                    <div>
                                                        <strong><?php echo formatDate($booking['booking_date']); ?></strong><br>
                                                        <small class="text-muted"><?php echo formatBookingTime($booking['booking_time']); ?></small>
                                                    </div>
                                                </td>
                                                <td class="align-middle"><?php echo getStatusBadge($booking['status']); ?></td>
                                                <td class="align-middle">
                                                    <?php if (empty($booking['test_result']) || $booking['test_result'] === 'pending'): ?>
                                                        <span class="badge bg-warning">Pending</span>
                                                    <?php else: ?>
                                                        <?php 
                                                            $resultBadges = [
                                                                'negative' => 'bg-success',
                                                                'positive' => 'bg-danger'
                                                            ];
                                                            $badgeClass = $resultBadges[$booking['test_result']] ?? 'bg-secondary';
                                                        ?>
                                                        <span class="badge <?php echo $badgeClass; ?>">
                                                            <?php echo ucfirst($booking['test_result']); ?>
                                                        </span>
                                                        <?php if ($booking['result_date']): ?>
                                                            <br><small class="text-muted"><?php echo formatDate($booking['result_date']); ?></small>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="align-middle">₹<?php echo number_format($booking['price'], 2); ?></td>
                                                <td class="align-middle"><?php echo formatDate($booking['created_at']); ?></td>
                                                <td class="align-middle">
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-info view-booking-btn">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <?php if ($booking['status'] === 'completed' && (empty($booking['test_result']) || $booking['test_result'] === 'pending')): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="updateResult(<?php echo $booking['id']; ?>)">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                        <?php endif; ?>
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

    <!-- Update Result Modal -->
    <div class="modal fade" id="resultModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header gradient-bg text-white">
                    <h5 class="modal-title">Update Test Result</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_result">
                        <input type="hidden" name="booking_id" id="bookingId">
                        
                        <div class="mb-3">
                            <label for="test_result" class="form-label">Test Result</label>
                            <select class="form-select" name="test_result" required>
                                <option value="">Select Result</option>
                                <option value="negative">Negative</option>
                                <option value="positive">Positive</option>
                            </select>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            The result date will be automatically set to today's date.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Result</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header gradient-bg text-white">
                    <h5 class="modal-title">Booking Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="bookingDetails">
                    <!-- Booking details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Booking details functionality
        document.querySelectorAll('.view-booking-btn').forEach(button => {
            button.addEventListener('click', function() {
                const bookingData = JSON.parse(this.closest('tr').dataset.booking);
                populateBookingModal(bookingData);
            });
        });

        function populateBookingModal(booking) {
            // Create custom status badges
            const statusBadges = {
                'pending': 'bg-warning',
                'approved': 'bg-success',
                'completed': 'bg-primary',
                'rejected': 'bg-danger'
            };
            
            const statusBadge = statusBadges[booking.status] || 'bg-secondary';
            
            // Create custom result badges
            const resultBadges = {
                'pending': 'bg-warning',
                'negative': 'bg-success',
                'positive': 'bg-danger'
            };
            
            const resultBadge = resultBadges[booking.test_result] || 'bg-secondary';
            const resultText = booking.test_result ? booking.test_result.charAt(0).toUpperCase() + booking.test_result.slice(1) : 'Pending';
            
            document.getElementById('bookingDetails').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Booking Information</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>ID:</strong></td><td>${booking.id}</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="badge ${statusBadge}">${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}</span></td></tr>
                            <tr><td><strong>Test Type:</strong></td><td>${booking.test_type}</td></tr>
                            <tr><td><strong>Date:</strong></td><td>${booking.booking_date} at ${formatTime(booking.booking_time)}</td></tr>
                            <tr><td><strong>Price:</strong></td><td>₹${parseFloat(booking.price).toFixed(2)}</td></tr>
                            <tr><td><strong>Booked On:</strong></td><td>${booking.created_at}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Test Results</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Result:</strong></td><td><span class="badge ${resultBadge}">${resultText}</span></td></tr>
                            <tr><td><strong>Result Date:</strong></td><td>${booking.result_date || 'N/A'}</td></tr>
                        </table>
                        
                        <h6 class="mt-4">Patient Information</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Name:</strong></td><td>${booking.patient_name}</td></tr>
                            <tr><td><strong>Phone:</strong></td><td>${booking.patient_phone}</td></tr>
                        </table>
                        
                        <h6 class="mt-4">Hospital Information</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Name:</strong></td><td>${booking.hospital_name}</td></tr>
                            <tr><td><strong>Phone:</strong></td><td>${booking.hospital_phone}</td></tr>
                        </table>
                    </div>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('bookingModal')).show();
        }
        
        function formatTime(timeString) {
            const time = new Date('1970-01-01T' + timeString);
            return time.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        }

        function updateResult(bookingId) {
            document.getElementById('bookingId').value = bookingId;
            new bootstrap.Modal(document.getElementById('resultModal')).show();
        }

        // Status filter functionality
        document.getElementById('statusFilter').addEventListener('change', function() {
            const filterValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#bookingsTable tbody tr');
            
            rows.forEach(row => {
                const statusValue = row.getAttribute('data-status');
                if (filterValue === '' || statusValue === filterValue) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>