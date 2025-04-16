document.addEventListener('DOMContentLoaded', function() {
    // SPA navigation
    const navLinks = document.querySelectorAll('.nav-link[data-section]');
    const sections = document.querySelectorAll('#content-area > div');

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.getAttribute('data-section');

            // Hide all sections
            sections.forEach(sec => sec.style.display = 'none');

            // Show selected section
            const targetSection = document.getElementById(`${section}-section`);
            if (targetSection) {
                targetSection.style.display = 'block';
            }

            // Optionally fetch content via AJAX
            if (section !== 'dashboard') {
                axios.get(`/api/${section}`)
                    .then(response => {
                        targetSection.innerHTML = response.data.html || `<h2>${section.charAt(0).toUpperCase() + section.slice(1)}</h2><p>Content loaded dynamically.</p>`;
                    })
                    .catch(error => {
                        targetSection.innerHTML = `<h2>Error</h2><p>Failed to load ${section} content.</p>`;
                    });
            }
        });
    });
});