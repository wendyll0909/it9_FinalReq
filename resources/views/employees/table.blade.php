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
               value="{{ $search ?? '' }}">
    </div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible" id="success-message">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible" id="error-message">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            {{ session('error') }}
        </div>
    @endif
    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible" id="error-message">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div id="fallback-error" class="alert alert-danger alert-dismissible" style="display: none;">
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        An unexpected error occurred. Please try again.
    </div>
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
                                  hx-push-url="false"
                                  hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}' 
                                  hx-indicator="#archive-loading-{{ $employee->employee_id }}"
                                  style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-warning" 
                                        onclick="return confirm('Are you sure you want to archive this employee?')">Archive</button>
                                <span id="archive-loading-{{ $employee->employee_id }}" class="htmx-indicator">Loading...</span>
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
            {{ $employees->appends(['search' => $search ?? ''])->links() }}
        </ul>
    </nav>
</div>