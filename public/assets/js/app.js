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
    let currentQrCode = null;

    if (!sidebar) console.warn('Sidebar element not found');
    if (!hamburgerMenu) console.warn('Hamburger menu element not found');
    if (!contentArea) console.warn('Content area element not found');

    function toggleSidebar() {
        isSidebarToggled = !isSidebarToggled;
        console.log('Sidebar toggled, states:', { isSidebarToggled, isHamburgerHovered, isSidebarHovered, isNavigating });
        if (sidebar) {
            sidebar.classList.toggle('visible', isSidebarToggled);
            contentArea.offsetHeight; // Force layout recalculation
            setTimeout(() => window.removeCardTilt && window.removeCardTilt(), 50);
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
            const dropdown = this.nextElementSibling;
            const isVisible = dropdown.style.display === 'block';
            console.log('Dropdown toggle clicked', { toggle: this.textContent, isVisible });

            if (this.getAttribute('data-toggle-dropdown') && !this.getAttribute('hx-get')) {
                e.preventDefault();
            }

            document.querySelectorAll('.employee-dropdown, .attendance-dropdown').forEach(menu => {
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
        const formIds = ['addEmployeeForm', 'editEmployeeForm', 'addPositionForm', 'editPositionForm', 'editAttendanceForm'];
        const isDeleteForm = e.target && e.target.id && (e.target.id.startsWith('deletePositionForm_') || e.target.id.startsWith('deleteAttendanceForm_'));
        const isFormRequest = e.target && e.target.id && (formIds.includes(e.target.id) || isDeleteForm);

        if (isFormRequest && e.detail.successful) {
            document.querySelectorAll('.modal').forEach(modalEl => {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            });

            const errorContainer = document.getElementById('error-message');
            if (errorContainer) errorContainer.style.display = 'none';
            const fallbackError = document.getElementById('fallback-error');
            if (fallbackError) fallbackError.style.display = 'none';

            if (e.target.id === 'editEmployeeForm' || e.target.id === 'editAttendanceForm') {
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
            currentQrCode = qrCode;
            console.log('View QR clicked', { qrCode });
            const qrModal = new bootstrap.Modal(document.getElementById('viewQrModal'));
            const qrImage = document.getElementById('qrImage');
            qrImage.src = `/qr_codes/${qrCode}.png`;
            qrModal.show();
        }

        if (e.target.id === 'downloadQrButton') {
            if (currentQrCode) {
                console.log('Download QR button clicked', { currentQrCode });
                downloadQR(currentQrCode);
            } else {
                console.error('No QR code selected for download');
                const errorContainer = document.getElementById('fallback-error');
                if (errorContainer) {
                    errorContainer.innerHTML = 'No QR code available to download.';
                    errorContainer.style.display = 'block';
                }
            }
        }
    });

    const viewQrModal = document.getElementById('viewQrModal');
    if (viewQrModal) {
        viewQrModal.addEventListener('hidden.bs.modal', () => {
            currentQrCode = null;
            console.log('QR modal hidden, currentQrCode reset');
            const qrImage = document.getElementById('qrImage');
            if (qrImage) qrImage.src = '';
        });
    }
});

function downloadQR(qrCode) {
    try {
        if (!qrCode) {
            throw new Error('QR code is not provided');
        }
        const url = `/qr_codes/${qrCode}.png`;
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