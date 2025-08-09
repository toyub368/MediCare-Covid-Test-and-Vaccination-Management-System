<?php

require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('patient');

// Define missing functions if not already available
if (!function_exists('formatDate')) {
    function formatDate($date) {
        if (empty($date) || $date === '0000-00-00') return 'N/A';
        return date('M d, Y', strtotime($date));
    }
}

if (!function_exists('formatTime')) {
    function formatTime($time) {
        if (empty($time)) return 'N/A';
        return date('h:i A', strtotime($time));
    }
}

// Validate and sanitize input
$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!in_array($type, ['test', 'vaccination']) || $id <= 0) {
    echo '<div class="alert alert-danger">Invalid request</div>';
    exit;
}

$db = new Database();
$patient_id = $_SESSION['user_id'];

if ($type === 'test') {
    $db->query("SELECT tb.*, h.hospital_name, h.address AS hospital_address, h.phone AS hospital_phone
                FROM test_bookings tb
                JOIN hospitals h ON tb.hospital_id = h.id
                WHERE tb.id = :id AND tb.patient_id = :patient_id");
    $db->bind(':id', $id);
    $db->bind(':patient_id', $patient_id);
    $booking = $db->single();

    if (!$booking) {
        echo '<div class="alert alert-warning">Test appointment not found.</div>';
        exit;
    }

    ?>
    <dl class="row modal-details">
        <dt class="col-sm-4">Test Type</dt>
        <dd class="col-sm-8"><?php echo htmlspecialchars($booking['test_type']); ?></dd>

        <dt class="col-sm-4">Hospital</dt>
        <dd class="col-sm-8">
            <?php echo htmlspecialchars($booking['hospital_name']); ?><br>
            <small><?php echo htmlspecialchars($booking['hospital_address']); ?></small><br>
            <small><i class="fas fa-phone"></i> <?php echo htmlspecialchars($booking['hospital_phone']); ?></small>
        </dd>

        <dt class="col-sm-4">Date & Time</dt>
        <dd class="col-sm-8"><?php echo formatDate($booking['booking_date']) . ' at ' . formatTime($booking['booking_time']); ?></dd>

        <dt class="col-sm-4">Status</dt>
        <dd class="col-sm-8"><?php echo htmlspecialchars($booking['status']); ?></dd>

        <dt class="col-sm-4">Test Result</dt>
        <dd class="col-sm-8">
            <?php echo htmlspecialchars($booking['test_result'] ?? 'Pending'); ?>
            <?php if (!empty($booking['result_date'])): ?>
                <br><small class="text-muted"><?php echo formatDate($booking['result_date']); ?></small>
            <?php endif; ?>
        </dd>

        <dt class="col-sm-4">Price</dt>
        <dd class="col-sm-8">₹<?php echo number_format($booking['price'], 2); ?></dd>

        <dt class="col-sm-4">Booked On</dt>
        <dd class="col-sm-8"><?php echo formatDate($booking['created_at']); ?></dd>
    </dl>
    <?php

} elseif ($type === 'vaccination') {
    $db->query("SELECT vb.*, h.hospital_name, h.address AS hospital_address, h.phone AS hospital_phone
                FROM vaccination_bookings vb
                JOIN hospitals h ON vb.hospital_id = h.id
                WHERE vb.id = :id AND vb.patient_id = :patient_id");
    $db->bind(':id', $id);
    $db->bind(':patient_id', $patient_id);
    $booking = $db->single();

    if (!$booking) {
        echo '<div class="alert alert-warning">Vaccination appointment not found.</div>';
        exit;
    }

    ?>
    <dl class="row modal-details">
        <dt class="col-sm-4">Vaccine</dt>
        <dd class="col-sm-8"><?php echo htmlspecialchars($booking['vaccine_name']); ?></dd>

        <dt class="col-sm-4">Dose Number</dt>
        <dd class="col-sm-8">Dose <?php echo htmlspecialchars($booking['dose_number']); ?></dd>

        <dt class="col-sm-4">Hospital</dt>
        <dd class="col-sm-8">
            <?php echo htmlspecialchars($booking['hospital_name']); ?><br>
            <small><?php echo htmlspecialchars($booking['hospital_address']); ?></small><br>
            <small><i class="fas fa-phone"></i> <?php echo htmlspecialchars($booking['hospital_phone']); ?></small>
        </dd>

        <dt class="col-sm-4">Date & Time</dt>
        <dd class="col-sm-8"><?php echo formatDate($booking['booking_date']) . ' at ' . formatTime($booking['booking_time']); ?></dd>

        <dt class="col-sm-4">Status</dt>
        <dd class="col-sm-8"><?php echo htmlspecialchars($booking['status']); ?></dd>

        <dt class="col-sm-4">Certificate Number</dt>
        <dd class="col-sm-8"><?php echo $booking['certificate_number'] ?: 'Not issued'; ?></dd>

        <dt class="col-sm-4">Vaccination Date</dt>
        <dd class="col-sm-8"><?php echo formatDate($booking['vaccination_date']); ?></dd>

        <dt class="col-sm-4">Booked On</dt>
        <dd class="col-sm-8"><?php echo formatDate($booking['created_at']); ?></dd>
    </dl>
    <?php
}
?>
