<?php 
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle full puzzle update via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json')) {
    $data = json_decode(file_get_contents("php://input"), true);

    if ($data['action'] === 'update_full_puzzle') {
        $difficulty = strtolower(trim($data['difficulty']));
        $language = strtolower(trim($data['language']));
        $content = $data['content'];

        $filepath = "puzzles/$difficulty/$language.txt";

        if (file_exists($filepath)) {
            file_put_contents($filepath, $content);
            echo "Puzzles saved successfully!";
        } else {
            echo "Puzzle file not found.";
        }
        exit();
    }
}

// Handle puzzle fetch
if (isset($_GET['get_puzzles']) && $_GET['get_puzzles'] == 1) {
    $difficulty = isset($_GET['difficulty']) ? strtolower($_GET['difficulty']) : 'beginner';
    $language = isset($_GET['language']) ? strtolower($_GET['language']) : 'python';
    $filepath = "puzzles/$difficulty/$language.txt";

    if (file_exists($filepath)) {
        $puzzles = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        echo implode("\n", $puzzles);
    } else {
        echo "No puzzles found for $difficulty / $language.";
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>View Puzzles - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <style>
        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .puzzle-container {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            width: 1000px;
            max-width: 100%;
            margin: 0 auto;
        }

        .button-container {
            margin: 20px 0;
        }

        .difficulty-btns, .language-btns {
            display: flex;
            gap: 10px;
            flex-wrap: nowrap;
            margin-bottom: 20px;
        }

        .language-btns {
            display: none !important;
        }

        .language-btns.active {
            display: flex !important;
        }

        button.btn, button.lang-btn {
            padding: 10px 20px;
            background-color: rgba(0, 0, 0, 0.1);
            color: black;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            white-space: nowrap;
        }

        button.btn:hover, button.lang-btn:hover {
            background-color: rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        button.active {
            background-color: rgba(0, 0, 0, 0.1);
            box-shadow: 0 0 6px rgba(0, 86, 179, 0.5);
        }

        textarea#full-puzzle-text {
            width: 100%;
            min-height: 400px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 15px;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            resize: vertical;
            box-sizing: border-box;
            margin-bottom: 15px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        textarea#full-puzzle-text:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.25);
        }

        button#save-full-puzzle {
            width: auto;
            padding: 12px 24px;
            background-color: #28a745;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button#save-full-puzzle:hover {
            background-color: #218838;
        }

        .button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            background-color: rgba(0, 0, 0, 0.1);
            color: black;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
            margin-left: 10px;
        }

        .button:hover {
            background-color: #0073E6;
        }

        #success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><span>Puzzle Management</span></h1>
            <div>
                <a href="admin_home.php" class="button">Back to Dashboard</a>
                <a href="logout.php" class="button">Logout</a>
            </div>
        </div>

        <div class="puzzle-container">
            <div id="success-message"></div>

            <div class="button-container">
                <h2>Select Difficulty</h2>
                <div class="difficulty-btns">
                    <button class="btn" data-difficulty="beginner">Beginner</button>
                    <button class="btn" data-difficulty="intermediate">Intermediate</button>
                    <button class="btn" data-difficulty="professional">Professional</button>
                </div>

                <div id="language-section" style="display: none;">
                    <h2>Select Language</h2>
                    <div class="language-btns" id="beginner-langs">
                        <button class="lang-btn" data-language="python">Python</button>
                        <button class="lang-btn" data-language="javascript">JavaScript</button>
                        <button class="lang-btn" data-language="java">Java</button>
                        <button class="lang-btn" data-language="php">PHP</button>
                    </div>
                    <div class="language-btns" id="intermediate-langs">
                        <button class="lang-btn" data-language="python">Python</button>
                        <button class="lang-btn" data-language="javascript">JavaScript</button>
                        <button class="lang-btn" data-language="java">Java</button>
                        <button class="lang-btn" data-language="php">PHP</button>
                    </div>
                    <div class="language-btns" id="professional-langs">
                        <button class="lang-btn" data-language="python">Python</button>
                        <button class="lang-btn" data-language="javascript">JavaScript</button>
                        <button class="lang-btn" data-language="java">Java</button>
                        <button class="lang-btn" data-language="php">PHP</button>
                    </div>
                </div>
            </div>

            <div id="puzzle-list">
                <p>Select a difficulty and language to view puzzles.</p>
            </div>
        </div>
    </div>

    <script>
    const difficultyButtons = document.querySelectorAll('.difficulty-btns .btn');
    const languageContainers = document.querySelectorAll('.language-btns');
    const puzzleList = document.getElementById('puzzle-list');
    const successMessage = document.getElementById('success-message');

    let currentDifficulty = null;
    let currentLanguage = null;

    function showSuccessMessage(message) {
        successMessage.textContent = message;
        successMessage.style.display = 'block';
        setTimeout(() => {
            successMessage.style.display = 'none';
        }, 3000);
    }

    function clearActiveButtons(container) {
        container.querySelectorAll('button').forEach(btn => btn.classList.remove('active'));
    }

    function loadPuzzles(difficulty, language) {
        fetch(`?get_puzzles=1&difficulty=${difficulty}&language=${language}`)
            .then(response => response.text())
            .then(data => {
                puzzleList.innerHTML = `
                    <h2>${difficulty.charAt(0).toUpperCase() + difficulty.slice(1)} ${language.charAt(0).toUpperCase() + language.slice(1)} Puzzles</h2>
                    <textarea id="full-puzzle-text" spellcheck="false">${data}</textarea>
                    <button id="save-full-puzzle">Save Changes</button>
                `;

                const saveButton = document.getElementById('save-full-puzzle');
                saveButton.addEventListener('click', () => {
                    const content = document.getElementById('full-puzzle-text').value;
                    
                    fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'update_full_puzzle',
                            difficulty: currentDifficulty,
                            language: currentLanguage,
                            content: content
                        })
                    })
                    .then(response => response.text())
                    .then(result => {
                        showSuccessMessage(result);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showSuccessMessage('Error saving puzzles. Please try again.');
                    });
                });
            })
            .catch(error => {
                console.error('Error:', error);
                puzzleList.innerHTML = '<p class="error">Error loading puzzles. Please try again.</p>';
            });
    }

    difficultyButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const difficulty = btn.dataset.difficulty;
            
            // If clicking the same difficulty button again
            if (currentDifficulty === difficulty) {
                // Toggle language section
                const languageSection = document.getElementById('language-section');
                if (languageSection.style.display === 'block') {
                    languageSection.style.display = 'none';
                    btn.classList.remove('active');
                    currentDifficulty = null;
                    currentLanguage = null;
                } else {
                    languageSection.style.display = 'block';
                    btn.classList.add('active');
                    currentDifficulty = difficulty;
                }
            } else {
                // Clicking a different difficulty button
                difficultyButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                const languageSection = document.getElementById('language-section');
                languageSection.style.display = 'block';
                
                languageContainers.forEach(container => {
                    container.classList.remove('active');
                });
                
                const relevantLanguages = document.getElementById(`${difficulty}-langs`);
                if (relevantLanguages) {
                    relevantLanguages.classList.add('active');
                }
                
                currentDifficulty = difficulty;
                currentLanguage = null;
            }

            // Reset puzzle list
            puzzleList.innerHTML = '<p>Select a language to view puzzles.</p>';
        });
    });

    document.querySelectorAll('.language-btns .lang-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!currentDifficulty) {
                showSuccessMessage('Please select a difficulty level first.');
                return;
            }

            const language = btn.dataset.language;
            
            // If clicking the same language button again
            if (currentLanguage === language) {
                btn.classList.remove('active');
                currentLanguage = null;
                puzzleList.innerHTML = '<p>Select a language to view puzzles.</p>';
            } else {
                // Clicking a different language button
                const languageButtons = btn.parentElement.querySelectorAll('.lang-btn');
                languageButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                currentLanguage = language;
                loadPuzzles(currentDifficulty, currentLanguage);
            }
        });
    });
    </script>
</body>
</html>
