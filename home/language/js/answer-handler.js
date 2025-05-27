// Update language card with progress data
function updateLanguageCard(progressData) {
    if (!progressData) return;
    
    const card = document.querySelector(`.language-card[data-language="${progressData.name}"]`);
    if (!card) return;

    // Update progress percentage
    card.querySelector('.progress-text').textContent = `${progressData.progress_percentage}%`;
    card.querySelector('.progress').style.width = `${progressData.progress_percentage}%`;

    // Update stats
    card.querySelector('[data-stat="completed"]').textContent = `✓ Completed: ${progressData.correct_attempts}`;
    card.querySelector('[data-stat="attempts"]').textContent = `🎯 Attempts: ${progressData.total_attempts}`;
    card.querySelector('[data-stat="success-rate"]').textContent = `⚡ Success Rate: ${progressData.success_rate}%`;
    card.querySelector('[data-stat="level"]').textContent = `📊 Level: ${progressData.level_completed}/9`;

    // Update proficiency badge
    const badge = card.querySelector('.proficiency-badge');
    badge.className = `proficiency-badge ${progressData.proficiency_level}`;
    badge.textContent = progressData.proficiency_level.charAt(0).toUpperCase() + progressData.proficiency_level.slice(1);

    // Update last attempt if exists
    if (progressData.last_attempt) {
        const lastAttempt = card.querySelector('.last-attempt');
        if (lastAttempt) {
            const date = new Date(progressData.last_attempt);
            lastAttempt.textContent = `Last attempt: ${date.toLocaleDateString('default', { month: 'short', day: 'numeric', year: 'numeric' })}`;
        }
    }
}

// Function to handle answer submission response
function handleAnswerResponse(data) {
    if (data.success && data.progress) {
        updateLanguageCard(data.progress);
    }
}

// Attach to the form submission event
document.addEventListener('DOMContentLoaded', function() {
    const answerForm = document.getElementById('answer-form');
    if (answerForm) {
        answerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                handleAnswerResponse(data);
                // Handle success/error message
                if (data.success) {
                    setTimeout(() => {
                        window.location.href = 'index.php'; // Redirect to levels page
                    }, 2000);
                }
                alert(data.message);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    }
});
