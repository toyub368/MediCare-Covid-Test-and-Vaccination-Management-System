<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('hospital');

// Fallback time formatting function
if (!function_exists('formatTime')) {
    function formatTime($timeStr) {
        if (empty($timeStr)) return 'N/A';
        return date('h:i A', strtotime($timeStr));
    }
}

$db = new Database();
$hospital_id = $_SESSION['user_id'];
$hospital_name = $_SESSION['hospital_name'] ?? 'Our Hospital'; // Get hospital name from session

// Handle actions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'approve':
                $booking_id = (int)$_POST['booking_id'];
                $db->query("UPDATE vaccination_bookings SET status = 'approved' WHERE id = :id AND hospital_id = :hospital_id");
                $db->bind(':id', $booking_id);
                $db->bind(':hospital_id', $hospital_id);
                
                if ($db->execute()) {
                    $success = "Vaccination booking approved successfully";
                } else {
                    $error = "Failed to approve vaccination booking";
                }
                break;
                
            case 'reject':
                $booking_id = (int)$_POST['booking_id'];
                $db->query("UPDATE vaccination_bookings SET status = 'rejected' WHERE id = :id AND hospital_id = :hospital_id");
                $db->bind(':id', $booking_id);
                $db->bind(':hospital_id', $hospital_id);
                
                if ($db->execute()) {
                    $success = "Vaccination booking rejected successfully";
                } else {
                    $error = "Failed to reject vaccination booking";
                }
                break;
                
            case 'complete':
                $booking_id = (int)$_POST['booking_id'];
                $vaccination_date = date('Y-m-d');
                $certificate_number = generateCertificateNumber();
                
                $db->query("UPDATE vaccination_bookings SET status = 'completed', vaccination_date = :vac_date, certificate_number = :cert WHERE id = :id AND hospital_id = :hospital_id");
                $db->bind(':vac_date', $vaccination_date);
                $db->bind(':cert', $certificate_number);
                $db->bind(':id', $booking_id);
                $db->bind(':hospital_id', $hospital_id);
                
                if ($db->execute()) {
                    // Update vaccine inventory
                    $db->query("SELECT vaccine_name FROM vaccination_bookings WHERE id = :id");
                    $db->bind(':id', $booking_id);
                    $booking_info = $db->single();
                    
                    $db->query("UPDATE vaccine_inventory SET available_doses = available_doses - 1 WHERE hospital_id = :hospital_id AND vaccine_name = :vaccine_name AND available_doses > 0");
                    $db->bind(':hospital_id', $hospital_id);
                    $db->bind(':vaccine_name', $booking_info['vaccine_name']);
                    $db->execute();
                    
                    $success = "Vaccination completed successfully. Certificate number: " . $certificate_number;
                } else {
                    $error = "Failed to complete vaccination";
                }
                break;
        }
    }
}

