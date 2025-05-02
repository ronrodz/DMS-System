<?php
session_start();
include('include/db.php');

// Restrict access to Admins only
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Dean') {
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

// Fetch students for search functionality
$students = [];
$studentQuery = $conn->query("SELECT stud_id, name FROM student");
while ($row = $studentQuery->fetch_assoc()) {
    $students[] = $row;
}

?>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dean Dashboard</title>
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
            <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show w-100" role="alert" id="registerAlert"
                style="position: fixed; top: 0; left: 0; width: 100%; z-index: 1050;">
                <?= $successMessage ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php elseif (!empty($errorMessage)): ?>
            <div class="alert alert-danger alert-dismissible fade show w-100" role="alert" id="registerAlert"
                style="position: fixed; top: 0; left: 0; width: 100%; z-index: 1050;">
                <?= $errorMessage ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            <div class="d-flex">
                <div class="dean-left-container">
                    <main class="content-area">
                        <section class="student-select">
                            <h2 class="text-center mb-4">Student Details</h2>
                            <form id="studentDetailsForm">
                                <div class="mb-3">
                                    <label for="studentInput" class="form-label">Select Student</label>
                                    <input type="text" class="form-control" id="studentInput" autocomplete="off"
                                        placeholder="Type to search...">
                                    <input type="hidden" name="stud_id" id="studIdHidden" required>
                                    <div id="studentDropdown" class="list-group position-absolute w-100 z-3"
                                        style="max-height: 200px; overflow-y: auto;"></div>
                                </div>
                            </form>
                        </section>

                        <section class="dean-status">
                            <label for="eligibilityStatus" class="form-label">Eligibility Status</label>
                                    <input type="text" class="form-control" id="eligibilityStatus" disabled>
                        </section>
                        <section class="dean-requirements">
                            <label for="requirements" class="form-label">Requirements</label>
                                    <textarea class="form-control" id="requirements" rows="4" disabled></textarea>
                        </section>
                    </main>
                </div>

                <div class="dean-right-container">
                    <main class="content-area">
                    <section class="dean-document-list">
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
        <tbody id="documentTableBody">
            <!-- JavaScript will populate this -->
        </tbody>
    </table>
</section>
                    </main>
                </div>
            </div>
        </div>
    </div>

    <script>
document.addEventListener("DOMContentLoaded", function () {
    const studentInput = document.getElementById("studentInput");
    const studIdHidden = document.getElementById("studIdHidden");
    const studentDropdown = document.getElementById("studentDropdown");
    const eligibilityStatus = document.getElementById("eligibilityStatus");
    const requirements = document.getElementById("requirements");
    const documentTableBody = document.getElementById("documentTableBody");

    const students = <?php echo json_encode($students); ?>;

    studentInput.addEventListener("input", function () {
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
                studIdHidden.value = s.stud_id;
                studentDropdown.innerHTML = "";
                fetchStudentDetails(s.stud_id);
            });
            studentDropdown.appendChild(item);
        });
    });

    function fetchStudentDetails(studId) {
        fetch(`include/get_student_details.php?stud_id=${studId}`)
            .then(response => response.json())
            .then(data => {
                eligibilityStatus.value = data.eligibility_status || "N/A";
                requirements.value = data.requirements || "N/A";

                // Update document table
                documentTableBody.innerHTML = "";
                data.documents.forEach(doc => {
                    const tr = document.createElement("tr");
                    const statusClass =
                        doc.status === 'Pending' ? 'text-warning' :
                        doc.status === 'Approved' ? 'text-success' :
                        doc.status === 'Declined' ? 'text-danger' :
                        'text-muted';

                    tr.innerHTML = `
                        <td>${doc.doc_id}</td>
                        <td>${doc.name}</td>
                        <td>${doc.doc_type}</td>
                        <td>${new Date(doc.uploaded_at).toLocaleString()}</td>
                        <td class="${statusClass}">${doc.status}</td>
                        <td><button type="button" class="btn btn-warning">Review</button></td>
                    `;
                    documentTableBody.appendChild(tr);
                });
            })
            .catch(err => console.error("Fetch error:", err));
    }

    document.addEventListener("click", function (e) {
        if (!studentInput.contains(e.target) && !studentDropdown.contains(e.target)) {
            studentDropdown.innerHTML = "";
        }
    });
});
</script>

</body>

</html>
