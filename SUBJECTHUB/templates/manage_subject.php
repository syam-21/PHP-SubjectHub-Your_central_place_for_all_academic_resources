<?php
$page_title = "Manage Subject";
require_once '../src/controllers/admin_controller.php';

// --- AUTHENTICATION & AUTHORIZATION ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php?error=You do not have permission to access this page.");
    exit();
}

$subject_id = isset($_GET['id']) ? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) : null;
$subject_data = ['name' => '', 'description' => '', 'icon_class' => ''];
$form_action = 'add_subject';

if ($subject_id) {
    $subject_data = get_subject_by_id($pdo, $subject_id);
    $form_action = 'edit_subject';
    if (!$subject_data) {
        header("Location: admin_dashboard.php?error=Subject not found.");
        exit();
    }
}

include 'partials/header.php';
?>

<div class="container">
    <h1 class="my-4"><?php echo $subject_id ? 'Edit Subject' : 'Add New Subject'; ?></h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Subject Details</h6>
        </div>
        <div class="card-body">
            <form action="../src/controllers/admin_controller.php?action=<?php echo $form_action; ?>" method="POST">
                <?php if ($subject_id): ?>
                    <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="name" class="form-label">Subject Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($subject_data['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($subject_data['description']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="icon_class" class="form-label">Font Awesome Icon Class</label>
                    <input type="text" class="form-control" id="icon_class" name="icon_class" value="<?php echo htmlspecialchars($subject_data['icon_class']); ?>" placeholder="e.g., fa-cogs">
                    <small class="form-text text-muted">Find icons on <a href="https://fontawesome.com/v5/search" target="_blank">Font Awesome 5</a>.</small>
                </div>

                <button type="submit" class="btn btn-primary">Save Subject</button>
                <a href="admin_dashboard.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
