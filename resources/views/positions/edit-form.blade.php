<form id="editPositionForm" hx-put="{{ route('positions.update', $position->position_id) }}" hx-target="#positions-section" hx-swap="innerHTML">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label for="position_name" class="form-label">Position Name</label>
        <input type="text" class="form-control" id="position_name" name="position_name" value="{{ htmlspecialchars($position->position_name) }}" required>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description">{{ htmlspecialchars($position->description ?? '') }}</textarea>
    </div>
    <div class="mb-3">
        <label for="base_salary" class="form-label">Base Salary</label>
        <input type="number" step="0.01" class="form-control" id="base_salary" name="base_salary" value="{{ $position->base_salary ?? '' }}">
    </div>
    <button type="submit" class="btn btn-primary">Update Position</button>
</form>