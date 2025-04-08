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
                <li class="nav-item"><a href="{{ route('dashboard') }}" class="nav-link"><i class="bi bi-house-fill"></i> Dashboard</a></li>
                <li class="nav-item"><a href="/employee/employee_reports.php" class="nav-link"><i class="bi bi-people-fill"></i> Employee</a></li>
                <li class="nav-item"><a href="/record_attendance.php" class="nav-link"><i class="bi bi-calendar2-plus-fill"></i> Record Attendance</a></li>
                <li class="nav-item"><a href="/payroll/generate_payroll.php" class="nav-link"><i class="bi bi-currency-dollar"></i> Payroll</a></li>
                <li class="nav-item"><a href="/add_position.php" class="nav-link"><i class="bi bi-person-fill-add"></i> Manage Position</a></li>
                <li class="nav-item"><a href="{{ route('logout') }}" class="nav-link text-danger" onclick="return confirm('Are you sure you want to logout?');"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>
        <div class="content" id="content-area">
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>
</body>
</html>