function loadEmployees(page, search, baseUrl, updatePagination) {
    const tbody = document.getElementById('employeeTable');
    if (!tbody) {
        console.error('employeeTable not found');
        return;
    }
    axios.get(`${baseUrl}/api/employees?page=${page}&search=${encodeURIComponent(search)}`)
        .then(response => {
            if (!response.data || !response.data.data) {
                throw new Error('Invalid response format: No data received');
            }
            const employees = response.data.data;
            const pagination = response.data;
            tbody.innerHTML = '';
            employees.forEach(employee => {
                tbody.innerHTML += `
                    <tr>
                        <td>${employee.fname} ${employee.mname ? employee.mname + ' ' : ''}${employee.lname}</td>
                        <td>${employee.position?.position_name || 'N/A'}</td>
                        <td>${employee.contact}</td>
                        <td>${employee.hire_date}</td>
                        <td>
                            ${employee.qr_code ? `<button class="btn btn-sm btn-info view-qr" data-qr="${employee.qr_code}">View QR</button>` : 'N/A'}
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-employee" data-id="${employee.employee_id}">Edit</button>
                            <button class="btn btn-sm btn-warning archive-employee" data-id="${employee.employee_id}">Archive</button>
                        </td>
                    </tr>
                `;
            });
            if (typeof updatePagination === 'function') {
                updatePagination('employeePagination', pagination, page, (p, s) => loadEmployees(p, s, baseUrl, updatePagination), search);
            }
            attachEmployeeEventListeners(baseUrl);
        })
        .catch(error => {
            console.error('Error loading employees:', {
                status: error.response?.status,
                data: error.response?.data,
                message: error.message
            });
            tbody.innerHTML = '<tr><td colspan="6">Error loading employees. Please try again later.</td></tr>';
        });
}

function loadInactiveEmployees(page, baseUrl, updatePagination, search = '') {
    const tbody = document.getElementById('inactiveEmployeeTable');
    if (!tbody) {
        console.error('inactiveEmployeeTable not found');
        return;
    }
    axios.get(`${baseUrl}/api/inactive-employees?page=${page}&search=${encodeURIComponent(search)}`)
        .then(response => {
            if (!response.data || !response.data.data) {
                throw new Error('Invalid response format: No data received');
            }
            const employees = response.data.data;
            const pagination = response.data;
            tbody.innerHTML = '';
            employees.forEach(employee => {
                tbody.innerHTML += `
                    <tr>
                        <td>${employee.fname} ${employee.mname ? employee.mname + ' ' : ''}${employee.lname}</td>
                        <td>${employee.position?.position_name || 'N/A'}</td>
                        <td>${employee.deleted_at}</td>
                        <td>
                            ${employee.qr_code ? `<button class="btn btn-sm btn-info view-qr" data-qr="${employee.qr_code}">View QR</button>` : 'N/A'}
                        </td>
                        <td>
                            <button class="btn btn-sm btn-success restore-employee" data-id="${employee.employee_id}">Restore</button>
                            <button class="btn btn-sm btn-danger delete-employee" data-id="${employee.employee_id}">Delete</button>
                        </td>
                    </tr>
                `;
            });
            if (typeof updatePagination === 'function') {
                updatePagination('inactiveEmployeePagination', pagination, page, (p, s) => loadInactiveEmployees(p, baseUrl, updatePagination, s), search);
            }
            attachInactiveEmployeeEventListeners(baseUrl);
        })
        .catch(error => {
            console.error('Error loading inactive employees:', {
                status: error.response?.status,
                data: error.response?.data,
                message: error.message
            });
            tbody.innerHTML = '<tr><td colspan="5">Error loading inactive employees. Please try again later.</td></tr>';
        });
}

