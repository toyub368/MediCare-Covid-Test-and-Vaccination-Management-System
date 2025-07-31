<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('patient');

$db = new Database();
$patient_id = $_SESSION['user_id'];

// Get hospital ID from URL if provided
$selected_hospital_id = $_GET['hospital_id'] ?? '';

// Handle form submission
if ($_POST) {
    $hospital_id = (int)$_POST['hospital_id'];
    $test_type = $_POST['test_type'];
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];
    
    // Validate inputs
    if (empty($hospital_id) || empty($test_type) || empty($booking_date) || empty($booking_time)) {
        $error = 'Please fill in all required fields';
    } elseif (strtotime($booking_date) < strtotime(date('Y-m-d'))) {
        $error = 'Booking date cannot be in the past';
    } else {
        // Check if hospital exists and offers tests
        $db->query("SELECT * FROM hospitals WHERE id = :id AND status = 'approved' AND test_available = 1");
        $db->bind(':id', $hospital_id);
        $hospital = $db->single();
        
        if (!$hospital) {
            $error = 'Selected hospital is not available for test bookings';
        } else {
            // Set price based on test type
            $prices = [
                'RT-PCR' => 500.00,
                'Rapid Antigen' => 300.00,
                'Antibody' => 400.00
            ];
            $price = $prices[$test_type] ?? 0;
            
            // Insert booking
            $db->query("INSERT INTO test_bookings (patient_id, hospital_id, test_type, booking_date, booking_time, price) 
                       VALUES (:patient_id, :hospital_id, :test_type, :booking_date, :booking_time, :price)");
            $db->bind(':patient_id', $patient_id);
            $db->bind(':hospital_id', $hospital_id);
            $db->bind(':test_type', $test_type);
            $db->bind(':booking_date', $booking_date);
            $db->bind(':booking_time', $booking_time);
            $db->bind(':price', $price);
            
            if ($db->execute()) {
                $success = "Test booking submitted successfully! You will receive confirmation once the hospital approves your request.";
                // Clear form
                $_POST = [];
                $selected_hospital_id = '';
            } else {
                $error = "Failed to submit test booking. Please try again.";
            }
        }
    }
}

// Get available hospitals for tests
$db->query("SELECT * FROM hospitals WHERE status = 'approved' AND test_available = 1 ORDER BY hospital_name");
$hospitals = $db->resultset();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book COVID Test - Patient Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 pb-0">
                            <h4 class="mb-0">
                                <i class="fas fa-vial me-2 text-primary"></i>Book COVID-19 Test
                            </h4>
                            <p class="text-muted mb-0">Schedule your COVID-19 test appointment</p>
                        </div>
                        <div class="card-body">
                            <?php if (isset($success)): ?>
                                <div class="alert alert-success alert-custom">
                                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                    <div class="mt-2">
                                        <a href="appointments.php" class="btn btn-sm btn-outline-success me-2">View Appointments</a>
                                        <a href="search-hospitals.php" class="btn btn-sm btn-outline-primary">Book Another Test</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger alert-custom">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (empty($hospitals)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-hospital fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No hospitals available</h5>
                                    <p class="text-muted">Currently, no hospitals are offering COVID-19 test services.</p>
                                    <a href="search-hospitals.php" class="btn btn-primary">Search Hospitals</a>
                                </div>
                            <?php else: ?>
                                <form method="POST" class="needs-validation" novalidate>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="hospital_id" class="form-label">Select Hospital *</label>
                                            <select class="form-select" name="hospital_id" required>
                                                <option value="">Choose a hospital</option>
                                                <?php foreach ($hospitals as $hospital): ?>
                                                    <option value="<?php echo $hospital['id']; ?>" 
                                                            <?php echo $selected_hospital_id == $hospital['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($hospital['hospital_name']); ?> - <?php echo htmlspecialchars($hospital['city']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="test_type" class="form-label">Test Type *</label>
                                            <select class="form-select" name="test_type" required>
                                                <option value="">Select test type</option>
                                                <option value="RT-PCR" <?php echo ($_POST['test_type'] ?? '') === 'RT-PCR' ? 'selected' : ''; ?>>
                                                    RT-PCR Test (₹500) - Most accurate
                                                </option>
                                                <option value="Rapid Antigen" <?php echo ($_POST['test_type'] ?? '') === 'Rapid Antigen' ? 'selected' : ''; ?>>
                                                    Rapid Antigen Test (₹300) - Quick results
                                                </option>
                                                <option value="Antibody" <?php echo ($_POST['test_type'] ?? '') === 'Antibody' ? 'selected' : ''; ?>>
                                                    Antibody Test (₹400) - Check immunity
                                                </option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="booking_date" class="form-label">Preferred Date *</label>
                                            <input type="date" class="form-control" name="booking_date" 
                                                   value="<?php echo htmlspecialchars($_POST['booking_date'] ?? ''); ?>"
                                                   min="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="booking_time" class="form-label">Preferred Time *</label>
                                            <select class="form-select" name="booking_time" required>
                                                <option value="">Select time slot</option>
                                                <option value="09:00:00" <?php echo ($_POST['booking_time'] ?? '') === '09:00:00' ? 'selected' : ''; ?>>09:00 AM</option>
                                                <option value="10:00:00" <?php echo ($_POST['booking_time'] ?? '') === '10:00:00' ? 'selected' : ''; ?>>10:00 AM</option>
                                                <option value="11:00:00" <?php echo ($_POST['booking_time'] ?? '') === '11:00:00' ? 'selected' : ''; ?>>11:00 AM</option>
                                                <option value="12:00:00" <?php echo ($_POST['booking_time'] ?? '') === '12:00:00' ? 'selected' : ''; ?>>12:00 PM</option>
                                                <option value="14:00:00" <?php echo ($_POST['booking_time'] ?? '') === '14:00:00' ? 'selected' : ''; ?>>02:00 PM</option>
                                                <option value="15:00:00" <?php echo ($_POST['booking_time'] ?? '') === '15:00:00' ? 'selected' : ''; ?>>03:00 PM</option>
                                                <option value="16:00:00" <?php echo ($_POST['booking_time'] ?? '') === '16:00:00' ? 'selected' : ''; ?>>04:00 PM</option>
                                                <option value="17:00:00" <?php echo ($_POST['booking_time'] ?? '') === '17:00:00' ? 'selected' : ''; ?>>05:00 PM</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle me-2"></i>Important Information:</h6>
                                        <ul class="mb-0">
                                            <li>Your booking request will be sent to the hospital for approval</li>
                                            <li>You will receive confirmation once the hospital approves your request</li>
                                            <li>Please arrive 15 minutes before your scheduled time</li>
                                            <li>Bring a valid ID proof and wear a mask</li>
                                            <li>Payment can be made at the hospital</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <a href="search-hospitals.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left me-2"></i>Back to Search
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-calendar-plus me-2"></i>Submit Booking Request
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Test Information -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2 text-primary"></i>COVID-19 Test Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fas fa-vial fa-2x text-primary mb-2"></i>
                                        <h6>RT-PCR Test</h6>
                                        <p class="text-muted small mb-0">Most accurate test. Results in 24-48 hours. Recommended for travel and official purposes.</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fas fa-clock fa-2x text-success mb-2"></i>
                                        <h6>Rapid Antigen</h6>
                                        <p class="text-muted small mb-0">Quick results in 15-30 minutes. Good for immediate screening and contact tracing.</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fas fa-shield-alt fa-2x text-info mb-2"></i>
                                        <h6>Antibody Test</h6>
                                        <p class="text-muted small mb-0">Checks for past infection and immunity. Results in 1-2 hours.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>