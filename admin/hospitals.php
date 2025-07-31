<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('admin');

$db = new Database();

// Handle actions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'approve':
                $hospital_id = (int)$_POST['hospital_id'];
                $db->query("UPDATE hospitals SET status = 'approved' WHERE id = :id");
                $db->bind(':id', $hospital_id);
                if ($db->execute()) {
                    $success = "Hospital approved successfully";
                } else {
                    $error = "Failed to approve hospital";
                }
                break;
                
            case 'reject':
                $hospital_id = (int)$_POST['hospital_id'];
                $db->query("UPDATE hospitals SET status = 'rejected' WHERE id = :id");
                $db->bind(':id', $hospital_id);
                if ($db->execute()) {
                    $success = "Hospital rejected successfully";
                } else {
                    $error = "Failed to reject hospital";
                }
                break;
                
            case 'delete':
                $hospital_id = (int)$_POST['hospital_id'];
                $db->query("DELETE FROM hospitals WHERE id = :id");
                $db->bind(':id', $hospital_id);
                if ($db->execute()) {
                    $success = "Hospital deleted successfully";
                } else {
                    $error = "Failed to delete hospital";
                }
                break;
        }
    }
}

// Get all hospitals
$db->query("SELECT * FROM hospitals ORDER BY created_at DESC");
$hospitals = $db->resultset();

// Export functionality
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    exportToExcel($hospitals, 'hospitals_list');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospitals - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        /* Custom styles for perfect centering */
        .hospital-icon-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #0dcaf0; /* Bootstrap info color */
        }
        
        .hospital-icon-container i {
            font-size: 16px;
            color: white;
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
                                        <i class="fas fa-hospital me-2 text-primary"></i>Hospital Management
                                    </h4>
                                    <p class="text-muted mb-0">Manage hospital registrations and approvals</p>
                                </div>
                                <div class="col-auto">
                                    <div class="d-flex gap-2">
                                        <input type="text" class="form-control search-input" placeholder="Search hospitals..." data-target="#hospitalsTable">
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
                                <table class="table table-hover" id="hospitalsTable">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>ID</th>
                                            <th>Hospital Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>City</th>
                                            <th>License</th>
                                            <th>Status</th>
                                            <th>Services</th>
                                            <th>Registered</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($hospitals as $hospital): ?>
                                            <tr data-hospital='<?php echo htmlspecialchars(json_encode($hospital)); ?>'>
                                                <td><?php echo $hospital['id']; ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <!-- Fixed hospital icon with perfect centering -->
                                                        <div class="hospital-icon-container me-2">
                                                            <i class="fas fa-hospital"></i>
                                                        </div>
                                                        <?php echo htmlspecialchars($hospital['hospital_name']); ?>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($hospital['email']); ?></td>
                                                <td><?php echo htmlspecialchars($hospital['phone']); ?></td>
                                                <td><?php echo htmlspecialchars($hospital['city']); ?></td>
                                                <td><?php echo htmlspecialchars($hospital['license_number']); ?></td>
                                                <td><?php echo getStatusBadge($hospital['status']); ?></td>
                                                <td>
                                                    <?php if ($hospital['test_available']): ?>
                                                        <span class="badge bg-primary me-1">Tests</span>
                                                    <?php endif; ?>
                                                    <?php if ($hospital['vaccine_available']): ?>
                                                        <span class="badge bg-success">Vaccines</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo formatDate($hospital['created_at']); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-info view-hospital-btn">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <?php if ($hospital['status'] === 'pending'): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-success" onclick="approveHospital(<?php echo $hospital['id']; ?>)">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="rejectHospital(<?php echo $hospital['id']; ?>)">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteHospital(<?php echo $hospital['id']; ?>)">
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

    <!-- Hospital Details Modal -->
    <div class="modal fade" id="hospitalModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header gradient-bg text-white">
                    <h5 class="modal-title">Hospital Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="hospitalDetails">
                    <!-- Hospital details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Fixed hospital view functionality
        document.querySelectorAll('.view-hospital-btn').forEach(button => {
            button.addEventListener('click', function() {
                const hospitalData = JSON.parse(this.closest('tr').dataset.hospital);
                populateHospitalModal(hospitalData);
            });
        });

        function populateHospitalModal(hospital) {
            document.getElementById('hospitalDetails').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Hospital Information</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>ID:</strong></td><td>${hospital.id}</td></tr>
                            <tr><td><strong>Name:</strong></td><td>${hospital.hospital_name}</td></tr>
                            <tr><td><strong>Email:</strong></td><td>${hospital.email}</td></tr>
                            <tr><td><strong>Phone:</strong></td><td>${hospital.phone}</td></tr>
                            <tr><td><strong>License:</strong></td><td>${hospital.license_number}</td></tr>
                            <tr><td><strong>Status:</strong></td><td>${getStatusBadgeHTML(hospital.status)}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Address & Services</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Address:</strong></td><td>${hospital.address || 'N/A'}</td></tr>
                            <tr><td><strong>City:</strong></td><td>${hospital.city}</td></tr>
                            <tr><td><strong>State:</strong></td><td>${hospital.state || 'N/A'}</td></tr>
                            <tr><td><strong>Pincode:</strong></td><td>${hospital.pincode || 'N/A'}</td></tr>
                            <tr><td><strong>Tests Available:</strong></td><td>${hospital.test_available ? 'Yes' : 'No'}</td></tr>
                            <tr><td><strong>Vaccines Available:</strong></td><td>${hospital.vaccine_available ? 'Yes' : 'No'}</td></tr>
                            <tr><td><strong>Registered:</strong></td><td>${hospital.created_at}</td></tr>
                        </table>
                    </div>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('hospitalModal')).show();
        }

        // Helper function for status badges in modal
        function getStatusBadgeHTML(status) {
            const statusClasses = {
                'pending': 'bg-warning',
                'approved': 'bg-success',
                'rejected': 'bg-danger'
            };
            return `<span class="badge ${statusClasses[status] || 'bg-secondary'}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
        }

        function approveHospital(hospitalId) {
            if (confirm('Are you sure you want to approve this hospital?')) {
                submitAction('approve', hospitalId);
            }
        }

        function rejectHospital(hospitalId) {
            if (confirm('Are you sure you want to reject this hospital?')) {
                submitAction('reject', hospitalId);
            }
        }

        function deleteHospital(hospitalId) {
            if (confirm('Are you sure you want to delete this hospital? This action cannot be undone.')) {
                submitAction('delete', hospitalId);
            }
        }

        function submitAction(action, hospitalId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="${action}">
                <input type="hidden" name="hospital_id" value="${hospitalId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>