function attachEmployeeEventListeners(baseUrl) {
    document.querySelectorAll('.edit-employee').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const editModal = document.getElementById('editEmployeeModal');
            if (!editModal) {
                console.error('editEmployeeModal not found');
                alert('Error: Edit modal not found.');
                return;
            }
            axios.get(`${baseUrl}/api/employees/${id}`)
                .then(response => {
                    if (!response.data) {
                        throw new Error('No employee data received');
                    }
                    const employee = response.data;
                    const editEmployeeId = document.getElementById('edit_employee_id');
                    const editFname = document.getElementById('edit_fname');
                    const editMname = document.getElementById('edit_mname');
                    const editLname = document.getElementById('edit_lname');
                    const editAddress = document.getElementById('edit_address');
                    const editContact = document.getElementById('edit_contact');
                    const editHireDate = document.getElementById('edit_hire_date');
                    const editPositionId = document.getElementById('edit_position_id');

                    if (!editEmployeeId || !editFname || !editLname || !editAddress || !editContact || !editHireDate || !editPositionId) {
                        console.error('One or more edit form fields not found');
                        alert('Error: Edit form fields missing.');
                        return;
                    }

                    editEmployeeId.value = employee.employee_id;
                    editFname.value = employee.fname;
                    editMname.value = employee.mname || '';
                    editLname.value = employee.lname;
                    editAddress.value = employee.address;
                    editContact.value = employee.contact;
                    editHireDate.value = employee.hire_date;

                    editPositionId.innerHTML = '<option value="">Select Position</option>';
                    axios.get(`${baseUrl}/api/positions`)
                        .then(res => {
                            if (!res.data) {
                                throw new Error('No positions data received');
                            }
                            res.data.forEach(pos => {
                                editPositionId.innerHTML += `<option value="${pos.position_id}" ${pos.position_id == employee.position_id ? 'selected' : ''}>${pos.position_name}</option>`;
                            });
                            new bootstrap.Modal(editModal).show();
                        })
                        .catch(error => {
                            console.error('Error loading positions for edit modal:', {
                                status: error.response?.status,
                                data: error.response?.data,
                                message: error.message
                            });
                            editPositionId.innerHTML = '<option value="">Error loading positions</option>';
                            alert('Error loading positions for edit modal.');
                        });
                })
                .catch(error => {
                    console.error('Error loading employee data:', {
                        status: error.response?.status,
                        data: error.response?.data,
                        message: error.message
                    });
                    alert('Error loading employee data: ' + (error.response?.data?.message || error.message));
                });
        });
    });

    document.querySelectorAll('.archive-employee').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to archive this employee?')) {
                const id = this.getAttribute('data-id');
                axios.post(`${baseUrl}/api/employees/${id}/archive`)
                    .then(() => {
                        alert('Employee archived successfully.');
                        loadEmployees(1, '', baseUrl);
                    })
                    .catch(error => {
                        console.error('Error archiving employee:', {
                            status: error.response?.status,
                            data: error.response?.data,
                            message: error.message
                        });
                        alert('Error archiving employee: ' + (error.response?.data?.message || error.message));
                    });
            }
        });
    });

    document.querySelectorAll('.view-qr').forEach(button => {
        button.addEventListener('click', function() {
            const qrCode = this.getAttribute('data-qr');
            const qrImage = document.getElementById('qrImage');
            const viewQrModal = document.getElementById('viewQrModal');
            
            if (qrImage && viewQrModal) {
                const timestamp = new Date().getTime();
                qrImage.src = `${baseUrl}/qr_codes/${qrCode}?t=${timestamp}`;
                qrImage.setAttribute('data-filename', qrCode);
                new bootstrap.Modal(viewQrModal).show();
            } else {
                console.error('qrImage or viewQrModal not found');
                alert('Error: QR code modal not found.');
            }
        });
    });
}

