<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nietes Design Builders - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
    <div class="container-fluid d-flex">
        <div class="sidebar">
            <div class="d-flex align-items-center justify-content-center my-3">
                <img src="{{ asset('assets/img/NDBLogo.png') }}" class="img-fluid me-3" style="max-width: 80px;" alt="Company Logo">
                <h1 class="m-0">Nietes Design Builders</h1>
            </div>
            <h1>MENU</h1>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="#" class="nav-link" data-section="dashboard"><i class="bi bi-house-fill"></i> Dashboard</a></li>
                <li class="nav-item"><a href="#" class="nav-link" data-section="employees"><i class="bi bi-people-fill"></i> Employees</a></li>
                <li class="nav-item"><a href="#" class="nav-link" data-section="attendance"><i class="bi bi-calendar2-plus-fill"></i> Record Attendance</a></li>
                <li class="nav-item"><a href="#" class="nav-link" data-section="leave-requests"><i class="bi bi-calendar-x-fill"></i> Leave Requests</a></li>
                <li class="nav-item"><a href="#" class="nav-link" data-section="overtime-requests"><i class="bi bi-clock-fill"></i> Overtime Requests</a></li>
                <li class="nav-item"><a href="#" class="nav-link" data-section="schedules"><i class="bi bi-calendar-week-fill"></i> Schedules</a></li>
                <li class="nav-item"><a href="#" class="nav-link" data-section="reports"><i class="bi bi-file-earmark-text-fill"></i> Attendance Reports</a></li>
                <li class="nav-item"><a href="#" class="nav-link" data-section="payroll"><i class="bi bi-currency-dollar"></i> Payroll Export</a></li>
                <li class="nav-item"><a href="#" class="nav-link" data-section="positions"><i class="bi bi-person-fill-add"></i> Manage Position</a></li>
                <li class="nav-item"><a href="{{ route('logout') }}" class="nav-link"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>
        <div class="content" id="content-area">
            <!-- Default Dashboard Content -->
            <div id="dashboard-section">
                <h2>Welcome, {{ htmlspecialchars($username) }}!</h2>
                <div class="row">
                    <div class="col-md-6">
                        <h3>Attendance (Last 7 Days)</h3>
                        <canvas id="attendanceChart" height="200"></canvas>
                    </div>
                    <div class="col-md-6">
                        <h3>Top 10 Attendance Ranking</h3>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Employee</th>
                                    <th>Total Hours</th>
                                </tr>
                            </thead>
                            <tbody id="attendanceRanking">
                                @foreach ($rankingData as $index => $employee)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $employee->full_name }}</td>
                                        <td>{{ number_format($employee->total_hours, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Placeholder Sections -->
            <div id="employees-section" style="display: none;">
                <h2>Employees</h2>
                <p>Employee management interface will be loaded here.</p>
            </div>
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
            <div id="positions-section" style="display: none;">
                <h2>Manage Position</h2>
                <p>Position management interface will be loaded here.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize attendance chart
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [@json($attendanceData->pluck('date'))],
                    datasets: [{
                        label: 'Total Hours Worked',
                        data: [@json($attendanceData->pluck('hours'))],
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: { beginAtZero: true, title: { display: true, text: 'Hours' } },
                        x: { title: { display: true, text: 'Date' } }
                    }
                }
            });

            // SPA navigation
            const navLinks = document.querySelectorAll('.nav-link[data-section]');
            const sections = document.querySelectorAll('#content-area > div');

            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const section = this.getAttribute('data-section');

                    // Hide all sections
                    sections.forEach(sec => sec.style.display = 'none');

                    // Show selected section
                    const targetSection = document.getElementById(`${section}-section`);
                    if (targetSection) {
                        targetSection.style.display = 'block';
                    }

                    // Optionally fetch content via AJAX
                    if (section !== 'dashboard') {
                        axios.get(`/api/${section}`)
                            .then(response => {
                                targetSection.innerHTML = response.data.html || `<h2>${section.charAt(0).toUpperCase() + section.slice(1)}</h2><p>Content loaded dynamically.</p>`;
                            })
                            .catch(error => {
                                targetSection.innerHTML = `<h2>Error</h2><p>Failed to load ${section} content.</p>`;
                            });
                    }
                });
            });
        });
    </script>
</body>
</html>