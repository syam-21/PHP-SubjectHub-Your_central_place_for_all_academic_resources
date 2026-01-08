<?php 
require_once '../config/database.php';
$page_title = "Sign Up - SubjectHub";
include 'partials/header.php'; 
?>

<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="card p-4">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">Create Your Account</h3>
                
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <form action="../src/controllers/auth_controller.php?action=signup" method="POST">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">University Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">I am a...</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="student" selected>Student</option>
                            <option value="teacher">Teacher</option>
                        </select>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Sign Up</button>
                    </div>
                </form>

                <p class="text-center mt-4 text-muted">
                    Already have an account? <a href="login.php">Login</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
