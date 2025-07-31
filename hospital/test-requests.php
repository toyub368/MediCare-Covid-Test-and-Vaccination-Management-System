<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('hospital');

// ADD THIS TIME FORMATTING FUNCTION
function formatTime($time) {
    if (!$time) return '';
    return date('h:i A', strtotime($time));
}

$db = new Database();
$hospital_id = $_SESSION['user_id'];

// Handle actions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'approve':
                $booking_id = (int)$_POST['booking_id'];
                $db->query("UPDATE test_bookings SET status = 'approved' WHERE id = :id AND hospital_id = :hospital_id");
                $db->bind(':id', $booking_id);
                $db->bind(':hospital_id', $hospital_id);
                
                if ($db->execute()) {
                    $success = "Test booking approved successfully";
                } else {
                    $error = "Failed to approve test booking";
                }
                break;
                
            case 'reject':
                $booking_id = (int)$_POST['booking_id'];
                $db->query("UPDATE test_bookings SET status = 'rejected' WHERE id = :id AND hospital_id = :hospital_id");
                $db->bind(':id', $booking_id);
                $db->bind(':hospital_id', $hospital_id);
                
                if ($db->execute()) {
                    $success = "Test booking rejected successfully";
                } else {
                    $error = "Failed to reject test booking";
                }
                break;
                
            case 'update_result':
                $booking_id = (int)$_POST['booking_id'];
                $test_result = $_POST['test_result'];
                $result_date = date('Y-m-d');
                
                $db->query("UPDATE test_bookings SET test_result = :result, result_date = :date, status = 'completed' WHERE id = :id AND hospital_id = :hospital_id");
                $db->bind(':result', $test_result);
                $db->bind(':date', $result_date);
                $db->bind(':id', $booking_id);
                $db->bind(':hospital_id', $hospital_id);
                
                if ($db->execute()) {
                    $success = "Test result updated successfully";
                } else {
                    $error = "Failed to update test result";
                }
                break;
        }
    }
}

