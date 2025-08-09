<?php

require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('patient');



if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid certificate ID.");
}

$booking_id = $_GET['id'];
$patient_id = $_SESSION['user_id'];

$db = new Database();
$db->query("SELECT vb.*, h.hospital_name, h.address, h.phone, p.full_name, p.cnic
            FROM vaccination_bookings vb
            JOIN hospitals h ON vb.hospital_id = h.id
            JOIN patients p ON vb.patient_id = p.id
            WHERE vb.id = :id AND vb.patient_id = :patient_id");
$db->bind(':id', $booking_id);
$db->bind(':patient_id', $patient_id);
$certificate = $db->single();

if (!$certificate) {
    die("Certificate not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>COVID-19 Vaccination Certificate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
            padding: 30px;
            position: relative;
        }
        .certificate-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: auto;
            position: relative;
        }
        .certificate-title {
            color: #28a745;
            font-size: 32px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 20px;
            margin-top: 15px;
        }
        .certificate-sub {
            text-align: center;
            color: #6c757d;
            margin-bottom: 40px;
        }
        .info-row {
            margin-bottom: 20px;
        }
        .info-label {
            font-weight: 600;
            color: #343a40;
        }
        .info-value {
            font-size: 18px;
            color: #495057;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-style: italic;
            color: #28a745;
        }
        .action-buttons {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 10;
        }

        @media print {
            body {
                padding: 0;
            }
            .certificate-container {
                box-shadow: none;
                margin: 0;
                border: none;
                padding: 30px 20px;
            }
            .no-print {
                display: none;
            }
            .certificate-title {
                margin-top: 0;
            }
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="action-buttons no-print">
            <div class="btn-group">
                <a href="vaccination-certificate.php" class="btn btn-sm btn-outline-secondary" title="Back to History">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <button onclick="window.print()" class="btn btn-sm btn-outline-primary" title="Print Certificate">
                    <i class="fas fa-print"></i>
                </button>
            </div>
        </div>
        
        <div class="certificate-title">
            COVID-19 VACCINATION CERTIFICATE
        </div>
        <div class="certificate-sub">
            Issued by Health Department, Government of India
        </div>

        <div class="info-row row">
            <div class="col-md-6">
                <div class="info-label">Name</div>
                <div class="info-value"><?php echo htmlspecialchars($certificate['full_name']); ?></div>
            </div>
            <div class="col-md-6">
                <div class="info-label">CNIC</div>
                <div class="info-value"><?php echo htmlspecialchars($certificate['cnic']); ?></div>
            </div>
        </div>

        <div class="info-row row">
            <div class="col-md-6">
                <div class="info-label">Vaccine</div>
                <div class="info-value"><?php echo htmlspecialchars($certificate['vaccine_name']); ?></div>
            </div>
            <div class="col-md-6">
                <div class="info-label">Dose Number</div>
                <div class="info-value">Dose <?php echo htmlspecialchars($certificate['dose_number']); ?></div>
            </div>
        </div>

        <div class="info-row row">
            <div class="col-md-6">
                <div class="info-label">Vaccination Date</div>
                <div class="info-value"><?php echo formatDate($certificate['vaccination_date']); ?></div>
            </div>
            <div class="col-md-6">
                <div class="info-label">Certificate No.</div>
                <div class="info-value"><?php echo htmlspecialchars($certificate['certificate_number']); ?></div>
            </div>
        </div>

        <div class="info-row row">
            <div class="col-12">
                <div class="info-label">Vaccination Center</div>
                <div class="info-value">
                    <?php echo htmlspecialchars($certificate['hospital_name']); ?>,
                    <?php echo htmlspecialchars($certificate['address']); ?>
                    (Phone: <?php echo htmlspecialchars($certificate['phone']); ?>)
                </div>
            </div>
        </div>

        <div class="footer">
            ✔ This certificate confirms that the above individual has been vaccinated against COVID-19.
        </div>
    </div>
</body>
</html>