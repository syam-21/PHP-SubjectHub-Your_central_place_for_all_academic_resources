<?php
// src/controllers/admin_controller.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/database.php';
require_once __DIR__ . '/../includes/activity_logger.php';

// --- HELPER FUNCTIONS ---

/**
 * Fetches the main statistics for the admin dashboard.
 */
function get_dashboard_data($pdo) {
    $data = [];

    // Get total users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $data['total_users'] = $stmt->fetchColumn();

    // Get total subjects
    $stmt = $pdo->query("SELECT COUNT(*) FROM subjects");
    $data['total_subjects'] = $stmt->fetchColumn();

    // Get total resources
    $stmt = $pdo->query("SELECT COUNT(*) FROM resources");
    $data['total_resources'] = $stmt->fetchColumn();

    return $data;
}



/**
 * Fetches all users from the database.
 */
function get_all_users($pdo) {
    $sql = "SELECT id, full_name, email, role FROM users ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetches a single user by their ID.
 */
function get_user_by_id($pdo, $user_id) {
    $sql = "SELECT id, full_name, email, role, student_id, teacher_designation, phone_number FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Fetches all subjects from the database.
 */
function get_all_subjects($pdo) {
    $sql = "SELECT id, name, description FROM subjects ORDER BY name ASC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetches a single subject by its ID.
 */
function get_subject_by_id($pdo, $subject_id) {
    $sql = "SELECT id, name, description, icon_class FROM subjects WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$subject_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Fetches the most recent activity logs.
 */
function get_recent_activity_logs($pdo, $limit = 20) {
    $sql = "SELECT a.action, a.created_at, u.full_name, u.role
            FROM activity_logs a
            JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC
            LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetches all resources from the database.
 */
function get_all_resources($pdo) {
    $sql = "SELECT r.id, r.title, r.resource_type, r.file_path, r.created_at,
                   s.name AS subject_name, u.full_name AS uploader_name, u.email AS uploader_email
            FROM resources r
            JOIN subjects s ON r.subject_id = s.id
            JOIN users u ON r.uploader_id = u.id
            ORDER BY r.created_at DESC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}




/**
 * Deletes a user from the database.
 */
function delete_user($pdo, $user_id) {
    // To preserve data integrity, you might want to handle user's content
    // (e.g., re-assign it) instead of just deleting.
    // For now, we will delete the user.
    $sql = "DELETE FROM users WHERE id = ?";
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$user_id]);
    } catch (PDOException $e) {
        // Handle potential foreign key constraint errors
        return false;
    }
}

/**
 * Deletes a subject from the database.
 */
function delete_subject($pdo, $subject_id) {
    // Deleting a subject will also delete associated resources due to foreign key constraints.
    $sql = "DELETE FROM subjects WHERE id = ?";
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$subject_id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Deletes a resource from the database and its associated file.
 */
function delete_resource($pdo, $resource_id) {
    // Get file path before deleting from DB
    $stmt = $pdo->prepare("SELECT file_path FROM resources WHERE id = ?");
    $stmt->execute([$resource_id]);
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resource && $resource['file_path']) {
        $file_to_delete = dirname(__DIR__, 2) . '/' . $resource['file_path']; // Adjust path
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
    }

    $sql = "DELETE FROM resources WHERE id = ?";
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$resource_id]);
    } catch (PDOException $e) {
        return false;
    }
}


