function loadPositions(baseUrl, updatePagination) {
    const tbody = document.getElementById('positionTable');
    if (!tbody) {
        console.error('positionTable not found');
        return;
    }
    axios.get(`${baseUrl}/api/positions`)
        .then(response => {
            if (!response.data) {
                throw new Error('No positions data received');
            }
            const positions = response.data;
            tbody.innerHTML = '';
            positions.forEach(position => {
                tbody.innerHTML += `
                    <tr>
                        <td>${position.position_name}</td>
                        <td>${position.description || 'N/A'}</td>
                        <td>${position.base_salary ? '₱' + parseFloat(position.base_salary).toFixed(2) : 'N/A'}</td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-position" data-id="${position.position_id}">Edit</button>
                            <button class="btn btn-sm btn-danger delete-position" data-id="${position.position_id}">Delete</button>
                        </td>
                    </tr>
                `;
            });
            attachPositionEventListeners(baseUrl);
        })
        .catch(error => {
            console.error('Error loading positions:', {
                status: error.response?.status,
                data: error.response?.data,
                message: error.message
            });
            tbody.innerHTML = '<tr><td colspan="4">Error loading positions. Please try again later.</td></tr>';
        });
}

function attachPositionEventListeners(baseUrl) {
    document.querySelectorAll('.edit-position').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const editModal = document.getElementById('editPositionModal');
            if (!editModal) {
                console.error('editPositionModal not found');
                alert('Error: Edit position modal not found.');
                return;
            }
            axios.get(`${baseUrl}/api/positions/${id}`)
                .then(response => {
                    if (!response.data) {
                        throw new Error('No position data received');
                    }
                    const position = response.data;
                    const editPositionId = document.getElementById('edit_position_id');
                    const editPositionName = document.getElementById('edit_position_name');
                    const editDescription = document.getElementById('edit_description');
                    const editBaseSalary = document.getElementById('edit_base_salary');

                    if (!editPositionId || !editPositionName || !editDescription || !editBaseSalary) {
                        console.error('One or more edit position form fields not found');
                        alert('Error: Edit position form fields missing.');
                        return;
                    }

                    editPositionId.value = position.position_id;
                    editPositionName.value = position.position_name;
                    editDescription.value = position.description || '';
                    editBaseSalary.value = position.base_salary || '';
                    new bootstrap.Modal(editModal).show();
                })
                .catch(error => {
                    console.error('Error loading position data:', {
                        status: error.response?.status,
                        data: error.response?.data,
                        message: error.message
                    });
                    alert('Error loading position data: ' + (error.response?.data?.message || error.message));
                });
        });
    });

    document.querySelectorAll('.delete-position').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this position?')) {
                const id = this.getAttribute('data-id');
                axios.delete(`${baseUrl}/api/positions/${id}`)
                    .then(() => {
                        alert('Position deleted successfully.');
                        loadPositions(baseUrl); // No updatePagination
                    })
                    .catch(error => {
                        console.error('Error deleting position:', {
                            status: error.response?.status,
                            data: error.response?.data,
                            message: error.message
                        });
                        alert('Error deleting position: ' + (error.response?.data?.message || error.message));
                    });
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = 'http://127.0.0.1:8000';
    const addPositionForm = document.getElementById('addPositionForm');
    if (addPositionForm) {
        addPositionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const data = {
                position_name: document.getElementById('position_name').value,
                description: document.getElementById('description').value,
                base_salary: document.getElementById('base_salary').value
            };
            axios.post(`${baseUrl}/api/positions`, data)
                .then(() => {
                    alert('Position added successfully.');
                    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('addPositionModal'));
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    this.reset();
                    loadPositions(baseUrl); // No updatePagination
                })
                .catch(error => {
                    console.error('Error adding position:', {
                        status: error.response?.status,
                        data: error.response?.data,
                        message: error.message
                    });
                    alert('Error adding position: ' + (error.response?.data?.message || error.message));
                });
        });
    }

    const editPositionForm = document.getElementById('editPositionForm');
    if (editPositionForm) {
        editPositionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('edit_position_id').value;
            const data = {
                position_name: document.getElementById('edit_position_name').value,
                description: document.getElementById('edit_description').value,
                base_salary: document.getElementById('edit_base_salary').value
            };
            axios.put(`${baseUrl}/api/positions/${id}`, data)
                .then(() => {
                    alert('Position updated successfully.');
                    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('editPositionModal'));
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    loadPositions(baseUrl); // No updatePagination
                })
                .catch(error => {
                    console.error('Error updating position:', {
                        status: error.response?.status,
                        data: error.response?.data,
                        message: error.message
                    });
                    alert('Error updating position: ' + (error.response?.data?.message || error.message));
                });
        });
    }
});