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

<header>
    <div onclick="window.location.href='landing.php'">Digi Docu</div>
    <div class="user">
        <i class="fas fa-user-circle"></i>
        <span><?php echo htmlspecialchars($user['user_name'] ?? 'User'); ?></span>
    </div>
</header>
