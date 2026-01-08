<?php
$page_title = "Student Logbook";
require_once '../config/database.php';

// --- AUTHENTICATION & DATA FETCHING ---

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/templates/login.php?error=Please log in to view this page.");
    exit();
}
include 'partials/header.php';

// 2. Fetch all students from the logbook table
$stmt = $pdo->prepare("SELECT * FROM student_logbook ORDER BY full_name ASC");
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="text-center mb-5">
        <h1>Student Logbook</h1>
        <p class="lead text-muted">Directory of the fixed class of 50 students.</p>
    </div>

    <!-- Search Bar -->
    <div class="row justify-content-center mb-4">
        <div class="col-lg-8">
            <input type="text" id="logbookSearch" class="form-control form-control-lg" placeholder="Search for a student by name or ID...">
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Full Name</th>
                            <th>Student ID</th>
                            <th>Email Address</th>
                            <th>Phone Number</th>
                        </tr>
                    </thead>
                    <tbody id="logbookTableBody">
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                    <td><a href="mailto:<?php echo htmlspecialchars($student['email']); ?>"><?php echo htmlspecialchars($student['email']); ?></a></td>
                                    <td><a href="tel:<?php echo htmlspecialchars($student['phone_number']); ?>"><?php echo htmlspecialchars($student['phone_number']); ?></a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">The student logbook is empty.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Custom JS for Search Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('logbookSearch');
    const tableBody = document.getElementById('logbookTableBody');
    const rows = tableBody.getElementsByTagName('tr');

    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toLowerCase();
        
        for (let i = 0; i < rows.length; i++) {
            let nameCol = rows[i].getElementsByTagName('td')[0];
            let idCol = rows[i].getElementsByTagName('td')[1];

            if (nameCol || idCol) {
                let nameText = nameCol.textContent || nameCol.innerText;
                let idText = idCol.textContent || idCol.innerText;
                
                if (nameText.toLowerCase().indexOf(filter) > -1 || idText.toLowerCase().indexOf(filter) > -1) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }
    });
});
</script>

<?php include 'partials/footer.php'; ?>
