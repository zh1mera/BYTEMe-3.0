<?php
// db/progress_functions.php

function getUserProgress($user_id) {
    global $pdo;    
    
    $progress = [
        'language' => [],
        'difficulty' => [],
        'recent' => [],
        'streak' => 0,
        'level_progress' => []
    ];
    
    try {
        error_log("Getting progress data for user_id: " . $user_id);
        
        // Get progress data for all languages
        $languages = ['python', 'javascript', 'java', 'php'];
        
        foreach ($languages as $lang) {
            // Count total attempts and correct answers
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_attempts,
                    SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct_answers
                FROM progress
                WHERE user_id = ? AND language = ?
            ");
            $stmt->execute([$user_id, $lang]);
            $counts = $stmt->fetch(PDO::FETCH_ASSOC);
              // Calculate progress using helper function
            $correct_answers = $counts['correct_answers'] ?? 0;
            $progress_info = calculateUserProgress($correct_answers);
            
            $currentLevel = $progress_info['level'];
            $progress_percentage = $progress_info['progress'];
            $proficiency = $progress_info['proficiency'];
            
            // Add to level progress array
            $progress['level_progress'][] = [
                'language_name' => $lang,
                'level_completed' => $currentLevel,
                'proficiency_level' => $proficiency,
                'progress_percentage' => $progress_percentage,
                'last_activity' => date('Y-m-d H:i:s')
            ];
            
            // Add to language array
            $progress['language'][] = [
                'language' => $lang,
                'total_attempts' => $counts['total_attempts'],
                'correct_attempts' => $counts['correct_answers'],
                'success_rate' => $counts['total_attempts'] > 0 ? 
                    round(($counts['correct_answers'] / $counts['total_attempts']) * 100, 2) : 0,
                'last_attempt' => null // We'll update this below
            ];
        }
        
        // Get most recent attempt for each language
        $stmt = $pdo->prepare("
            SELECT language, MAX(created_at) as last_attempt
            FROM progress
            WHERE user_id = ?
            GROUP BY language
        ");
        $stmt->execute([$user_id]);
        $lastAttempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Update last_attempt in language array
        foreach ($progress['language'] as &$lang) {
            foreach ($lastAttempts as $attempt) {
                if ($attempt['language'] === $lang['language']) {
                    $lang['last_attempt'] = $attempt['last_attempt'];
                    break;
                }
            }
        }
        error_log("Level progress data: " . json_encode($progress['level_progress']));
        
        // Query to get detailed language progress with attempts and success rate
        $stmt = $pdo->prepare("
            SELECT 
                language,
                COUNT(*) as total_attempts,
                SUM(is_correct) as correct_attempts,
                ROUND(100.0 * SUM(is_correct) / COUNT(*), 2) AS success_rate,
                MAX(created_at) as last_attempt,
                MIN(CASE WHEN is_correct = 1 THEN attempts_before_success ELSE NULL END) as min_attempts_to_success
            FROM (
                SELECT 
                    *,
                    (SELECT COUNT(*) 
                    FROM progress p2 
                    WHERE p2.user_id = p1.user_id 
                    AND p2.language = p1.language 
                    AND p2.created_at <= p1.created_at) as attempts_before_success
                FROM progress p1
                WHERE user_id = ?
            ) as attempts_data
            GROUP BY language
            ORDER BY last_attempt DESC
        ");
        $stmt->execute([$user_id]);
        $progress['language'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Language progress data: " . json_encode($progress['language']));

        // Query to get difficulty progress
        $stmt = $pdo->prepare("
            SELECT difficulty, 
                COUNT(*) as total_attempts,
                SUM(is_correct) as correct_attempts,
                ROUND(100.0 * SUM(is_correct) / COUNT(*), 2) AS success_rate
            FROM progress
            WHERE user_id = ?
            GROUP BY difficulty
        ");
        $stmt->execute([$user_id]);
        $progress['difficulty'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Difficulty progress data: " . json_encode($progress['difficulty']));

        // Get recent activity (last 10) with attempt counts
        $stmt = $pdo->prepare("
            SELECT 
                language, 
                difficulty, 
                is_correct, 
                created_at,
                (SELECT COUNT(*) FROM progress p2 
                WHERE p2.user_id = p1.user_id 
                AND p2.language = p1.language
                AND p2.created_at <= p1.created_at) as attempt_number
            FROM progress p1
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$user_id]);
        $progress['recent'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Recent activity data: " . json_encode($progress['recent']));

        // Calculate streak
        if (!empty($progress['recent'])) {
            try {
                $streak = 0;
                $lastDate = null;
                $today = new DateTime('today');
                
                foreach ($progress['recent'] as $activity) {
                    $activityDate = new DateTime($activity['created_at']);
                    $activityDate->setTime(0, 0, 0);
                    
                    if ($lastDate === null) {
                        if ($activityDate >= $today) {
                            $streak = 1;
                        }
                        $lastDate = $activityDate;
                    } else {
                        $diff = $lastDate->diff($activityDate);
                        if ($diff->days == 1) {
                            $streak++;
                            $lastDate = $activityDate;
                        } else {
                            break;
                        }
                    }
                }
                $progress['streak'] = $streak;
            } catch (Exception $e) {
                error_log("Error calculating streak: " . $e->getMessage());
                $progress['streak'] = 0;
            }
        }
    } catch (PDOException $e) {
        error_log("Error fetching progress: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return [];
    }

    return $progress;
}

// Function to get current level for a language
function getCurrentLevel($user_id, $language) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as attempts,
                SUM(is_correct) as correct_answers
            FROM progress
            WHERE user_id = ? AND language = ?
        ");
        $stmt->execute([$user_id, $language]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If no attempts yet, start at level 1
        if ($result['attempts'] == 0) {
            return 1;
        }
        
        // Calculate level based on correct answers
        // Each level requires 3 correct answers to unlock the next
        $level = floor($result['correct_answers'] / 3) + 1;
        
        // Cap at level 10
        return min($level, 10);
        
    } catch (PDOException $e) {
        error_log("Error getting current level: " . $e->getMessage());
        return 1;
    }
}

function updateLanguageProgress($user_id, $language, $level, $is_correct) {
    global $pdo;
    
    // Calculate proficiency level
    $proficiency_level = 'beginner';
    if ($level > 3 && $level <= 6) {
        $proficiency_level = 'intermediate';
    } elseif ($level > 6) {
        $proficiency_level = 'professional';
    }
    
    // Calculate progress percentage (9 total levels per language)
    $progress_percentage = ($level / 9) * 100;
      try {
        error_log("Attempting to update language progress for user $user_id, language $language");
        error_log("Level: $level, Is Correct: " . ($is_correct ? "true" : "false"));
        error_log("Proficiency: $proficiency_level, Progress: $progress_percentage%");
        
        // Calculate current level from progress records
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_correct
            FROM progress 
            WHERE user_id = ? 
            AND language = ? 
            AND is_correct = 1
        ");
        $stmt->execute([$user_id, $language]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalCorrect = $result['total_correct'];
          // Calculate progress using helper function
        $progress_info = calculateUserProgress($totalCorrect);
        $currentLevel = $progress_info['level'];
        $proficiency_level = $progress_info['proficiency'];
        $progress_percentage = $progress_info['progress'];
        
        // First check if we have a record
        $stmt = $pdo->prepare("SELECT * FROM user_progress WHERE user_id = ? AND language_name = ?");
        $stmt->execute([$user_id, $language]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        error_log("Existing record: " . ($existing ? json_encode($existing) : "none"));
        
        if ($existing) {
            // Update existing record
            $stmt = $pdo->prepare("
                UPDATE user_progress 
                SET level_completed = ?,
                    proficiency_level = ?,
                    progress_percentage = ?,
                    last_activity = CURRENT_TIMESTAMP
                WHERE user_id = ? AND language_name = ?
            ");
            $result = $stmt->execute([
                $currentLevel,
                $proficiency_level,
                $progress_percentage,
                $user_id, $language
            ]);
        } else {
            // Insert new record
            $stmt = $pdo->prepare("
                INSERT INTO user_progress 
                    (user_id, language_name, level_completed, proficiency_level, progress_percentage)
                VALUES 
                    (?, ?, ?, ?, ?)
            ");
            $result = $stmt->execute([
                $user_id,
                $language,
                $level,
                $proficiency_level,
                $progress_percentage
            ]);
        }
        
        error_log("Update/Insert result: " . ($result ? "success" : "failed"));
        error_log("SQL Error Info: " . json_encode($stmt->errorInfo()));
        
        return $result;
    } catch (PDOException $e) {
        error_log("Error updating language progress: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return false;
    }
}

function getLanguageProgress($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                language_name,
                level_completed,
                proficiency_level,
                progress_percentage,
                last_activity
            FROM user_progress
            WHERE user_id = ?
            ORDER BY progress_percentage DESC
        ");
        
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting language progress: " . $e->getMessage());
        return [];
    }
}

// Helper function to calculate user level and progress
function calculateUserProgress($correct_answers) {
    // For new users with no correct answers, start at level 0 with 0% progress
    if ($correct_answers <= 0) {
        return [
            'level' => 0,
            'progress' => 0,
            'proficiency' => 'beginner'
        ];
    }

    // Calculate level (3 correct answers per level)
    $level = floor($correct_answers / 3) + 1;
    $level = min($level, 9); // Cap at level 9

    // Calculate proficiency
    $proficiency = 'beginner';
    if ($level > 6) {
        $proficiency = 'professional';
    } elseif ($level > 3) {
        $proficiency = 'intermediate';
    }

    // Calculate progress percentage
    $progress = ($level / 9) * 100;

    return [
        'level' => $level,
        'progress' => number_format($progress, 2),
        'proficiency' => $proficiency
    ];
}
?>

