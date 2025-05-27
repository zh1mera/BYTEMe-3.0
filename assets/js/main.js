// Main JavaScript file for BYTEMe application
document.addEventListener('DOMContentLoaded', function() {
    // Form validation for signup
    const signupForm = document.querySelector('form');
    if (signupForm && signupForm.querySelector('#confirm_password')) {
        signupForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    }

    // Add input validation styles
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });

    // Create modal if it doesn't exist
    if (!document.getElementById('difficultyModal')) {
        const modal = document.createElement('div');
        modal.id = 'difficultyModal';
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content">
                <p class="modal-message"></p>
                <a href="welcome.php" class="return-button">Return to Welcome Page</a>
            </div>
        `;
        document.body.appendChild(modal);
    }
});

// Password toggle functionality
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    
    if (input.type === 'password') {
        input.type = 'text';
        button.style.opacity = '1';
    } else {
        input.type = 'password';
        button.style.opacity = '0.6';
    }
}

// Difficulty selection handling
function selectDifficulty(level) {
    fetch('process_difficulty.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'difficulty=' + level
    })
    .then(response => response.json())
    .then(data => {
        const modal = document.getElementById('difficultyModal');
        const message = modal.querySelector('.modal-message');
        
        if (data.status === 'success') {
            message.textContent = data.message;
            message.style.color = '#000';
        } else {
            message.textContent = 'Error: ' + data.message;
            message.style.color = '#ff0000';
        }
        
        modal.style.display = 'flex';
    })
    .catch(error => {
        console.error('Error:', error);
        const modal = document.getElementById('difficultyModal');
        const message = modal.querySelector('.modal-message');
        message.textContent = 'An error occurred while setting difficulty level.';
        message.style.color = '#ff0000';
        modal.style.display = 'flex';
    });
}

// Admin user deletion
function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        fetch('delete_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'user_id=' + userId
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the user.');
        });
    }
}
