<div id="positions-section">
    <h2>Manage Positions</h2>
    <div class="mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPositionModal">Add Position</button>
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
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Position Name</th>
                <th>Description</th>
                <th>Base Salary</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($positions as $position)
                <tr>
                    <td>{{ $position->position_name }}</td>
                    <td>{{ $position->description ?? 'N/A' }}</td>
                    <td>{{ $position->base_salary ? '₱' . number_format($position->base_salary, 2) : 'N/A' }}</td>
                    <td>
                        <button class="btn btn-sm btn-primary edit-position" data-id="{{ $position->position_id }}">Edit</button>
                        <form hx-delete="{{ route('positions.destroy', $position->position_id) }}" 
                              hx-target="#positions-section" 
                              hx-swap="innerHTML" 
                              hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}' 
                              style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this position?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>