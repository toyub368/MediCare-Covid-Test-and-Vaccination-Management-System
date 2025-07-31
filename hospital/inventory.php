<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin('hospital');

$db = new Database();
$hospital_id = $_SESSION['user_id'];

// Handle actions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_vaccine':
                $vaccine_name = sanitize($_POST['vaccine_name']);
                $available_doses = (int)$_POST['available_doses'];
                $price = (float)$_POST['price'];
                
                // Check if vaccine already exists for this hospital
                $db->query("SELECT id FROM vaccine_inventory WHERE hospital_id = :hospital_id AND vaccine_name = :vaccine_name");
                $db->bind(':hospital_id', $hospital_id);
                $db->bind(':vaccine_name', $vaccine_name);
                
                if ($db->single()) {
                    $error = "This vaccine already exists in your inventory. Please update the existing entry.";
                } else {
                    $db->query("INSERT INTO vaccine_inventory (hospital_id, vaccine_name, available_doses, price) 
                               VALUES (:hospital_id, :vaccine_name, :doses, :price)");
                    $db->bind(':hospital_id', $hospital_id);
                    $db->bind(':vaccine_name', $vaccine_name);
                    $db->bind(':doses', $available_doses);
                    $db->bind(':price', $price);
                    
                    if ($db->execute()) {
                        $success = "Vaccine added to inventory successfully";
                    } else {
                        $error = "Failed to add vaccine to inventory";
                    }
                }
                break;
                
            case 'update_inventory':
                $inventory_id = (int)$_POST['inventory_id'];
                $available_doses = (int)$_POST['available_doses'];
                $price = (float)$_POST['price'];
                
                $db->query("UPDATE vaccine_inventory SET available_doses = :doses, price = :price WHERE id = :id AND hospital_id = :hospital_id");
                $db->bind(':doses', $available_doses);
                $db->bind(':price', $price);
                $db->bind(':id', $inventory_id);
                $db->bind(':hospital_id', $hospital_id);
                
                if ($db->execute()) {
                    $success = "Vaccine inventory updated successfully";
                } else {
                    $error = "Failed to update vaccine inventory";
                }
                break;
                
            case 'delete_inventory':
                $inventory_id = (int)$_POST['inventory_id'];
                $db->query("DELETE FROM vaccine_inventory WHERE id = :id AND hospital_id = :hospital_id");
                $db->bind(':id', $inventory_id);
                $db->bind(':hospital_id', $hospital_id);
                
                if ($db->execute()) {
                    $success = "Vaccine removed from inventory successfully";
                } else {
                    $error = "Failed to remove vaccine from inventory";
                }
                break;
        }
    }
}

// Get vaccine inventory for this hospital
$db->query("SELECT * FROM vaccine_inventory WHERE hospital_id = :hospital_id ORDER BY vaccine_name");
$db->bind(':hospital_id', $hospital_id);
$inventory = $db->resultset();

