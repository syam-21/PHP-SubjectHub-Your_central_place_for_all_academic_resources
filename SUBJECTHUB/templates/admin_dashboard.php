<?php
$page_title = "Admin Dashboard";
require_once '../src/controllers/admin_controller.php';

// --- AUTHENTICATION & AUTHORIZATION ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "/templates/login.php?error=You do not have permission to access this page.");
    exit();
}

// Fetch data for the dashboard
$dashboard_data = get_dashboard_data($pdo);
$activity_logs = get_recent_activity_logs($pdo);
$all_users = get_all_users($pdo);
$all_subjects = get_all_subjects($pdo);
$all_resources = get_all_resources($pdo);

include 'partials/header.php';
?>

<div class="container-fluid">
    <h1 class="my-4">Admin Dashboard</h1>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $dashboard_data['total_users']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Subjects</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $dashboard_data['total_subjects']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Uploads</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $dashboard_data['total_resources']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-upload fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    <div class="row">
        <!-- User Management -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">User Management</h6>
                    <a href="manage_user.php" class="btn btn-primary btn-sm">Add New User</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="usersTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                                        <td>
                                            <a href="manage_user.php?id=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="../src/controllers/admin_controller.php?action=delete_user&id=<?php echo $user['id']; ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Are you sure you want to delete this user? This action is permanent and cannot be undone.');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subject Management -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Subject Management</h6>
                    <a href="manage_subject.php" class="btn btn-primary btn-sm">Add New Subject</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="subjectsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_subjects as $subject): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($subject['name']); ?></td>
                                        <td><?php echo htmlspecialchars($subject['description']); ?></td>
                                        <td>
                                            <a href="manage_subject.php?id=<?php echo $subject['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="../src/controllers/admin_controller.php?action=delete_subject&id=<?php echo $subject['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Are you sure you want to delete this subject? This action is permanent and will also delete associated resources.');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resource Management -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Resource Management</h6>
            <!-- Optionally add filter/search here later -->
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="resourcesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Subject</th>
                            <th>Uploader</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($all_resources)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No resources available.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($all_resources as $resource): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($resource['title']); ?></td>
                                    <td><?php echo htmlspecialchars($resource['subject_name']); ?></td>
                                    <td><?php echo htmlspecialchars($resource['uploader_name']); ?> (<?php echo htmlspecialchars($resource['uploader_email']); ?>)</td>
                                    <td><?php echo htmlspecialchars($resource['resource_type']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($resource['created_at'])); ?></td>
                                    <td>
                                        <a href="../uploads/<?php echo htmlspecialchars(basename($resource['file_path'])); ?>" target="_blank" class="btn btn-info btn-sm" title="View File">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="../src/controllers/admin_controller.php?action=delete_resource&id=<?php echo $resource['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this resource? This action is permanent and cannot be undone.');" title="Delete Resource">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Activity Logs -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Recent Activity Logs</h6>
        </div>
        <div class="card-body">
            <ul class="list-group">
                <?php if (empty($activity_logs)): ?>
                    <li class="list-group-item">No recent activity.</li>
                <?php else: ?>
                    <?php foreach ($activity_logs as $log): ?>
                        <li class="list-group-item">
                            [<?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?>]
                            <strong><?php echo htmlspecialchars($log['full_name']); ?></strong>
                            (<?php echo htmlspecialchars($log['role']); ?>):
                            <?php echo htmlspecialchars($log['action']); ?>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
