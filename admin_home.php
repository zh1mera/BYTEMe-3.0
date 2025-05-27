<?php 
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'db/db_connect.php';
require_once 'db/progress_functions.php';

// Handle user role updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['role'])) {
    try {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$_POST['role'], $_POST['user_id']]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch(PDOException $e) {
        $error = "Error updating user role: " . $e->getMessage();
    }
}

// Fetch all users
try {
    $stmt = $pdo->query("SELECT id, username, email, role, difficulty_level, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error fetching users: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css" />    <style>
        body {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .container {
            width: 90%;
            margin: 20px auto;
            padding: 0;
        }

        .header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .button {
            padding: 15px;
            border: none;
            border-radius: 8px;
            background-color: rgba(0, 0, 0, 0.1);
            color: #000000;
            font-size: 18px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.3s;
            margin-right: 10px;
        }        .user-showcase {
            width: 100%;
            margin-top: 40px;
            padding: 30px;
            background: #f5f5f5;
            border-radius: 8px;
            box-sizing: border-box;
        }
        
        .user-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .user-info {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .info-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            align-items: center;
        }

        .info-item {
            padding: 5px;
        }        .info-item strong {
            color: #666;
            margin-right: 5px;
        }

        .info-item select {
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
            background-color: white;
            font-size: 14px;
        }

        .info-item select:disabled {
            background-color: #f5f5f5;
            cursor: not-allowed;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
        }

        .action-button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .action-button.delete {
            background-color: #dc3545;
            color: white;
        }

        .action-button.delete:hover {
            background-color: #c82333;
        }

        .user-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            cursor: pointer;
        }

        .user-progress {
            display: none;
            margin-top: 15px;
        }

        .progress-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }

        .language-progress {
            margin-top: 15px;
        }

        .language-bar {
            background: #e9ecef;
            height: 20px;
            border-radius: 10px;
            margin-top: 5px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: #007bff;
            transition: width 0.3s ease;
        }

        .show {
            display: block;
        }

        .arrow {
            transition: transform 0.3s ease;
        }

        .arrow.rotated {
            transform: rotate(180deg);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header" style="margin-top: 800px;">
            <h1><span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span></h1>
            <div>
                <a href="adminpuzz.php" class="button">View Puzzles</a>
                <a href="logout.php" class="button">Logout</a>
            </div>
        </div>

        <div class="user-showcase">
            <h2>User Progress Overview</h2>
            <?php foreach ($users as $user): 
                $userProgress = getUserProgress($user['id']);
            ?>                <div class="user-card">
                    <div class="user-info">
                        <div class="info-row">
                            <div class="info-item"><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></div>
                            <div class="info-item"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></div>                            <div class="info-item">
                                <strong>Role:</strong>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <select name="role" onchange="this.form.submit()" <?php echo $user['role'] === 'admin' && $user['id'] === $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                        <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </form>
                            </div>
                            <div class="info-item"><strong>Difficulty:</strong> <?php echo ucfirst(htmlspecialchars($user['difficulty_level'] ?? 'Not Set')); ?></div>
                            <div class="info-item"><strong>Created:</strong> <?php echo date('M j, Y', strtotime($user['created_at'])); ?></div>
                            <div class="info-item actions">
                                <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="action-button delete">Delete User</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="user-header" onclick="toggleProgress('<?php echo $user['id']; ?>')">
                        <h3>Show Progress</h3>
                        <span class="arrow" id="arrow-<?php echo $user['id']; ?>">▼</span>
                    </div>
                    <div class="user-progress" id="progress-<?php echo $user['id']; ?>">
                        <div class="progress-stats">
                            <div class="stat-card">
                                <h4>Languages Explored</h4>
                                <p><?php echo count($userProgress['language']); ?></p>
                            </div>
                            <div class="stat-card">
                                <h4>Current Streak</h4>
                                <p><?php echo $userProgress['streak']; ?> days</p>
                            </div>
                        </div>
                        
                        <div class="language-progress">
                            <h4>Language Progress</h4>
                            <?php foreach ($userProgress['language'] as $lang): ?>
                                <div class="language-stat">
                                    <p><?php echo ucfirst($lang['language']); ?>: 
                                    <?php echo $lang['success_rate']; ?>% Success Rate
                                    </p>
                                    <div class="language-bar">
                                        <div class="progress-fill" style="width: <?php echo $lang['success_rate']; ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>    <script>
        function toggleProgress(userId) {
            const progressDiv = document.getElementById(`progress-${userId}`);
            const arrow = document.getElementById(`arrow-${userId}`);
            progressDiv.classList.toggle('show');
            arrow.classList.toggle('rotated');
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                fetch('delete_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Refresh the page to show updated user list
                        location.reload();
                    } else {
                        alert(data.message || 'Error deleting user');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting user');
                });
            }
        }
    </script>
</body>
</html>