// --- ACTION ROUTING ---
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'delete_resource') {
    // Check for admin privileges
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header("Location: " . BASE_URL . "/templates/login.php?error=Unauthorized action.");
        exit();
    }

    $resource_id = isset($_GET['id']) ? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) : null;

    if ($resource_id) {
        if (delete_resource($pdo, $resource_id)) {
            
            log_activity($pdo, $_SESSION['user_id'], "Deleted resource with ID: {$resource_id}");
            header("Location: " . BASE_URL . "/templates/admin_dashboard.php?message=Resource deleted successfully.");
        } else {
            header("Location: " . BASE_URL . "/templates/admin_dashboard.php?error=Failed to delete resource.");
        }
    } else {
        header("Location: " . BASE_URL . "/templates/admin_dashboard.php?error=Invalid resource ID.");
    }
    exit();
}
 elseif ($action === 'delete_user') {
    // Check for admin privileges
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header("Location: " . BASE_URL . "/templates/login.php?error=Unauthorized action.");
        exit();
    }

    $user_id = isset($_GET['id']) ? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) : null;

    if ($user_id && $user_id !== $_SESSION['user_id']) { // Prevent admin from deleting themselves
        if (delete_user($pdo, $user_id)) {
            
            log_activity($pdo, $_SESSION['user_id'], "Deleted user with ID: {$user_id}");
            header("Location: " . BASE_URL . "/templates/admin_dashboard.php?message=User deleted successfully.");
        } else {
            header("Location: " . BASE_URL . "/templates/admin_dashboard.php?error=Failed to delete user. The user may have associated resources or logs.");
        }
    } else {
        header("Location: " . BASE_URL . "/templates/admin_dashboard.php?error=Invalid user ID or you cannot delete your own account.");
    }
    exit();
} elseif ($action === 'delete_subject') {
    // Check for admin privileges
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header("Location: " . BASE_URL . "/templates/login.php?error=Unauthorized action.");
        exit();
    }

    $subject_id = isset($_GET['id']) ? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) : null;

    if ($subject_id) {
        if (delete_subject($pdo, $subject_id)) {
            
            log_activity($pdo, $_SESSION['user_id'], "Deleted subject with ID: {$subject_id}");
            header("Location: " . BASE_URL . "/templates/admin_dashboard.php?message=Subject deleted successfully.");
        } else {
            header("Location: " . BASE_URL . "/templates/admin_dashboard.php?error=Failed to delete subject.");
        }
    } else {
        header("Location: " . BASE_URL . "/templates/admin_dashboard.php?error=Invalid subject ID.");
    }
    exit();
} elseif ($action === 'add_subject' || $action === 'edit_subject') {
    // Check for admin privileges
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header("Location: " . BASE_URL . "/templates/login.php?error=Unauthorized action.");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $subject_id = filter_input(INPUT_POST, 'subject_id', FILTER_VALIDATE_INT);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $icon_class = trim($_POST['icon_class']);

        if (empty($name)) {
            header("Location: " . BASE_URL . "/templates/manage_subject.php?error=Subject name cannot be empty.");
            exit();
        }

        if ($action === 'add_subject') {
            $sql = "INSERT INTO subjects (name, description, icon_class) VALUES (?, ?, ?)";
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $description, $icon_class]);
                
                log_activity($pdo, $_SESSION['user_id'], "Added new subject: {$name}");
                header("Location: " . BASE_URL . "/templates/admin_dashboard.php?message=Subject added successfully.");
                exit();
            } catch (PDOException $e) {
                header("Location: " . BASE_URL . "/templates/manage_subject.php?error=Error adding subject: " . $e->getMessage());
                exit();
            }
        } elseif ($action === 'edit_subject') {
            if (!$subject_id) {
                header("Location: " . BASE_URL . "/templates/admin_dashboard.php?error=Invalid subject ID for editing.");
                exit();
            }
            $sql = "UPDATE subjects SET name = ?, description = ?, icon_class = ? WHERE id = ?";
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $description, $icon_class, $subject_id]);
                
                log_activity($pdo, $_SESSION['user_id'], "Edited subject ID: {$subject_id} ({$name})");
                header("Location: " . BASE_URL . "/templates/admin_dashboard.php?message=Subject updated successfully.");
                exit();
            } catch (PDOException $e) {
                header("Location: " . BASE_URL . "/templates/manage_subject.php?id={$subject_id}&error=Error updating subject: " . $e->getMessage());
                exit();
            }
        }
    }
    exit();
} elseif ($action === 'add_user' || $action === 'edit_user') {
    // Check for admin privileges
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header("Location: " . BASE_URL . "/templates/login.php?error=Unauthorized action.");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $role = trim($_POST['role']);
        $phone_number = trim($_POST['phone_number']);
        $student_id = trim($_POST['student_id']);
        $teacher_designation = trim($_POST['teacher_designation']);
        
        // Basic validation
        if (empty($full_name) || empty($email) || empty($role)) {
            header("Location: " . BASE_URL . "/templates/manage_user.php?error=Full name, email, and role are required.");
            exit();
        }

        if ($action === 'add_user') {
            $password = $_POST['password'];
            if (empty($password)) {
                header("Location: " . BASE_URL . "/templates/manage_user.php?error=Password is required for new users.");
                exit();
            }
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (full_name, email, password, role, student_id, teacher_designation, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?)";
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$full_name, $email, $password_hash, $role, $student_id, $teacher_designation, $phone_number]);
                
                log_activity($pdo, $_SESSION['user_id'], "Added new user: {$full_name} ({$email})");
                header("Location: " . BASE_URL . "/templates/admin_dashboard.php?message=User added successfully.");
                exit();
            } catch (PDOException $e) {
                header("Location: " . BASE_URL . "/templates/manage_user.php?error=Error adding user: " . $e->getMessage());
                exit();
            }
        } elseif ($action === 'edit_user') {
            if (!$user_id) {
                header("Location: " . BASE_URL . "/templates/admin_dashboard.php?error=Invalid user ID for editing.");
                exit();
            }
            
            $sql = "UPDATE users SET full_name = ?, email = ?, role = ?, student_id = ?, teacher_designation = ?, phone_number = ? WHERE id = ?";
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$full_name, $email, $role, $student_id, $teacher_designation, $phone_number, $user_id]);
                
                log_activity($pdo, $_SESSION['user_id'], "Edited user ID: {$user_id} ({$email})");
                header("Location: " . BASE_URL . "/templates/admin_dashboard.php?message=User updated successfully.");
                exit();
            } catch (PDOException $e) {
                header("Location: " . BASE_URL . "/templates/manage_user.php?id={$user_id}&error=Error updating user: " . $e->getMessage());
                exit();
            }
        }
    }
}
