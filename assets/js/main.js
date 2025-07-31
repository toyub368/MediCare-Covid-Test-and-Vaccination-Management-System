// COVID-19 Booking System JavaScript

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeAnimations();
    initializeTooltips();
    initializeModals();
    initializeForms();
    initializeCharts();
});

// Animation Functions
function initializeAnimations() {
    // Add fade-in animation to cards
    const cards = document.querySelectorAll('.card, .stats-card, .glass-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in');
    });

    // Add slide-up animation to tables
    const tables = document.querySelectorAll('.table-custom');
    tables.forEach(table => {
        table.classList.add('slide-up');
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Initialize Bootstrap tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Initialize modals
function initializeModals() {
    // Auto-focus first input in modals
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('shown.bs.modal', function () {
            const firstInput = modal.querySelector('input, select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        });
    });
}

// Form handling
function initializeForms() {
    // Add loading state to forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                submitBtn.disabled = true;
            }
        });
    });

    // Real-time form validation
    document.querySelectorAll('input[type="email"]').forEach(input => {
        input.addEventListener('blur', function() {
            validateEmail(this);
        });
    });

    document.querySelectorAll('input[type="tel"]').forEach(input => {
        input.addEventListener('blur', function() {
            validatePhone(this);
        });
    });

    // Password strength indicator
    document.querySelectorAll('input[type="password"]').forEach(input => {
        if (input.name === 'password' || input.id === 'password') {
            input.addEventListener('input', function() {
                showPasswordStrength(this);
            });
        }
    });
}

// Email validation
function validateEmail(input) {
    const email = input.value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email && !emailRegex.test(email)) {
        input.classList.add('is-invalid');
        showFieldError(input, 'Please enter a valid email address');
    } else {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        hideFieldError(input);
    }
}

// Phone validation
function validatePhone(input) {
    const phone = input.value;
    const phoneRegex = /^[6-9]\d{9}$/;
    
    if (phone && !phoneRegex.test(phone)) {
        input.classList.add('is-invalid');
        showFieldError(input, 'Please enter a valid 10-digit mobile number');
    } else {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        hideFieldError(input);
    }
}

// Password strength
function showPasswordStrength(input) {
    const password = input.value;
    let strength = 0;
    let feedback = '';

    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    const strengthBar = input.parentNode.querySelector('.password-strength');
    if (!strengthBar) {
        const bar = document.createElement('div');
        bar.className = 'password-strength mt-2';
        bar.innerHTML = '<div class="progress" style="height: 5px;"><div class="progress-bar" role="progressbar"></div></div><small class="text-muted"></small>';
        input.parentNode.appendChild(bar);
    }

    const progressBar = input.parentNode.querySelector('.progress-bar');
    const feedbackText = input.parentNode.querySelector('small');

    switch (strength) {
        case 0:
        case 1:
            progressBar.style.width = '20%';
            progressBar.className = 'progress-bar bg-danger';
            feedback = 'Very weak';
            break;
        case 2:
            progressBar.style.width = '40%';
            progressBar.className = 'progress-bar bg-warning';
            feedback = 'Weak';
            break;
        case 3:
            progressBar.style.width = '60%';
            progressBar.className = 'progress-bar bg-info';
            feedback = 'Fair';
            break;
        case 4:
            progressBar.style.width = '80%';
            progressBar.className = 'progress-bar bg-primary';
            feedback = 'Good';
            break;
        case 5:
            progressBar.style.width = '100%';
            progressBar.className = 'progress-bar bg-success';
            feedback = 'Strong';
            break;
    }

    feedbackText.textContent = feedback;
}

// Field error handling
function showFieldError(input, message) {
    hideFieldError(input);
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    input.parentNode.appendChild(errorDiv);
}

function hideFieldError(input) {
    const errorDiv = input.parentNode.querySelector('.invalid-feedback');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Initialize charts (using Chart.js if available)
function initializeCharts() {
    // Test Results Chart
    const testChartCanvas = document.getElementById('testResultsChart');
    if (testChartCanvas && typeof Chart !== 'undefined') {
        new Chart(testChartCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Negative', 'Positive', 'Pending'],
                datasets: [{
                    data: [65, 25, 10],
                    backgroundColor: ['#10b981', '#ef4444', '#f59e0b'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Vaccination Chart
    const vaccineChartCanvas = document.getElementById('vaccinationChart');
    if (vaccineChartCanvas && typeof Chart !== 'undefined') {
        new Chart(vaccineChartCanvas, {
            type: 'bar',
            data: {
                labels: ['Covishield', 'Covaxin', 'Sputnik V'],
                datasets: [{
                    label: 'Doses Administered',
                    data: [450, 320, 180],
                    backgroundColor: ['#2563eb', '#0891b2', '#f59e0b'],
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

// Utility Functions
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show alert-custom`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.main-content') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

function formatTime(timeString) {
    const [hours, minutes] = timeString.split(':');
    const time = new Date();
    time.setHours(hours, minutes);
    return time.toLocaleTimeString('en-US', { 
        hour: 'numeric', 
        minute: '2-digit',
        hour12: true 
    });
}

// Search functionality
function initializeSearch() {
    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const targetTable = document.querySelector(this.dataset.target);
            if (targetTable) {
                const rows = targetTable.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            }
        });
    });
}

// Export functionality
function exportTable(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;

    let csv = '';
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = Array.from(cols).map(col => {
            return '"' + col.textContent.replace(/"/g, '""') + '"';
        }).join(',');
        csv += rowData + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename + '.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

// Print functionality
function printReport(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>COVID-19 Report</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { font-family: Arial, sans-serif; }
                    .no-print { display: none; }
                    @media print {
                        .btn, .no-print { display: none !important; }
                    }
                </style>
            </head>
            <body>
                <div class="container mt-4">
                    ${element.innerHTML}
                </div>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

// Sidebar toggle for mobile
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('show');
    }
}

// Auto-refresh data every 30 seconds
function startAutoRefresh() {
    setInterval(() => {
        const refreshElements = document.querySelectorAll('[data-auto-refresh]');
        refreshElements.forEach(element => {
            // Implement refresh logic based on element type
            if (element.classList.contains('stats-card')) {
                // Refresh stats
                refreshStats();
            }
        });
    }, 30000);
}

function refreshStats() {
    // Implement AJAX call to refresh statistics
    fetch('ajax/refresh_stats.php')
        .then(response => response.json())
        .then(data => {
            // Update stats cards with new data
            Object.keys(data).forEach(key => {
                const element = document.querySelector(`[data-stat="${key}"]`);
                if (element) {
                    element.textContent = data[key];
                }
            });
        })
        .catch(error => console.error('Error refreshing stats:', error));
}

// Initialize everything
document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
    startAutoRefresh();
});