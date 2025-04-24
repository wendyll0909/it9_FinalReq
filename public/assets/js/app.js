document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector('.sidebar');
    const hamburgerMenu = document.querySelector('.hamburger-menu');
    const contentArea = document.getElementById('content-area');
    const dropdownToggles = document.querySelectorAll('.nav-link[data-toggle-dropdown]');
    let isSidebarToggled = false;
    let isSidebarHovered = false;
    let isHamburgerHovered = false;
    let isNavigating = false;
    let dropdownTimeout;
    let currentQrCode = null; // Store the current QR code for downloading

    // Log missing elements for debugging
    if (!sidebar) console.warn('Sidebar element not found');
    if (!hamburgerMenu) console.warn('Hamburger menu element not found');
    if (!contentArea) console.warn('Content area element not found');

    function toggleSidebar() {
        isSidebarToggled = !isSidebarToggled;
        console.log('Sidebar toggled, states:', { isSidebarToggled, isHamburgerHovered, isSidebarHovered, isNavigating });
        if (sidebar) {
            sidebar.classList.toggle('visible', isSidebarToggled);
        }
    }

    if (hamburgerMenu) {
        hamburgerMenu.addEventListener('click', toggleSidebar);
    }

    function debouncedToggleSidebar() {
        clearTimeout(dropdownTimeout);
        dropdownTimeout = setTimeout(() => {
            if (!isSidebarToggled && !isSidebarHovered && !isHamburgerHovered && !isNavigating && sidebar) {
                sidebar.classList.remove('visible');
                console.log('Sidebar hidden due to no hover, states:', { isHamburgerHovered, isSidebarHovered, isSidebarToggled, isNavigating });
            }
        }, 200);
    }

    if (sidebar) {
        sidebar.addEventListener('mouseenter', () => {
            isSidebarHovered = true;
            console.log('Sidebar mouseenter, states:', { isHamburgerHovered, isSidebarHovered, isSidebarToggled, isNavigating });
            if (!isSidebarToggled && !isNavigating) {
                sidebar.classList.add('visible');
            }
        });

        sidebar.addEventListener('mouseleave', () => {
            isSidebarHovered = false;
            console.log('Sidebar mouseleave, states:', { isHamburgerHovered, isSidebarHovered, isSidebarToggled, isNavigating });
            debouncedToggleSidebar();
        });
    }

    if (hamburgerMenu) {
        hamburgerMenu.addEventListener('mouseenter', () => {
            isHamburgerHovered = true;
            console.log('Hamburger mouseenter, states:', { isHamburgerHovered, isSidebarHovered, isSidebarToggled, isNavigating });
            if (!isSidebarToggled && !isNavigating && sidebar) {
                sidebar.classList.add('visible');
            }
        });

        hamburgerMenu.addEventListener('mouseleave', () => {
            isHamburgerHovered = false;
            console.log('Hamburger mouseleave, states:', { isHamburgerHovered, isSidebarHovered, isSidebarToggled, isNavigating });
            debouncedToggleSidebar();
        });
    }

    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const dropdown = this.nextElementSibling;
            const isVisible = dropdown.style.display === 'block';
            console.log('Dropdown toggle clicked', { toggle: this.textContent, isVisible });

            document.querySelectorAll('.employee-dropdown').forEach(menu => {
                menu.style.display = 'none';
            });

            dropdown.style.display = isVisible ? 'none' : 'block';
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
    
        if (isFormRequest && e.detail.successful) {
            // Close all modals
            document.querySelectorAll('.modal').forEach(modalEl => {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            });
    
            // Clear error messages
            const errorContainer = document.getElementById('error-message');
            if (errorContainer) errorContainer.style.display = 'none';
            const fallbackError = document.getElementById('fallback-error');
            if (fallbackError) fallbackError.style.display = 'none';
    
            // Show success message for edit form
            if (e.target.id === 'editEmployeeForm') {
                const successContainer = document.getElementById('success-message');
                if (successContainer) {
                    successContainer.style.display = 'block';
                    setTimeout(() => {
                        successContainer.style.display = 'none';
                    }, 3000);
                }
            }
        }
    
        if (isFormRequest && !e.detail.successful) {
            console.error(`HTMX request failed for ${e.target.id || 'unknown form'}:`, e.detail.xhr.status, e.detail.xhr.responseText);
            const errorContainer = document.getElementById('error-message') || document.getElementById('fallback-error');
            if (errorContainer) {
                let errorMessage = 'An unexpected error occurred. Please try again.';
                if (e.detail.xhr.status === 422) {
                    errorMessage = e.detail.xhr.responseText.match(/<li[^>]*>([^<]*)<\/li>/)?.[1] || 'Validation error occurred';
                } else if (e.detail.xhr.status === 500) {
                    errorMessage = e.detail.xhr.responseText.match(/<div[^>]*alert-danger[^>]*>([^<]*)<\/div>/)?.[1] || 'Server error occurred. Please try again later.';
                }
                errorContainer.innerHTML = errorMessage;
                errorContainer.style.display = 'block';
            }
        }
    
        // Reset navigation state
        if (typeof isNavigating !== 'undefined' && isNavigating) {
            isNavigating = false;
            console.log('Navigation completed, isNavigating reset, states:', { isNavigating, isSidebarToggled });
            if (typeof isSidebarToggled !== 'undefined' && !isSidebarToggled && typeof isSidebarHovered !== 'undefined' && !isSidebarHovered && sidebar) {
                sidebar.classList.remove('visible');
                console.log('Sidebar hidden after navigation, states:', { isHamburgerHovered, isSidebarHovered, isSidebarToggled, isNavigating });
            }
        }
    });

    document.body.addEventListener('click', function (e) {
        if (e.target.classList.contains('view-qr')) {
            const qrCode = e.target.getAttribute('data-qr');
            currentQrCode = qrCode; // Store the QR code for downloading
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

    // Handle modal "Download QR" button
    const downloadQrButton = document.getElementById('downloadQrButton');
    if (downloadQrButton) {
        downloadQrButton.addEventListener('click', function () {
            if (currentQrCode) {
                downloadQR(currentQrCode);
            } else {
                console.error('No QR code selected for download');
                const errorContainer = document.getElementById('fallback-error');
                if (errorContainer) {
                    errorContainer.innerHTML = 'No QR code available to download.';
                    errorContainer.style.display = 'block';
                }
            }
        });
    }

    const addEmployeeModal = document.getElementById('addEmployeeModal');
    if (addEmployeeModal) {
        addEmployeeModal.addEventListener('shown.bs.modal', function () {
            console.log('Add employee modal shown');
            htmx.ajax('GET', '/dashboard/positions/list', {
                target: '#addEmployeeModal select[name="position_id"]',
                swap: 'innerHTML'
            });
        });
    } else {
        console.warn('Add employee modal not found');
    }

    // Download QR code function
    function downloadQR(qrCode) {
        try {
            if (!qrCode) {
                throw new Error('QR code is not provided');
            }
            const url = `/qr_codes/${qrCode}.png`; // Server-side QR code image
            const link = document.createElement('a');
            link.href = url;
            link.download = `qr_code_${qrCode}.png`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            console.log('QR code downloaded:', qrCode);
        } catch (error) {
            console.error('Error in downloadQR:', error);
            const errorContainer = document.getElementById('fallback-error');
            if (errorContainer) {
                errorContainer.innerHTML = 'An unexpected error occurred while downloading the QR code.';
                errorContainer.style.display = 'block';
            }
        }
    }
});