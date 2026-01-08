<?php
$page_title = "My Profile";
require_once '../config/database.php';
require_once '../src/controllers/profile_controller.php';

// --- AUTHENTICATION ---
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/templates/login.php?error=Please log in to view your profile.");
    exit();
}
include 'partials/header.php';

$user_id = $_SESSION['user_id'];
$user_info = get_user_info($pdo, $user_id);
$upload_history = get_upload_history($pdo, $user_id);
$activity_logs = get_activity_logs($pdo, $user_id);
?>

<div class="container">
    <div class="row">
        <!-- Profile Sidebar -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="https://via.placeholder.com/150" alt="Profile Picture" class="rounded-circle mb-3" width="150" height="150">
                    <h4 class="card-title"><?php echo htmlspecialchars($user_info['full_name']); ?></h4>
                    <p class="text-muted"><?php echo ucfirst(htmlspecialchars($user_info['role'])); ?></p>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($user_info['email']); ?></li>
                    <li class="list-group-item"><strong>Phone:</strong> <?php echo htmlspecialchars($user_info['phone_number']); ?></li>
                    <?php if ($user_info['role'] === 'student'): ?>
                        <li class="list-group-item"><strong>Student ID:</strong> <?php echo htmlspecialchars($user_info['student_id']); ?></li>
                    <?php else: ?>
                        <li class="list-group-item"><strong>Designation:</strong> <?php echo htmlspecialchars($user_info['teacher_designation']); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Upload History -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Upload History</h4>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php if (empty($upload_history)): ?>
                            <li class="list-group-item">You have not uploaded any resources yet.</li>
                        <?php else: ?>
                            <?php foreach ($upload_history as $upload): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($upload['title']); ?></strong>
                                        <small class="text-muted">(<?php echo htmlspecialchars($upload['resource_type']); ?>)</small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill"><?php echo date('M d, Y', strtotime($upload['created_at'])); ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Activity Logs -->
            <div class="card">
                <div class="card-header">
                    <h4>Activity Logs</h4>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php if (empty($activity_logs)): ?>
                            <li class="list-group-item">No activity to display.</li>
                        <?php else: ?>
                            <?php foreach ($activity_logs as $log): ?>
                                <li class="list-group-item">
                                    <i class="fas fa-history me-2"></i>
                                    <?php echo htmlspecialchars($log['action']); ?>
                                    <small class="text-muted float-end"><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></small>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
