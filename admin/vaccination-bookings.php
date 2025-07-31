<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('admin');

$db = new Database();

// Define helper functions
if (!function_exists('formatTime')) {
    function formatTime($timeString) {
        if (empty($timeString)) return 'N/A';
        return date("h:i A", strtotime($timeString));
    }
}

if (!function_exists('formatDate')) {
    function formatDate($dateString, $format = "M d, Y") {
        if (empty($dateString)) return 'N/A';
        return date($format, strtotime($dateString));
    }
}

if (!function_exists('getStatusBadge')) {
    function getStatusBadge($status) {
        $badges = [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'approved' => '<span class="badge bg-info">Approved</span>',
            'completed' => '<span class="badge bg-success">Completed</span>',
            'rejected' => '<span class="badge bg-danger">Rejected</span>',
        ];
        return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }
}

if (!function_exists('generateCertificateNumber')) {
    function generateCertificateNumber() {
        return 'CERT-' . strtoupper(uniqid());
    }
}

// Handle actions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                $booking_id = (int)$_POST['booking_id'];
                $status = $_POST['status'];
                $vaccination_date = $_POST['vaccination_date'] ?? null;
                $certificate_number = $_POST['certificate_number'] ?? null;
                
                if ($status === 'completed') {
                    $certificate_number = generateCertificateNumber();
                    $vaccination_date = date('Y-m-d');
                }
                
                $db->query("UPDATE vaccination_bookings SET status = :status, vaccination_date = :vac_date, certificate_number = :cert WHERE id = :id");
                $db->bind(':status', $status);
                $db->bind(':vac_date', $vaccination_date);
                $db->bind(':cert', $certificate_number);
                $db->bind(':id', $booking_id);
                
                if ($db->execute()) {
                    $success = "Vaccination status updated successfully";
                } else {
                    $error = "Failed to update vaccination status";
                }
                break;
        }
    }
}

// Get all vaccination bookings with patient and hospital details
$db->query("SELECT vb.*, p.full_name as patient_name, p.phone as patient_phone, 
                   h.hospital_name, h.phone as hospital_phone
            FROM vaccination_bookings vb 
            JOIN patients p ON vb.patient_id = p.id 
            JOIN hospitals h ON vb.hospital_id = h.id 
            ORDER BY vb.created_at DESC");
$bookings = $db->resultset();

// Export functionality
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    exportToExcel($bookings, 'vaccination_bookings');
}

