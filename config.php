<?php
$servername = "sql12.freesqldatabase.com"; // e.g., sql12.freesqldatabase.com
$username = "sql12806194";                  // as provided
$password = "FU6ABxbqg4";                   // no extra spaces
$dbname = "sql12806194";                    // no extra spaces
$port = 3306;                               // usually 3306

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
