<?php
include('db.php'); // Include database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

// Get user details if not already set
if (!isset($user)) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}

// Get user role
$role = $_SESSION['role'] ?? 'Guest'; // Default to 'Guest' if role is not set

?>

<aside>
  <div class="profile">
    <div class="avatar"></div>
    <div class="name"><?php echo htmlspecialchars($user['user_name'] ?? 'User'); ?></div>
  </div>
  
  <div class="search-wrapper">
    <input type="text" placeholder="Search..." />
    <i class="fas fa-search"></i>
  </div>

  <nav>
    <?php if ($role == 'Admin' || $role == 'Dean') { ?>
        <a href="admin_dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="documents.php"><i class="fas fa-file-alt"></i> Student Files</a>
    <?php } ?>
    
    <?php if ($role == 'Admin') { ?>
        <a href="users.php"><i class="fas fa-users"></i> Users</a>
        <a href="tags.php"><i class="fas fa-tags"></i> Tags</a>
        <div class="settings" onclick="window.location.href='settings.php'">
          <div><i class="fas fa-cog"></i> Settings</div>
        <i class="fas fa-chevron-down"></i>
        </div>
    <?php } ?>
  </nav>
</aside>
