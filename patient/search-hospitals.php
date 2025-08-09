<?php

require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('patient');

$db = new Database();

// Handle search filters
$city = $_GET['city'] ?? '';
$service = $_GET['service'] ?? '';
$search = $_GET['search'] ?? '';

// Build query based on filters
$query = "SELECT h.*, 
                 COUNT(DISTINCT vi.id) as vaccine_count,
                 GROUP_CONCAT(DISTINCT vi.vaccine_name) as available_vaccines
          FROM hospitals h 
          LEFT JOIN vaccine_inventory vi ON h.id = vi.hospital_id AND vi.available_doses > 0
          WHERE h.status = 'approved'";

$params = [];

if (!empty($city)) {
    $query .= " AND h.city LIKE :city";
    $params[':city'] = "%$city%";
}

if (!empty($search)) {
    $query .= " AND (h.hospital_name LIKE :search OR h.address LIKE :search2)";
    $params[':search'] = "%$search%";
    $params[':search2'] = "%$search%";
}

if ($service === 'test') {
    $query .= " AND h.test_available = 1";
} elseif ($service === 'vaccine') {
    $query .= " AND h.vaccine_available = 1";
}

$query .= " GROUP BY h.id ORDER BY h.hospital_name";

$db->query($query);
foreach ($params as $param => $value) {
    $db->bind($param, $value);
}
$hospitals = $db->resultset();

// Get unique cities for filter
$db->query("SELECT DISTINCT city FROM hospitals WHERE status = 'approved' ORDER BY city");
$cities = $db->resultset();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Hospitals - Patient Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container-fluid">
            <!-- Search Filters -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pb-0">
                    <h4 class="mb-0">
                        <i class="fas fa-search me-2 text-primary"></i>Find Hospitals
                    </h4>
                    <p class="text-muted mb-0">Search for hospitals offering COVID-19 tests and vaccinations</p>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Hospital Name or Location</label>
                            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search hospitals...">
                        </div>
                        <div class="col-md-3">
                            <label for="city" class="form-label">City</label>
                            <select class="form-select" name="city">
                                <option value="">All Cities</option>
                                <?php foreach ($cities as $city_option): ?>
                                    <option value="<?php echo htmlspecialchars($city_option['city']); ?>" 
                                            <?php echo $city === $city_option['city'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($city_option['city']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="service" class="form-label">Service Type</label>
                            <select class="form-select" name="service">
                                <option value="">All Services</option>
                                <option value="test" <?php echo $service === 'test' ? 'selected' : ''; ?>>COVID Tests</option>
                                <option value="vaccine" <?php echo $service === 'vaccine' ? 'selected' : ''; ?>>Vaccinations</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                            <a href="search-hospitals.php" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Search Results -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-hospital me-2 text-primary"></i>Available Hospitals
                                    <span class="badge bg-primary ms-2"><?php echo count($hospitals); ?> found</span>
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (empty($hospitals)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No hospitals found</h5>
                                    <p class="text-muted">Try adjusting your search criteria or browse all available hospitals.</p>
                                    <a href="search-hospitals.php" class="btn btn-primary">View All Hospitals</a>
                                </div>
                            <?php else: ?>
                                <div class="row g-4">
                                    <?php foreach ($hospitals as $hospital): ?>
                                        <div class="col-lg-6">
                                            <div class="card border-0 shadow-sm h-100 hospital-card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-start mb-3">
                                                        <!-- Fixed: Added justify-content-center to center the icon -->
                                                        <div class="bg-info rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                                            <i class="fas fa-hospital text-white fa-lg"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($hospital['hospital_name']); ?></h5>
                                                            <p class="text-muted mb-0">
                                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                                <?php echo htmlspecialchars($hospital['city']); ?>, <?php echo htmlspecialchars($hospital['state']); ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <p class="text-muted mb-2">
                                                            <i class="fas fa-map-marker-alt me-2"></i>
                                                            <?php echo htmlspecialchars($hospital['address']); ?>
                                                        </p>
                                                        <p class="text-muted mb-2">
                                                            <i class="fas fa-phone me-2"></i>
                                                            <?php echo htmlspecialchars($hospital['phone']); ?>
                                                        </p>
                                                        <p class="text-muted mb-0">
                                                            <i class="fas fa-envelope me-2"></i>
                                                            <?php echo htmlspecialchars($hospital['email']); ?>
                                                        </p>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <h6 class="mb-2">Available Services:</h6>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <?php if ($hospital['test_available']): ?>
                                                                <span class="badge bg-primary">
                                                                    <i class="fas fa-vial me-1"></i>COVID Tests
                                                                </span>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($hospital['vaccine_available'] && $hospital['vaccine_count'] > 0): ?>
                                                                <span class="badge bg-success">
                                                                    <i class="fas fa-syringe me-1"></i>Vaccinations
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <?php if ($hospital['available_vaccines']): ?>
                                                            <div class="mt-2">
                                                                <small class="text-muted">Available Vaccines:</small><br>
                                                                <?php 
                                                                $vaccines = explode(',', $hospital['available_vaccines']);
                                                                foreach ($vaccines as $vaccine): 
                                                                    if (trim($vaccine)):
                                                                ?>
                                                                    <span class="badge bg-light text-dark me-1"><?php echo trim($vaccine); ?></span>
                                                                <?php 
                                                                    endif;
                                                                endforeach; 
                                                                ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="d-grid gap-2 d-md-flex">
                                                        <?php if ($hospital['test_available']): ?>
                                                            <a href="book-test.php?hospital_id=<?php echo $hospital['id']; ?>" class="btn btn-outline-primary flex-fill">
                                                                <i class="fas fa-vial me-2"></i>Book Test
                                                            </a>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($hospital['vaccine_available'] && $hospital['vaccine_count'] > 0): ?>
                                                            <a href="book-vaccination.php?hospital_id=<?php echo $hospital['id']; ?>" class="btn btn-outline-success flex-fill">
                                                                <i class="fas fa-syringe me-2"></i>Book Vaccination
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <style>
        .hospital-card {
            transition: transform 0.2s ease-in-out;
        }
        
        .hospital-card:hover {
            transform: translateY(-5px);
        }
    </style>
</body>
</html>