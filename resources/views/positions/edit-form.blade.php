<form id="editPositionForm" hx-put="{{ route('positions.update', $position->position_id) }}" hx-target="#positions-section" hx-swap="innerHTML">
    @csrf
    @method('PUT')
    <input type="hidden" id="edit_position_id" name="position_id" value="{{ $position->position_id }}">
    <div class="mb-3">
        <label for="edit_position_name" class="form-label">Position Name</label>
        <input type="text" class="form-control" id="edit_position_name" name="position_name" value="{{ $position->position_name }}" required>
    </div>
    <div class="mb-3">
        <label for="edit_description" class="form-label">Description</label>
        <textarea class="form-control" id="edit_description" name="description">{{ $position->description }}</textarea>
    </div>
    <div class="mb-3">
        <label for="edit_base_salary" class="form-label">Base Salary</label>
        <input type="number" step="0.01" class="form-control" id="edit_base_salary" name="base_salary" value="{{ $position->base_salary }}">
    </div>
    <button type="submit" class="btn btn-primary">Update Position</button>
</form>