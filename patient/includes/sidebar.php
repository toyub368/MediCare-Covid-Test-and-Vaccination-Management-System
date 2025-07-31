<div class="sidebar">
    <div class="p-4">
        <div class="d-flex align-items-center mb-4">
            <div class="auth-logo me-3" style="width: 40px; height: 40px; font-size: 18px;">
                <i class="fas fa-user-injured"></i>
            </div>
            <div>
                <h6 class="text-white mb-0">COVID-19</h6>
                <small class="text-white-50">Patient Portal</small>
            </div>
        </div>
        
        <nav class="nav flex-column">
            <a href="dashboard.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt me-3"></i>Dashboard
            </a>
            
            <a href="search-hospitals.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'search-hospitals.php' ? 'active' : ''; ?>">
                <i class="fas fa-search me-3"></i>Search Hospitals
            </a>
            
            <a href="book-test.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'book-test.php' ? 'active' : ''; ?>">
                <i class="fas fa-vial me-3"></i>Book COVID Test
            </a>
            
            <a href="book-vaccination.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'book-vaccination.php' ? 'active' : ''; ?>">
                <i class="fas fa-syringe me-3"></i>Book Vaccination
            </a>
            
            <a href="appointments.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt me-3"></i>My Appointments
            </a>
            
            <a href="test-results.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'test-results.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-medical me-3"></i>Test Results
            </a>
            
            <a href="vaccination-certificate.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'vaccination-certificate.php' ? 'active' : ''; ?>">
                <i class="fas fa-certificate me-3"></i>Vaccination Certificate
            </a>
            
            <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
            
            <a href="profile.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user me-3"></i>My Profile
            </a>
            
            <a href="../auth/logout.php" class="sidebar-link">
                <i class="fas fa-sign-out-alt me-3"></i>Logout
            </a>
        </nav>
    </div>
</div>