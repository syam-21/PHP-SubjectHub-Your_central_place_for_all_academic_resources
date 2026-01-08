<?php 
require_once '../config/database.php';
$page_title = "Login - SubjectHub";
include 'partials/header.php'; 
?>

<div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
        <div class="card glass-effect p-4">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">Welcome Back!</h3>
                
                <!-- Display feedback messages -->
                <?php if(isset($_GET['message'])): ?>
                    <div class="alert alert-info"><?php echo htmlspecialchars($_GET['message']); ?></div>
                <?php endif; ?>
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <form action="../src/controllers/auth_controller.php?action=login" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">University Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="text-end mt-2">
                            <a href="forgot_password.php">Forgot Password?</a>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Login</button>
                    </div>
                </form>

                <p class="text-center mt-4 text-muted">
                    Don't have an account? <a href="signup.php">Sign Up</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
