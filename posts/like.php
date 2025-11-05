<?php
include '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = intval($_GET['id']);

// ✅ Toggle Like
$check = $conn->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");
$check->bind_param("ii", $user_id, $post_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    $conn->query("DELETE FROM likes WHERE user_id = $user_id AND post_id = $post_id");
    $conn->query("UPDATE posts SET likes = likes - 1 WHERE id = $post_id AND likes > 0");
} else {
    $conn->query("INSERT INTO likes (user_id, post_id) VALUES ($user_id, $post_id)");
    $conn->query("UPDATE posts SET likes = likes + 1 WHERE id = $post_id");
}

header("Location: ../home.php");
exit;
?>
