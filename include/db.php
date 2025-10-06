<?php
$servername = "localhost";
$username = "root";
$password = "9320718a";
$dbname = "dmsbackend";

// Infinityfree Credentials
// $servername = "sql212.infinityfree.com";
// $username = "if0_40075618";
// $password = "svJPN49FHGq0b";
// $dbname = "if0_40075618_dmsbackend";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>