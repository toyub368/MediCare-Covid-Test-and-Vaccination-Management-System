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
$db = new Database();

$db->query("SELECT * FROM patients WHERE id = :id");
$db->bind(':id', $patient_id);
$patient = $db->single();

if ($patient) {
    echo json_encode(['success' => true, 'patient' => $patient]);
} else {
    echo json_encode(['success' => false, 'message' => 'Patient not found']);
}
?>