function attachInactiveEmployeeEventListeners(baseUrl) {
    document.querySelectorAll('.restore-employee').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to restore this employee?')) {
                const id = this.getAttribute('data-id');
                axios.post(`${baseUrl}/api/employees/${id}/restore`)
                    .then(() => {
                        alert('Employee restored successfully.');
                        loadInactiveEmployees(1, baseUrl);
                    })
                    .catch(error => {
                        console.error('Error restoring employee:', {
                            status: error.response?.status,
                            data: error.response?.data,
                            message: error.message
                        });
                        alert('Error restoring employee: ' + (error.response?.data?.message || error.message));
                    });
            }
        });
    });

    document.querySelectorAll('.delete-employee').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to permanently delete this employee?')) {
                const id = this.getAttribute('data-id');
                axios.delete(`${baseUrl}/api/employees/${id}`)
                    .then(() => {
                        alert('Employee deleted successfully.');
                        loadInactiveEmployees(1, baseUrl);
                    })
                    .catch(error => {
                        console.error('Error deleting employee:', {
                            status: error.response?.status,
                            data: error.response?.data,
                            message: error.message
                        });
                        alert('Error deleting employee: ' + (error.response?.data?.message || error.message));
                    });
            }
        });
    });

    document.querySelectorAll('.view-qr').forEach(button => {
        button.addEventListener('click', function() {
            const qrCode = this.getAttribute('data-qr');
            const qrImage = document.getElementById('qrImage');
            const viewQrModal = document.getElementById('viewQrModal');
            
            if (qrImage && viewQrModal) {
                const timestamp = new Date().getTime();
                qrImage.src = `${baseUrl}/qr_codes/${qrCode}?t=${timestamp}`;
                qrImage.setAttribute('data-filename', qrCode);
                new bootstrap.Modal(viewQrModal).show();
            } else {
                console.error('qrImage or viewQrModal not found');
                alert('Error: QR code modal not found.');
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = 'http://127.0.0.1:8000';
    const addEmployeeModal = document.getElementById('addEmployeeModal');
    if (addEmployeeModal) {
        addEmployeeModal.addEventListener('show.bs.modal', function() {
            const addPositionSelect = document.getElementById('position_id');
            if (addPositionSelect) {
                addPositionSelect.innerHTML = '<option value="">Select Position</option>';
                axios.get(`${baseUrl}/api/positions`)
                    .then(res => {
                        if (!res.data) {
                            throw new Error('No positions data received');
                        }
                        res.data.forEach(pos => {
                            addPositionSelect.innerHTML += `<option value="${pos.position_id}">${pos.position_name}</option>`;
                        });
                    })
                    .catch(error => {
                        console.error('Error loading positions for dropdown:', {
                            status: error.response?.status,
                            data: error.response?.data,
                            message: error.message
                        });
                        addPositionSelect.innerHTML = '<option value="">Error loading positions</option>';
                        alert('Error loading positions for dropdown.');
                    });
            } else {
                console.error('position_id select not found');
                alert('Error: Position select not found.');
            }
        });
    } else {
        console.error('addEmployeeModal not found');
    }

    const addEmployeeForm = document.getElementById('addEmployeeForm');
    if (addEmployeeForm) {
        addEmployeeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const data = {
                fname: document.getElementById('fname').value,
                mname: document.getElementById('mname').value,
                lname: document.getElementById('lname').value,
                address: document.getElementById('address').value,
                contact: document.getElementById('contact').value,
                hire_date: document.getElementById('hire_date').value,
                position_id: document.getElementById('position_id').value
            };
            axios.post(`${baseUrl}/api/employees`, data)
                .then(() => {
                    alert('Employee added successfully.');
                    const modalInstance = bootstrap.Modal.getInstance(addEmployeeModal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    this.reset();
                    loadEmployees(1, '', baseUrl);
                })
                .catch(error => {
                    console.error('Error adding employee:', {
                        status: error.response?.status,
                        data: error.response?.data,
                        message: error.message
                    });
                    alert('Error adding employee: ' + (error.response?.data?.message || error.message));
                });
        });
    }

    const editEmployeeForm = document.getElementById('editEmployeeForm');
    if (editEmployeeForm) {
        editEmployeeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('edit_employee_id').value;
            const data = {
                fname: document.getElementById('edit_fname').value,
                mname: document.getElementById('edit_mname').value,
                lname: document.getElementById('edit_lname').value,
                address: document.getElementById('edit_address').value,
                contact: document.getElementById('edit_contact').value,
                hire_date: document.getElementById('edit_hire_date').value,
                position_id: document.getElementById('edit_position_id').value
            };
            axios.put(`${baseUrl}/api/employees/${id}`, data)
                .then(() => {
                    alert('Employee updated successfully.');
                    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('editEmployeeModal'));
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    loadEmployees(1, '', baseUrl);
                })
                .catch(error => {
                    console.error('Error updating employee:', {
                        status: error.response?.status,
                        data: error.response?.data,
                        message: error.message
                    });
                    alert('Error updating employee: ' + (error.response?.data?.message || error.message));
                });
        });
    }

    const employeeSearch = document.getElementById('employeeSearch');
    if (employeeSearch) {
        employeeSearch.addEventListener('input', function() {
            loadEmployees(1, this.value, baseUrl);
        });
    }

    const inactiveEmployeeSearch = document.getElementById('inactiveEmployeeSearch');
    if (inactiveEmployeeSearch) {
        inactiveEmployeeSearch.addEventListener('input', function() {
            loadInactiveEmployees(1, baseUrl, null, this.value);
        });
    }
});