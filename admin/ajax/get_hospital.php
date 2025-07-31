<?php
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
requireLogin('admin');

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Hospital ID required']);
    exit;
}

$hospital_id = (int)$_GET['id'];
$db = new Database();

$db->query("SELECT * FROM hospitals WHERE id = :id");
$db->bind(':id', $hospital_id);
$hospital = $db->single();

if ($hospital) {
    echo json_encode(['success' => true, 'hospital' => $hospital]);
} else {
    echo json_encode(['success' => false, 'message' => 'Hospital not found']);
}
?>