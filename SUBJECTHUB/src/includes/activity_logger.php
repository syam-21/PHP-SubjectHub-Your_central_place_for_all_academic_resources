<?php
// activity_logger.php

/**
 * Logs a user's action to the activity_logs table.
 *
 * @param PDO $pdo The database connection object.
 * @param int $user_id The ID of the user performing the action.
 * @param string $action A description of the action performed.
 * @return void
 */
function log_activity($pdo, $user_id, $action) {
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, ?)");
        $stmt->execute([$user_id, $action]);
    } catch (PDOException $e) {
        // In a real production environment, you would log this error to a file
        // or a monitoring service instead of halting the script.
        // For this project, we'll suppress the error to avoid interrupting user flow.
    }
}
?>