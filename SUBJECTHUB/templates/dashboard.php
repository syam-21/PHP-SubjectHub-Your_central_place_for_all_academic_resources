<?php
$page_title = "Dashboard";
require_once '../config/database.php'; // For profile completion logic
include 'partials/header.php';

// --- AUTHENTICATION & PROFILE CHECK ---

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/templates/login.php?error=Please log in to access the dashboard.");
    exit();
}

// 2. Check if profile is complete
// Re-fetch user data to get the latest profile status
$stmt = $pdo->prepare("SELECT profile_completed FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$_SESSION['profile_completed'] = $user['profile_completed']; // Update session

if (!$_SESSION['profile_completed']) {
    // --- PROFILE COMPLETION FORM ---
?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card p-4 border-primary">
            <div class="card-body">
                <h3 class="card-title text-center mb-3">Complete Your Profile</h3>
                <p class="text-center text-muted mb-4">You must complete your profile before you can access the platform.</p>

                <form action="../src/controllers/profile_controller.php?action=complete_profile" method="POST">
                    <div class="mb-3">
                        <label for="student_id" class="form-label">
                            <?php echo $_SESSION['user_role'] === 'student' ? 'Student ID' : 'Teacher Designation'; ?>
                        </label>
                        <input type="text" class="form-control" id="user_id_field" name="user_id_field" required>
                        <div class="form-text">
                            <?php echo $_SESSION['user_role'] === 'student' ? 'e.g., 202101050' : 'e.g., Professor, Assistant Professor'; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Save and Continue</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
} else {
    // --- MAIN DASHBOARD CONTENT (Profile is complete) ---
?>
<div class="container">
    <div class="text-center mb-5">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_full_name']); ?>!</h1>
        <p class="lead text-muted">Select a subject to view its resources.</p>
    </div>

    <div class="row g-4">
        <?php
        // Fetch and display subjects from the database
        $stmt = $pdo->prepare("SELECT id, name, icon_class FROM subjects ORDER BY name ASC");
        $stmt->execute();
        $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($subjects as $subject) {
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 text-center">
                <div class="card-body p-5 d-flex flex-column justify-content-center align-items-center">
                    <i class="fas <?php echo htmlspecialchars($subject['icon_class']); ?> fa-3x mb-3 text-primary"></i>
                    <h5 class="card-title"><?php echo htmlspecialchars($subject['name']); ?></h5>
                    <a href="subject_dashboard.php?id=<?php echo $subject['id']; ?>" class="btn btn-outline-primary mt-3 stretched-link">View Dashboard</a>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
<?php
} // End of main dashboard content

include 'partials/footer.php'; 
?>