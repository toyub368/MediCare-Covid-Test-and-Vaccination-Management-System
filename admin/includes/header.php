<nav class="navbar navbar-expand-lg navbar-custom mb-4">
    <div class="container-fluid">
        <button class="btn btn-link d-lg-none" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="navbar-nav ms-auto">
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                    <div class="me-2">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 32px; height: 32px;">
                            <i class="fas fa-user text-white"></i>
                        </div>
                    </div>
                    <span><?php echo $_SESSION['name']; ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile.php">
                        <i class="fas fa-user me-2"></i>Profile
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="../auth/logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>