// Check if we're viewing a certificate
$certificateData = null;
if (isset($_GET['certificate']) && is_numeric($_GET['certificate'])) {
    $certificate_id = (int)$_GET['certificate'];
    // REMOVED p.dob and p.gender FROM THE QUERY
    $db->query("SELECT vb.*, p.full_name as patient_name, 
                       h.hospital_name, h.address as hospital_address
                FROM vaccination_bookings vb 
                JOIN patients p ON vb.patient_id = p.id 
                JOIN hospitals h ON vb.hospital_id = h.id 
                WHERE vb.id = :id");
    $db->bind(':id', $certificate_id);
    $certificateData = $db->single();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaccination Bookings - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        /* Certificate Styles */
        .certificate-container {
            background: #fff;
            border: 20px solid #1a5276;
            border-radius: 10px;
            padding: 50px;
            margin: 30px auto;
            max-width: 1000px;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .certificate-header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px double #1a5276;
            padding-bottom: 20px;
        }
        
        .certificate-title {
            font-size: 36px;
            font-weight: 700;
            color: #1a5276;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        
        .certificate-subtitle {
            font-size: 24px;
            font-weight: 400;
            color: #3498db;
            margin-top: 10px;
        }
        
        .certificate-body {
            margin: 40px 0;
            font-size: 18px;
            line-height: 1.8;
        }
        
        .certificate-field {
            margin-bottom: 15px;
        }
        
        .certificate-label {
            font-weight: 600;
            display: inline-block;
            min-width: 200px;
            color: #2c3e50;
        }
        
        .certificate-value {
            display: inline-block;
            color: #34495e;
        }
        
        .certificate-footer {
            margin-top: 50px;
            text-align: center;
        }
        
        .signature-area {
            display: inline-block;
            width: 300px;
            border-top: 1px solid #7f8c8d;
            padding-top: 10px;
            margin: 0 20px;
        }
        
        .certificate-stamp {
            position: absolute;
            bottom: 50px;
            right: 50px;
            width: 150px;
            opacity: 0.8;
        }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            font-weight: 800;
            color: rgba(26, 82, 118, 0.1);
            pointer-events: none;
            z-index: 0;
            text-transform: uppercase;
        }
        
        /* Print-specific styles */
        @media print {
            body {
                background: white !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            .certificate-container {
                border: none;
                padding: 0;
                margin: 0;
                box-shadow: none;
            }
            
            .main-content {
                padding: 0;
            }
            
            .container-fluid {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <?php if (!$certificateData): ?>
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
                                            <i class="fas fa-syringe me-2 text-primary"></i>Vaccination Bookings Management
                                        </h4>
                                        <p class="text-muted mb-0">Monitor and manage all COVID-19 vaccination bookings</p>
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
                                    <table class="table table-hover" id="bookingsTable">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>ID</th>
                                                <th>Patient</th>
                                                <th>Hospital</th>
                                                <th>Vaccine</th>
                                                <th>Dose</th>
                                                <th>Date & Time</th>
                                                <th>Status</th>
                                                <th>Certificate</th>
                                                <th>Booked On</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($bookings as $booking): ?>
                                                <tr data-booking-id="<?php echo $booking['id']; ?>">
                                                    <td><?php echo $booking['id']; ?></td>
                                                    <td>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($booking['patient_name']); ?></strong><br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($booking['patient_phone']); ?></small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($booking['hospital_name']); ?></strong><br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($booking['hospital_phone']); ?></small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success"><?php echo $booking['vaccine_name']; ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info">Dose <?php echo $booking['dose_number']; ?></span>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong><?php echo formatDate($booking['booking_date']); ?></strong><br>
                                                            <small class="text-muted"><?php echo formatTime($booking['booking_time']); ?></small>
                                                        </div>
                                                    </td>
                                                    <td><?php echo getStatusBadge($booking['status']); ?></td>
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
                                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="viewBooking(<?php echo $booking['id']; ?>)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <?php if ($booking['status'] !== 'completed'): ?>
                                                                <button type="button" class="btn btn-sm btn-outline-success" onclick="updateStatus(<?php echo $booking['id']; ?>)">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                            <?php if ($booking['certificate_number']): ?>
                                                                <a href="?certificate=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                                    <i class="fas fa-print"></i>
                                                                </a>
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

        <!-- Update Status Modal -->
        <div class="modal fade" id="statusModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header gradient-bg text-white">
                        <h5 class="modal-title">Update Vaccination Status</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="booking_id" id="bookingId">
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="approved">Approved</option>
                                    <option value="completed">Completed</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                If status is set to "Completed", vaccination date will be set to today and a certificate number will be generated automatically.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Booking Modal -->
        <div class="modal fade" id="viewBookingModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header gradient-bg text-white">
                        <h5 class="modal-title">Vaccination Booking Details</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Patient Information</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th>Name:</th>
                                        <td id="viewPatientName"></td>
                                    </tr>
                                    <tr>
                                        <th>Phone:</th>
                                        <td id="viewPatientPhone"></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Hospital Information</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th>Hospital:</th>
                                        <td id="viewHospitalName"></td>
                                    </tr>
                                    <tr>
                                        <th>Phone:</th>
                                        <td id="viewHospitalPhone"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h6>Booking Details</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th>Vaccine:</th>
                                        <td id="viewVaccineName"></td>
                                    </tr>
                                    <tr>
                                        <th>Dose Number:</th>
                                        <td id="viewDoseNumber"></td>
                                    </tr>
                                    <tr>
                                        <th>Booking Date:</th>
                                        <td id="viewBookingDate"></td>
                                    </tr>
                                    <tr>
                                        <th>Booking Time:</th>
                                        <td id="viewBookingTime"></td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td id="viewStatus"></td>
                                    </tr>
                                    <tr>
                                        <th>Vaccination Date:</th>
                                        <td id="viewVaccinationDate"></td>
                                    </tr>
                                    <tr>
                                        <th>Certificate Number:</th>
                                        <td id="viewCertificateNumber"></td>
                                    </tr>
                                    <tr>
                                        <th>Booked On:</th>
                                        <td id="viewCreatedAt"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Certificate View -->
        <div class="certificate-container">
            <div class="watermark">COVID-19 VACCINATION</div>
            
            <div class="certificate-header">
                <div class="certificate-title">COVID-19 Vaccination Certificate</div>
                <div class="certificate-subtitle">Proof of Vaccination</div>
            </div>
            
            <div class="certificate-body">
                <div class="certificate-field">
                    <span class="certificate-label">Certificate Number:</span>
                    <span class="certificate-value"><?php echo $certificateData['certificate_number']; ?></span>
                </div>
                
                <div class="certificate-field">
                    <span class="certificate-label">Patient Name:</span>
                    <span class="certificate-value"><?php echo htmlspecialchars($certificateData['patient_name']); ?></span>
                </div>
                
                <!-- REMOVED DATE OF BIRTH FIELD -->
                <!-- REMOVED GENDER FIELD -->
                
                <div class="certificate-field">
                    <span class="certificate-label">Vaccine Administered:</span>
                    <span class="certificate-value"><?php echo $certificateData['vaccine_name']; ?></span>
                </div>
                
                <div class="certificate-field">
                    <span class="certificate-label">Dose Number:</span>
                    <span class="certificate-value">Dose <?php echo $certificateData['dose_number']; ?></span>
                </div>
                
                <div class="certificate-field">
                    <span class="certificate-label">Date of Vaccination:</span>
                    <span class="certificate-value"><?php echo formatDate($certificateData['vaccination_date'], "F j, Y"); ?></span>
                </div>
                
                <div class="certificate-field">
                    <span class="certificate-label">Administered By:</span>
                    <span class="certificate-value"><?php echo htmlspecialchars($certificateData['hospital_name']); ?></span>
                </div>
                
                <div class="certificate-field">
                    <span class="certificate-label">Facility Address:</span>
                    <span class="certificate-value"><?php echo htmlspecialchars($certificateData['hospital_address']); ?></span>
                </div>
                
                <div class="certificate-field mt-5">
                    <p>This is to certify that the above-named individual has received the COVID-19 vaccination as indicated. This certificate serves as official proof of vaccination.</p>
                </div>
            </div>
            
            <div class="certificate-footer">
                <div class="d-flex justify-content-around">
                    <div class="signature-area">
                        <div>Authorized Signature</div>
                        <div class="mt-3">_________________________</div>
                        <div class="mt-2">Dr. Jane Smith</div>
                        <div>Medical Director</div>
                    </div>
                    
                    <div class="signature-area">
                        <div>Facility Stamp</div>
                        <div class="mt-3">
                            <svg width="120" height="120" viewBox="0 0 120 120" class="certificate-stamp">
                                <circle cx="60" cy="60" r="55" fill="none" stroke="#c0392b" stroke-width="4"/>
                                <circle cx="60" cy="60" r="45" fill="none" stroke="#c0392b" stroke-width="2"/>
                                <text x="60" y="50" text-anchor="middle" font-size="10" fill="#c0392b">OFFICIAL</text>
                                <text x="60" y="65" text-anchor="middle" font-size="10" fill="#c0392b">VACCINATION</text>
                                <text x="60" y="80" text-anchor="middle" font-size="10" fill="#c0392b">RECORD</text>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-5 text-center">
                <div class="alert alert-info d-inline-block">
                    <i class="fas fa-info-circle me-2"></i>
                    This certificate contains a unique identifier that can be verified through our official system.
                </div>
            </div>
            
            <div class="text-center mt-4 no-print">
                <button class="btn btn-primary me-2" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Print Certificate
                </button>
                <button class="btn btn-secondary" onclick="window.close()">
                    <i class="fas fa-times me-2"></i>Close Window
                </button>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        <?php if (!$certificateData): ?>
        function updateStatus(bookingId) {
            document.getElementById('bookingId').value = bookingId;
            new bootstrap.Modal(document.getElementById('statusModal')).show();
        }

        function viewBooking(bookingId) {
            // Get booking row
            const row = document.querySelector(`tr[data-booking-id="${bookingId}"]`);
            if (!row) return;
            
            // Extract data from row
            const cells = row.cells;
            
            // Populate modal
            document.getElementById('viewPatientName').textContent = cells[1].querySelector('strong').textContent;
            document.getElementById('viewPatientPhone').textContent = cells[1].querySelector('small').textContent;
            document.getElementById('viewHospitalName').textContent = cells[2].querySelector('strong').textContent;
            document.getElementById('viewHospitalPhone').textContent = cells[2].querySelector('small').textContent;
            document.getElementById('viewVaccineName').textContent = cells[3].querySelector('.badge').textContent;
            document.getElementById('viewDoseNumber').textContent = cells[4].querySelector('.badge').textContent;
            document.getElementById('viewBookingDate').textContent = cells[5].querySelector('strong').textContent;
            document.getElementById('viewBookingTime').textContent = cells[5].querySelector('small').textContent;
            document.getElementById('viewStatus').innerHTML = cells[6].innerHTML;
            
            // Handle certificate details
            const certElement = cells[7];
            if (certElement.querySelector('div')) {
                document.getElementById('viewVaccinationDate').textContent = certElement.querySelector('small').textContent;
                document.getElementById('viewCertificateNumber').textContent = certElement.querySelector('strong').textContent;
            } else {
                document.getElementById('viewVaccinationDate').textContent = 'N/A';
                document.getElementById('viewCertificateNumber').textContent = 'Not issued';
            }
            
            document.getElementById('viewCreatedAt').textContent = cells[8].textContent;
            
            // Show the modal
            new bootstrap.Modal(document.getElementById('viewBookingModal')).show();
        }

        // Status filter functionality
        document.getElementById('statusFilter').addEventListener('change', function() {
            const filterValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#bookingsTable tbody tr');
            
            rows.forEach(row => {
                const statusCell = row.cells[6].textContent.toLowerCase();
                if (filterValue === '' || statusCell.includes(filterValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        <?php endif; ?>
    </script>
</body>
</html>