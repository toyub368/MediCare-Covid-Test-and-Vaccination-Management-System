<?php

require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('patient');

$db = new Database();
$patient_id = $_SESSION['user_id'];

// Get all completed vaccinations with certificates
$db->query("SELECT vb.*, h.hospital_name, h.phone as hospital_phone, h.address as hospital_address 
           FROM vaccination_bookings vb 
           JOIN hospitals h ON vb.hospital_id = h.id 
           WHERE vb.patient_id = :patient_id AND vb.status = 'completed' AND vb.certificate_number IS NOT NULL
           ORDER BY vb.vaccination_date DESC");
$db->bind(':patient_id', $patient_id);
$vaccinations = $db->resultset();

// Get patient info for certificates
$db->query("SELECT * FROM patients WHERE id = :id");
$db->bind(':id', $patient_id);
$patient_info = $db->single();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaccination Certificate - Patient Portal</title>
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
                                        <i class="fas fa-certificate me-2 text-primary"></i>COVID-19 Vaccination Certificates
                                    </h4>
                                    <p class="text-muted mb-0">Download and share your vaccination certificates</p>
                                </div>
                                <div class="col-auto">
                                    <a href="book-vaccination.php" class="btn btn-success">
                                        <i class="fas fa-plus me-2"></i>Book Vaccination
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($vaccinations)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-certificate fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No vaccination certificates available</h5>
                                    <p class="text-muted">You don't have any completed vaccinations yet. Book a vaccination to get your certificate.</p>
                                    <a href="book-vaccination.php" class="btn btn-success">Book Vaccination</a>
                                </div>
                            <?php else: ?>
                                <div class="row g-4">
                                    <?php foreach ($vaccinations as $vaccination): ?>
                                        <div class="col-lg-6">
                                            <div class="card border-0 shadow-sm h-100 certificate-card">
                                                <div class="card-header bg-success text-white">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h6 class="mb-0 text-white">
                                                                <i class="fas fa-certificate me-2"></i>Vaccination Certificate
                                                            </h6>
                                                            <small class="text-white-50">Certificate #<?php echo $vaccination['certificate_number']; ?></small>
                                                        </div>
                                                        <div>
                                                            <span class="badge bg-light text-success">Verified</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row mb-3">
                                                        <div class="col-12 text-center mb-3">
                                                            <div class="bg-light rounded p-3">
                                                                <i class="fas fa-user-check fa-3x text-success mb-2"></i>
                                                                <h5 class="mb-0"><?php echo htmlspecialchars($patient_info['full_name']); ?></h5>
                                                                <small class="text-muted">CNIC: <?php echo htmlspecialchars($patient_info['cnic']); ?></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mb-3">
                                                        <div class="col-6">
                                                            <small class="text-muted">Vaccine</small>
                                                            <div class="fw-bold"><?php echo $vaccination['vaccine_name']; ?></div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted">Dose Number</small>
                                                            <div class="fw-bold">Dose <?php echo $vaccination['dose_number']; ?></div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mb-3">
                                                        <div class="col-6">
                                                            <small class="text-muted">Vaccination Date</small>
                                                            <div class="fw-bold"><?php echo formatDate($vaccination['vaccination_date']); ?></div>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted">Certificate Date</small>
                                                            <div class="fw-bold"><?php echo formatDate($vaccination['vaccination_date']); ?></div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <small class="text-muted">Vaccination Center</small>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($vaccination['hospital_name']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($vaccination['hospital_phone']); ?></small>
                                                    </div>
                                                    
                                                    <div class="alert alert-success">
                                                        <i class="fas fa-shield-alt me-2"></i>
                                                        <strong>Vaccination Completed:</strong> This certificate confirms that the above-named person has been vaccinated against COVID-19.
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-white border-0">
                                                    <div class="d-grid gap-2 d-md-flex">
                                                        <button type="button" class="btn btn-outline-success flex-fill" onclick="downloadCertificate(<?php echo $vaccination['id']; ?>)">
                                                            <i class="fas fa-download me-2"></i>Download PDF
                                                        </button>
                                                        <button type="button" class="btn btn-outline-primary flex-fill" onclick="shareCertificate(<?php echo $vaccination['id']; ?>)">
                                                            <i class="fas fa-share me-2"></i>Share
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info flex-fill" onclick="viewCertificate(<?php echo htmlspecialchars(json_encode($vaccination)); ?>, <?php echo htmlspecialchars(json_encode($patient_info)); ?>)">
                                                            <i class="fas fa-eye me-2"></i>View
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
            
            <!-- Vaccination Summary -->
            <?php if (!empty($vaccinations)): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 pb-0">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-line me-2 text-primary"></i>Vaccination Summary
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="p-3">
                                            <i class="fas fa-syringe fa-2x text-success mb-2"></i>
                                            <h4><?php echo count($vaccinations); ?></h4>
                                            <p class="text-muted mb-0">Total Doses</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3">
                                            <i class="fas fa-shield-alt fa-2x text-primary mb-2"></i>
                                            <h4><?php 
                                                $vaccines = array_unique(array_column($vaccinations, 'vaccine_name'));
                                                echo count($vaccines);
                                            ?></h4>
                                            <p class="text-muted mb-0">Vaccine Types</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3">
                                            <i class="fas fa-certificate fa-2x text-warning mb-2"></i>
                                            <h4><?php echo count($vaccinations); ?></h4>
                                            <p class="text-muted mb-0">Certificates</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3">
                                            <i class="fas fa-calendar fa-2x text-info mb-2"></i>
                                            <h4><?php echo !empty($vaccinations) ? formatDate($vaccinations[0]['vaccination_date']) : 'N/A'; ?></h4>
                                            <p class="text-muted mb-0">Latest Vaccination</p>
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

    <!-- Certificate Modal -->
    <div class="modal fade" id="certificateModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-certificate me-2"></i>COVID-19 Vaccination Certificate
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="certificateContent">
                    <!-- Certificate content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="printCertificate()">
                        <i class="fas fa-print me-2"></i>Print Certificate
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        function downloadCertificate(vaccinationId) {
            // Generate and download PDF certificate
            window.open(`generate_certificate.php?id=${vaccinationId}`, '_blank');
        }

        function shareCertificate(vaccinationId) {
            // Show share options
            if (navigator.share) {
                navigator.share({
                    title: 'COVID-19 Vaccination Certificate',
                    text: 'My COVID-19 vaccination certificate',
                    url: `${window.location.origin}/patient/generate_certificate.php?id=${vaccinationId}`
                });
            } else {
                // Fallback for browsers that don't support Web Share API
                const url = `${window.location.origin}/patient/generate_certificate.php?id=${vaccinationId}`;
                navigator.clipboard.writeText(url).then(() => {
                    showAlert('Certificate link copied to clipboard!', 'success');
                });
            }
        }

        function viewCertificate(vaccination, patient) {
            const content = `
                <div class="certificate-view p-4 text-center">
                    <div class="border border-success rounded p-4">
                        <div class="mb-4">
                            <i class="fas fa-certificate fa-4x text-success mb-3"></i>
                            <h3 class="text-success">COVID-19 VACCINATION CERTIFICATE</h3>
                            <hr class="border-success">
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="mb-3">This is to certify that</h4>
                                <h2 class="text-primary mb-2">${patient.full_name}</h2>
                                <p class="text-muted">CNIC No: ${patient.cnic}</p>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="text-muted">Vaccine</h6>
                                    <h5>${vaccination.vaccine_name}</h5>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="text-muted">Dose Number</h6>
                                    <h5>Dose ${vaccination.dose_number}</h5>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="text-muted">Vaccination Date</h6>
                                    <h5>${new Date(vaccination.vaccination_date).toLocaleDateString()}</h5>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="text-muted">Certificate Number</h6>
                                    <h5>${vaccination.certificate_number}</h5>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="text-muted">Vaccination Center</h6>
                            <h5>${vaccination.hospital_name}</h5>
                        </div>
                        
                        <div class="alert alert-success">
                            <i class="fas fa-shield-alt me-2"></i>
                            This certificate is digitally verified and confirms successful COVID-19 vaccination.
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('certificateContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('certificateModal')).show();
        }

        function printCertificate() {
            window.print();
        }
    </script>
    
    <style>
        .certificate-card {
            transition: transform 0.2s ease-in-out;
        }
        
        .certificate-card:hover {
            transform: translateY(-5px);
        }
        
        @media print {
            .sidebar, .navbar, .btn, .no-print {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .certificate-view {
                page-break-inside: avoid;
            }
        }
    </style>
</body>
</html>