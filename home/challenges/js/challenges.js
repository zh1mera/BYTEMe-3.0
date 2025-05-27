// Daily Challenges specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.querySelector('.code-editor textarea');
    const runButton = document.querySelector('.run-btn');
    const submitButton = document.querySelector('.submit-btn');
    const languageBadge = document.querySelector('.language-badge');
    const questionText = document.querySelector('.challenge-description');

    if (textarea) {
        textarea.focus();

        // Add tab key support
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                e.preventDefault();
                const start = this.selectionStart;
                const end = this.selectionEnd;
                this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
                this.selectionStart = this.selectionEnd = start + 4;
            }
        });
    }

    async function validateAnswer(answer) {
        try {
            const response = await fetch('validate_answer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },                body: JSON.stringify({
                    language: languageBadge.textContent.trim().toLowerCase(),
                    question: questionText.textContent.trim(),
                    answer: answer,
                    action: 'validate'
                })
            });
            
            const result = await response.json();
            if (result.error) {
                throw new Error(result.error);
            }
            return result;
        } catch (error) {
            console.error('Error:', error);
            return { correct: false, error: error.message };
        }
    }

    if (runButton) {
        runButton.addEventListener('click', async function() {
            const answer = textarea.value.trim();
            if (!answer) {
                alert('Please write your answer first!');
                return;
            }

            runButton.disabled = true;
            const result = await validateAnswer(answer);
            runButton.disabled = false;

            if (result.error) {
                alert('Error checking answer: ' + result.error);
            } else {
                alert(result.correct ? 'Congratulations! Your answer is correct!' : 'Not quite right. Try again!');
            }
        });
    }

    if (submitButton) {
        submitButton.addEventListener('click', async function() {
            const answer = textarea.value.trim();
            if (!answer) {
                alert('Please write your answer first!');
                return;
            }

            try {
                submitButton.disabled = true;
                const result = await validateAnswer(answer);
                
                if (result.error) {
                    throw new Error(result.error);
                }

                // Store the answer in session and disable controls
                await fetch('validate_answer.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'save_answer',
                        answer: answer,
                        completed: result.correct
                    })
                });
                
                // Disable all controls regardless of result
                textarea.disabled = true;
                runButton.disabled = true;
                submitButton.disabled = true;
                
                if (result.correct) {
                    const alertResult = window.confirm('Congratulations! Your answer is correct! Click OK to return to homepage.');
                    if (alertResult) {
                        window.location.href = '../index.php';
                    }
                } else {
                    const alertResult = window.confirm('Your answer is incorrect. Try again next time! Click OK to return to homepage.');
                    if (alertResult) {
                        window.location.href = '../index.php';
                    }
                }
            } catch (error) {
                alert('Error: ' + error.message);
                submitButton.disabled = false;
            }
        });
    }
});
