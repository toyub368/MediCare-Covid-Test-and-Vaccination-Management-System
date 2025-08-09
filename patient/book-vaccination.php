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
    $vaccine_name = $_POST['vaccine_name'];
    $dose_number = $_POST['dose_number'];
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];
    
    // Validate inputs
    if (empty($hospital_id) || empty($vaccine_name) || empty($dose_number) || empty($booking_date) || empty($booking_time)) {
        $error = 'Please fill in all required fields';
    } elseif (strtotime($booking_date) < strtotime(date('Y-m-d'))) {
        $error = 'Booking date cannot be in the past';
    } else {
        // Check if hospital has the vaccine available
        $db->query("SELECT vi.*, h.hospital_name FROM vaccine_inventory vi 
                   JOIN hospitals h ON vi.hospital_id = h.id 
                   WHERE vi.hospital_id = :hospital_id AND vi.vaccine_name = :vaccine_name 
                   AND vi.available_doses > 0 AND h.status = 'approved' AND h.vaccine_available = 1");
        $db->bind(':hospital_id', $hospital_id);
        $db->bind(':vaccine_name', $vaccine_name);
        $vaccine_info = $db->single();
        
        if (!$vaccine_info) {
            $error = 'Selected vaccine is not available at this hospital';
        } else {
            // Check if patient already has a pending/approved booking for the same vaccine and dose
            $db->query("SELECT id FROM vaccination_bookings 
                       WHERE patient_id = :patient_id AND vaccine_name = :vaccine_name 
                       AND dose_number = :dose_number AND status IN ('pending', 'approved')");
            $db->bind(':patient_id', $patient_id);
            $db->bind(':vaccine_name', $vaccine_name);
            $db->bind(':dose_number', $dose_number);
            
            if ($db->single()) {
                $error = 'You already have a pending or approved booking for this vaccine dose';
            } else {
                // Insert booking
                $db->query("INSERT INTO vaccination_bookings (patient_id, hospital_id, vaccine_name, dose_number, booking_date, booking_time) 
                           VALUES (:patient_id, :hospital_id, :vaccine_name, :dose_number, :booking_date, :booking_time)");
                $db->bind(':patient_id', $patient_id);
                $db->bind(':hospital_id', $hospital_id);
                $db->bind(':vaccine_name', $vaccine_name);
                $db->bind(':dose_number', $dose_number);
                $db->bind(':booking_date', $booking_date);
                $db->bind(':booking_time', $booking_time);
                
                if ($db->execute()) {
                    $success = "Vaccination booking submitted successfully! You will receive confirmation once the hospital approves your request.";
                    // Clear form
                    $_POST = [];
                    $selected_hospital_id = '';
                } else {
                    $error = "Failed to submit vaccination booking. Please try again.";
                }
            }
        }
    }
}

// Get available hospitals with vaccines
$db->query("SELECT DISTINCT h.*, vi.vaccine_name, vi.price 
           FROM hospitals h 
           JOIN vaccine_inventory vi ON h.id = vi.hospital_id 
           WHERE h.status = 'approved' AND h.vaccine_available = 1 AND vi.available_doses > 0 
           ORDER BY h.hospital_name, vi.vaccine_name");
$hospital_vaccines = $db->resultset();

// Group by hospital
$hospitals = [];
foreach ($hospital_vaccines as $hv) {
    if (!isset($hospitals[$hv['id']])) {
        $hospitals[$hv['id']] = [
            'id' => $hv['id'],
            'hospital_name' => $hv['hospital_name'],
            'city' => $hv['city'],
            'vaccines' => []
        ];
    }
    $hospitals[$hv['id']]['vaccines'][] = [
        'name' => $hv['vaccine_name'],
        'price' => $hv['price']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Vaccination - Patient Portal</title>
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
                                <i class="fas fa-syringe me-2 text-primary"></i>Book COVID-19 Vaccination
                            </h4>
                            <p class="text-muted mb-0">Schedule your COVID-19 vaccination appointment</p>
                        </div>
                        <div class="card-body">
                            <?php if (isset($success)): ?>
                                <div class="alert alert-success alert-custom">
                                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                    <div class="mt-2">
                                        <a href="appointments.php" class="btn btn-sm btn-outline-success me-2">View Appointments</a>
                                        <a href="search-hospitals.php" class="btn btn-sm btn-outline-primary">Book Another Vaccination</a>
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
                                    <i class="fas fa-syringe fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No vaccines available</h5>
                                    <p class="text-muted">Currently, no hospitals have vaccines in stock.</p>
                                    <a href="search-hospitals.php" class="btn btn-primary">Search Hospitals</a>
                                </div>
                            <?php else: ?>
                                <form method="POST" class="needs-validation" novalidate>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="hospital_id" class="form-label">Select Hospital *</label>
                                            <select class="form-select" name="hospital_id" id="hospital_id" required onchange="updateVaccines()">
                                                <option value="">Choose a hospital</option>
                                                <?php foreach ($hospitals as $hospital): ?>
                                                    <option value="<?php echo $hospital['id']; ?>" 
                                                            <?php echo $selected_hospital_id == $hospital['id'] ? 'selected' : ''; ?>
                                                            data-vaccines='<?php echo json_encode($hospital['vaccines']); ?>'>
                                                        <?php echo htmlspecialchars($hospital['hospital_name']); ?> - <?php echo htmlspecialchars($hospital['city']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="vaccine_name" class="form-label">Select Vaccine *</label>
                                            <select class="form-select" name="vaccine_name" id="vaccine_name" required>
                                                <option value="">First select a hospital</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="dose_number" class="form-label">Dose Number *</label>
                                            <select class="form-select" name="dose_number" required>
                                                <option value="">Select dose</option>
                                                <option value="1" <?php echo ($_POST['dose_number'] ?? '') === '1' ? 'selected' : ''; ?>>First Dose</option>
                                                <option value="2" <?php echo ($_POST['dose_number'] ?? '') === '2' ? 'selected' : ''; ?>>Second Dose</option>
                                                <option value="booster" <?php echo ($_POST['dose_number'] ?? '') === 'booster' ? 'selected' : ''; ?>>Booster Dose</option>
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
                                            <li>Bring your vaccination card (if you have one) and a valid ID proof</li>
                                            <li>Please arrive 15 minutes before your scheduled time</li>
                                            <li>Stay for 15-30 minutes after vaccination for observation</li>
                                            <li>Payment can be made at the hospital</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <a href="search-hospitals.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left me-2"></i>Back to Search
                                        </a>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-calendar-plus me-2"></i>Submit Booking Request
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Vaccine Information -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2 text-primary"></i>COVID-19 Vaccine Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fas fa-syringe fa-2x text-success mb-2"></i>
                                        <h6>Covishield</h6>
                                        <p class="text-muted small mb-0">Oxford-AstraZeneca vaccine. Two doses required with 4-8 weeks gap.</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fas fa-shield-virus fa-2x text-primary mb-2"></i>
                                        <h6>Covaxin</h6>
                                        <p class="text-muted small mb-0">Bharat Biotech vaccine. Two doses required with 4-6 weeks gap.</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fas fa-virus fa-2x text-info mb-2"></i>
                                        <h6>Sputnik V</h6>
                                        <p class="text-muted small mb-0">Russian vaccine. Two doses required with 3 weeks gap.</p>
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
    <script>
        function updateVaccines() {
            const hospitalSelect = document.getElementById('hospital_id');
            const vaccineSelect = document.getElementById('vaccine_name');
            
            // Clear vaccine options
            vaccineSelect.innerHTML = '<option value="">Select vaccine</option>';
            
            if (hospitalSelect.value) {
                const selectedOption = hospitalSelect.options[hospitalSelect.selectedIndex];
                const vaccines = JSON.parse(selectedOption.dataset.vaccines || '[]');
                
                vaccines.forEach(vaccine => {
                    const option = document.createElement('option');
                    option.value = vaccine.name;
                    option.textContent = `${vaccine.name} (PKR ${parseFloat(vaccine.price).toFixed(2)})`;
                    vaccineSelect.appendChild(option);
                });
            }
        }
        
        // Initialize vaccines if hospital is pre-selected
        document.addEventListener('DOMContentLoaded', function() {
            updateVaccines();
        });
    </script>
</body>
</html>