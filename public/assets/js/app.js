document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const hamburger = document.getElementById('hamburger');
    const contentArea = document.getElementById('content-area');
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    let isSidebarToggled = false;
    let isSidebarHovered = false;
    let isHamburgerHovered = false;
    let isNavigating = false;
    let dropdownTimeout;

    function toggleSidebar() {
        isSidebarToggled = !isSidebarToggled;
        console.log('Sidebar toggled, states:', { isSidebarToggled, isHamburgerHovered, isSidebarHovered, isNavigating });
        if (isSidebarToggled) {
            sidebar.classList.add('visible');
            hamburger.style.display = 'none';
        } else {
            sidebar.classList.remove('visible');
            hamburger.style.display = 'block';
        }
    }

    hamburger.addEventListener('click', toggleSidebar);

    function debouncedToggleSidebar() {
        clearTimeout(dropdownTimeout);
        dropdownTimeout = setTimeout(() => {
            if (!isSidebarToggled && !isSidebarHovered && !isHamburgerHovered && !isNavigating) {
                sidebar.classList.remove('visible');
                hamburger.style.display = 'block';
                console.log('Sidebar hidden due to no hover, states:', { isHamburgerHovered, isSidebarHovered, isSidebarToggled, isNavigating });
            }
        }, 200);
    }

    sidebar.addEventListener('mouseenter', () => {
        isSidebarHovered = true;
        console.log('Sidebar mouseenter, states:', { isHamburgerHovered, isSidebarHovered, isSidebarToggled, isNavigating });
        if (!isSidebarToggled && !isNavigating) {
            sidebar.classList.add('visible');
            hamburger.style.display = 'none';
        }
    });

    sidebar.addEventListener('mouseleave', () => {
        isSidebarHovered = false;
        console.log('Sidebar mouseleave, states:', { isHamburgerHovered, isSidebarHovered, isSidebarToggled, isNavigating });
        debouncedToggleSidebar();
    });

    hamburger.addEventListener('mouseenter', () => {
        isHamburgerHovered = true;
        console.log('Hamburger mouseenter, states:', { isHamburgerHovered, isSidebarHovered, isSidebarToggled, isNavigating });
        if (!isSidebarToggled && !isNavigating) {
            sidebar.classList.add('visible');
            hamburger.style.display = 'none';
        }
    });

    hamburger.addEventListener('mouseleave', () => {
        isHamburgerHovered = false;
        console.log('Hamburger mouseleave, states:', { isHamburgerHovered, isSidebarHovered, isSidebarToggled, isNavigating });
        debouncedToggleSidebar();
    });

    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const parentLi = this.parentElement;
            const isActive = parentLi.classList.contains('active');
            console.log('Dropdown toggle clicked', { toggle: this.textContent, isActive });

            document.querySelectorAll('.sidebar-menu li.active').forEach(item => {
                item.classList.remove('active');
            });

            if (!isActive) {
                parentLi.classList.add('active');
            }
        });
    });

    document.body.addEventListener('htmx:beforeRequest', function (e) {
        if (e.target.tagName === 'A' && e.target.getAttribute('hx-get')) {
            isNavigating = true;
            console.log('HTMX navigation started, states:', { isNavigating, isSidebarToggled, isSidebarHovered, target: e.target.href });
        }
    });

    document.body.addEventListener('htmx:afterRequest', function(e) {
        const formIds = ['addEmployeeForm', 'editEmployeeForm', 'addPositionForm', 'editPositionForm'];
        const isDeleteForm = e.target && e.target.id && e.target.id.startsWith('deletePositionForm_');
        const isFormRequest = e.target && e.target.id && (formIds.includes(e.target.id) || isDeleteForm);

        if (isFormRequest) {
            if (e.detail.successful) {
                const modalId = e.target.id.includes('Employee') ? 'addEmployeeModal' : 'addPositionModal';
                const modalElement = document.getElementById(modalId);
                if (modalElement) {
                    const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                    modal.hide();
                    console.log(`Closed modal: ${modalId}`);
                } else if (!isDeleteForm) {
                    console.error(`Modal ${modalId} not found`);
                }
                const errorContainer = document.getElementById('error-message');
                if (errorContainer) errorContainer.innerHTML = '';
            } else {
                console.error(`HTMX request failed for ${e.target.id || 'unknown form'}:`, e.detail.xhr.status, e.detail.xhr.responseText);
                const errorContainer = document.getElementById('error-message') || document.createElement('div');
                if (!errorContainer.id) {
                    errorContainer.id = 'error-message';
                    errorContainer.className = 'alert alert-danger';
                    document.querySelector('#content-area').prepend(errorContainer);
                }
                let errorMessage = 'An error occurred. Please try again.';
                if (e.detail.xhr.status === 422) {
                    errorMessage = e.detail.xhr.responseText.match(/<div[^>]*error[^>]*>([^<]*)<\/div>/)?.[1] || 'Validation error occurred';
                } else if (e.detail.xhr.status === 500) {
                    errorMessage = 'Server error occurred. Please try again later.';
                }
                errorContainer.style.display = 'block';
                errorContainer.innerHTML = errorMessage;
            }
        } else {
            // Handle non-form requests (e.g., navigation links)
            if (!e.detail.successful) {
                console.error(`HTMX request failed for ${e.detail.path}:`, e.detail.xhr.status, e.detail.xhr.responseText);
                const errorContainer = document.getElementById('error-message') || document.createElement('div');
                if (!errorContainer.id) {
                    errorContainer.id = 'error-message';
                    errorContainer.className = 'alert alert-danger';
                    document.querySelector('#content-area').prepend(errorContainer);
                }
                let errorMessage = e.detail.xhr.status === 500 ? 'Server error occurred. Please try again later.' : 'Request failed.';
                errorContainer.style.display = 'block';
                errorContainer.innerHTML = errorMessage;
            }
        }
        // Reset navigation state
        if (isNavigating) {
            isNavigating = false;
            console.log('Navigation completed, isNavigating reset, states:', { isNavigating, isSidebarToggled });
            if (!isSidebarToggled && !isSidebarHovered) {
                sidebar.classList.remove('visible');
                hamburger.style.display = 'block';
                console.log('Sidebar hidden after navigation, states:', { isHamburgerHovered, isSidebarHovered, isSidebarToggled, isNavigating });
            }
        }
    });

    document.body.addEventListener('click', function (e) {
        if (e.target.classList.contains('view-qr')) {
            const qrCode = e.target.getAttribute('data-qr');
            console.log('View QR clicked', { qrCode });
            const qrModal = new bootstrap.Modal(document.getElementById('viewQrModal'));
            const qrImage = document.getElementById('qrImage');
            qrImage.src = `/qr_codes/${qrCode}.png`;
            qrModal.show();
        }

        if (e.target.classList.contains('edit-employee')) {
            const employeeId = e.target.getAttribute('data-id');
            console.log('Edit employee clicked', { employeeId });
            htmx.ajax('GET', `/dashboard/employees/${employeeId}`, {
                target: '#editEmployeeModal .modal-body',
                swap: 'innerHTML'
            }).then(() => {
                const editModal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));
                editModal.show();
            });
        }

        if (e.target.classList.contains('edit-position')) {
            const positionId = e.target.getAttribute('data-id');
            console.log('Edit position clicked', { positionId });
            htmx.ajax('GET', `/dashboard/positions/${positionId}`, {
                target: '#editPositionModal .modal-body',
                swap: 'innerHTML'
            }).then(() => {
                const editModal = new bootstrap.Modal(document.getElementById('editPositionModal'));
                editModal.show();
            });
        }
    });

    document.getElementById('addEmployeeModal').addEventListener('show.bs.modal', function () {
        console.log('Add employee modal shown');
        htmx.ajax('GET', '/dashboard/positions/list', {
            target: '#addEmployeeModal select[name="position_id"]',
            swap: 'innerHTML'
        });
    });
});