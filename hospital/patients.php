<?php
require_once '../includes/session.php';
require_once '../includes/functions.php'; // Contains formatDate()
requireLogin('hospital');

$db = new Database();
$hospital_id = $_SESSION['user_id'];

// Get all patients who have bookings with this hospital
$db->query("SELECT DISTINCT p.*, 
                   COUNT(DISTINCT tb.id) as test_bookings,
                   COUNT(DISTINCT vb.id) as vaccination_bookings
            FROM patients p 
            LEFT JOIN test_bookings tb ON p.id = tb.patient_id AND tb.hospital_id = :hospital_id
            LEFT JOIN vaccination_bookings vb ON p.id = vb.patient_id AND vb.hospital_id = :hospital_id2
            WHERE (tb.id IS NOT NULL OR vb.id IS NOT NULL)
            GROUP BY p.id
            ORDER BY p.full_name");
$db->bind(':hospital_id', $hospital_id);
$db->bind(':hospital_id2', $hospital_id);
$patients = $db->resultset();

// Export functionality
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    exportToExcel($patients, 'hospital_patients');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients - Hospital Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .patient-avatar {
            width: 40px;
            height: 40px;
            background-color: #0d6efd;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        .action-buttons .btn {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }
        .badge-count {
            font-size: 0.85rem;
            padding: 0.35em 0.65em;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
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
                                        <i class="fas fa-users me-2 text-primary"></i>Patient Records
                                    </h4>
                                    <p class="text-muted mb-0">Patients who have bookings with your hospital</p>
                                </div>
                                <div class="col-auto">
                                    <div class="d-flex gap-2">
                                        <input type="text" class="form-control search-input" placeholder="Search patients..." data-target="#patientsTable">
                                        <a href="?export=excel" class="btn btn-success">
                                            <i class="fas fa-file-excel me-2"></i>Export
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($patients)): ?>
                                <div class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="fas fa-users-slash fa-3x text-muted"></i>
                                    </div>
                                    <h5 class="text-muted">No patients found</h5>
                                    <p class="text-muted">Your hospital doesn't have any patient bookings yet.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="patientsTable">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>ID</th>
                                                <th>Patient Name</th>
                                                <th>Contact Info</th>
                                                <th>Age & Gender</th>
                                                <th>Location</th>
                                                <th class="text-center">Tests</th>
                                                <th class="text-center">Vaccinations</th>
                                                <th>Registered</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($patients as $patient): ?>
                                                <tr>
                                                    <td><?php echo $patient['id']; ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="patient-avatar">
                                                                <i class="fas fa-user"></i>
                                                            </div>
                                                            <div class="ms-2">
                                                                <strong><?php echo htmlspecialchars($patient['full_name']); ?></strong><br>
                                                                <small class="text-muted">ID: <?php echo $patient['cnic']; ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <i class="fas fa-envelope me-1 text-muted"></i><?php echo htmlspecialchars($patient['email']); ?><br>
                                                            <i class="fas fa-phone me-1 text-muted"></i><?php echo htmlspecialchars($patient['phone']); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong><?php echo date_diff(date_create($patient['date_of_birth']), date_create('today'))->y; ?> years</strong><br>
                                                            <span class="badge bg-<?php echo $patient['gender'] === 'male' ? 'primary' : ($patient['gender'] === 'female' ? 'danger' : 'secondary'); ?>">
                                                                <?php echo ucfirst($patient['gender']); ?>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <i class="fas fa-map-marker-alt me-1 text-muted"></i><?php echo htmlspecialchars($patient['city']); ?>, <?php echo htmlspecialchars($patient['state']); ?><br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($patient['pincode']); ?></small>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-info badge-count"><?php echo $patient['test_bookings']; ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-success badge-count"><?php echo $patient['vaccination_bookings']; ?></span>
                                                    </td>
                                                    <td><?php echo formatDate($patient['created_at']); ?></td>
                                                    <td class="text-center action-buttons">
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#historyModal" onclick="viewPatientHistory(<?php echo $patient['id']; ?>)">
                                                                <i class="fas fa-history"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#patientModal" onclick="viewPatientDetails(<?php echo $patient['id']; ?>)">
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

    <!-- Patient Details Modal -->
    <div class="modal fade" id="patientModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header gradient-bg text-white">
                    <h5 class="modal-title">Patient Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="patientDetails">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading patient details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Patient History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header gradient-bg text-white">
                    <h5 class="modal-title">Patient Booking History</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="patientHistory">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading booking history...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show fixed-top mx-auto mt-3`;
            alertDiv.style.maxWidth = '500px';
            alertDiv.style.zIndex = '1060';
            alertDiv.role = 'alert';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.classList.remove('show');
                setTimeout(() => document.body.removeChild(alertDiv), 150);
            }, 3000);
        }

        function viewPatientDetails(patientId) {
            document.getElementById('patientDetails').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading patient details...</p>
                </div>
            `;
            
            // AJAX call to fetch patient details
            fetch(`get_patient_details.php?patient_id=${patientId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('patientDetails').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('patientDetails').innerHTML = `
                        <div class="alert alert-danger">
                            Failed to load patient details. Please try again.
                        </div>
                    `;
                    console.error('Error:', error);
                });
        }

        function viewPatientHistory(patientId) {
            document.getElementById('patientHistory').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading booking history...</p>
                </div>
            `;
            
            // AJAX call to fetch patient history
            fetch(`get_patient_history.php?patient_id=${patientId}&hospital_id=<?php echo $hospital_id; ?>`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('patientHistory').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('patientHistory').innerHTML = `
                        <div class="alert alert-danger">
                            Failed to load booking history. Please try again.
                        </div>
                    `;
                    console.error('Error:', error);
                });
        }
    </script>
</body>
</html>