// Home page specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Handle progress view button click
    const viewProgressBtn = document.getElementById('viewProgress');
    if (viewProgressBtn) {
        viewProgressBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // TODO: Implement progress view functionality
            alert('Progress tracking coming soon!');
        });
    }

    // Add active class to current nav link
    const currentPage = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-links a');
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage.split('/').pop()) {
            link.classList.add('active');
        }
    });
});