// Get all vaccination bookings for this hospital
$db->query("SELECT vb.*, p.full_name as patient_name, p.phone as patient_phone, p.email as patient_email,
            p.date_of_birth, p.gender, p.aadhar_number, p.address, p.city, p.state, p.pincode
            FROM vaccination_bookings vb 
            JOIN patients p ON vb.patient_id = p.id 
            WHERE vb.hospital_id = :hospital_id 
            ORDER BY vb.created_at DESC");
$db->bind(':hospital_id', $hospital_id);
$bookings = $db->resultset();

// Export functionality
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    exportToExcel($bookings, 'vaccination_requests');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaccination Requests - Hospital Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        /* Details modal styling */
        .detail-item {
            display: flex;
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            min-width: 160px;
        }
        .detail-value {
            flex: 1;
        }
        
        /* Table adjustments */
        #requestsTable th:nth-child(1), 
        #requestsTable td:nth-child(1) {
            width: 5%;
        }
        
        #requestsTable th:nth-child(9), 
        #requestsTable td:nth-child(9) {
            width: 15%;
            white-space: nowrap;
        }
        
        /* Certificate styling */
        .certificate-container {
            border: 15px solid #0d6efd;
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            position: relative;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #0d6efd;
            font-weight: bold;
        }
        .details {
            margin: 30px 0;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
        }
        .watermark {
            position: absolute;
            opacity: 0.1;
            font-size: 120px;
            transform: rotate(-45deg);
            z-index: -1;
            top: 30%;
            left: 10%;
            font-weight: bold;
            color: #0d6efd;
        }
        .print-only {
            display: none;
        }
        @media print {
            body * {
                visibility: hidden;
            }
            .certificate-container, .certificate-container * {
                visibility: visible;
            }
            .certificate-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none;
                padding: 0;
            }
            .no-print {
                display: none;
            }
            .print-only {
                display: block;
            }
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
                                        <i class="fas fa-syringe me-2 text-primary"></i>Vaccination Requests Management
                                    </h4>
                                    <p class="text-muted mb-0">Manage COVID-19 vaccination bookings and certificates</p>
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
                                            <th>Vaccine & Dose</th>
                                            <th>Appointment</th>
                                            <th>Status</th>
                                            <th>Certificate</th>
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
                                                data-patient-dob="<?= $booking['date_of_birth'] ?>"
                                                data-patient-gender="<?= $booking['gender'] ?>"
                                                data-patient-aadhar="<?= $booking['aadhar_number'] ?>"
                                                data-patient-address="<?= htmlspecialchars($booking['address']) ?>"
                                                data-patient-city="<?= htmlspecialchars($booking['city']) ?>"
                                                data-patient-state="<?= htmlspecialchars($booking['state']) ?>"
                                                data-patient-pincode="<?= $booking['pincode'] ?>"
                                                data-vaccine-name="<?= htmlspecialchars($booking['vaccine_name']) ?>"
                                                data-dose-number="<?= $booking['dose_number'] ?>"
                                                data-booking-date="<?= formatDate($booking['booking_date']) ?>"
                                                data-booking-time="<?= formatTime($booking['booking_time']) ?>"
                                                data-status="<?= $booking['status'] ?>"
                                                data-certificate="<?= $booking['certificate_number'] ?>"
                                                data-vaccination-date="<?= $booking['vaccination_date'] ? formatDate($booking['vaccination_date']) : '' ?>"
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
                                                    <div>
                                                        <span class="badge bg-success"><?php echo $booking['vaccine_name']; ?></span><br>
                                                        <span class="badge bg-info mt-1">Dose <?php echo $booking['dose_number']; ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo formatDate($booking['booking_date']); ?></strong><br>
                                                        <small class="text-muted"><?php echo formatTime($booking['booking_time']); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= getStatusBadge($booking['status']) ?>">
                                                        <?= ucfirst($booking['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($booking['certificate_number']): ?>
                                                        <div>
                                                            <strong><?php echo $booking['certificate_number']; ?></strong><br>
                                                            <small class="text-muted"><?php echo formatDate($booking['vaccination_date']); ?></small>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not issued</span>
                                                    <?php endif; ?>
                                                </td>
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
                                                        <?php elseif ($booking['status'] === 'approved'): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="completeVaccination(<?php echo $booking['id']; ?>)">
                                                                <i class="fas fa-check-circle"></i> Complete
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($booking['certificate_number']): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="printCertificate(<?php echo $booking['id']; ?>)">
                                                                <i class="fas fa-print"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="viewDetails(<?php echo $booking['id']; ?>)">
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

    <!-- Booking Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header gradient-bg text-white">
                    <h5 class="modal-title">Vaccination Booking Details</h5>
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

    <!-- Certificate Modal -->
    <div class="modal fade" id="certificateModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header gradient-bg text-white">
                    <h5 class="modal-title">Vaccination Certificate</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="certificateContent">
                    <!-- Certificate content will be populated here by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print Certificate
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Printable Certificate (hidden when not printing) -->
    <div class="print-only" id="printableCertificate">
        <!-- Printable content will be populated here by JavaScript -->
    </div>

    <!-- Hidden field for hospital name -->
    <input type="hidden" id="hospitalName" value="<?= htmlspecialchars($hospital_name) ?>">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        function approveRequest(bookingId) {
            if (confirm('Are you sure you want to approve this vaccination request?')) {
                submitAction('approve', bookingId);
            }
        }

        function rejectRequest(bookingId) {
            if (confirm('Are you sure you want to reject this vaccination request?')) {
                submitAction('reject', bookingId);
            }
        }

        function completeVaccination(bookingId) {
            if (confirm('Are you sure you want to mark this vaccination as completed? This will generate a certificate and update inventory.')) {
                submitAction('complete', bookingId);
            }
        }

        function printCertificate(bookingId) {
            // Find the booking row
            const row = document.querySelector(`tr[data-id="${bookingId}"]`);
            if (!row) return;
            
            // Get hospital name from hidden field
            const hospitalName = document.getElementById('hospitalName').value;
            
            // Get all data attributes
            const data = {
                patientName: row.getAttribute('data-patient-name'),
                patientDOB: row.getAttribute('data-patient-dob'),
                patientGender: row.getAttribute('data-patient-gender'),
                patientAadhar: row.getAttribute('data-patient-aadhar'),
                vaccineName: row.getAttribute('data-vaccine-name'),
                doseNumber: row.getAttribute('data-dose-number'),
                certificate: row.getAttribute('data-certificate'),
                vaccinationDate: row.getAttribute('data-vaccination-date')
            };
            
            // Calculate age
            const dob = new Date(data.patientDOB);
            const today = new Date();
            const age = Math.floor((today - dob) / (365.25 * 24 * 60 * 60 * 1000));
            
            // Create HTML content for certificate
            const certificateContent = `
                <div class="certificate-container">
                    <div class="watermark">COVID-19 VACCINATION</div>
                    
                    <div class="header">
                        <h1>COVID-19 Vaccination Certificate</h1>
                        <p>Ministry of Health and Family Welfare, Government of India</p>
                    </div>
                    
                    <div class="details">
                        <p>This is to certify that the following individual has received the COVID-19 vaccine:</p>
                        
                        <table class="table table-bordered">
                            <tr>
                                <th>Beneficiary Name</th>
                                <td>${data.patientName}</td>
                            </tr>
                            <tr>
                                <th>Age & Gender</th>
                                <td>${age} years, ${data.patientGender.charAt(0).toUpperCase() + data.patientGender.slice(1)}</td>
                            </tr>
                            <tr>
                                <th>Aadhar Number</th>
                                <td>${data.patientAadhar || 'N/A'}</td>
                            </tr>
                            <tr>
                                <th>Certificate ID</th>
                                <td>${data.certificate}</td>
                            </tr>
                            <tr>
                                <th>Vaccine Name</th>
                                <td>${data.vaccineName}</td>
                            </tr>
                            <tr>
                                <th>Dose Number</th>
                                <td>${data.doseNumber}</td>
                            </tr>
                            <tr>
                                <th>Date of Vaccination</th>
                                <td>${data.vaccinationDate}</td>
                            </tr>
                            <tr>
                                <th>Administered At</th>
                                <td>${hospitalName}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="footer">
                        <p>This certificate is digitally verified and can be verified at https://covidbook.com/verify</p>
                        <p>Issued on: ${new Date().toLocaleDateString()}</p>
                    </div>
                </div>
            `;
            
            // Set content and show certificate modal
            document.getElementById('certificateContent').innerHTML = certificateContent;
            
            // Also set content for printable version
            document.getElementById('printableCertificate').innerHTML = certificateContent;
            
            // Show the certificate modal
            new bootstrap.Modal(document.getElementById('certificateModal')).show();
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
                patientDOB: row.getAttribute('data-patient-dob'),
                patientGender: row.getAttribute('data-patient-gender'),
                patientAadhar: row.getAttribute('data-patient-aadhar'),
                patientAddress: row.getAttribute('data-patient-address'),
                patientCity: row.getAttribute('data-patient-city'),
                patientState: row.getAttribute('data-patient-state'),
                patientPincode: row.getAttribute('data-patient-pincode'),
                vaccineName: row.getAttribute('data-vaccine-name'),
                doseNumber: row.getAttribute('data-dose-number'),
                bookingDate: row.getAttribute('data-booking-date'),
                bookingTime: row.getAttribute('data-booking-time'),
                status: row.getAttribute('data-status'),
                certificate: row.getAttribute('data-certificate'),
                vaccinationDate: row.getAttribute('data-vaccination-date'),
                createdAt: row.getAttribute('data-created-at'),
                notes: row.getAttribute('data-notes') || 'No notes available'
            };
            
            // Calculate age
            const dob = new Date(data.patientDOB);
            const today = new Date();
            const age = Math.floor((today - dob) / (365.25 * 24 * 60 * 60 * 1000));
            
            // Map status to badge class
            const statusBadgeClass = {
                'pending': 'warning',
                'approved': 'primary',
                'completed': 'success',
                'rejected': 'danger'
            };
            
            // Create HTML content for modal
            const detailsContent = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">Booking ID:</div>
                            <div class="detail-value">${data.id}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Patient Name:</div>
                            <div class="detail-value">${data.patientName}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Aadhar Number:</div>
                            <div class="detail-value">${data.patientAadhar || 'N/A'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Age & Gender:</div>
                            <div class="detail-value">${age} years, ${data.patientGender.charAt(0).toUpperCase() + data.patientGender.slice(1)}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Phone:</div>
                            <div class="detail-value">${data.patientPhone}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Email:</div>
                            <div class="detail-value">${data.patientEmail}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-item">
                            <div class="detail-label">Vaccine:</div>
                            <div class="detail-value">
                                <span class="badge bg-success">${data.vaccineName}</span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Dose Number:</div>
                            <div class="detail-value">
                                <span class="badge bg-info">Dose ${data.doseNumber}</span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Appointment:</div>
                            <div class="detail-value">${data.bookingDate} at ${data.bookingTime}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Status:</div>
                            <div class="detail-value">
                                <span class="badge bg-${statusBadgeClass[data.status] || 'secondary'}">
                                    ${data.status.charAt(0).toUpperCase() + data.status.slice(1)}
                                </span>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Certificate:</div>
                            <div class="detail-value">
                                ${data.certificate ? 
                                    `<div><strong>${data.certificate}</strong><br>
                                     <small>${data.vaccinationDate}</small></div>` : 
                                    'Not issued'}
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Requested On:</div>
                            <div class="detail-value">${data.createdAt}</div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="detail-item">
                            <div class="detail-label">Address:</div>
                            <div class="detail-value">
                                ${data.patientAddress || 'N/A'}, 
                                ${data.patientCity || ''}, 
                                ${data.patientState || ''} - ${data.patientPincode || ''}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="detail-item">
                            <div class="detail-label">Notes:</div>
                            <div class="detail-value">${data.notes}</div>
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