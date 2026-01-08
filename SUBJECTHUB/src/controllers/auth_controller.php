<?php
// SUBJECTHUB - Authentication Controller
// --------------------------------------

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once '../../config/database.php';
require_once __DIR__ . '/../includes/activity_logger.php';

// --- ACTION ROUTING ---
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'signup':
        handle_signup($pdo);
        break;
    case 'login':
        handle_login($pdo);
        break;
    case 'logout':
        handle_logout();
        break;
    case 'forgot_password':
        handle_forgot_password($pdo);
        break;
    case 'reset_password':
        handle_reset_password($pdo);
        break;
    default:
        // Redirect to home or login page if no action is specified
        header("Location: " . BASE_URL . "/templates/login.php?error=Invalid action.");
        exit();
}

// --- ACTION HANDLERS ---


/**
 * Handles forgot password requests.
 */
function handle_forgot_password($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: " . BASE_URL . "/templates/forgot_password.php");
        exit();
    }

    $email = trim($_POST['email']);

    if (empty($email)) {
        header("Location: " . BASE_URL . "/templates/forgot_password.php?error=Email is required.");
        exit();
    }

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if (!$stmt->fetch()) {
        header("Location: " . BASE_URL . "/templates/forgot_password.php?error=No user found with that email address.");
        exit();
    }

    // Generate a unique token
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Store the token in the database
    $sql = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $token, $expires_at]);
        
        // In a real app, you would send an email with this link
        $reset_link = "http://localhost" . BASE_URL . "/templates/reset_password.php?token=$token";
        header("Location: " . BASE_URL . "/templates/forgot_password.php?message=A password reset link has been sent to your email address (for now, check this link: $reset_link)");
        exit();
    } catch (PDOException $e) {
        header("Location: " . BASE_URL . "/templates/forgot_password.php?error=Database error.");
        exit();
    }
}

/**
 * Handles password reset.
 */
function handle_reset_password($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: " . BASE_URL . "/templates/reset_password.php");
        exit();
    }

    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($token) || empty($password) || empty($confirm_password)) {
        header("Location: " . BASE_URL . "/templates/reset_password.php?token=$token&error=All fields are required.");
        exit();
    }

    if ($password !== $confirm_password) {
        header("Location: " . BASE_URL . "/templates/reset_password.php?token=$token&error=Passwords do not match.");
        exit();
    }

    // Check if token is valid
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset_request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset_request) {
        header("Location: " . BASE_URL . "/templates/reset_password.php?token=$token&error=Invalid or expired token.");
        exit();
    }

    // Hash the new password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $email = $reset_request['email'];

    // Update the user's password
    $sql = "UPDATE users SET password = ? WHERE email = ?";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$password_hash, $email]);

        // Delete the reset token
        $sql = "DELETE FROM password_resets WHERE token = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$token]);

        header("Location: " . BASE_URL . "/templates/login.php?message=Your password has been reset successfully. Please login.");
        exit();
    } catch (PDOException $e) {
        header("Location: " . BASE_URL . "/templates/reset_password.php?token=$token&error=Database error.");
        exit();
    }
}


/**
 * Handles user registration.
 */
function handle_signup($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: " . BASE_URL . "/templates/signup.php");
        exit();
    }

    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Basic validation
    if (empty($full_name) || empty($email) || empty($password) || empty($role)) {
        header("Location: " . BASE_URL . "/templates/signup.php?error=All fields are required.");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: " . BASE_URL . "/templates/signup.php?error=Invalid email format.");
        exit();
    }
    
    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header("Location: " . BASE_URL . "/templates/signup.php?error=An account with this email already exists.");
        exit();
    }

    // Hash the password for security
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into the database
    $sql = "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$full_name, $email, $password_hash, $role]);
        
        // Redirect to login page with a success message
        header("Location: " . BASE_URL . "/templates/login.php?message=Signup successful! Please login.");
        exit();
    } catch (PDOException $e) {
        // In a real app, log this error instead of displaying it
        header("Location: " . BASE_URL . "/templates/signup.php?error=Database error during registration.");
        exit();
    }
}

/**
 * Handles user login.
 */
function handle_login($pdo) {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        header("Location: " . BASE_URL . "/templates/login.php");
        exit();
    }

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {

        header("Location: " . BASE_URL . "/templates/login.php?error=Email and password are required.");
        exit();
    }

    // Fetch user from the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    // Verify user and password
    if ($user && password_verify($password, $user['password'])) {

        // Password is correct, start the session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_full_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['profile_completed'] = $user['profile_completed'];


        // Log the login activity
        log_activity($pdo, $user['id'], 'User logged in');

        // Role-based redirection
        if ($user['role'] === 'admin') {

            header("Location: " . BASE_URL . "/templates/admin_dashboard.php");
        } else {
    
            header("Location: " . BASE_URL . "/templates/dashboard.php");
        }
        exit();
    } else {
    
        // Invalid credentials
        header("Location: " . BASE_URL . "/templates/login.php?error=Invalid email or password.");
        exit();
    }
}

/**
 * Handles user logout.
 */
function handle_logout() {
    // Unset all of the session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to login page
    header("Location: " . BASE_URL . "/templates/login.php?message=You have been logged out.");
    exit();
}
?>