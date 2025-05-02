<?php
// Fetch user info from the database (make sure user is logged in)
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT user_name FROM user WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}
?>

<style>
    header {
    background-color: #3f51b5;
    color: white;
    height: 56px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 24px;
    font-weight: 600;
    font-size: 1.125rem; /* 18px */
    flex-shrink: 0;
    }
    header .user {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.875rem; /* 14px */
    }
    header .user i {
        font-size: 1.125rem; /* 18px */
    }
</style>

<header>
    <div onclick="window.location.href='landing.php'">Digi Docu</div>
    <div class="user">
        <i class="fas fa-user-circle"></i>
        <span><?php echo htmlspecialchars($user['user_name'] ?? 'User'); ?></span>
    </div>
</header>
