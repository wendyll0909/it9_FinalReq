<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <title></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo asset('assets/css/styles.css'); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://unpkg.com/htmx.org@2.0.3"></script>
    <style>
        .content {
            flex-grow: 1;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
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
        }
        .attendance-card {
            height: 475px;
        }
        .small-card {
            height: 180px;
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
    </style>
</head>
<body>
    <div class="container-fluid d-flex">
        <!-- Hamburger Menu Button -->
        <div class="hamburger-menu">
            <i class="bi bi-list"></i>
        </div>
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="d-flex align-items-center justify-content-center my-3">
                <img src="<?php echo asset('assets/img/NDBLogo.png'); ?>" class="img-fluid me-3" style="max-width: 80px;" alt="Company Logo">
                <h1 class="m-0">Nietes Design Builders</h1>
            </div>
            <h1>MENU</h1>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="<?php echo route('dashboard'); ?>" hx-target="#content-area" hx-swap="innerHTML" hx-push-url="true" data-persist-sidebar>
                        <i class="bi bi-house-fill"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="<?php echo route('employees.index'); ?>" hx-target="#content-area" hx-swap="innerHTML" data-persist-sidebar data-toggle-dropdown>
                        <i class="bi bi-people-fill"></i> Employees
                    </a>
                    <ul class="employee-dropdown" style="display: none;">
                        <li>
                            <a href="#" class="nav-link dropdown-link" hx-get="<?php echo route('employees.inactive'); ?>" hx-target="#content-area" hx-swap="innerHTML" data-persist-sidebar>
                                View Inactive Employees
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link dropdown-link" hx-get="<?php echo route('positions.index'); ?>" hx-target="#content-area" hx-swap="innerHTML" data-persist-sidebar>
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
                            <a href="#" class="nav-link dropdown-link" hx-get="<?php echo route('attendance.checkin'); ?>" hx-target="#content-area" hx-swap="innerHTML" data-persist-sidebar>
                                Check In
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link dropdown-link" hx-get="<?php echo route('attendance.checkout'); ?>" hx-target="#content-area" hx-swap="innerHTML" data-persist-sidebar>
                                Check Out
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="<?php echo url('/dashboard/leave-requests'); ?>" hx-target="#content-area" hx-swap="innerHTML" data-persist-sidebar>
                        <i class="bi bi-calendar-x-fill"></i> Leave Requests
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="<?php echo url('/dashboard/overtime-requests'); ?>" hx-target="#content-area" hx-swap="innerHTML" data-persist-sidebar>
                        <i class="bi bi-clock-fill"></i> Overtime Requests
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="<?php echo url('/dashboard/reports'); ?>" hx-target="#content-area" hx-swap="innerHTML" data-persist-sidebar>
                        <i class="bi bi-file-earmark-text-fill"></i> Attendance Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="<?php echo url('/dashboard/payroll'); ?>" hx-target="#content-area" hx-swap="innerHTML" data-persist-sidebar>
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
                            <div class="col-12 mb-4">
                                <div class="card small-card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Top Employee</h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <img src="<?php echo asset('assets/img/placeholder.jpg'); ?>" class="rounded-circle mb-2" style="width: 50px; height: 50px;" alt="Employee">
                                        <h6>John Doe</h6>
                                        <p class="text-muted">Employee of the Month</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Employee Ranking Card -->
                            <div class="col-12 mb-4">
                                <div class="card small-card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Employee Ranking</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">1. John Doe - 95%</li>
                                            <li class="list-group-item">2. Jane Smith - 90%</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <!-- Employee Evaluation Card -->
                            <div class="col-12 mb-4">
                                <div class="card small-card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Employee Evaluation</h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>John Doe</strong></p>
                                        <p>Performance: 4.5/5</p>
                                        <a href="#" class="btn btn-primary btn-sm">View Report</a>
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
                    <a class="dropdown-item" href="#">
                        <i class="bi bi-person-circle"></i> User
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" onclick="alert('Logout functionality not implemented yet. Please implement login/logout first.');">
                        <i class="bi bi-box-arrow-left"></i> Logout
                    </a>
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
                    <form id="addEmployeeForm" hx-post="<?php echo route('employees.store'); ?>" hx-target="#employees-section" hx-swap="innerHTML">
                        <?php echo csrf_field(); ?>
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
                                    hx-get="<?php echo route('positions.list'); ?>"
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
                    <form id="addPositionForm" hx-post="<?php echo route('positions.store'); ?>" hx-target="#positions-section" hx-swap="innerHTML">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label for="position_name" class="form-label">Position Name</label>
                            <input type="text" class="form-control" id="position_name" name="position_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="base_salary" class="form-label">Base Salary</label>
                            <input type="number" step="0.01" class="form-control" id="base_salary" name="base_salary">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="<?php echo asset('assets/js/app.js'); ?>"></script>
    <script>
        // Line Chart for Attendance
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['2025-05-03', '2025-05-04', '2025-05-05', '2025-05-06', '2025-05-07', '2025-05-08', '2025-05-09'],
                datasets: [{
                    label: 'Attendance Count',
                    data: [50, 48, 52, 45, 50, 49, 51],
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
                        title: {
                            display: true,
                            text: 'Number of Employees'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                }
            }
        });

        // Dynamic Date and Time Display
        function updateDateTime() {
            const now = new Date();
            const dateString = now.toLocaleDateString('en-US', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
            const timeString = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
            document.getElementById('currentDate').textContent = dateString;
            document.getElementById('currentTime').textContent = timeString;
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);
    </script>
</body>
</html>