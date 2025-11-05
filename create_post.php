<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$content = trim($_POST['content']);
$image = '';

if (!empty($_FILES['image']['name'])) {
    $imageName = time() . '_' . basename($_FILES['image']['name']);
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    $targetPath = $targetDir . $imageName;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $image = $imageName;
    }
}

$stmt = $conn->prepare("INSERT INTO posts (user_id, content, image, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iss", $user_id, $content, $image);
$stmt->execute();

header("Location: profile.php");
exit();
?>
