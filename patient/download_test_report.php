<?php
session_start();

// Security check
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    die("Unauthorized access.");
}

$patient_id = $_SESSION['user_id'];
$test_id = $_GET['id'];

// ✅ Correct DB credentials for your setup
$dsn = 'mysql:host=localhost;dbname=covid_booking_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

// Utility functions
function formatDate($date) {
    return (!empty($date) && $date !== '0000-00-00') ? date('M d, Y', strtotime($date)) : 'N/A';
}

function formatTime($time) {
    return (!empty($time)) ? date('h:i A', strtotime($time)) : 'N/A';
}

// Fetch test booking
$stmt = $pdo->prepare("SELECT tb.*, h.hospital_name, h.address AS hospital_address, h.phone AS hospital_phone
                       FROM test_bookings tb
                       JOIN hospitals h ON tb.hospital_id = h.id
                       WHERE tb.id = :id AND tb.patient_id = :patient_id");
$stmt->execute([':id' => $test_id, ':patient_id' => $patient_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking || strtolower($booking['test_result']) === 'pending') {
    die("Test result not available.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>COVID-19 Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; color: #333; }
        .header, .footer { text-align: center; }
        .header h2 { margin-bottom: 0; }
        .header p { margin: 5px 0; }
        .section { margin-top: 30px; }
        .section h4 { border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        td, th { padding: 10px; border: 1px solid #ccc; }
        .result { font-size: 1.2em; font-weight: bold; text-transform: uppercase; }
        .positive { color: red; }
        .negative { color: green; }
        .print-button { margin-top: 20px; text-align: center; }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        @media print {
            .print-button { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2><?php echo htmlspecialchars($booking['hospital_name']); ?></h2>
        <p><?php echo htmlspecialchars($booking['hospital_address']); ?></p>
        <p>Phone: <?php echo htmlspecialchars($booking['hospital_phone']); ?></p>
        <hr>
        <h3>COVID-19 Test Report</h3>
    </div>

    <div class="section">
        <h4>Patient Information</h4>
        <table>
            <tr><th>Patient ID</th><td><?php echo htmlspecialchars($patient_id); ?></td></tr>
            <tr><th>Test Type</th><td><?php echo htmlspecialchars($booking['test_type']); ?></td></tr>
            <tr><th>Booking Date</th><td><?php echo formatDate($booking['booking_date']) . ' at ' . formatTime($booking['booking_time']); ?></td></tr>
            <tr><th>Booked On</th><td><?php echo formatDate($booking['created_at']); ?></td></tr>
        </table>
    </div>

    <div class="section">
        <h4>Test Result</h4>
        <table>
            <tr>
                <th>Result</th>
                <td class="result <?php echo strtolower($booking['test_result']); ?>">
                    <?php echo htmlspecialchars($booking['test_result']); ?>
                </td>
            </tr>
            <?php if (!empty($booking['result_date'])): ?>
                <tr><th>Result Date</th><td><?php echo formatDate($booking['result_date']); ?></td></tr>
            <?php endif; ?>
            <tr><th>Price</th><td>₹<?php echo number_format($booking['price'], 2); ?></td></tr>
        </table>
    </div>

    <div class="footer">
        <p>This report is generated electronically and does not require a signature.</p>
    </div>

    <div class="print-button">
        <a href="#" onclick="window.print()" class="btn">Print Again</a>
    </div>
</body>
</html>
