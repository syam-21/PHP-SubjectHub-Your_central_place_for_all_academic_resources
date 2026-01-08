<?php
$page_title = "Manage User";
require_once '../src/controllers/admin_controller.php';

// --- AUTHENTICATION & AUTHORIZATION ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php?error=You do not have permission to access this page.");
    exit();
}

$user_id = isset($_GET['id']) ? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) : null;
$user_data = [
    'full_name' => '',
    'email' => '',
    'role' => 'student',
    'student_id' => '',
    'teacher_designation' => '',
    'phone_number' => ''
];
$form_action = 'add_user';

if ($user_id) {
    $user_data = get_user_by_id($pdo, $user_id);
    $form_action = 'edit_user';
    if (!$user_data) {
        header("Location: admin_dashboard.php?error=User not found.");
        exit();
    }
}

include 'partials/header.php';
?>

<div class="container">
    <h1 class="my-4"><?php echo $user_id ? 'Edit User' : 'Add New User'; ?></h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">User Details</h6>
        </div>
        <div class="card-body">
            <form action="../src/controllers/admin_controller.php?action=<?php echo $form_action; ?>" method="POST">
                <?php if ($user_id): ?>
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_data['full_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                </div>
                <?php if (!$user_id): // Password only required for new users or separate password change ?>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                <?php endif; ?>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="student" <?php echo ($user_data['role'] === 'student') ? 'selected' : ''; ?>>Student</option>
                        <option value="teacher" <?php echo ($user_data['role'] === 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                        <option value="admin" <?php echo ($user_data['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user_data['phone_number']); ?>">
                </div>
                <div class="mb-3">
                    <label for="student_id" class="form-label">Student ID (if Student)</label>
                    <input type="text" class="form-control" id="student_id" name="student_id" value="<?php echo htmlspecialchars($user_data['student_id']); ?>">
                </div>
                <div class="mb-3">
                    <label for="teacher_designation" class="form-label">Teacher Designation (if Teacher)</label>
                    <input type="text" class="form-control" id="teacher_designation" name="teacher_designation" value="<?php echo htmlspecialchars($user_data['teacher_designation']); ?>">
                </div>

                <button type="submit" class="btn btn-primary">Save User</button>
                <a href="admin_dashboard.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
