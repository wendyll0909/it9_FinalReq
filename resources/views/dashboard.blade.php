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
                <li class="nav-item"><a href="#" class="nav-link" data-section="dashboard"><i class="bi bi-house-fill"></i> Dashboard</a></li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-section="employees"><i class="bi bi-people-fill"></i> Employees</a>
                    <ul class="employee-dropdown" style="display: none;">
                        <li><a href="#" class="nav-link dropdown-link" data-section="inactive-employees">View Inactive Employees</a></li>
                        <li><a href="#" class="nav-link dropdown-link" data-section="positions">View Positions</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a href="#" class="nav-link" data-section="attendance"><i class="bi bi-calendar2-plus-fill"></i> Record Attendance</a></li>
                <li class="nav-item"><a href="#" class="nav-link" data-section="leave-requests"><i class="bi bi-calendar-x-fill"></i> Leave Requests</a></li>
                <li class="nav-item"><a href="#" class="nav-link" data-section="overtime-requests"><i class="bi bi-clock-fill"></i> Overtime Requests</a></li>
                <li class="nav-item"><a href="#" class="nav-link" data-section="schedules"><i class="bi bi-calendar-week-fill"></i> Schedules</a></li>
                <li class="nav-item"><a href="#" class="nav-link" data-section="reports"><i class="bi bi-file-earmark-text-fill"></i> Attendance Reports</a></li>
                <li class="nav-item"><a href="#" class="nav-link" data-section="payroll"><i class="bi bi-currency-dollar"></i> Payroll Export</a></li>
            </ul>
        </div>
        <div class="content" id="content-area">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb" id="breadcrumb">
                    <li class="breadcrumb-item"><a href="#" data-section="dashboard">Home</a></li>
                </ol>
            </nav>
            <!-- Default Dashboard Content -->
            <div id="dashboard-section">
                <h2>Dashboard</h2>
                <p>Welcome to the Nietes Design Builders Employee Attendance Monitoring System.</p>
            </div>
            <!-- Employees Section -->
            <div id="employees-section" style="display: none;">
                <h2>Employees</h2>
                <div class="mb-3">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">Add Employee</button>
                    <input type="text" id="employeeSearch" class="form-control mt-2" placeholder="Search by name...">
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Contact</th>
                            <th>Hire Date</th>
                            <th>QR Code</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="employeeTable"></tbody>
                </table>
                <nav aria-label="Page navigation">
                    <ul class="pagination" id="employeePagination"></ul>
                </nav>
            </div>
            <!-- Inactive Employees Section -->
            <div id="inactive-employees-section" style="display: none;">
                <h2>Inactive Employees</h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Inactive Date</th>
                            <th>QR Code</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="inactiveEmployeeTable"></tbody>
                </table>
                <nav aria-label="Page navigation">
                    <ul class="pagination" id="inactiveEmployeePagination"></ul>
                </nav>
            </div>
            <!-- Positions Section -->
            <div id="positions-section" style="display: none;">
                <h2>Manage Positions</h2>
                <div class="mb-3">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPositionModal">Add Position</button>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Position Name</th>
                            <th>Description</th>
                            <th>Base Salary</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="positionTable"></tbody>
                </table>
            </div>
            <!-- Other Sections -->
            <div id="attendance-section" style="display: none;">
                <h2>Record Attendance</h2>
                <p>QR code scanning and manual check-in/check-out interface will be loaded here.</p>
            </div>
            <div id="leave-requests-section" style="display: none;">
                <h2>Leave Requests</h2>
                <p>Leave request form and list will be loaded here.</p>
            </div>
            <div id="overtime-requests-section" style="display: none;">
                <h2>Overtime Requests</h2>
                <p>Overtime request form and list will be loaded here.</p>
            </div>
            <div id="schedules-section" style="display: none;">
                <h2>Schedules</h2>
                <p>Schedule management interface will be loaded here.</p>
            </div>
            <div id="reports-section" style="display: none;">
                <h2>Attendance Reports</h2>
                <p>Daily, weekly, monthly report generation will be loaded here.</p>
            </div>
            <div id="payroll-section" style="display: none;">
                <h2>Payroll Export</h2>
                <p>Payroll data export interface will be loaded here.</p>
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
                            <form id="addEmployeeForm">
                                <div class="mb-3">
                                    <label for="fname" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="fname" required>
                                </div>
                                <div class="mb-3">
                                    <label for="mname" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="mname">
                                </div>
                                <div class="mb-3">
                                    <label for="lname" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lname" required>
                                </div>
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="address" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contact" class="form-label">Contact</label>
                                    <input type="text" class="form-control" id="contact" required>
                                </div>
                                <div class="mb-3">
                                    <label for="hire_date" class="form-label">Hire Date</label>
                                    <input type="date" class="form-control" id="hire_date" required>
                                </div>
                                <div class="mb-3">
                                    <label for="position_id" class="form-label">Position</label>
                                    <select class="form-control" id="position_id" name="position_id" required>
                                        <option value="">Select Position</option>
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
                        <div class="modal-body">
                            <form id="editEmployeeForm">
                                <input type="hidden" id="edit_employee_id">
                                <div class="mb-3">
                                    <label for="edit_fname" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="edit_fname" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_mname" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="edit_mname">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_lname" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="edit_lname" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="edit_address" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_contact" class="form-label">Contact</label>
                                    <input type="text" class="form-control" id="edit_contact" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_hire_date" class="form-label">Hire Date</label>
                                    <input type="date" class="form-control" id="edit_hire_date" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_position_id" class="form-label">Position</label>
                                    <select class="form-control" id="edit_position_id" name="position_id" required>
                                        <option value="">Select Position</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Employee</button>
                            </form>
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
                            <form id="addPositionForm">
                                <div class="mb-3">
                                    <label for="position_name" class="form-label">Position Name</label>
                                    <input type="text" class="form-control" id="position_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="base_salary" class="form-label">Base Salary</label>
                                    <input type="number" step="0.01" class="form-control" id="base_salary">
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
                        <div class="modal-body">
                            <form id="editPositionForm">
                                <input type="hidden" id="edit_position_id">
                                <div class="mb-3">
                                    <label for="edit_position_name" class="form-label">Position Name</label>
                                    <input type="text" class="form-control" id="edit_position_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_description" class="form-label">Description</label>
                                    <textarea class="form-control" id="edit_description"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_base_salary" class="form-label">Base Salary</label>
                                    <input type="number" step="0.01" class="form-control" id="edit_base_salary">
                                </div>
                                <button type="submit" class="btn btn-primary">Update Position</button>
                            </form>
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
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
</body>
</html>