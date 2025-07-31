<?php
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
requireLogin('hospital');

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Patient ID required']);
    exit;
}

$patient_id = (int)$_GET['id'];
$hospital_id = $_SESSION['user_id'];
$db = new Database();

// Get test bookings
$db->query("SELECT * FROM test_bookings WHERE patient_id = :patient_id AND hospital_id = :hospital_id ORDER BY created_at DESC");
$db->bind(':patient_id', $patient_id);
$db->bind(':hospital_id', $hospital_id);
$tests = $db->resultset();

// Get vaccination bookings
$db->query("SELECT * FROM vaccination_bookings WHERE patient_id = :patient_id AND hospital_id = :hospital_id ORDER BY created_at DESC");
$db->bind(':patient_id', $patient_id);
$db->bind(':hospital_id', $hospital_id);
$vaccinations = $db->resultset();

echo json_encode([
    'success' => true, 
    'tests' => $tests,
    'vaccinations' => $vaccinations
]);
?>