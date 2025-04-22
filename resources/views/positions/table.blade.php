<div id="positions-section">
    <h2>Manage Positions</h2>
    <div class="mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPositionModal">Add Position</button>
    </div>
    <div id="error-message" class="alert alert-danger" style="display: none;"></div>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger error">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
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
                    <th>Position Name</th>
                    <th>Description</th>
                    <th>Base Salary</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($positions as $position)
                    <tr>
                        <td>{{ htmlspecialchars($position->position_name) }}</td>
                        <td>{{ htmlspecialchars($position->description ?? '-') }}</td>
                        <td>₱{{ number_format($position->base_salary ?? 0, 2) }}</td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-position" data-id="{{ $position->position_id }}">Edit</button>
                            <form id="deletePositionForm_{{ $position->position_id }}"
                                  hx-delete="{{ route('positions.destroy', $position->position_id) }}"
                                  hx-target="#positions-section"
                                  hx-swap="innerHTML"
                                  hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('Are you sure you want to delete this position? This will also delete associated employees.')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No positions available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>