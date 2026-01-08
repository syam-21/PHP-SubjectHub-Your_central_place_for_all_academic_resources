<?php
require_once '../config/database.php';
// forgot_password.php
// -------------------

// Include header
include 'partials/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mt-5">
                <div class="card-header">
                    <h4>Forgot Password</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_GET['message'])): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
                    <?php endif; ?>
                    <form action="../src/controllers/auth_controller.php?action=forgot_password" method="post">
                        <div class="form-group">
                            <label for="email">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'partials/footer.php';
?>
