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

$limit = 10; // rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Count total documents
$total_result = $conn->query("SELECT COUNT(*) AS total FROM document");
$total_docs = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_docs / $limit);

// Fetch documents for current page
$sql = "SELECT document.doc_id, document.doc_type, document.uploaded_at, document.status, student.name
        FROM document
        INNER JOIN student ON document.stud_id = student.stud_id
        LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $start, $limit);
$stmt->execute();
$result = $stmt->get_result();
$documents = [];
while ($row = $result->fetch_assoc()) {
    $documents[] = $row;
}

// For displaying error messages from the server-side validation
$error_message = '';
if (isset($_GET['error']) && $_GET['error'] == 'student_not_found') {
    $error_message = 'Error: The selected student does not exist.';
}

?>

<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>FEU Roosevelt Dean's List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="css/global.css">
</head>

<body>

    <?php include('include/header.php'); ?>
    <div class="layout-wrapper">
        <?php include('include/sidebar.php'); ?>
        <div class="main-content flex-grow-1">
            <?php if (isset($_GET['upload']) && $_GET['upload'] === 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show w-100" role="alert" id="uploadAlert"
                style="position: fixed; top: 0; left: 0; width: 100%; z-index: 1050;">
                Document uploaded successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="d-flex">
                <div class="admin-left-container">
                    <main class="content-area">
                        <section class="student-select">
                            <h2 class="text-center mb-4">Student Information</h2>
                            <input type="text" id="studentSearch" class="form-control mb-3"
                                placeholder="Search by ID, Name, or Email">
                        </section>
                        <section class="quick-upload">
                            <h2 class="text-center mb-4">Quick Upload</h2>
                            <form action="include/upload_document.php" method="POST" enctype="multipart/form-data">
                                <div class="mb-3 position-relative">
                                    <label for="studentInput" class="form-label">Select Student</label>
                                    <input type="text" class="form-control" id="studentInput"
                                        placeholder="Type to search..." autocomplete="off">
                                    <input type="hidden" name="stud_id" id="studIdHidden" required>
                                    <div id="studentDropdown" class="list-group position-absolute w-100 z-3"
                                        style="max-height: 200px; overflow-y: auto;"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="docName" class="form-label">Document Name</label>
                                    <input type="text" class="form-control" id="docName" name="file_name" required>
                                </div>

                                <div class="mb-3">
                                    <label for="docType" class="form-label">Document Type</label>
                                    <select class="form-select" id="docType" name="doc_type" required>
                                        <option value="Grades">Grades</option>
                                        <option value="Certificate">Certificate</option>
                                        <option value="ID">ID</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="docDesc" class="form-label">Description</label>
                                    <input type="text" class="form-control" id="docDesc" name="doc_desc" required>
                                </div>

                                <div class="mb-3">
                                    <label for="fileUpload" class="form-label">Upload File</label>
                                    <input class="form-control" type="file" id="fileUpload" name="document_file"
                                        required>
                                </div>

                                <!-- Hidden Fields -->
                                <input type="hidden" name="status" value="Pending">
                                <input type="hidden" name="created_by"
                                    value="<?php echo htmlspecialchars($_SESSION['email']); ?>">

                                <button type="submit" class="btn btn-primary">Upload Document</button>
                            </form>

                        </section>
                    </main>
                </div>

                <div class="admin-right-container">
                    <main class="content-area">
                        <section class="student-select">
                            <h2 class="text-center mb-4">Available Documents</h2>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">File ID</th>
                                        <th scope="col">Student Name</th>
                                        <th scope="col">File Type</th>
                                        <th scope="col">Date Submitted</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documents as $doc): ?>
                                    <tr
                                        onclick="window.location.href='document-detail.php?doc_id=<?php echo $doc['doc_id']; ?>'">
                                        <td><?php echo htmlspecialchars($doc['doc_id']); ?></td>
                                        <td><?php echo htmlspecialchars($doc['name']); ?></td>
                                        <td><?php echo htmlspecialchars($doc['doc_type']); ?></td>
                                        <td><?= date('m/d/Y h:i A', strtotime($doc['uploaded_at'])) ?></td>
                                        <td class="<?php 
            // Apply Bootstrap color classes based on the status
            if ($doc['status'] == 'Pending') {
                echo 'text-warning'; 
            } elseif ($doc['status'] == 'Approved') {
                echo 'text-success'; 
            } elseif ($doc['status'] == 'Declined') {
                echo 'text-danger'; 
            } elseif ($doc['status'] == 'In Review') {
                echo 'text-warning'; 
            } else {
                echo 'text-muted'; 
            }
        ?>">
                                            <?php echo htmlspecialchars($doc['status']); ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-warning"
                                                onclick="event.stopPropagation(); window.location.href='document-detail.php?doc_id=<?php echo $doc['doc_id']; ?>'">Review</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-center mt-3">
                                <nav aria-label="Page navigation example">
                                    <ul class="pagination">
                                        <?php if ($page > 1): ?>
                                        <li class="page-item"><a class="page-link"
                                                href="?page=<?php echo $page - 1; ?>">&laquo;
                                                Prev</a></li>
                                        <?php endif; ?>

                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                        <?php endfor; ?>

                                        <?php if ($page < $total_pages): ?>
                                        <li class="page-item"><a class="page-link"
                                                href="?page=<?php echo $page + 1; ?>">Next
                                                &raquo;</a></li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        </section>
                    </main>
                </div>
            </div>

            <script>
            document.getElementById("studentSearch").addEventListener("keyup", function() {
                let input = this.value.toLowerCase();
                let rows = document.querySelectorAll("table tbody tr");

                rows.forEach(row => {
                    const text = row.innerText.toLowerCase();
                    row.style.display = text.includes(input) ? "" : "none";
                });
            });

            document.addEventListener("DOMContentLoaded", function() {
                const form = document.querySelector("form");
                const studentInput = document.getElementById("studentInput");
                const studIdHidden = document.getElementById("studIdHidden");
                const studentDropdown = document.getElementById("studentDropdown");

                form.addEventListener("submit", function(event) {
                    // Check if student is selected
                    if (studIdHidden.value === "") {
                        event.preventDefault(); // Prevent form submission
                        alert("Please select a valid student.");
                    }
                });

                // Existing code for student search functionality...
                const students = [
                    <?php
        $studentQuery = $conn->query("SELECT stud_id, name FROM student");
        while ($s = $studentQuery->fetch_assoc()) {
            echo "{ id: {$s['stud_id']}, name: '".addslashes($s['name'])."' },"; 
        }
        ?>
                ];

                studentInput.addEventListener("input", function() {
                    const value = this.value.toLowerCase();
                    studentDropdown.innerHTML = "";
                    if (value.length === 0) return;

                    const matches = students.filter(s => s.name.toLowerCase().includes(value));
                    matches.forEach(s => {
                        const item = document.createElement("button");
                        item.type = "button";
                        item.className = "list-group-item list-group-item-action";
                        item.textContent = s.name;
                        item.addEventListener("click", () => {
                            studentInput.value = s.name;
                            studIdHidden.value = s.id;
                            studentDropdown.innerHTML = "";
                        });
                        studentDropdown.appendChild(item);
                    });
                });

                document.addEventListener("click", function(e) {
                    if (!studentInput.contains(e.target) && !studentDropdown.contains(e.target)) {
                        studentDropdown.innerHTML = "";
                    }
                });
            });

            document.addEventListener("DOMContentLoaded", function() {
                // Check if the alert exists
                const alertBox = document.getElementById('uploadAlert');
                if (alertBox) {
                    // Remove the upload=success parameter from the URL
                    history.replaceState(null, '', window.location.pathname);

                    // Automatically dismiss the alert after 3 seconds
                    setTimeout(function() {
                        alertBox.classList.remove('show');
                        alertBox.classList.add('fade');
                    }, 3000); // 3 seconds
                }
            });
            </script>

</body>

</html>