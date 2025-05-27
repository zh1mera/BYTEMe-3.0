document.addEventListener('DOMContentLoaded', function() {
    // Function to format numbers to 2 decimal places
    function formatNumber(num) {
        return parseFloat(num).toFixed(2);
    }

    // Function to update the profile page with new progress data
    function updateProgress() {
        fetch('../home/js/progress_data.php')
            .then(response => response.json())
            .then(data => {
                // Update each language card
                data.languages.forEach(language => {
                    const card = document.querySelector(`.language-card[data-language="${language.name}"]`);
                    if (card) {
                        // Update progress percentage
                        const progressPercentage = formatNumber(language.progress_percentage);
                        card.querySelector('.progress-text').textContent = `${progressPercentage}%`;
                        card.querySelector('.progress').style.width = `${progressPercentage}%`;

                        // Update stats
                        card.querySelector('[data-stat="completed"]').textContent = `✓ Completed: ${language.correct_attempts}`;
                        card.querySelector('[data-stat="attempts"]').textContent = `🎯 Attempts: ${language.total_attempts}`;
                        card.querySelector('[data-stat="success-rate"]').textContent = `⚡ Success Rate: ${language.success_rate}%`;
                        card.querySelector('[data-stat="level"]').textContent = `📊 Level: ${language.level_completed}/9`;

                        // Update proficiency badge
                        const badge = card.querySelector('.proficiency-badge');
                        badge.className = `proficiency-badge ${language.proficiency_level}`;
                        badge.textContent = language.proficiency_level.charAt(0).toUpperCase() + language.proficiency_level.slice(1);

                        // Update last attempt if exists
                        const lastAttempt = card.querySelector('.last-attempt');
                        if (lastAttempt && language.last_attempt) {
                            const date = new Date(language.last_attempt);
                            lastAttempt.textContent = `Last attempt: ${date.toLocaleDateString('default', { month: 'short', day: 'numeric', year: 'numeric' })}`;
                        }
                    }
                });
            })
            .catch(error => {
                console.error('Error updating progress:', error);
            });
    }

    // Update progress every 30 seconds
    setInterval(updateProgress, 30000);

    // Also update when the page becomes visible again
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            updateProgress();
        }
    });
});
