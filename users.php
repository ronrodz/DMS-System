<?php
session_start();
include('include/db.php');

// Restrict access to Admins only
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Admin') {
    header('Location: landing.php');
    exit();
}

// Fetch logged-in user's details
$email = $_SESSION['email'] ?? '';

if (!empty($email)) {
    $sql = "SELECT * FROM user WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
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

// Handle approve/decline actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['doc_id'])) {
    $action = $_POST['action'];
    $doc_id = $_POST['doc_id'];
    $comments = $_POST['comments'] ?? '';

    if ($action === 'approve' || $action === 'decline') {
        $status = $action === 'approve' ? 'Approved' : 'Declined';
        $sql = "UPDATE document SET status=?, comments=? WHERE doc_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $status, $comments, $doc_id);
        if ($stmt->execute()) {
            $successMessage = "Document $status successfully.";
        } else {
            $errorMessage = "Error updating document status.";
        }
    }
}
?>


<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>FEU Roosevelt Dean's List</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<header>
    <div>FEU Roosevelt Dean's List</div>
    <div class="user">
      <i class="fas fa-user-circle"></i>
      <span><?php echo htmlspecialchars($user['user_name'] ?? 'User'); ?></span>
    </div>
</header>

  <div class="container">
  <?php include('include/sidebar.php'); ?>

    <main>
    <!-- Success/Error Messages -->
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>

    <section class="quick-upload">
        <h2>Account Creation</h2>
        <div class="container my-4">
    <form method="post" action="users.php" class="user-form needs-validation" novalidate>
        <div class="mb-3">
            <label for="user_name" class="form-label">Name</label>
            <input type="text" name="user_name" id="user_name" class="form-control" required>
            <div class="invalid-feedback">
                Please enter a name.
            </div>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" required>
            <div class="invalid-feedback">
                Please enter a valid email.
            </div>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
            <div class="invalid-feedback">
                Please enter a password.
            </div>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select name="role" id="role" class="form-select" required>
                <option value="Admin">Admin</option>
                <option value="Dean">Dean</option>
                <option value="Student">Student</option>
            </select>
            <div class="invalid-feedback">
                Please select a role.
            </div>
        </div>

        <button type="submit" name="create_account" class="btn btn-primary">Create Account</button>
    </form>
      </section>
    </main>
  </div>
</body>
</html>