// Get all test bookings for this hospital
$db->query("SELECT tb.*, p.full_name as patient_name, p.phone as patient_phone, p.email as patient_email
            FROM test_bookings tb 
            JOIN patients p ON tb.patient_id = p.id 
            WHERE tb.hospital_id = :hospital_id 
            ORDER BY tb.created_at DESC");
$db->bind(':hospital_id', $hospital_id);
$bookings = $db->resultset();

// Export functionality
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    exportToExcel($bookings, 'test_requests');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Requests - Hospital Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        /* FIX TABLE ALIGNMENT */
        #requestsTable th:nth-child(1), 
        #requestsTable td:nth-child(1) {
            width: 5%;
        }
        
        #requestsTable th:nth-child(3), 
        #requestsTable td:nth-child(3) {
            width: 10%;
        }
        
        #requestsTable th:nth-child(4), 
        #requestsTable td:nth-child(4) {
            width: 12%;
        }
        
        #requestsTable th:nth-child(5), 
        #requestsTable td:nth-child(5) {
            width: 10%;
        }
        
        #requestsTable th:nth-child(6), 
        #requestsTable td:nth-child(6) {
            width: 12%;
        }
        
        #requestsTable th:nth-child(7), 
        #requestsTable td:nth-child(7) {
            width: 8%;
        }
        
        #requestsTable th:nth-child(8), 
        #requestsTable td:nth-child(8) {
            width: 10%;
        }
        
        #requestsTable th:nth-child(9), 
        #requestsTable td:nth-child(9) {
            width: 15%;
            white-space: nowrap;
        }
        
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        /* Details modal styling */
        .detail-item {
            display: flex;
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            min-width: 150px;
        }
    </style>
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
                                        <i class="fas fa-vial me-2 text-primary"></i>Test Requests Management
                                    </h4>
                                    <p class="text-muted mb-0">Manage COVID-19 test bookings and results</p>
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
                                        <input type="text" class="form-control search-input" placeholder="Search requests..." data-target="#requestsTable">
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
                                <table class="table table-hover" id="requestsTable">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>ID</th>
                                            <th>Patient Details</th>
                                            <th>Test Type</th>
                                            <th>Appointment</th>
                                            <th>Status</th>
                                            <th>Test Result</th>
                                            <th>Price</th>
                                            <th>Requested On</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bookings as $booking): ?>
                                            <tr data-id="<?= $booking['id'] ?>"
                                                data-patient-name="<?= htmlspecialchars($booking['patient_name']) ?>"
                                                data-patient-phone="<?= htmlspecialchars($booking['patient_phone']) ?>"
                                                data-patient-email="<?= htmlspecialchars($booking['patient_email']) ?>"
                                                data-test-type="<?= htmlspecialchars($booking['test_type']) ?>"
                                                data-booking-date="<?= formatDate($booking['booking_date']) ?>"
                                                data-booking-time="<?= formatTime($booking['booking_time']) ?>"
                                                data-status="<?= $booking['status'] ?>"
                                                data-test-result="<?= $booking['test_result'] ?? '' ?>"
                                                data-result-date="<?= $booking['result_date'] ? formatDate($booking['result_date']) : '' ?>"
                                                data-price="<?= $booking['price'] ?>"
                                                data-created-at="<?= formatDate($booking['created_at']) ?>"
                                                data-notes="<?= htmlspecialchars($booking['notes'] ?? '') ?>">
                                                <td><?php echo $booking['id']; ?></td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($booking['patient_name']); ?></strong><br>
                                                        <small class="text-muted">
                                                            <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($booking['patient_phone']); ?><br>
                                                            <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($booking['patient_email']); ?>
                                                        </small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo $booking['test_type']; ?></span>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo formatDate($booking['booking_date']); ?></strong><br>
                                                        <!-- FIXED TIME FORMATTING -->
                                                        <small class="text-muted"><?php echo formatTime($booking['booking_time']); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= getStatusBadge($booking['status']) ?>">
                                                        <?= ucfirst($booking['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($booking['test_result'] === 'pending' || empty($booking['test_result'])): ?>
                                                        <span class="badge bg-warning">Pending</span>
                                                    <?php else: ?>
                                                        <!-- FIXED RESULT STATUS BADGE -->
                                                        <span class="badge bg-<?= $booking['test_result'] === 'positive' ? 'danger' : 'success' ?>">
                                                            <?= ucfirst($booking['test_result']) ?>
                                                        </span>
                                                        <?php if ($booking['result_date']): ?>
                                                            <br><small class="text-muted"><?php echo formatDate($booking['result_date']); ?></small>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>₹<?php echo number_format($booking['price'], 2); ?></td>
                                                <td><?php echo formatDate($booking['created_at']); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <?php if ($booking['status'] === 'pending'): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="approveRequest(<?php echo $booking['id']; ?>)">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="rejectRequest(<?php echo $booking['id']; ?>)">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        <?php elseif ($booking['status'] === 'approved' || ($booking['status'] === 'completed' && $booking['test_result'] === 'pending')): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="updateResult(<?php echo $booking['id']; ?>)">
                                                                <i class="fas fa-edit"></i> Result
                                                            </button>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="viewDetails(<?php echo $booking['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
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
                            The result date will be automatically set to today's date and status will be updated to "Completed".
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
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header gradient-bg text-white">
                    <h5 class="modal-title">Test Booking Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="bookingDetails">
                    <!-- Details will be populated here by JavaScript -->
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
        function approveRequest(bookingId) {
            if (confirm('Are you sure you want to approve this test request?')) {
                submitAction('approve', bookingId);
            }
        }

        function rejectRequest(bookingId) {
            if (confirm('Are you sure you want to reject this test request?')) {
                submitAction('reject', bookingId);
            }
        }

        function updateResult(bookingId) {
            document.getElementById('bookingId').value = bookingId;
            new bootstrap.Modal(document.getElementById('resultModal')).show();
        }

        function viewDetails(bookingId) {
            // Find the booking row
            const row = document.querySelector(`tr[data-id="${bookingId}"]`);
            if (!row) return;
            
            // Get all data attributes
            const data = {
                id: row.getAttribute('data-id'),
                patientName: row.getAttribute('data-patient-name'),
                patientPhone: row.getAttribute('data-patient-phone'),
                patientEmail: row.getAttribute('data-patient-email'),
                testType: row.getAttribute('data-test-type'),
                bookingDate: row.getAttribute('data-booking-date'),
                bookingTime: row.getAttribute('data-booking-time'),
                status: row.getAttribute('data-status'),
                testResult: row.getAttribute('data-test-result'),
                resultDate: row.getAttribute('data-result-date'),
                price: row.getAttribute('data-price'),
                createdAt: row.getAttribute('data-created-at'),
                notes: row.getAttribute('data-notes') || 'No notes available'
            };
            
            // Map status to badge class
            const statusBadgeClass = {
                'pending': 'warning',
                'approved': 'primary',
                'completed': 'success',
                'rejected': 'danger'
            };
            
            // Map test result to badge class
            const resultBadgeClass = {
                'positive': 'danger',
                'negative': 'success',
                'pending': 'warning'
            };
            
            // Create HTML content for modal
            const detailsContent = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">Booking ID:</div>
                            <div>${data.id}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Patient Name:</div>
                            <div>${data.patientName}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Phone:</div>
                            <div>${data.patientPhone}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Email:</div>
                            <div>${data.patientEmail}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Test Type:</div>
                            <div><span class="badge bg-info">${data.testType}</span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">Appointment Date:</div>
                            <div>${data.bookingDate}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Appointment Time:</div>
                            <div>${data.bookingTime}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Status:</div>
                            <div>
                                <span class="badge bg-${statusBadgeClass[data.status] || 'secondary'}">
                                    ${data.status.charAt(0).toUpperCase() + data.status.slice(1)}
                                </span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Price:</div>
                            <div>₹${parseFloat(data.price).toFixed(2)}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Requested On:</div>
                            <div>${data.createdAt}</div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="detail-item">
                            <div class="detail-label">Test Result:</div>
                            <div>
                                ${data.testResult ? 
                                    `<span class="badge bg-${resultBadgeClass[data.testResult] || 'secondary'}">
                                        ${data.testResult.charAt(0).toUpperCase() + data.testResult.slice(1)}
                                    </span>` : 
                                    '<span class="badge bg-warning">Pending</span>'
                                }
                                ${data.resultDate ? `(on ${data.resultDate})` : ''}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="detail-item">
                            <div class="detail-label">Notes:</div>
                            <div>${data.notes}</div>
                        </div>
                    </div>
                </div>
            `;
            
            // Set content and show modal
            document.getElementById('bookingDetails').innerHTML = detailsContent;
            new bootstrap.Modal(document.getElementById('detailsModal')).show();
        }

        function submitAction(action, bookingId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="${action}">
                <input type="hidden" name="booking_id" value="${bookingId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        // Status filter functionality
        document.getElementById('statusFilter').addEventListener('change', function() {
            const filterValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#requestsTable tbody tr');
            
            rows.forEach(row => {
                const statusCell = row.cells[4].textContent.toLowerCase();
                if (filterValue === '' || statusCell.includes(filterValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>