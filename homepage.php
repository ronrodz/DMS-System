<?php
session_start();
include('include/db.php');

// Restrict access to Students only
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Student') {
    header('Location: landing.php');
    exit();
}

// Fetch logged-in user's details
$email = $_SESSION['email'] ?? '';

// Ensure the email is set and valid
if (!empty($email)) {
    // Step 1: Get stud_id from the user table based on email
    $sql = "SELECT stud_id FROM user WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // Step 2: Fetch the stud_id from the user table
    $stud_id = $user['stud_id'] ?? null;

    // If no stud_id is found, redirect to login
    if (!$stud_id) {
        header('Location: login.php');
        exit();
    }
}

// Initialize variables for counting files
$totalFiles = 0;
$pendingFiles = 0;
$approvedFiles = 0;
$declinedFiles = 0;
$documents = [];

// Step 3: Query to fetch the documents for the logged-in student using stud_id
$sql = "SELECT * FROM document WHERE stud_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $stud_id);
$stmt->execute();
$result = $stmt->get_result();

// Store the documents for the table display
while ($doc = $result->fetch_assoc()) {
    $documents[] = $doc;

    // Count total files
    $totalFiles++;

    // Count files by status
    if ($doc['status'] == 'Pending') {
        $pendingFiles++;
    } elseif ($doc['status'] == 'Approved') {
        $approvedFiles++;
    } elseif ($doc['status'] == 'Declined') {
        $declinedFiles++;
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
<?php include('include/header.php'); ?>
<div class="container">
<?php include('include/sidebar.php'); ?>
<main>
    <section class="stats-grid">
        <div class="stat-card">
          <p>Total Student Files</p>
          <p><?php echo $totalFiles; ?></p>
        </div>
        <div class="stat-card">
          <p>Pending Verifications</p>
          <p class="stat-yellow"><?php echo $pendingFiles; ?></p>
        </div>
        <div class="stat-card">
          <p>Approved Files</p>
          <p class="stat-green"><?php echo $approvedFiles; ?></p>
        </div>
        <div class="stat-card">
          <p>Declined Files</p>
          <p class="stat-red"><?php echo $declinedFiles; ?></p>
        </div>
    </section>

    <section class="table-container">
        <h2>Student Files</h2>
        <table>
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>File Description</th>
                    <th>Date Submitted</th>
                    <th>Status</th>
                    <!-- <th>Actions</th> -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $doc): ?>
                <tr>
                    <td><?php echo htmlspecialchars($doc['file_name']); ?></td>
                    <td><?php echo htmlspecialchars($doc['doc_desc']); ?></td>
                    <td><?= date('m/d/Y h:i A', strtotime($doc['uploaded_at'])) ?></td>
                    <td class="<?php 
                        echo ($doc['status'] == 'Pending') ? 'status-pending' : 
                            (($doc['status'] == 'Approved') ? 'status-approved' : 
                            (($doc['status'] == 'Declined') ? 'status-declined' : 'status-inreview')); 
                    ?>">
                        <?php echo htmlspecialchars($doc['status']); ?>
                    </td>
                    <!-- <td>
                      <button class="action-button" onclick="event.stopPropagation(); window.location.href='view_document.php?doc_id=<?php echo $doc['doc_id']; ?>'">View</button>
                    </td> -->
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
    
</main>
</div>
</body>
</html>
