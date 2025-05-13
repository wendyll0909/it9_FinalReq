<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nietes Design Builders - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://unpkg.com/htmx.org@2.0.3"></script>
    <style>
        .content {
            flex-grow: 1;
            padding: 20px;
            background-color: #F5F5F5;
            color: #333333;
            width: 100%;
            min-height: 100vh;
            transition: none; /* Remove transitions to prevent shifts */
        }
        /* Extremely high specificity to override any tilt */
        div#dashboard-section div.row div.col-lg-8 div.card.attendance-card,
        div#dashboard-section div.row div.col-lg-4 div.card.small-card {
            transform: none !important;
            transition: none !important;
            animation: none !important;
            -webkit-transform: none !important;
            -moz-transform: none !important;
            -ms-transform: none !important;
            -o-transform: none !important;
            will-change: auto !important;
        }
        /* Ensure parent elements don't introduce transforms */
        div#dashboard-section,
        div#dashboard-section div.row,
        div#dashboard-section div.col-lg-8,
        div#dashboard-section div.col-lg-4 {
            transform: none !important;
            transition: none !important;
            animation: none !important;
            -webkit-transform: none !important;
            -moz-transform: none !important;
            -ms-transform: none !important;
            -o-transform: none !important;
        }
        .card-header {
            background-color: #343a40;
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: 500;
        }
        .card-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 100px;
        }
        .attendance-card {
            height: 585px;
        }
        .small-card {
            height: calc(585px / 3 - 16px);
            display: flex;
            flex-direction: column;
            margin-bottom: 24px;
        }
        canvas {
            max-height: 500px;
        }
        .datetime-display {
            position: fixed;
            bottom: 10px;
            right: 10px;
            background-color: #343a40;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 2000;
            font-size: 1.1rem;
            font-weight: 400;
            display: flex;
            align-items: center;
            gap: 15px;
            min-width: 250px;
        }
        @media (max-width: 768px) {
            .datetime-display {
                left: 10px;
            }
            .small-card {
                height: auto;
                min-height: 180px;
            }
        }
        .user-menu {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 3000;
        }
        .user-menu .dropdown-menu {
            min-width: 150px;
        }
        .user-menu .btn {
            background-color: #343a40;
            border: none;
            color: white;
            font-size: 1.2rem;
            padding: 8px 12px;
        }
        .user-menu .btn:hover {
            background-color: #495057;
        }
        .user-menu .dropdown-item i {
            margin-right: 8px;
        }
        .user-menu .dropdown-item.logout {
            color: #dc3545;
        }
        .user-menu .dropdown-item.logout:hover {
            background-color: #f8d7da;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container-fluid d-flex">
        <!-- Hamburger Menu Button -->
        <div class="hamburger-menu" id="hamburger-menu">
            <i class="bi bi-list"></i>
        </div>
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="d-flex align-items-center justify-content-center my-3">
                <img src="{{ asset('assets/img/NDBLogo.png') }}" class="img-fluid me-3" style="max-width: 80px;" alt="Company Logo">
                <h1 class="m-0">Nietes Design Builders</h1>
            </div>
            <h1>MENU</h1>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="{{ route('dashboard') }}" hx-target="#content-area" hx-swap="outerHTML show:window:top" hx-push-url="true" data-persist-sidebar>
                        <i class="bi bi-house-fill"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="{{ route('employees.index') }}" hx-target="#content-area" hx-swap="innerHTML show:window:top" data-persist-sidebar data-toggle-dropdown>
                        <i class="bi bi-people-fill"></i> Employees
                    </a>
                    <ul class="employee-dropdown" style="display: none;">
                        <li>
                            <a href="#" class="nav-link dropdown-link" hx-get="{{ route('employees.inactive') }}" hx-target="#content-area" hx-swap="innerHTML show:window:top" data-persist-sidebar>
                                View Inactive Employees
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link dropdown-link" hx-get="{{ route('positions.index') }}" hx-target="#content-area" hx-swap="innerHTML show:window:top" data-persist-sidebar>
                                View Positions
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-toggle-dropdown>
                        <i class="bi bi-calendar2-plus-fill"></i> Record Attendance
                    </a>
                    <ul class="attendance-dropdown" style="display: none;">
                        <li>
                            <a href="#" class="nav-link dropdown-link" hx-get="{{ route('attendance.checkin') }}" hx-target="#content-area" hx-swap="innerHTML show:window:top" data-persist-sidebar>
                                Check In
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link dropdown-link" hx-get="{{ route('attendance.checkout') }}" hx-target="#content-area" hx-swap="innerHTML show:window:top" data-persist-sidebar>
                                Check Out
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="{{ url('/dashboard/leave-requests') }}" hx-target="#content-area" hx-swap="innerHTML show:window:top" data-persist-sidebar>
                        <i class="bi bi-calendar-x-fill"></i> Leave Requests
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="{{ url('/dashboard/overtime-requests') }}" hx-target="#content-area" hx-swap="innerHTML show:window:top" data-persist-sidebar>
                        <i class="bi bi-clock-fill"></i> Overtime Requests
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="{{ url('/dashboard/reports') }}" hx-target="#content-area" hx-swap="innerHTML show:window:top" data-persist-sidebar>
                        <i class="bi bi-file-earmark-text-fill"></i> Attendance Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="{{ url('/dashboard/payroll') }}" hx-target="#content-area" hx-swap="innerHTML show:window:top" data-persist-sidebar>
                        <i class="bi bi-currency-dollar"></i> Payroll Export
                    </a>
                </li>
            </ul>
        </div>
        <div class="content" id="content-area">
            <!-- Dashboard Content -->
            <div id="dashboard-section">
                <h2>Dashboard</h2>
                <div class="row">
                    <!-- Large Attendance Line Chart Card -->
                    <div class="col-lg-8 mb-4">
                        <div class="card attendance-card">
                            <div class="card-header">
                                <h5 class="mb-0">Employee Attendance</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="attendanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <!-- Smaller Cards -->
                    <div class="col-lg-4">
                        <div class="row">
                            <!-- Top Employee Card -->
                            <div class="col-12">
                                <div class="card small-card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Top Employee</h5>
                                    </div>
                                    <div class="card-body text-center" 
                                         hx-get="{{ route('dashboard.topEmployee') }}" 
                                         hx-trigger="load" 
                                         hx-swap="innerHTML"
                                         hx-indicator="#top-employee-spinner">
                                        <div id="top-employee-spinner" class="text-center">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Employee Ranking Card -->
                            <div class="col-12">
                                <div class="card small-card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Employee Ranking</h5>
                                    </div>
                                    <div class="card-body" 
                                         hx-get="{{ route('dashboard.rankings') }}" 
                                         hx-trigger="load" 
                                         hx-swap="innerHTML"
                                         hx-indicator="#rankings-spinner">
                                        <div id="rankings-spinner" class="text-center">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Employee Evaluation Card -->
                            <div class="col-12">
                                <div class="card small-card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Employee Evaluation</h5>
                                    </div>
                                    <div class="card-body" 
                                         hx-get="{{ route('dashboard.evaluation') }}" 
                                         hx-trigger="load" 
                                         hx-swap="innerHTML"
                                         hx-indicator="#evaluation-spinner">
                                        <div id="evaluation-spinner" class="text-center">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Date and Time Display -->
                <div class="datetime-display">
                    <span id="currentDate"></span>
                    <span id="currentTime"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- User Menu -->
    <div class="user-menu">
        <div class="dropdown">
            <button class="btn" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-list"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuDropdown">
                <li>
                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editUserModal">
                        <i class="bi bi-person-circle"></i> User
                    </a>
                </li>
                <li>
                    <a class="dropdown-item logout" href="#" onclick="document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-left"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <!-- Modals -->
    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEmployeeModalLabel">Add Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addEmployeeForm" hx-post="{{ route('employees.store') }}" hx-target="#employees-section" hx-swap="innerHTML">
                        @csrf
                        <div class="mb-3">
                            <label for="fname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="fname" name="fname" required>
                        </div>
                        <div class="mb-3">
                            <label for="mname" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="mname" name="mname">
                        </div>
                        <div class="mb-3">
                            <label for="lname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lname" name="lname" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>
                        <div class="mb-3">
                            <label for="contact" class="form-label">Contact</label>
                            <input type="text" class="form-control" id="contact" name="contact" required>
                        </div>
                        <div class="mb-3">
                            <label for="hire_date" class="form-label">Hire Date</label>
                            <input type="date" class="form-control" id="hire_date" name="hire_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="position_id" class="form-label">Position</label>
                            <select class="form-control" id="position_id" name="position_id" required
                                    hx-get="{{ route('positions.list') }}"
                                    hx-target="this"
                                    hx-swap="innerHTML"
                                    hx-trigger="shown.bs.modal from:#addEmployeeModal">
                                <option value="">Loading positions...</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Employee</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEmployeeModalLabel">Edit Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="edit-employee-form">
                    <p>Loading employee data...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Position Modal -->
    <div class="modal fade" id="addPositionModal" tabindex="-1" aria-labelledby="addPositionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPositionModalLabel">Add Position</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPositionForm" hx-post="{{ route('positions.store') }}" hx-target="#positions-section" hx-swap="innerHTML">
                        @csrf
                        <div class="mb-3">
                            <label for="title" class="form-label">Position Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Position</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Position Modal -->
    <div class="modal fade" id="editPositionModal" tabindex="-1" aria-labelledby="editPositionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPositionModalLabel">Edit Position</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="edit-position-form">
                    <p>Loading position data...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- View QR Code Modal -->
    <div class="modal fade" id="viewQrModal" tabindex="-1" aria-labelledby="viewQrModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewQrModalLabel">Employee QR Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="qrImage" src="" alt="QR Code" class="img-fluid" style="max-width: 100%; height: auto;">
                    <div class="mt-3">
                        <button class="btn btn-primary" id="downloadQrButton">Download QR Code</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Attendance Modal -->
    <div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-labelledby="editAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAttendanceModalLabel">Edit Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="edit-attendance-form">
                    <p>Loading attendance data...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm" action="{{ route('user.profile') }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ auth()->user()->name ?? '' }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ auth()->user()->email ?? '' }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script>
        // Initialize Chart.js
        let attendanceChartInstance = null;
        function initAttendanceChart() {
            const ctx = document.getElementById('attendanceChart');
            if (!ctx) return;

            if (attendanceChartInstance) {
                attendanceChartInstance.destroy();
            }

            fetch('{{ route('dashboard.data') }}')
                .then(response => response.json())
                .then(data => {
                    attendanceChartInstance = new Chart(ctx.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Attendance Count',
                                data: data.data,
                                borderColor: '#0a58ca',
                                backgroundColor: 'rgba(10, 88, 202, 0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: { display: true, text: 'Number of Employees' }
                                },
                                x: { title: { display: true, text: 'Date' } }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error fetching attendance data:', error));
        }

        function updateDateTime() {
            const now = new Date();
            const dateString = now.toLocaleDateString('en-US', { year: 'numeric', month: '2-digit', day: '2-digit' });
            const timeString = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
            document.getElementById('currentDate').textContent = dateString;
            document.getElementById('currentTime').textContent = timeString;
        }

        // Function to remove tilt from all cards and parent elements
        function removeCardTilt() {
            const elements = document.querySelectorAll(
                '#dashboard-section, #dashboard-section .row, #dashboard-section .col-lg-8, #dashboard-section .col-lg-4, #dashboard-section .card'
            );
            elements.forEach(el => {
                const computedStyle = window.getComputedStyle(el);
                if (computedStyle.transform !== 'none' && computedStyle.transform !== 'matrix(1, 0, 0, 1, 0, 0)') {
                    console.warn('Unexpected transform detected:', {
                        element: el.className,
                        transform: computedStyle.transform
                    });
                }
                el.style.transform = 'none';
                el.style.transition = 'none';
                el.style.animation = 'none';
                el.style.willChange = 'auto';
                ['webkitTransform', 'mozTransform', 'msTransform', 'oTransform'].forEach(prop => {
                    el.style[prop] = 'none';
                });
            });
            console.log('removeCardTilt executed at:', new Date().toISOString());
        }

        // Reprocess HTMX elements to ensure sidebar buttons work
        function reprocessHtmx() {
            const sidebar = document.querySelector('#sidebar');
            if (sidebar) {
                htmx.process(sidebar);
                sidebar.querySelectorAll('[hx-get]').forEach(el => {
                    htmx.process(el);
                });
                console.log('HTMX reprocessed sidebar at:', new Date().toISOString());
            } else {
                console.warn('Sidebar not found for HTMX reprocessing');
            }
        }

        // Initial setup
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOMContentLoaded: Initializing setup');
            initAttendanceChart();
            updateDateTime();
            removeCardTilt();
            reprocessHtmx();
            setInterval(updateDateTime, 1000);

            // Log sidebar link clicks for debugging
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                link.addEventListener('click', function() {
                    console.log('Sidebar link clicked:', {
                        text: this.textContent,
                        hxGet: this.getAttribute('hx-get'),
                        target: this.getAttribute('hx-target')
                    });
                });
            });
        });

        // Handle HTMX swaps
        document.body.addEventListener('htmx:afterSwap', function(event) {
            if (event.detail.target.id === 'content-area') {
                console.log('HTMX afterSwap: Content area updated');
                initAttendanceChart();
                updateDateTime();
                removeCardTilt();
                reprocessHtmx();
                // Force layout recalculation
                document.getElementById('content-area').offsetHeight;
            }
        });

        // Handle HTMX after settle
        document.body.addEventListener('htmx:afterSettle', function() {
            console.log('HTMX afterSettle: Reinitializing sidebar links');
            removeCardTilt();
            reprocessHtmx();
            // Reinitialize sidebar link behavior
            const navLinks = document.querySelectorAll('.sidebar .nav-link[hx-get]');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    console.log('Sidebar link clicked:', link.getAttribute('hx-get'));
                    htmx.trigger(link, 'htmx:trigger');
                });
            });
        });

        // Handle HTMX response errors
        htmx.on('htmx:responseError', function(event) {
            console.error('HTMX request failed:', {
                url: event.detail.xhr.responseURL,
                status: event.detail.xhr.status,
                response: event.detail.xhr.responseText
            });
            alert('An error occurred while processing your request. Please try again.');
        });

        // Sync with app.js sidebar toggle
        document.body.addEventListener('click', function(e) {
            if (e.target.classList.contains('hamburger-menu') || e.target.closest('.hamburger-menu')) {
                console.log('Hamburger menu clicked, scheduling tilt removal');
                setTimeout(removeCardTilt, 50);
                reprocessHtmx();
            }
        });

        // Sync with app.js navigation
        document.body.addEventListener('htmx:afterRequest', function(e) {
            if (typeof isNavigating !== 'undefined' && !isNavigating) {
                console.log('HTMX afterRequest: Navigation completed');
                setTimeout(removeCardTilt, 50);
                reprocessHtmx();
            }
        });

        // Form validation for Edit User Modal
        document.getElementById('editUserForm').addEventListener('submit', function(event) {
            const password = document.getElementById('password').value;
            const passwordConfirmation = document.getElementById('password_confirmation').value;
            if (password && password !== passwordConfirmation) {
                event.preventDefault();
                alert('Passwords do not match!');
            }
        });

        // HTMX CSRF handling
        htmx.on('htmx:configRequest', function(event) {
            event.detail.headers['X-CSRF-Token'] = document.querySelector('meta[name="csrf-token"]').content;
        });
    </script>
</body>
</html>