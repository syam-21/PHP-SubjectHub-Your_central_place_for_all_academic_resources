<?php
// SUBJECTHUB - Resource Controller
// --------------------------------

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/database.php';
require_once __DIR__ . '/../includes/activity_logger.php';

// --- ACTION ROUTING ---
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'upload':
        handle_upload($pdo);
        break;
    default:
        // Redirect if no valid action is specified
        header("Location: " . BASE_URL . "/templates/dashboard.php?error=Invalid resource action.");
        exit();
}

/**
 * Handles all resource uploads (files and text-only posts).
 */
function handle_upload($pdo) {
    // 1. Check if user is logged in and form is submitted
    if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: " . BASE_URL . "/templates/login.php");
        exit();
    }
    
    // 2. Get common data from POST request
    $subject_id = filter_input(INPUT_POST, 'subject_id', FILTER_VALIDATE_INT);
    $uploader_id = $_SESSION['user_id'];
    $resource_type = trim($_POST['resource_type']);
    $title = trim($_POST['title']);
    $instructions = isset($_POST['instructions']) ? trim($_POST['instructions']) : null;

    $redirect_url = BASE_URL . "/templates/subject_dashboard.php?id={$subject_id}";

    // 3. Basic Validation
    if (!$subject_id || empty($resource_type) || empty($title)) {
        header("Location: {$redirect_url}&error=Missing required fields.");
        exit();
    }
    
    // 4. Handle File Upload (if applicable)
    $file_path = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/';
        $file_name = uniqid() . '-' . basename($_FILES['file']['name']);
        $target_file = $upload_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Basic security checks
        $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($file_type, $allowed_types)) {
            header("Location: {$redirect_url}&error=Invalid file type.");
            exit();
        }

        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
            $file_path = 'uploads/' . $file_name; // Store relative path
        } else {
            header("Location: {$redirect_url}&error=Failed to upload file.");
            exit();
        }
    } elseif ($resource_type !== 'assignment' && !isset($_FILES['file'])) {
        // File is required for non-assignment types
        header("Location: {$redirect_url}&error=A file is required for this resource type.");
        exit();
    }
    
    // 5. Insert into Database
    $sql = "INSERT INTO resources (subject_id, uploader_id, resource_type, title, instructions, file_path) 
            VALUES (?, ?, ?, ?, ?, ?)";
    

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$subject_id, $uploader_id, $resource_type, $title, $instructions, $file_path]);
        
        // Log the resource upload activity
        log_activity($pdo, $uploader_id, "Uploaded a resource: " . $title . " (Type: " . $resource_type . ")");

        // 6. Redirect with success message
        header("Location: {$redirect_url}&message=Resource uploaded successfully!");
        exit();

    } catch (PDOException $e) {
        // In a real app, log this error
        error_log("Resource Upload Error: " . $e->getMessage());
        header("Location: {$redirect_url}&error=A database error occurred while saving the resource.");
        exit();
    }
}
?>
