<div id="employees-section">
    <h2>Employees</h2>
    <div class="mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">Add Employee</button>
        <input type="text" id="employeeSearch" class="form-control mt-2" placeholder="Search by name..." 
               hx-get="{{ route('employees.index') }}" 
               hx-target="#employees-section" 
               hx-swap="innerHTML" 
               hx-trigger="input delay:500ms" 
               name="search" 
               value="{{ $search }}">
    </div>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="table-responsive">
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
            <tbody>
                @forelse ($employees as $employee)
                    <tr>
                        <td>{{ $employee->fname }} {{ $employee->mname ? $employee->mname . ' ' : '' }}{{ $employee->lname }}</td>
                        <td>{{ $employee->position->position_name ?? 'N/A' }}</td>
                        <td>{{ $employee->contact }}</td>
                        <td>{{ $employee->hire_date }}</td>
                        <td>
                            @if ($employee->qr_code)
                                <button class="btn btn-sm btn-info view-qr" data-qr="{{ $employee->qr_code }}">View QR</button>
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-employee" data-id="{{ $employee->employee_id }}">Edit</button>
                            <form hx-post="{{ route('employees.archive', $employee->employee_id) }}" 
                                  hx-target="#employees-section" 
                                  hx-swap="innerHTML" 
                                  hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}' 
                                  style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-warning" 
                                        onclick="return confirm('Are you sure you want to archive this employee?')">Archive</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No employees found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <nav aria-label="Page navigation">
        <ul class="pagination">
            {{ $employees->appends(['search' => $search])->links() }}
        </ul>
    </nav>
</div>