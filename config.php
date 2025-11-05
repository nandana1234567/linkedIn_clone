<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "linkedin_clone";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}
?>
