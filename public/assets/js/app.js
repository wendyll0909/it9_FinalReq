document.addEventListener('DOMContentLoaded', function() {
    let isSidebarToggled = false;
    let isSidebarHovered = false;
    let isHamburgerHovered = false;
    let debounceTimeout = null;

    // Debounce function to prevent rapid event firing
    function debounce(fn, ms) {
        return function(...args) {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => fn.apply(this, args), ms);
        };
    }

    // Sidebar persistence on navigation
    document.addEventListener('click', function(e) {
        const target = e.target.closest('[data-persist-sidebar]');
        if (target) {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.add('visible');
                isSidebarToggled = true;
            }
        }
    });

    // Hamburger menu and sidebar hover handling
    const hamburger = document.querySelector('.hamburger-menu');
    const sidebar = document.querySelector('.sidebar');
    if (hamburger && sidebar) {
        // Hamburger click to toggle sidebar
        hamburger.addEventListener('click', function() {
            isSidebarToggled = !isSidebarToggled;
            sidebar.classList.toggle('visible', isSidebarToggled);
            hamburger.style.display = isSidebarToggled ? 'none' : 'block';
            console.log('Hamburger clicked, isSidebarToggled:', isSidebarToggled);
        });

        // Hamburger hover
        hamburger.addEventListener('mouseenter', debounce(function() {
            if (!isSidebarToggled) {
                isHamburgerHovered = true;
                sidebar.classList.add('visible');
                hamburger.style.display = 'block'; // Keep visible during hover
                console.log('Hamburger mouseenter, sidebar visible');
            }
        }, 100));

        hamburger.addEventListener('mouseleave', debounce(function() {
            isHamburgerHovered = false;
            if (!isSidebarToggled && !isSidebarHovered) {
                sidebar.classList.remove('visible');
                hamburger.style.display = 'block';
                console.log('Hamburger mouseleave, sidebar hidden');
            } else {
                hamburger.style.display = 'none'; // Hide if sidebar is hovered
                console.log('Hamburger mouseleave, sidebar still visible');
            }
        }, 100));

        // Sidebar hover
        sidebar.addEventListener('mouseenter', debounce(function() {
            if (!isSidebarToggled) {
                isSidebarHovered = true;
                sidebar.classList.add('visible');
                hamburger.style.display = 'none';
                console.log('Sidebar mouseenter, sidebar visible');
            }
        }, 100));

        sidebar.addEventListener('mouseleave', debounce(function() {
            isSidebarHovered = false;
            if (!isSidebarToggled && !isHamburgerHovered) {
                sidebar.classList.remove('visible');
                hamburger.style.display = 'block';
                console.log('Sidebar mouseleave, sidebar hidden');
            }
            console.log('Sidebar mouseleave, hamburger visible');
        }, 100));
    }

    // Toggle employee dropdown and handle navigation
    const employeeLink = document.querySelector('.nav-link[data-toggle-dropdown]');
    const employeeDropdown = document.querySelector('.employee-dropdown');
    if (employeeLink && employeeDropdown) {
        employeeLink.addEventListener('click', debounce(function(e) {
            e.preventDefault();
            console.log('Employee link clicked, dropdown display:', employeeDropdown.style.display);
            employeeDropdown.style.display = employeeDropdown.style.display === 'block' ? 'none' : 'block';
            if (!employeeLink.classList.contains('htmx-request')) {
                console.log('Triggering HTMX navigation to:', employeeLink.getAttribute('hx-get'));
                htmx.ajax('GET', employeeLink.getAttribute('hx-get'), {
                    target: employeeLink.getAttribute('hx-target'),
                    swap: employeeLink.getAttribute('hx-swap')
                }).catch(error => {
                    console.error('HTMX navigation failed:', error);
                });
            }
        }, 200));
    }

    // View QR code
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('view-qr')) {
            const qrCode = e.target.getAttribute('data-qr');
            const qrImage = document.getElementById('qrImage');
            const viewQrModal = document.getElementById('viewQrModal');
            if (qrImage && viewQrModal) {
                const timestamp = new Date().getTime();
                qrImage.src = `/qr_codes/${qrCode}?t=${timestamp}`;
                qrImage.setAttribute('data-filename', qrCode);
                new bootstrap.Modal(viewQrModal).show();
            } else {
                console.error('QR modal elements not found');
            }
        }
    });

    // Edit employee/position modals
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('edit-employee') || e.target.classList.contains('edit-position')) {
            const id = e.target.getAttribute('data-id');
            const type = e.target.classList.contains('edit-employee') ? 'employees' : 'positions';
            const modalId = type === 'employees' ? 'editEmployeeModal' : 'editPositionModal';
            const formContainer = document.getElementById(`edit-${type.slice(0, -1)}-form`);
            if (formContainer) {
                formContainer.innerHTML = '<p>Loading...</p>';
                console.log(`Fetching ${type}/${id}`);
                htmx.ajax('GET', `/dashboard/${type}/${id}`, {
                    target: `#edit-${type.slice(0, -1)}-form`,
                    swap: 'innerHTML'
                }).then(() => {
                    console.log(`Loaded ${type}/${id} successfully`);
                    const modal = new bootstrap.Modal(document.getElementById(modalId));
                    modal.show();
                }).catch(error => {
                    console.error(`Failed to load ${type}/${id}:`, error);
                    formContainer.innerHTML = `<p>Error loading ${type.slice(0, -1)} data. Please try again.</p>`;
                });
            } else {
                console.error(`Form container for ${type} not found`);
            }
        }
    });

    // Modal closing for forms
    document.body.addEventListener('htmx:afterRequest', function(e) {
        const formIds = ['addEmployeeForm', 'addPositionForm', 'editEmployeeForm', 'editPositionForm'];
        if (formIds.includes(e.target.id) && e.detail.successful) {
            const modalId = e.target.id.includes('Employee') ? 'addEmployeeModal' : 'addPositionModal';
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                modal.hide();
                console.log(`Closed modal: ${modalId}`);
            } else {
                console.error(`Modal ${modalId} not found`);
            }
        }
    });

    // Auto-dismiss alerts
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => alert.classList.add('fade-out'));
    }, 1000);
});

// Download QR code
function downloadQR() {
    const qrImage = document.getElementById('qrImage');
    const filename = qrImage.getAttribute('data-filename');
    if (filename) {
        const link = document.createElement('a');
        link.href = `/qr_codes/${filename}`;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}