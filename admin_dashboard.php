<?php
session_start();
include('db.php');

// Restrict access to Admins only
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Admin') {
    header('Location: landing.php');
    exit();
}

// Initialize variables for alerts
$successMessage = "";
$errorMessage = "";

// Handle account creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_account'])) {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Check if email already exists
    $sql = "SELECT * FROM user WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $errorMessage = "Email already exists.";
    } else {
        // Insert new user into the database
        $sql = "INSERT INTO user (email, password, role) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $email, $password, $role);

        if ($stmt->execute()) {
            $successMessage = "User created successfully!";
        } else {
            $errorMessage = "Error creating user.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" data-bs-toggle="modal" data-bs-target="#createAccountModal">Create Accounts</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#viewAnalytics">View Analytics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#viewDocuments">View Documents</a>
                    </li>
                </ul>
                <button class="btn btn-danger ms-auto"><a href="logout.php" style="color: white; text-decoration: none;">Logout</a></button>
            </div>
        </div>
    </nav>

    <!-- Success and Error Alerts -->
    <div class="container mt-5 pt-5">
        <?php if (!empty($successMessage)): ?>
            <div id="successAlert" class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $successMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <div id="errorAlert" class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $errorMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Create Accounts Modal -->
    <div class="modal fade" id="createAccountModal" tabindex="-1" aria-labelledby="createAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createAccountModalLabel">Create Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select name="role" id="role" class="form-select">
                                <option value="" disabled selected>Select Role</option>
                                <option value="Student">Student</option>
                                <option value="Admin">Admin</option>
                                <option value="Dean">Dean</option>
                                <option value="Registrar">Registrar</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="create_account" class="btn btn-primary">Create Account</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Analytics Section -->
    <section id="viewAnalytics" class='container mt-5 pt-5'>
        <!-- Add analytics content here -->
        <h2>View Analytics</h2>
        <p>Insert analytics charts or data here.</p>
    </section>

    <!-- View Documents Section -->
    <section id="viewDocuments" class='container mt-5 pt-5'>
        <!-- Add documents content here -->
        <h2>View Documents</h2>
        <p>Insert documents list or viewer here.</p>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto fixed-bottom">
        &copy; 2025 Admin Dashboard. All rights reserved.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>
