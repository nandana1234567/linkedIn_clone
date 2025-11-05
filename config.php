<?php
$servername = "sql12.freesqldatabase.com"; // e.g., sql12.freesqldatabase.com
$username = "sql12806194";                 // as provided
$password = "FU6ABxbqg4";                 // as set
$dbname = "sql12806194";                 // your db name
$port =3306;                        // usually 3306

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Use $pdo for database queries
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

