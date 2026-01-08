<?php
$page_title = "Subject Dashboard";
require_once '../config/database.php';

// --- AUTHENTICATION & DATA FETCHING ---

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/templates/login.php?error=Please log in to view this page.");
    exit();
}

// 2. Check for a valid subject ID
$subject_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($subject_id <= 0) {
    header("Location: " . BASE_URL . "/templates/dashboard.php?error=Invalid subject selected.");
    exit();
}

// 3. Fetch subject details from the database
$stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
$stmt->execute([$subject_id]);
$subject = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$subject) {
    header("Location: " . BASE_URL . "/templates/dashboard.php?error=Subject not found.");
    exit();
}
include 'partials/header.php';

$page_title = htmlspecialchars($subject['name']); // Set dynamic page title

// 4. Fetch all approved resources for this subject and group them by type
$resources = [
    'student_note' => [],
    'teacher_note' => [],
    'book' => [],
    'assignment' => [],
    'question_paper' => []
];

$sql = "SELECT r.*, u.full_name, u.role, u.student_id, u.teacher_designation 
        FROM resources r
        JOIN users u ON r.uploader_id = u.id
        WHERE r.subject_id = ?
        ORDER BY r.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$subject_id]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if (array_key_exists($row['resource_type'], $resources)) {
        $resources[$row['resource_type']][] = $row;
    }
}
?>

