document.addEventListener('DOMContentLoaded', function() {
    const contentArea = document.getElementById('content-area');
    if (!contentArea) {
        console.log('No content area found on load.');
        return;
    }

    // Function to show success alert
    function showSuccessAlert(message) {
        let successAlert = document.getElementById('successAlert');
        if (!successAlert) {
            successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success mt-3 text-center';
            successAlert.id = 'successAlert';
            successAlert.role = 'alert';
            document.querySelector('.container')?.prepend(successAlert);
        }
        if (successAlert) {
            successAlert.textContent = message;
            successAlert.style.display = 'block';
            setTimeout(() => { successAlert.style.display = 'none'; }, 1000);
        }
    }

    // Refresh employee list
    function refreshEmployeeList() {
        if (!contentArea) return;
        fetch('/payroll66/employee/employee_reports.php', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(data => {
            contentArea.innerHTML = data;
            // No need to re-attach listeners here; they're already on contentArea
        })
        .catch(error => {
            console.error('Refresh error:', error);
            contentArea.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
        });
    }

    // Single submit handler
    function handleSubmit(event) {
        const target = event.target;
        if (target.matches('#searchForm')) {
            event.preventDefault();
            let formData = new FormData(target);
            fetch('/payroll66/employee/employee_reports.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                return response.text();
            })
            .then(data => {
                contentArea.innerHTML = data;
                const searchQuery = formData.get('employee_id');
                history.pushState({ page: 'employee', search: searchQuery }, 'Employee Search', `?page=employee&employee_id=${encodeURIComponent(searchQuery)}`);
            })
            .catch(error => {
                console.error('Search error:', error);
                contentArea.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
            });
        }

        if (target.matches('#editEmployeeForm')) {
            event.preventDefault();
            let formData = new FormData(target);
            fetch('/payroll66/employee/employee_edit.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                const messageDiv = document.getElementById('editMessage');
                if (data.success) {
                    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                    if (modalInstance) modalInstance.hide();
                    showSuccessAlert(data.message);
                    refreshEmployeeList();
                } else if (messageDiv) {
                    messageDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Edit error:', error);
                const messageDiv = document.getElementById('editMessage');
                if (messageDiv) {
                    messageDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                }
            });
        }

        if (target.matches('#addEmployeeForm')) {
            event.preventDefault();
            let formData = new FormData(target);
            fetch('/payroll66/employee/employee_add.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                const messageDiv = document.getElementById('addMessage');
                if (data.success) {
                    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('addEmployeeModal'));
                    if (modalInstance) modalInstance.hide();
                    showSuccessAlert(data.message);
                    target.reset();
                    refreshEmployeeList();
                } else if (messageDiv) {
                    messageDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Add error:', error);
                const messageDiv = document.getElementById('addMessage');
                if (messageDiv) {
                    messageDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                }
            });
        }
    }

    // Single click handler
    function handleClick(event) {
        const target = event.target;
        if (target.matches('.edit-btn')) {
            event.preventDefault();
            const button = target;
            const employeeId = button.getAttribute('data-employee_id');
            const fname = button.getAttribute('data-fname');
            const mname = button.getAttribute('data-mname');
            const lname = button.getAttribute('data-lname');
            const address = button.getAttribute('data-address');
            const contact = button.getAttribute('data-contact');
            const hireDate = button.getAttribute('data-hire_date');
            const positionId = button.getAttribute('data-position_id');
            const status = button.getAttribute('data-status');

            console.log('Edit button clicked:', { employeeId, fname, mname, lname, status });

            document.getElementById('edit_employee_id').value = employeeId || '';
            document.getElementById('edit_Fname').value = fname || '';
            document.getElementById('edit_Mname').value = mname || '';
            document.getElementById('edit_Lname').value = lname || '';
            document.getElementById('edit_Address').value = address || '';
            document.getElementById('edit_Contact').value = contact || '';
            document.getElementById('edit_hire_date').value = hireDate || '';
            document.getElementById('edit_position_id').value = positionId || '';
            document.getElementById('edit_status').value = status || 'Active';

            const modalElement = document.getElementById('editModal');
            const modal = new bootstrap.Modal(modalElement);
            modal.show();

            modalElement.addEventListener('hidden.bs.modal', function handler() {
                modal.dispose();
                modalElement.removeEventListener('hidden.bs.modal', handler);
            }, { once: true });
        }

        if (target.matches('.delete-btn')) {
            event.preventDefault();
            const employeeId = target.getAttribute('data-employee_id');
            console.log('Delete button clicked for ID:', employeeId);
            if (confirm('Are you sure you want to delete this employee?')) {
                // Add timeout to prevent hanging
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 5000); // 5s timeout

                fetch(`/payroll66/employee/employee_delete.php?id=${encodeURIComponent(employeeId)}`, {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: controller.signal
                })
                .then(response => {
                    clearTimeout(timeoutId);
                    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    console.log('Delete response:', data);
                    if (data.success) {
                        showSuccessAlert(data.message);
                        refreshEmployeeList();
                    } else {
                        alert('Delete failed: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    if (error.name === 'AbortError') {
                        contentArea.innerHTML = `<div class="alert alert-danger">Delete request timed out.</div>`;
                    } else {
                        contentArea.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                    }
                });
            }
        }

        if (target.closest('[data-page-link]')) {
            event.preventDefault();
            const link = target.closest('[data-page-link]');
            fetch(link.href, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(data => {
                contentArea.innerHTML = data;
            })
            .catch(error => {
                console.error('Pagination error:', error);
            });
        }
    }

    // Attach listeners once
    contentArea.addEventListener('submit', handleSubmit);
    contentArea.addEventListener('click', handleClick);

    // Hide Alerts
    function hideAlerts() {
        let successAlert = document.getElementById('successAlert');
        let searchErrorAlert = document.getElementById('searchErrorAlert');
        if (successAlert) setTimeout(() => { successAlert.style.display = 'none'; }, 1000);
        if (searchErrorAlert) setTimeout(() => { searchErrorAlert.style.display = 'none'; }, 1000);
    }
    hideAlerts();
});