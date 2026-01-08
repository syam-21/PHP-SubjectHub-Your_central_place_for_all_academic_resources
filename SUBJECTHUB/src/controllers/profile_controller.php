<?php
// src/controllers/profile_controller.php

function get_user_info($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_upload_history($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM resources WHERE uploader_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_activity_logs($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

require_once __DIR__ . '/../includes/activity_logger.php';

// --- ACTION ROUTING ---
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'complete_profile') {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    require_once '../../config/database.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $user_id_field = trim($_POST['user_id_field']);
        $phone = trim($_POST['phone']);
        $role = $_SESSION['user_role'];

        if ($role === 'student') {
            $sql = "UPDATE users SET student_id = ?, phone_number = ?, profile_completed = 1 WHERE id = ?";
        } else {
            $sql = "UPDATE users SET teacher_designation = ?, phone_number = ?, profile_completed = 1 WHERE id = ?";
        }

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id_field, $phone, $user_id]);
            $_SESSION['profile_completed'] = 1;
            log_activity($pdo, $user_id, 'Completed profile');
            header("Location: " . BASE_URL . "/templates/dashboard.php");
            exit();
        } catch (PDOException $e) {
            header("Location: " . BASE_URL . "/templates/dashboard.php?error=Database error during profile completion.");
            exit();
        }
    }
}