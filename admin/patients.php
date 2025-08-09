<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('admin');

$db = new Database();

// Handle actions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                $patient_id = (int)$_POST['patient_id'];
                $db->query("DELETE FROM patients WHERE id = :id");
                $db->bind(':id', $patient_id);
                if ($db->execute()) {
                    $success = "Patient deleted successfully";
                } else {
                    $error = "Failed to delete patient";
                }
                break;
        }
    }
}

// Get all patients with CNIC instead of Aadhar
$db->query("SELECT *, cnic AS aadhar_number FROM patients ORDER BY created_at DESC");
$patients = $db->resultset();

// Export functionality
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    exportToExcel($patients, 'patients_list');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .patient-icon-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #0d6efd;
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
                                        <i class="fas fa-users me-2 text-primary"></i>Patient Management
                                    </h4>
                                    <p class="text-muted mb-0">Manage all registered patients</p>
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
                                <table class="table table-hover" id="patientsTable">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Age</th>
                                            <th>Gender</th>
                                            <th>City</th>
                                            <th>CNIC</th> <!-- Changed from Aadhar to CNIC -->
                                            <th>Registered</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($patients as $patient): ?>
                                            <tr data-patient='<?php echo htmlspecialchars(json_encode($patient)); ?>'>
                                                <td><?php echo $patient['id']; ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="patient-icon-container me-2">
                                                            <i class="fas fa-user text-white"></i>
                                                        </div>
                                                        <?php echo htmlspecialchars($patient['full_name']); ?>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($patient['email']); ?></td>
                                                <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                                                <td><?php echo date_diff(date_create($patient['date_of_birth']), date_create('today'))->y; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $patient['gender'] === 'male' ? 'primary' : ($patient['gender'] === 'female' ? 'danger' : 'secondary'); ?>">
                                                        <?php echo ucfirst($patient['gender']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($patient['city']); ?></td>
                                                <td><?php echo htmlspecialchars($patient['cnic']); ?></td> <!-- Changed from aadhar_number to cnic -->
                                                <td><?php echo formatDate($patient['created_at']); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-info view-patient-btn">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deletePatient(<?php echo $patient['id']; ?>)">
                                                            <i class="fas fa-trash"></i>
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

    <!-- Patient Details Modal -->
    <div class="modal fade" id="patientModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header gradient-bg text-white">
                    <h5 class="modal-title">Patient Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="patientDetails">
                    <!-- Patient details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        document.querySelectorAll('.view-patient-btn').forEach(button => {
            button.addEventListener('click', function() {
                const patientData = JSON.parse(this.closest('tr').dataset.patient);
                populatePatientModal(patientData);
            });
        });

        function populatePatientModal(patient) {
            document.getElementById('patientDetails').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Personal Information</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>ID:</strong></td><td>${patient.id}</td></tr>
                            <tr><td><strong>Name:</strong></td><td>${patient.full_name}</td></tr>
                            <tr><td><strong>Email:</strong></td><td>${patient.email}</td></tr>
                            <tr><td><strong>Phone:</strong></td><td>${patient.phone}</td></tr>
                            <tr><td><strong>Date of Birth:</strong></td><td>${patient.date_of_birth}</td></tr>
                            <tr><td><strong>Age:</strong></td><td>${new Date().getFullYear() - new Date(patient.date_of_birth).getFullYear()}</td></tr>
                            <tr><td><strong>Gender:</strong></td><td>${patient.gender}</td></tr>
                            <tr><td><strong>CNIC:</strong></td><td>${patient.cnic}</td></tr> <!-- Changed from Aadhar to CNIC -->
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Address Information</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Address:</strong></td><td>${patient.address || 'N/A'}</td></tr>
                            <tr><td><strong>City:</strong></td><td>${patient.city}</td></tr>
                            <tr><td><strong>State:</strong></td><td>${patient.state || 'N/A'}</td></tr>
                            <tr><td><strong>Pincode:</strong></td><td>${patient.pincode || 'N/A'}</td></tr>
                            <tr><td><strong>Registered:</strong></td><td>${patient.created_at}</td></tr>
                        </table>
                    </div>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('patientModal')).show();
        }

        function deletePatient(patientId) {
            if (confirm('Are you sure you want to delete this patient? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="patient_id" value="${patientId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>