<div class="container-fluid">
    <!-- Subject Header -->
    <div class="p-5 mb-4 bg-light rounded-3 bg-gradient-soft text-center">
        <div class="container-fluid py-3">
            <i class="fas <?php echo htmlspecialchars($subject['icon_class']); ?> fa-3x text-primary mb-3"></i>
            <h1 class="display-5 fw-bold"><?php echo htmlspecialchars($subject['name']); ?></h1>
            <p class="fs-4 text-muted">All your collaborative resources in one place.</p>
        </div>
    </div>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs nav-fill mb-4" id="resourceTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="student-notes-tab" data-bs-toggle="tab" data-bs-target="#student-notes" type="button" role="tab">Student Notes</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="teacher-notes-tab" data-bs-toggle="tab" data-bs-target="#teacher-notes" type="button" role="tab">Teacher Notes & Books</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="assignments-tab" data-bs-toggle="tab" data-bs-target="#assignments" type="button" role="tab">Assignment Topics</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="questions-tab" data-bs-toggle="tab" data-bs-target="#questions" type="button" role="tab">Previous Questions</button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="resourceTabsContent">
        <!-- Student Notes Section -->
        <div class="tab-pane fade show active" id="student-notes" role="tabpanel">
            <div class="card glass-effect mb-4">
                <div class="card-body">
                    <h4 class="card-title">Upload a Study Note (PDF or Image)</h4>
                    <form action="../src/controllers/resource_controller.php?action=upload" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                        <input type="hidden" name="resource_type" value="student_note">
                        <div class="mb-3">
                            <label for="title" class="form-label">Note Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="file" class="form-label">File (PDF, JPG, PNG)</label>
                            <input type="file" name="file" class="form-control" required accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <button type="submit" class="btn btn-primary">Upload Note</button>
                    </form>
                </div>
            </div>
            <!-- Display Student Notes Here -->
            <div class="row g-4">
                <?php if (!empty($resources['student_note'])): ?>
                    <?php foreach ($resources['student_note'] as $resource): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($resource['title']); ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted">
                                        By: <?php echo htmlspecialchars($resource['full_name']); ?>
                                        <br>
                                        <small>ID: <?php echo htmlspecialchars($resource['student_id']); ?></small>
                                    </h6>
                                    <p class="card-text">
                                        <small>Uploaded on: <?php echo date('d M Y', strtotime($resource['created_at'])); ?></small>
                                    </p>
                                    <a href="../<?php echo htmlspecialchars($resource['file_path']); ?>" class="btn btn-sm btn-outline-primary" download>
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col">
                        <p class="text-muted">No student notes uploaded yet. Be the first!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Teacher Notes & Books Section -->
        <div class="tab-pane fade" id="teacher-notes" role="tabpanel">
            <?php if ($_SESSION['user_role'] === 'teacher'): ?>
            <div class="card glass-effect mb-4">
                <div class="card-body">
                    <h4 class="card-title">Upload Lecture Notes or a Reference Book (PDF)</h4>
                     <form action="../src/controllers/resource_controller.php?action=upload" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                         <div class="mb-3">
                            <label for="resource_type" class="form-label">Type</label>
                            <select name="resource_type" class="form-select">
                                <option value="teacher_note">Lecture Note</option>
                                <option value="book">Reference Book</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="file" class="form-label">File (PDF only)</label>
                            <input type="file" name="file" class="form-control" required accept=".pdf">
                        </div>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            <!-- Display Teacher Notes & Books Here -->
            <div class="row g-4">
                <?php 
                $teacher_resources = array_merge($resources['teacher_note'], $resources['book']);
                if (!empty($teacher_resources)): 
                ?>
                    <?php foreach ($teacher_resources as $resource): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <span class="badge bg-primary mb-2"><?php echo $resource['resource_type'] == 'book' ? 'Book' : 'Note'; ?></span>
                                    <h5 class="card-title"><?php echo htmlspecialchars($resource['title']); ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted">
                                        By: <?php echo htmlspecialchars($resource['full_name']); ?>
                                        <br>
                                        <small><?php echo htmlspecialchars($resource['teacher_designation']); ?></small>
                                    </h6>
                                    <p class="card-text">
                                        <small>Uploaded on: <?php echo date('d M Y', strtotime($resource['created_at'])); ?></small>
                                    </p>
                                    <a href="../<?php echo htmlspecialchars($resource['file_path']); ?>" class="btn btn-sm btn-outline-primary" download>
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col">
                        <p class="text-muted">No teacher resources available yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Assignments Section -->
        <div class="tab-pane fade" id="assignments" role="tabpanel">
             <?php if ($_SESSION['user_role'] === 'teacher'): ?>
            <div class="card glass-effect mb-4">
                <div class="card-body">
                    <h4 class="card-title">Post a New Assignment Topic</h4>
                     <form action="../src/controllers/resource_controller.php?action=upload" method="POST">
                        <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                        <input type="hidden" name="resource_type" value="assignment">
                        <div class="mb-3">
                            <label for="title" class="form-label">Assignment Topic</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="instructions" class="form-label">Short Instructions</label>
                            <textarea name="instructions" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Post Topic</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            <!-- Display Assignments Here -->
            <div class="row g-4">
                <?php if (!empty($resources['assignment'])): ?>
                    <?php foreach ($resources['assignment'] as $resource): ?>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($resource['title']); ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted">
                                        Posted by: <?php echo htmlspecialchars($resource['full_name']); ?>
                                        (<?php echo htmlspecialchars($resource['teacher_designation']); ?>)
                                    </h6>
                                    <p class="card-text mt-3">
                                        <strong>Instructions:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($resource['instructions'])); ?>
                                    </p>
                                    <p class="card-text mt-3">
                                        <small>Posted on: <?php echo date('d M Y', strtotime($resource['created_at'])); ?></small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col">
                        <p class="text-muted">No assignments posted yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Previous Questions Section -->
        <div class="tab-pane fade" id="questions" role="tabpanel">
            <div class="card glass-effect mb-4">
                <div class="card-body">
                    <h4 class="card-title">Upload a Previous Year's Question Paper</h4>
                     <form action="../src/controllers/resource_controller.php?action=upload" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                        <input type="hidden" name="resource_type" value="question_paper">
                        <div class="mb-3">
                            <label for="title" class="form-label">Question Title (e.g., "Mid-Term Exam 2023")</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="file" class="form-label">File (PDF, JPG, PNG)</label>
                            <input type="file" name="file" class="form-control" required accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <button type="submit" class="btn btn-primary">Upload Question</button>
                    </form>
                </div>
            </div>
            <!-- Display Previous Questions Here -->
            <div class="row g-4">
                <?php if (!empty($resources['question_paper'])): ?>
                    <?php foreach ($resources['question_paper'] as $resource): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($resource['title']); ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted">
                                        By: <?php echo htmlspecialchars($resource['full_name']); ?>
                                        <br>
                                        <small>
                                            <?php if ($resource['role'] === 'student'): ?>
                                                ID: <?php echo htmlspecialchars($resource['student_id']); ?>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($resource['teacher_designation']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </h6>
                                    <p class="card-text">
                                        <small>Uploaded on: <?php echo date('d M Y', strtotime($resource['created_at'])); ?></small>
                                    </p>
                                    <a href="../<?php echo htmlspecialchars($resource['file_path']); ?>" class="btn btn-sm btn-outline-primary" download>
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col">
                        <p class="text-muted">No previous questions uploaded yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
