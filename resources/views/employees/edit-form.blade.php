<form id="editEmployeeForm" 
      hx-put="{{ route('employees.update', $employee->employee_id) }}" 
      hx-target="#employees-section" 
      hx-swap="innerHTML" 
      hx-push-url="false"
      hx-indicator="#edit-loading"
      hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'>
    @csrf
    @method('PUT')
    <input type="hidden" id="edit_employee_id" name="employee_id" value="{{ $employee->employee_id }}">
    <input type="hidden" name="search" value="{{ $search ?? '' }}">
    <div class="mb-3">
        <label for="edit_fname" class="form-label">First Name</label>
        <input type="text" class="form-control" id="edit_fname" name="fname" value="{{ $employee->fname }}" required>
    </div>
    <div class="mb-3">
        <label for="edit_mname" class="form-label">Middle Name</label>
        <input type="text" class="form-control" id="edit_mname" name="mname" value="{{ $employee->mname }}">
    </div>
    <div class="mb-3">
        <label for="edit_lname" class="form-label">Last Name</label>
        <input type="text" class="form-control" id="edit_lname" name="lname" value="{{ $employee->lname }}" required>
    </div>
    <div class="mb-3">
        <label for="edit_address" class="form-label">Address</label>
        <input type="text" class="form-control" id="edit_address" name="address" value="{{ $employee->address }}" required>
    </div>
    <div class="mb-3">
        <label for="edit_contact" class="form-label">Contact</label>
        <input type="text" class="form-control" id="edit_contact" name="contact" value="{{ $employee->contact }}" required>
    </div>
    <div class="mb-3">
        <label for="edit_hire_date" class="form-label">Hire Date</label>
        <input type="date" class="form-control" id="edit_hire_date" name="hire_date" value="{{ $employee->hire_date }}" required>
    </div>
    <div class="mb-3">
        <label for="edit_position_id" class="form-label">Position</label>
        <select class="form-control" id="edit_position_id" name="position_id" required>
            <option value="">Select Position</option>
            @foreach ($positions as $pos)
                <option value="{{ $pos->position_id }}" {{ $pos->position_id == $employee->position_id ? 'selected' : '' }}>
                    {{ $pos->position_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label for="edit_status" class="form-label">Status</label>
        <select class="form-control" id="edit_status" name="status" required>
            <option value="active" {{ $employee->status == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ $employee->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Update Employee</button>
    <span id="edit-loading" class="htmx-indicator">Loading...</span>
</form>