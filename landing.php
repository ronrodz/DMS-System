<?php
session_start();
include('db.php');

// Retrieve messages from session and clear them
$invalidPassword = $_SESSION['invalidPassword'] ?? "";
$noUserFound = $_SESSION['noUserFound'] ?? "";
$lockMessage = $_SESSION['lockMessage'] ?? "";
unset($_SESSION['invalidPassword'], $_SESSION['noUserFound'], $_SESSION['lockMessage']);

$lockDuration = 30; // Lock duration in seconds
$maxAttempts = 3;

// Reset lockout if duration has expired
if (isset($_SESSION['lockout_time']) && time() >= $_SESSION['lockout_time']) {
    unset($_SESSION['lockout_time']);
    unset($_SESSION['lockout']);
    $_SESSION['failed_attempts'] = 0;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
        $_SESSION['lockMessage'] = "Too many failed login attempts. Please try again later.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $sql = "SELECT * FROM user WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Successful login
            unset($_SESSION['failed_attempts']);
            unset($_SESSION['lockout_time']);
            unset($_SESSION['lockout']);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];

            if ($user['role'] == 'Admin') {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: homepage.php');
            }
            exit();
        } else {
            $_SESSION['failed_attempts'] = ($_SESSION['failed_attempts'] ?? 0) + 1;

            if ($_SESSION['failed_attempts'] >= $maxAttempts) {
                $_SESSION['lockout_time'] = time() + $lockDuration;
                $_SESSION['lockMessage'] = "Too many failed login attempts. Please try again in $lockDuration seconds.";
            } else {
                $_SESSION['invalidPassword'] = "Invalid password. You have " . ($maxAttempts - $_SESSION['failed_attempts']) . " attempts left.";
            }
        }
    } else {
        $_SESSION['failed_attempts'] = ($_SESSION['failed_attempts'] ?? 0) + 1;

        if ($_SESSION['failed_attempts'] >= $maxAttempts) {
            $_SESSION['lockout_time'] = time() + $lockDuration;
            $_SESSION['lockMessage'] = "Too many failed login attempts. Please try again in $lockDuration seconds.";
        } else {
            $_SESSION['noUserFound'] = "No user found with that email. You have " . ($maxAttempts - $_SESSION['failed_attempts']) . " attempts left.";
        }
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="video-container">
        <video loop autoplay muted>
            <source src="assets/background.mp4" type="video/mp4">
        </video>
        <div class="overlay"></div>
    </div>

    <!-- Alerts -->
    <div class="container mt-5 pt-5">
        <?php if (!empty($invalidPassword)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($invalidPassword) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($noUserFound)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($noUserFound) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($lockMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($lockMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Login Form -->
    <div class="login-form">
        <div class="text-center mb-2">
            <h1>DMS Portal</h1>
            <?php if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']): ?>
                <div class="text-danger fw-bold" style="font-size: 0.95rem; margin-top: 5px;">
                    You are locked out. Try again in <span id="cooldownTimer">--</span> seconds.
                </div>
            <?php endif; ?>
        </div>
        <form method="POST" class="mx-auto" style="max-width: 400px;">
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" name="email" id="email" class="form-control"
                       placeholder="<?= isset($_SESSION['lockout_time']) ? 'Locked' : 'Enter your email'; ?>"
                       required <?= isset($_SESSION['lockout_time']) ? 'readonly title="You are locked out temporarily."' : ''; ?>>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control"
                       placeholder="<?= isset($_SESSION['lockout_time']) ? 'Locked' : 'Enter your password'; ?>"
                       required <?= isset($_SESSION['lockout_time']) ? 'readonly title="You are locked out temporarily."' : ''; ?>>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100"
                    <?= isset($_SESSION['lockout_time']) ? 'disabled' : ''; ?>>Login</button>
        </form>
    </div>

    <script>
        <?php if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']): ?>
        const lockoutEndTime = <?= $_SESSION['lockout_time'] ?> * 1000;

        function updateCooldownTimer() {
            const now = new Date().getTime();
            const distance = lockoutEndTime - now;

            if (distance > 0) {
                const seconds = Math.ceil(distance / 1000);
                document.getElementById("cooldownTimer").textContent = seconds;
            } else {
                location.reload(); // Reload page when lockout ends
            }
        }

        updateCooldownTimer(); // Initial call
        setInterval(updateCooldownTimer, 1000);
        <?php endif; ?>
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>
