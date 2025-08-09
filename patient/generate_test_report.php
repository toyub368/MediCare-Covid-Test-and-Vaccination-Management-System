<?php

require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('patient');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request");
}

$test_id = $_GET['id'];
$patient_id = $_SESSION['user_id'];

$db = new Database();
$db->query("SELECT tb.*, h.hospital_name, h.address as hospital_address, h.phone as hospital_phone, p.full_name, p.email
            FROM test_bookings tb
            JOIN hospitals h ON tb.hospital_id = h.id
            JOIN patients p ON tb.patient_id = p.id
            WHERE tb.id = :id AND tb.patient_id = :patient_id");
$db->bind(':id', $test_id);
$db->bind(':patient_id', $patient_id);
$report = $db->single();

if (!$report) {
    die("Report not found");
}

// Check if we're in download mode
$is_download = isset($_GET['download']) && $_GET['download'] == 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>COVID-19 Test Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f8ff;
            padding: 2rem;
            position: relative;
        }
        .report-card {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            max-width: 800px;
            margin: auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: relative;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #ccc;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
            position: relative;
        }
        .header h2 {
            color: #0056b3;
            margin-top: 10px;
        }
        .section-title {
            font-weight: bold;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
            color: #333;
            border-bottom: 1px solid #ddd;
        }
        .value {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }
        .status {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .status.positive {
            color: red;
        }
        .status.negative {
            color: green;
        }
        .footer {
            text-align: center;
            margin-top: 3rem;
            font-size: 0.9rem;
            color: #666;
        }
        .action-buttons {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 10;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                background: none;
                padding: 0;
            }
            .report-card {
                box-shadow: none;
                margin: 0;
                padding: 1rem;
            }
            .header {
                padding-top: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="report-card">
        <?php if (!$is_download): ?>
            <div class="action-buttons no-print">
                <div class="btn-group">
                    <a href="test-results.php" class="btn btn-sm btn-outline-secondary" title="Back to Results">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <button onclick="window.print()" class="btn btn-sm btn-outline-primary" title="Print Report">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="header">
            <h2>COVID-19 Test Report</h2>
            <p>Report ID: <?php echo htmlspecialchars($report['id']); ?></p>
        </div>

        <div>
            <div class="section-title">Patient Information</div>
            <div class="value">Name: <?php echo htmlspecialchars($report['full_name']); ?></div>
            <div class="value">Email: <?php echo htmlspecialchars($report['email']); ?></div>

            <div class="section-title">Hospital Information</div>
            <div class="value">Hospital: <?php echo htmlspecialchars($report['hospital_name']); ?></div>
            <div class="value">Address: <?php echo htmlspecialchars($report['hospital_address']); ?></div>
            <div class="value">Phone: <?php echo htmlspecialchars($report['hospital_phone']); ?></div>

            <div class="section-title">Test Details</div>
            <div class="value">Test Type: <?php echo htmlspecialchars($report['test_type']); ?></div>
            <div class="value">Booking Date: <?php echo formatDate($report['booking_date']); ?></div>
            <div class="value">Result Date: <?php echo formatDate($report['result_date']); ?></div>
            <div class="value status <?php echo $report['test_result'] === 'positive' ? 'positive' : 'negative'; ?>">
                Result: <?php echo strtoupper($report['test_result']); ?>
            </div>
            <div class="value">Status: <?php echo htmlspecialchars($report['status']); ?></div>
            <div class="value">Price: PKR <?php echo number_format($report['price'] ?? 0, 2); ?></div>
        </div>

        <div class="footer">
            Printed on: <?php echo date("d M Y, h:i A"); ?>
        </div>
    </div>
</body>
</html>