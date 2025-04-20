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
        console.error('CSRF token meta tag not found. AJAX requests may fail.');
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
            loadEmployees(1, '', baseUrl, updatePagination); // From employee.js
        } else if (section === 'inactive-employees') {
            loadInactiveEmployees(1, baseUrl, updatePagination); // From employee.js
        } else if (section === 'positions') {
            loadPositions(baseUrl, updatePagination); // From positions.js
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

    // Download QR code
    window.downloadQR = function() {
        const qrImage = document.getElementById('qrImage');
        const filename = qrImage.getAttribute('data-filename');
        if (filename) {
            const link = document.createElement('a');
            link.href = `${baseUrl}/qr_codes/${filename}`;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            console.error('QR code filename not found');
        }
    };

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