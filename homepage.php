<?php
session_start();
include('db.php');

// Restrict access to non-Admin roles
if (!isset($_SESSION['role']) || $_SESSION['role'] == 'Admin') {
    header('Location: landing.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">DMS Portal</a>
            <button class="btn btn-danger ms-auto"><a href="logout.php" style="color: white; text-decoration: none;">Logout</a></button>
        </div>
    </nav>

    <!-- Welcome Message -->
    <div class="container mt-5 pt-5">
        <h2>Welcome to DMS Portal</h2>
        <p>Hello, <?php echo $_SESSION['email']; ?>!</p>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto fixed-bottom">
        &copy; 2025 DMS Portal. All rights reserved.
    </footer>

</body>
</html>
