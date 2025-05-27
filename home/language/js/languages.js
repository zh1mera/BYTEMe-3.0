// Programming Languages specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Handle start learning buttons
    const startButtons = document.querySelectorAll('.start-learning');
    startButtons.forEach(button => {
        button.addEventListener('click', function() {
            const languageCard = this.closest('.language-card');
            const language = languageCard.dataset.language;
            
            // Store selected language in session storage
            sessionStorage.setItem('selectedLanguage', language);
            
        });
    });

    // Add hover effects
    const languageCards = document.querySelectorAll('.language-card');
    languageCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
