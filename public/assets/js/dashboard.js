document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-link[data-section]');
    const dropdownLinks = document.querySelectorAll('.dropdown-link[data-section]');
    const sections = document.querySelectorAll('#content-area > div');
    const employeeDropdown = document.querySelector('.employee-dropdown');
    const breadcrumb = document.getElementById('breadcrumb');

    // Toggle section and dropdown
    function toggleSection(section, isDropdown = false) {
        sections.forEach(sec => sec.style.display = 'none');
        const targetSection = document.getElementById(`${section}-section`);
        if (targetSection) {
            targetSection.style.display = 'block';
        }
        if (section === 'employees' || isDropdown) {
            employeeDropdown.style.display = 'block';
        } else {
            employeeDropdown.style.display = 'none';
        }
        updateBreadcrumbs(section);
        if (section === 'employees') {
            loadEmployees(1);
        } else if (section === 'inactive-employees') {
            loadInactiveEmployees(1);
        } else if (section === 'positions') {
            loadPositions();
        } else if (section !== 'dashboard') {
            axios.get(`/api/${section}`)
                .then(response => {
                    targetSection.innerHTML = response.data.html || `<h2>${section.charAt(0).toUpperCase() + section.slice(1)}</h2><p>Content loaded dynamically.</p>`;
                })
                .catch(error => {
                    targetSection.innerHTML = `<h2>Error</h2><p>Failed to load ${section} content.</p>`;
                });
        }
    }

    // Update breadcrumbs
    function updateBreadcrumbs(section) {
        const sectionNames = {
            dashboard: 'Dashboard',
            employees: 'Employees',
            'inactive-employees': 'Inactive Employees',
            positions: 'Positions',
            attendance: 'Record Attendance',
            'leave-requests': 'Leave Requests',
            'overtime-requests': 'Overtime Requests',
            schedules: 'Schedules',
            reports: 'Attendance Reports',
            payroll: 'Payroll Export'
        };
        breadcrumb.innerHTML = `
            <li class="breadcrumb-item"><a href="#" data-section="dashboard">Home</a></li>
            ${section !== 'dashboard' ? `<li class="breadcrumb-item active" aria-current="page">${sectionNames[section]}</li>` : ''}
        `;
        breadcrumb.querySelector('[data-section="dashboard"]').addEventListener('click', function(e) {
            e.preventDefault();
            toggleSection('dashboard');
        });
    }

    // Load active employees
    function loadEmployees(page, search = '') {
        axios.get(`/api/employees?page=${page}&search=${encodeURIComponent(search)}`)
            .then(response => {
                const employees = response.data.data;
                const pagination = response.data;
                const tbody = document.getElementById('employeeTable');
                tbody.innerHTML = '';
                employees.forEach(employee => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${employee.fname} ${employee.mname ? employee.mname + ' ' : ''}${employee.lname}</td>
                            <td>${employee.position.position_name}</td>
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
                updatePagination('employeePagination', pagination, page, loadEmployees, search);
                attachEmployeeEventListeners();
            })
            .catch(error => {
                document.getElementById('employeeTable').innerHTML = '<tr><td colspan="6">Error loading employees.</td></tr>';
            });
    }

    // Load inactive employees
    function loadInactiveEmployees(page) {
        axios.get(`/api/inactive-employees?page=${page}`)
            .then(response => {
                const employees = response.data.data;
                const pagination = response.data;
                const tbody = document.getElementById('inactiveEmployeeTable');
                tbody.innerHTML = '';
                employees.forEach(employee => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${employee.fname} ${employee.mname ? employee.mname + ' ' : ''}${employee.lname}</td>
                            <td>${employee.position.position_name}</td>
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
                updatePagination('inactiveEmployeePagination', pagination, page, loadInactiveEmployees);
                attachInactiveEmployeeEventListeners();
            })
            .catch(error => {
                document.getElementById('inactiveEmployeeTable').innerHTML = '<tr><td colspan="5">Error loading inactive employees.</td></tr>';
            });
    }

    // Load positions
    function loadPositions() {
        axios.get('/api/positions')
            .then(response => {
                const positions = response.data;
                const tbody = document.getElementById('positionTable');
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
                attachPositionEventListeners();
            })
            .catch(error => {
                document.getElementById('positionTable').innerHTML = '<tr><td colspan="4">Error loading positions.</td></tr>';
            });
    }

    // Update pagination
    function updatePagination(containerId, pagination, currentPage, loadFunction, search = '') {
        const paginationContainer = document.getElementById(containerId);
        paginationContainer.innerHTML = '';
        if (pagination.last_page <= 1) return;

        paginationContainer.innerHTML += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
            </li>
        `;
        for (let i = 1; i <= pagination.last_page; i++) {
            paginationContainer.innerHTML += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }
        paginationContainer.innerHTML += `
            <li class="page-item ${currentPage === pagination.last_page ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
            </li>
        `;

        paginationContainer.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.getAttribute('data-page'));
                if (page) loadFunction(page, search);
            });
        });
    }

    // Attach employee event listeners
    function attachEmployeeEventListeners() {
        // Edit employee
        document.querySelectorAll('.edit-employee').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                axios.get(`/api/employees/${id}`)
                    .then(response => {
                        const employee = response.data;
                        document.getElementById('edit_employee_id').value = employee.employee_id;
                        document.getElementById('edit_fname').value = employee.fname;
                        document.getElementById('edit_mname').value = employee.mname || '';
                        document.getElementById('edit_lname').value = employee.lname;
                        document.getElementById('edit_address').value = employee.address;
                        document.getElementById('edit_contact').value = employee.contact;
                        document.getElementById('edit_hire_date').value = employee.hire_date;
                        const select = document.getElementById('edit_position_id');
                        select.innerHTML = '<option value="">Select Position</option>';
                        axios.get('/api/positions')
                            .then(res => {
                                res.data.forEach(pos => {
                                    select.innerHTML += `<option value="${pos.position_id}" ${pos.position_id == employee.position_id ? 'selected' : ''}>${pos.position_name}</option>`;
                                });
                                new bootstrap.Modal(document.getElementById('editEmployeeModal')).show();
                            });
                    });
            });
        });

        // Archive employee
        document.querySelectorAll('.archive-employee').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to archive this employee?')) {
                    const id = this.getAttribute('data-id');
                    axios.post(`/api/employees/${id}/archive`)
                        .then(() => {
                            alert('Employee archived successfully.');
                            loadEmployees(1);
                        })
                        .catch(error => alert('Error archiving employee.'));
                }
            });
        });

        // View QR code
        document.querySelectorAll('.view-qr').forEach(button => {
            button.addEventListener('click', function() {
                const qrCode = this.getAttribute('data-qr');
                document.getElementById('qrImage').src = `/qr_codes/${qrCode}.png`;
                new bootstrap.Modal(document.getElementById('viewQrModal')).show();
            });
        });

        // Populate position dropdown
        const addPositionSelect = document.getElementById('position_id');
        if (addPositionSelect && addPositionSelect.innerHTML === '<option value="">Select Position</option>') {
            axios.get('/api/positions')
                .then(res => {
                    res.data.forEach(pos => {
                        addPositionSelect.innerHTML += `<option value="${pos.position_id}">${pos.position_name}</option>`;
                    });
                });
        }
    }

    // Attach inactive employee event listeners
    function attachInactiveEmployeeEventListeners() {
        // Restore employee
        document.querySelectorAll('.restore-employee').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to restore this employee?')) {
                    const id = this.getAttribute('data-id');
                    axios.post(`/api/employees/${id}/restore`)
                        .then(() => {
                            alert('Employee restored successfully.');
                            loadInactiveEmployees(1);
                        })
                        .catch(error => alert('Error restoring employee.'));
                }
            });
        });

        // Permanently delete employee
        document.querySelectorAll('.delete-employee').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to permanently delete this employee?')) {
                    const id = this.getAttribute('data-id');
                    axios.delete(`/api/employees/${id}`)
                        .then(() => {
                            alert('Employee deleted successfully.');
                            loadInactiveEmployees(1);
                        })
                        .catch(error => alert('Error deleting employee.'));
                }
            });
        });

        // View QR code
        document.querySelectorAll('.view-qr').forEach(button => {
            button.addEventListener('click', function() {
                const qrCode = this.getAttribute('data-qr');
                document.getElementById('qrImage').src = `/qr_codes/${qrCode}.png`;
                new bootstrap.Modal(document.getElementById('viewQrModal')).show();
            });
        });
    }

    // Attach position event listeners
    function attachPositionEventListeners() {
        // Edit and delete position logic can be added if needed
    }

    // Handle add employee form
    document.getElementById('addEmployeeForm')?.addEventListener('submit', function(e) {
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
        axios.post('/api/employees', data)
            .then(() => {
                alert('Employee added successfully.');
                bootstrap.Modal.getInstance(document.getElementById('addEmployeeModal')).hide();
                this.reset();
                loadEmployees(1);
            })
            .catch(error => alert('Error adding employee: ' + (error.response?.data?.message || 'Unknown error')));
    });

    // Handle edit employee form
    document.getElementById('editEmployeeForm')?.addEventListener('submit', function(e) {
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
        axios.put(`/api/employees/${id}`, data)
            .then(() => {
                alert('Employee updated successfully.');
                bootstrap.Modal.getInstance(document.getElementById('editEmployeeModal')).hide();
                loadEmployees(1);
            })
            .catch(error => alert('Error updating employee: ' + (error.response?.data?.message || 'Unknown error')));
    });

    // Handle add position form
    document.getElementById('addPositionForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const data = {
            position_name: document.getElementById('position_name').value,
            description: document.getElementById('description').value,
            base_salary: document.getElementById('base_salary').value
        };
        axios.post('/api/positions', data)
            .then(() => {
                alert('Position added successfully.');
                bootstrap.Modal.getInstance(document.getElementById('addPositionModal')).hide();
                this.reset();
                loadPositions();
            })
            .catch(error => alert('Error adding position: ' + (error.response?.data?.message || 'Unknown error')));
    });

    // Handle employee search
    document.getElementById('employeeSearch')?.addEventListener('input', function() {
        loadEmployees(1, this.value);
    });

    // Navigation event listeners
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.getAttribute('data-section');
            toggleSection(section);
        });
    });

    dropdownLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.getAttribute('data-section');
            toggleSection(section, true);
        });
    });
});