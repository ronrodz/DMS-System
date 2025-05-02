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
<style>
aside {
    background-color: #d7defc;
    width: 224px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    color: #374151; /* gray-700 */
    font-size: 0.875rem; /* 14px */
    flex-shrink: 0;
    overflow-y: auto;
  }
  aside .profile {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
  }
  aside .profile .avatar {
    width: 32px;
    height: 32px;
    border-radius: 9999px;
    background: linear-gradient(45deg, #3f51b5, #00bcd4);
    color: white;
    font-weight: 600;
    font-size: 0.75rem; /* 12px */
    display: flex;
    align-items: center;
    justify-content: center;
    user-select: none;
  }
  aside .search-wrapper {
    position: relative;
  }
  aside input[type="text"] {
    width: 100%;
    padding: 6px 32px 6px 12px;
    border: 1px solid #d1d5db; /* gray-300 */
    border-radius: 6px;
    font-size: 0.75rem; /* 12px */
    outline-offset: 2px;
    outline-color: #3f51b5;
  }
  aside .search-wrapper i {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af; /* gray-400 */
    font-size: 0.75rem; /* 12px */
    pointer-events: none;
  }
  aside nav {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  aside nav a,
  aside nav .settings {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    color: #374151; /* gray-700 */
    font-size: 0.875rem; /* 14px */
    user-select: none;
  }
  aside nav a:hover,
  aside nav .settings:hover {
    background-color: #c5cdfd;
    color: #3f51b5;
    font-weight: 600;
  }
  aside nav a.active {
    background-color: #c5cdfd;
    color: #3f51b5;
    font-weight: 600;
  }
  aside nav .settings {
    justify-content: space-between;
  }
  aside nav .settings i {
    font-size: 0.75rem; /* 12px */
    color: #374151;
  }
</style>

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
    <?php if ($role == 'Admin') { ?>
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
