<?php
session_start();
include('include/db.php');

// Restrict access to Admins only
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Admin') {
    header('Location: landing.php');
    exit();
}

// Fetch document counts
$totalFiles = 0;
$pendingFiles = 0;
$approvedFiles = 0;
$declinedFiles = 0;
$documents = [];

// Query to count total documents
$sql = "SELECT 
            COUNT(*) AS total, 
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
            SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS approved,
            SUM(CASE WHEN status = 'Declined' THEN 1 ELSE 0 END) AS declined
        FROM document";
        
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $totalFiles = $row['total'];
    $pendingFiles = $row['pending'];
    $approvedFiles = $row['approved'];
    $declinedFiles = $row['declined'];
}

$sql = "SELECT document.doc_id, document.doc_type, document.uploaded_at, document.status, student.name
        FROM document
        INNER JOIN student ON document.stud_id = student.stud_id";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $documents[] = $row;
    }
}
?>


<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Docu Dashboard</title>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
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
              <th>File ID</th>
              <th>Student Name</th>
              <th>File Type</th>
              <th>Date Submitted</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($documents as $doc): ?>
            <tr onclick="window.location.href='document-detail.php?doc_id=<?php echo $doc['doc_id']; ?>'">
              <td><?php echo htmlspecialchars($doc['doc_id']); ?></td>
              <td><?php echo htmlspecialchars($doc['name']); ?></td>
              <td><?php echo htmlspecialchars($doc['doc_type']); ?></td>
              <td><?= date('m/d/Y h:i A', strtotime($doc['uploaded_at'])) ?></td>
              <td class="<?php 
                  echo ($doc['status'] == 'Pending') ? 'status-pending' : 
                      (($doc['status'] == 'Approved') ? 'status-approved' : 
                      (($doc['status'] == 'Declined') ? 'status-declined' : 'status-inreview')); 
              ?>">
                <?php echo htmlspecialchars($doc['status']); ?>
              </td>
              <td>
                <button class="action-button" onclick="event.stopPropagation(); window.location.href='document-detail.php?doc_id=<?php echo $doc['doc_id']; ?>'">Review</button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </section>
    </main>
  </div>
</body>
</html>