// Export functionality
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    exportToExcel($inventory, 'vaccine_inventory');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaccine Inventory - Hospital Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 pb-0">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h4 class="mb-0">
                                        <i class="fas fa-boxes me-2 text-primary"></i>Vaccine Inventory Management
                                    </h4>
                                    <p class="text-muted mb-0">Manage your hospital's vaccine stock and pricing</p>
                                </div>
                                <div class="col-auto">
                                    <div class="d-flex gap-2">
                                        <input type="text" class="form-control search-input" placeholder="Search vaccines..." data-target="#inventoryTable">
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVaccineModal">
                                            <i class="fas fa-plus me-2"></i>Add Vaccine
                                        </button>
                                        <a href="?export=excel" class="btn btn-success">
                                            <i class="fas fa-file-excel me-2"></i>Export
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (isset($success)): ?>
                                <div class="alert alert-success alert-custom">
                                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger alert-custom">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (empty($inventory)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-boxes fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No vaccines in inventory</h5>
                                    <p class="text-muted">Add vaccines to your inventory to start accepting vaccination bookings.</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVaccineModal">
                                        <i class="fas fa-plus me-2"></i>Add Your First Vaccine
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="inventoryTable">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>ID</th>
                                                <th>Vaccine Name</th>
                                                <th>Available Doses</th>
                                                <th>Price per Dose</th>
                                                <th>Total Value</th>
                                                <th>Stock Status</th>
                                                <th>Last Updated</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($inventory as $item): ?>
                                                <tr>
                                                    <td><?php echo $item['id']; ?></td>
                                                    <td>
                                                        <span class="badge bg-success fs-6"><?php echo $item['vaccine_name']; ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $item['available_doses'] > 50 ? 'success' : ($item['available_doses'] > 10 ? 'warning' : 'danger'); ?> fs-6">
                                                            <?php echo $item['available_doses']; ?> doses
                                                        </span>
                                                    </td>
                                                    <td>PKR <?php echo number_format($item['price'], 2); ?></td>
                                                    <td>PKR <?php echo number_format($item['available_doses'] * $item['price'], 2); ?></td>
                                                    <td>
                                                        <?php if ($item['available_doses'] > 50): ?>
                                                            <span class="badge bg-success">In Stock</span>
                                                        <?php elseif ($item['available_doses'] > 10): ?>
                                                            <span class="badge bg-warning">Low Stock</span>
                                                        <?php elseif ($item['available_doses'] > 0): ?>
                                                            <span class="badge bg-danger">Critical</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-dark">Out of Stock</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo formatDateTime($item['updated_at']); ?></td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editInventory(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteInventory(<?php echo $item['id']; ?>)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Vaccine Modal -->
    <div class="modal fade" id="addVaccineModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header gradient-bg text-white">
                    <h5 class="modal-title">Add Vaccine to Inventory</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_vaccine">
                        
                        <div class="mb-3">
                            <label for="vaccine_name" class="form-label">Vaccine Name</label>
                            <select class="form-select" name="vaccine_name" required>
                                <option value="">Select Vaccine</option>
                                <option value="Covishield">Covishield</option>
                                <option value="Covaxin">Covaxin</option>
                                <option value="Sputnik V">Sputnik V</option>
                                <option value="Pfizer">Pfizer</option>
                                <option value="Moderna">Moderna</option>
                                <option value="Johnson & Johnson">Johnson & Johnson</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="available_doses" class="form-label">Available Doses</label>
                            <input type="number" class="form-control" name="available_doses" min="0" required>
                            <small class="text-muted">Number of vaccine doses available</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="price" class="form-label">Price per Dose (PKR)</label>
                            <input type="number" class="form-control" name="price" step="0.01" min="0" required>
                            <small class="text-muted">Price in PKR per vaccination dose</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Vaccine</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Inventory Modal -->
    <div class="modal fade" id="editInventoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header gradient-bg text-white">
                    <h5 class="modal-title">Update Vaccine Inventory</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_inventory">
                        <input type="hidden" name="inventory_id" id="editInventoryId">
                        
                        <div class="mb-3">
                            <label class="form-label">Vaccine Name</label>
                            <input type="text" class="form-control" id="editVaccineName" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_available_doses" class="form-label">Available Doses</label>
                            <input type="number" class="form-control" name="available_doses" id="editAvailableDoses" min="0" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_price" class="form-label">Price per Dose (PKR)</label>
                            <input type="number" class="form-control" name="price" id="editPrice" step="0.01" min="0" required>
                            <small class="text-muted">Price in PKR per vaccination dose</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Inventory</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        function editInventory(item) {
            document.getElementById('editInventoryId').value = item.id;
            document.getElementById('editVaccineName').value = item.vaccine_name;
            document.getElementById('editAvailableDoses').value = item.available_doses;
            document.getElementById('editPrice').value = item.price;
            
            new bootstrap.Modal(document.getElementById('editInventoryModal')).show();
        }

        function deleteInventory(inventoryId) {
            if (confirm('Are you sure you want to remove this vaccine from inventory? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_inventory">
                    <input type="hidden" name="inventory_id" value="${inventoryId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>