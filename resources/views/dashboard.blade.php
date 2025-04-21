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
                <img src="{{ asset('assets/img/NDBLogo.png') }}" class="img-fluid me-3" style="max-width: 80px;" alt="Company Logo">
                <h1 class="m-0">Nietes Design Builders</h1>
            </div>
            <h1>MENU</h1>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="{{ route('dashboard') }}" hx-target="#content-area" hx-swap="innerHTML" data-persist-sidebar>
                        <i class="bi bi-house-fill"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="{{ route('employees.index') }}" hx-target="#content-area" hx-swap="innerHTML" data-persist-sidebar data-toggle-dropdown>
                        <i class="bi bi-people-fill"></i> Employees
                    </a>
                    <ul class="employee-dropdown" style="display: none;">
                        <li>
                            <a href="#" class="nav-link dropdown-link" hx-get="{{ route('employees.inactive') }}" hx-target="#content-area" hx-swap="innerHTML" data-persist-sidebar>
                                View Inactive Employees
                            </a>
                        </li>
                        <li>
                            <a href="#" class="nav-link dropdown-link" hx-get="{{ route('positions.index') }}" hx-target="#content-area" hx-swap="innerHTML" data-persist-sidebar>
                                View Positions
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="{{ url('/dashboard/attendance') }}" hx-target="#content-area" hx-swap="innerHTML" data-persist-sidebar>
                        <i class="bi bi-calendar2-plus-fill"></i> Record Attendance
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="{{ url('/dashboard/leave-requests') }}" hx-target="#content-area" hx-swap="innerHTML" data-persist-sidebar>
                        <i class="bi bi-calendar-x-fill"></i> Leave Requests
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="{{ url('/dashboard/overtime-requests') }}" hx-target="#content-area" hx-swap="innerHTML" data-persist-sidebar>
                        <i class="bi bi-clock-fill"></i> Overtime Requests
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="{{ url('/dashboard/schedules') }}" hx-target="#content-area" hx-swap="innerHTML" data-persist-sidebar>
                        <i class="bi bi-calendar-week-fill"></i> Schedules
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="{{ url('/dashboard/reports') }}" hx-target="#content-area" hx-swap="innerHTML" data-persist-sidebar>
                        <i class="bi bi-file-earmark-text-fill"></i> Attendance Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" hx-get="{{ url('/dashboard/payroll') }}" hx-target="#content-area" hx-swap="innerHTML" data-persist-sidebar>
                        <i class="bi bi-currency-dollar"></i> Payroll Export
                    </a>
                </li>
            </ul>
        </div>
        <div class="content" id="content-area">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb" id="breadcrumb">
                    <li class="breadcrumb-item"><a href="#" hx-get="{{ route('dashboard') }}" hx-target="#content-area" hx-swap="innerHTML" data-persist-sidebar>Home</a></li>
                </ol>
            </nav>
            <!-- Default Dashboard Content -->
            <div id="dashboard-section">
                <h2>Dashboard</h2>
                <p>Welcome to the Nietes Design Builders Employee Attendance Monitoring System.</p>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true"
         hx-get="{{ url('/dashboard/positions/list') }}" hx-target="#employeePositionSelect" hx-swap="innerHTML" hx-trigger="load">
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
                            <select class="form-control" id="position_id" name="position_id" required>
                                <option value="">Loading positions...</option>
                            </select>
                            <div id="employeePositionSelect"></div>
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
                        <button class="btn btn-primary" onclick="downloadQR()">Download QR Code</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>