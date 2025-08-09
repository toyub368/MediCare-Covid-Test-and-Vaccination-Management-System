<?php
require_once '../config/database.php';

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function generateCertificateNumber() {
    return 'CERT' . date('Y') . rand(100000, 999999);
}

function formatDate($date) {
    return date('d M Y', strtotime($date));
}

// UPDATED FUNCTION: Handles invalid dates and new format
function formatDateTime($dateString) {
    if (!$dateString || $dateString === '0000-00-00') return 'N/A';
    return date('M j, Y g:i A', strtotime($dateString));
}

// UPDATED FUNCTION: Returns Bootstrap badge class instead of HTML
function getStatusBadge($status) {
    switch (strtolower($status)) {
        case 'completed': return 'success';
        case 'pending': return 'warning';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}

function sendEmail($to, $subject, $message) {
    $headers = "From: noreply@covidbook.com\r\n";
    $headers .= "Reply-To: noreply@covidbook.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    return mail($to, $subject, $message, $headers);
}

function exportToExcel($data, $filename) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
    header('Cache-Control: max-age=0');
    
    echo '<table border="1">';
    if (!empty($data)) {
        echo '<tr>';
        foreach (array_keys($data[0]) as $header) {
            echo '<th>' . ucfirst(str_replace('_', ' ', $header)) . '</th>';
        }
        echo '</tr>';
        
        foreach ($data as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . htmlspecialchars($cell) . '</td>';
            }
            echo '</tr>';
        }
    }
    echo '</table>';
    exit();

    
}

?>