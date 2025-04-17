document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-link[data-section]');
    const dropdownLinks = document.querySelectorAll('.dropdown-link[data-section]');
    const sections = document.querySelectorAll('#content-area > div');
    const employeeDropdown = document.querySelector('.employee-dropdown');
    const breadcrumb = document.getElementById('breadcrumb');
    const baseUrl = 'http://127.0.0.1:8000';
    // Alternative for XAMPP virtual host
    // const baseUrl = 'http://it9-finalreq.local';

    // Log baseUrl for debugging
    console.log('Base URL:', baseUrl);

    // Check CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
    } else {
        console.error('CSRF token meta tag not found');
    }

    // Toggle section and dropdown
    function toggleSection(section, isDropdown = false) {
        sections.forEach(sec => sec.style.display = 'none');
        const targetSection = document.getElementById(`${section}-section`);
        if (targetSection) {
            targetSection.style.display = 'block';
        } else {
            console.error(`Section ${section}-section not found`);
        }
        if (employeeDropdown) {
            employeeDropdown.style.display = (section === 'employees' || isDropdown) ? 'block' : 'none';
        }
        updateBreadcrumbs(section);
        if (section === 'employees') {
            loadEmployees(1);
        } else if (section === 'inactive-employees') {
            loadInactiveEmployees(1);
        } else if (section === 'positions') {
            loadPositions();
        } else if (section !== 'dashboard') {
            axios.get(`${baseUrl}/api/${section}`)
                .then(response => {
                    targetSection.innerHTML = response.data.html || `<h2>${section.charAt(0).toUpperCase() + section.slice(1)}</h2><p>Content loaded dynamically.</p>`;
                })
                .catch(error => {
                    console.error(`Error loading ${section}:`, {
                        status: error.response?.status,
                        data: error.response?.data,
                        message: error.message
                    });
                    if (targetSection) {
                        targetSection.innerHTML = `<h2>Error</h2><p>Failed to load ${section} content.</p>`;
                    }
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
        if (breadcrumb) {
            breadcrumb.innerHTML = `
                <li class="breadcrumb-item"><a href="#" data-section="dashboard">Home</a></li>
                ${section !== 'dashboard' ? `<li class="breadcrumb-item active" aria-current="page">${sectionNames[section]}</li>` : ''}
            `;
            const homeLink = breadcrumb.querySelector('[data-section="dashboard"]');
            if (homeLink) {
                homeLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    toggleSection('dashboard');
                });
            }
        }
    }

    // Load active employees
    function loadEmployees(page, search = '') {
        const tbody = document.getElementById('employeeTable');
        if (!tbody) {
            console.error('employeeTable not found');
            return;
        }
        axios.get(`${baseUrl}/api/employees?page=${page}&search=${encodeURIComponent(search)}`)
            .then(response => {
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
                updatePagination('employeePagination', pagination, page, loadEmployees, search);
                attachEmployeeEventListeners();
            })
            .catch(error => {
                console.error('Error loading employees:', {
                    status: error.response?.status,
                    data: error.response?.data,
                    message: error.message
                });
                tbody.innerHTML = '<tr><td colspan="6">Error loading employees.</td></tr>';
            });
    }

    // Load inactive employees
    function loadInactiveEmployees(page) {
        const tbody = document.getElementById('inactiveEmployeeTable');
        if (!tbody) {
            console.error('inactiveEmployeeTable not found');
            return;
        }
        axios.get(`${baseUrl}/api/inactive-employees?page=${page}`)
            .then(response => {
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
                updatePagination('inactiveEmployeePagination', pagination, page, loadInactiveEmployees);
                attachInactiveEmployeeEventListeners();
            })
            .catch(error => {
                console.error('Error loading inactive employees:', {
                    status: error.response?.status,
                    data: error.response?.data,
                    message: error.message
                });
                tbody.innerHTML = '<tr><td colspan="5">Error loading inactive employees.</td></tr>';
            });
    }

    // Load positions
    function loadPositions() {
        const tbody = document.getElementById('positionTable');
        if (!tbody) {
            console.error('positionTable not found');
            return;
        }
        axios.get(`${baseUrl}/api/positions`)
            .then(response => {
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
                attachPositionEventListeners();
            })
            .catch(error => {
                console.error('Error loading positions:', {
                    status: error.response?.status,
                    data: error.response?.data,
                    message: error.message
                });
                tbody.innerHTML = '<tr><td colspan="4">Error loading positions.</td></tr>';
            });
    }

    // Update pagination
    function updatePagination(containerId, pagination, currentPage, loadFunction, search = '') {
        const paginationContainer = document.getElementById(containerId);
        if (!paginationContainer) {
            console.error(`Pagination container ${containerId} not found`);
            return;
        }
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
        document.querySelectorAll('.edit-employee').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                axios.get(`${baseUrl}/api/employees/${id}`)
                    .then(response => {
                        const employee = response.data;
                        const editModal = document.getElementById('editEmployeeModal');
                        if (!editModal) {
                            console.error('editEmployeeModal not found');
                            return;
                        }
                        document.getElementById('edit_employee_id').value = employee.employee_id;
                        document.getElementById('edit_fname').value = employee.fname;
                        document.getElementById('edit_mname').value = employee.mname || '';
                        document.getElementById('edit_lname').value = employee.lname;
                        document.getElementById('edit_address').value = employee.address;
                        document.getElementById('edit_contact').value = employee.contact;
                        document.getElementById('edit_hire_date').value = employee.hire_date;
                        const select = document.getElementById('edit_position_id');
                        if (select) {
                            select.innerHTML = '<option value="">Select Position</option>';
                            axios.get(`${baseUrl}/api/positions`)
                                .then(res => {
                                    res.data.forEach(pos => {
                                        select.innerHTML += `<option value="${pos.position_id}" ${pos.position_id == employee.position_id ? 'selected' : ''}>${pos.position_name}</option>`;
                                    });
                                    new bootstrap.Modal(editModal).show();
                                })
                                .catch(error => {
                                    console.error('Error loading positions for edit modal:', {
                                        status: error.response?.status,
                                        data: error.response?.data,
                                        message: error.message
                                    });
                                    select.innerHTML = '<option value="">Error loading positions</option>';
                                });
                        }
                    })
                    .catch(error => {
                        console.error('Error loading employee:', {
                            status: error.response?.status,
                            data: error.response?.data,
                            message: error.message
                        });
                        alert('Error loading employee data.');
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
                            loadEmployees(1);
                        })
                        .catch(error => {
                            console.error('Error archiving employee:', {
                                status: error.response?.status,
                                data: error.response?.data,
                                message: error.message
                            });
                            alert('Error archiving employee: ' + (error.response?.data?.message || 'Unknown error'));
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
                    qrImage.src = `${baseUrl}/qr_codes/${qrCode}.png`;
                    new bootstrap.Modal(viewQrModal).show();
                } else {
                    console.error('qrImage or viewQrModal not found');
                }
            });
        });
    }

    // Attach inactive employee event listeners
    function attachInactiveEmployeeEventListeners() {
        document.querySelectorAll('.restore-employee').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to restore this employee?')) {
                    const id = this.getAttribute('data-id');
                    axios.post(`${baseUrl}/api/employees/${id}/restore`)
                        .then(() => {
                            alert('Employee restored successfully.');
                            loadInactiveEmployees(1);
                        })
                        .catch(error => {
                            console.error('Error restoring employee:', {
                                status: error.response?.status,
                                data: error.response?.data,
                                message: error.message
                            });
                            alert('Error restoring employee: ' + (error.response?.data?.message || 'Unknown error'));
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
                            loadInactiveEmployees(1);
                        })
                        .catch(error => {
                            console.error('Error deleting employee:', {
                                status: error.response?.status,
                                data: error.response?.data,
                                message: error.message
                            });
                            alert('Error deleting employee: ' + (error.response?.data?.message || 'Unknown error'));
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
                    qrImage.src = `${baseUrl}/qr_codes/${qrCode}.png`;
                    new bootstrap.Modal(viewQrModal).show();
                } else {
                    console.error('qrImage or viewQrModal not found');
                }
            });
        });
    }

    // Attach position event listeners
    function attachPositionEventListeners() {
        document.querySelectorAll('.edit-position').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                axios.get(`${baseUrl}/api/positions/${id}`)
                    .then(response => {
                        const position = response.data;
                        const editModal = document.getElementById('editPositionModal');
                        if (!editModal) {
                            console.error('editPositionModal not found');
                            return;
                        }
                        document.getElementById('edit_position_id').value = position.position_id;
                        document.getElementById('edit_position_name').value = position.position_name;
                        document.getElementById('edit_description').value = position.description || '';
                        document.getElementById('edit_base_salary').value = position.base_salary || '';
                        new bootstrap.Modal(editModal).show();
                    })
                    .catch(error => {
                        console.error('Error loading position:', {
                            status: error.response?.status,
                            data: error.response?.data,
                            message: error.message
                        });
                        alert('Error loading position data.');
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
                            loadPositions();
                        })
                        .catch(error => {
                            console.error('Error deleting position:', {
                                status: error.response?.status,
                                data: error.response?.data,
                                message: error.message
                            });
                            alert('Error deleting position: ' + (error.response?.data?.message || 'Unknown error'));
                        });
                }
            });
        });
    }

    // Populate position dropdown
    const addEmployeeModal = document.getElementById('addEmployeeModal');
    if (addEmployeeModal) {
        addEmployeeModal.addEventListener('show.bs.modal', function() {
            const addPositionSelect = document.getElementById('position_id');
            if (addPositionSelect) {
                addPositionSelect.innerHTML = '<option value="">Select Position</option>';
                axios.get(`${baseUrl}/api/positions`)
                    .then(res => {
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
                    });
            } else {
                console.error('position_id select not found');
            }
        });
    } else {
        console.error('addEmployeeModal not found');
    }

    // Handle add employee form
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
                    bootstrap.Modal.getInstance(addEmployeeModal).hide();
                    this.reset();
                    loadEmployees(1);
                })
                .catch(error => {
                    console.error('Error adding employee:', {
                        status: error.response?.status,
                        data: error.response?.data,
                        message: error.message
                    });
                    alert('Error adding employee: ' + (error.response?.data?.message || 'Unknown error'));
                });
        });
    }

    // Handle edit employee form
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
                    bootstrap.Modal.getInstance(document.getElementById('editEmployeeModal')).hide();
                    loadEmployees(1);
                })
                .catch(error => {
                    console.error('Error updating employee:', {
                        status: error.response?.status,
                        data: error.response?.data,
                        message: error.message
                    });
                    alert('Error updating employee: ' + (error.response?.data?.message || 'Unknown error'));
                });
        });
    }

    // Handle add position form
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
                    bootstrap.Modal.getInstance(document.getElementById('addPositionModal')).hide();
                    this.reset();
                    loadPositions();
                })
                .catch(error => {
                    console.error('Error adding position:', {
                        status: error.response?.status,
                        data: error.response?.data,
                        message: error.message
                    });
                    alert('Error adding position: ' + (error.response?.data?.message || 'Unknown error'));
                });
        });
    }

    // Handle edit position form
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
                    bootstrap.Modal.getInstance(document.getElementById('editPositionModal')).hide();
                    loadPositions();
                })
                .catch(error => {
                    console.error('Error updating position:', {
                        status: error.response?.status,
                        data: error.response?.data,
                        message: error.message
                    });
                    alert('Error updating position: ' + (error.response?.data?.message || 'Unknown error'));
                });
        });
    }

    // Handle employee search
    const employeeSearch = document.getElementById('employeeSearch');
    if (employeeSearch) {
        employeeSearch.addEventListener('input', function() {
            loadEmployees(1, this.value);
        });
    }

    // Navigation event listeners
    if (navLinks.length === 0) {
        console.error('No nav links with data-section found');
    }
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.getAttribute('data-section');
            toggleSection(section);
        });
    });

    if (dropdownLinks.length === 0) {
        console.error('No dropdown links with data-section found');
    }
    dropdownLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.getAttribute('data-section');
            toggleSection(section, true);
        });
    });
});