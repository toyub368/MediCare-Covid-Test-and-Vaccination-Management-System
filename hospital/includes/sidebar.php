<div class="sidebar">
    <div class="p-4">
        <div class="d-flex align-items-center mb-4">
            <div class="auth-logo me-3" style="width: 40px; height: 40px; font-size: 18px;">
                <i class="fas fa-hospital"></i>
            </div>
            <div>
                <h6 class="text-white mb-0">COVID-19</h6>
                <small class="text-white-50">Hospital Panel</small>
            </div>
        </div>
        
        <nav class="nav flex-column">
            <a href="dashboard.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt me-3"></i>Dashboard
            </a>
            
            <a href="patients.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'patients.php' ? 'active' : ''; ?>">
                <i class="fas fa-users me-3"></i>Patients
            </a>
            
            <a href="test-requests.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'test-requests.php' ? 'active' : ''; ?>">
                <i class="fas fa-vial me-3"></i>Test Requests
            </a>
            
            <a href="vaccination-requests.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'vaccination-requests.php' ? 'active' : ''; ?>">
                <i class="fas fa-syringe me-3"></i>Vaccination Requests
            </a>
            
            <a href="inventory.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'active' : ''; ?>">
                <i class="fas fa-boxes me-3"></i>Vaccine Inventory
            </a>
            
            <a href="reports.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar me-3"></i>Reports
            </a>
            
            <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
            
            <a href="profile.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-hospital-user me-3"></i>Hospital Profile
            </a>
            
            <a href="../auth/logout.php" class="sidebar-link">
                <i class="fas fa-sign-out-alt me-3"></i>Logout
            </a>
        </nav>
    </div>
</div>