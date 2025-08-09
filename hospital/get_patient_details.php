<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('hospital');

$db = new Database();
$patient_id = $_GET['patient_id'] ?? 0;

$db->query("SELECT * FROM patients WHERE id = :id");
$db->bind(':id', $patient_id);
$patient = $db->single();

if (!$patient) {
    echo '<div class="alert alert-danger">Patient not found</div>';
    exit;
}
?>
<div class="row">
    <div class="col-md-4 text-center mb-4">
        <div class="d-inline-block rounded-circle bg-primary p-4">
            <i class="fas fa-user fa-3x text-white"></i>
        </div>
        <h4 class="mt-3"><?= htmlspecialchars($patient['full_name']) ?></h4>
        <p class="text-muted">ID: <?= htmlspecialchars($patient['cnic']) ?></p>
    </div>
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-6">
                <h6>Personal Information</h6>
                <table class="table table-borderless">
                    <tr><td><strong>Name:</strong></td><td><?= htmlspecialchars($patient['full_name']) ?></td></tr>
                    <tr><td><strong>Email:</strong></td><td><?= htmlspecialchars($patient['email']) ?></td></tr>
                    <tr><td><strong>Phone:</strong></td><td><?= htmlspecialchars($patient['phone']) ?></td></tr>
                    <tr><td><strong>Date of Birth:</strong></td><td><?= formatDate($patient['date_of_birth']) ?></td></tr>
                    <tr><td><strong>Gender:</strong></td><td><?= ucfirst($patient['gender']) ?></td></tr>
                    <tr><td><strong>CNIC:</strong></td><td><?= htmlspecialchars($patient['cnic']) ?></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Address Information</h6>
                <table class="table table-borderless">
                    <tr><td><strong>Address:</strong></td><td><?= htmlspecialchars($patient['address']) ?></td></tr>
                    <tr><td><strong>City:</strong></td><td><?= htmlspecialchars($patient['city']) ?></td></tr>
                    <tr><td><strong>State:</strong></td><td><?= htmlspecialchars($patient['state']) ?></td></tr>
                    <tr><td><strong>Pincode:</strong></td><td><?= htmlspecialchars($patient['pincode']) ?></td></tr>
                    <tr><td><strong>Registered:</strong></td><td><?= formatDate($patient['created_at']) ?></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>