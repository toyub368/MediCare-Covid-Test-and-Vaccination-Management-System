<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('hospital');

$db = new Database();
$patient_id = $_GET['patient_id'] ?? 0;
$hospital_id = $_SESSION['user_id'];

// Get test bookings
$db->query("SELECT * FROM test_bookings 
            WHERE patient_id = :patient_id 
            AND hospital_id = :hospital_id
            ORDER BY booking_date DESC");
$db->bind(':patient_id', $patient_id);
$db->bind(':hospital_id', $hospital_id);
$tests = $db->resultset();

// Get vaccination bookings
$db->query("SELECT * FROM vaccination_bookings 
            WHERE patient_id = :patient_id 
            AND hospital_id = :hospital_id
            ORDER BY vaccination_date DESC");
$db->bind(':patient_id', $patient_id);
$db->bind(':hospital_id', $hospital_id);
$vaccinations = $db->resultset();
?>
<div class="row">
    <div class="col-md-6">
        <h5 class="mb-3 text-center"><i class="fas fa-vial text-primary me-2"></i>Test Bookings (<?= count($tests) ?>)</h5>
        <?php if (empty($tests)): ?>
            <div class="alert alert-info">No test bookings found</div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($tests as $test): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-1"><?= htmlspecialchars($test['test_type']) ?></h6>
                                <p class="mb-1">
                                    <?= formatDate($test['booking_date']) ?>
                                    <?php if (!empty($test['booking_time'])): ?>
                                        at <?= date('h:i A', strtotime($test['booking_time'])) ?>
                                    <?php endif; ?>
                                </p>
                                <span class="badge bg-<?= getStatusBadge($test['status']) ?>">
                                    <?= ucfirst($test['status']) ?>
                                </span>
                            </div>
                            <small><?= formatDate($test['created_at']) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <h5 class="mb-3 text-center"><i class="fas fa-syringe text-success me-2"></i>Vaccination Bookings (<?= count($vaccinations) ?>)</h5>
        <?php if (empty($vaccinations)): ?>
            <div class="alert alert-info">No vaccination bookings found</div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($vaccinations as $vax): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="mb-1"><?= htmlspecialchars($vax['vaccine_name']) ?> - Dose <?= $vax['dose_number'] ?></h6>
                                <p class="mb-1">
                                    <?= formatDate($vax['vaccination_date']) ?>
                                    <?php if (!empty($vax['vaccination_time'])): ?>
                                        at <?= date('h:i A', strtotime($vax['vaccination_time'])) ?>
                                    <?php endif; ?>
                                </p>
                                <span class="badge bg-<?= getStatusBadge($vax['status']) ?>">
                                    <?= ucfirst($vax['status']) ?>
                                </span>
                            </div>
                            <small><?= formatDate($vax['created_at']) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>