<?php
include '../config.php';
session_start();

if (!isset($_SESSION['user_name'])) {
    header("Location: ../index.php");
    exit;
}

$post_id = (int)$_GET['id'];
$user = $_SESSION['user_name'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $comment = trim($_POST['comment']);
    
   $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iis", $post_id, $_SESSION['user_id'], $comment);

    $stmt->bind_param("iss", $post_id, $user, $comment);
    $stmt->execute();

    header("Location: ../home.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>💬 Add a Comment</title>
</head>
<body style="font-family:'Segoe UI'; background:#f3f2ef; text-align:center; padding:40px;">
    <form method="POST" 
          style="background:#fff; padding:30px; border-radius:12px; 
                 display:inline-block; box-shadow:0 4px 10px rgba(0,0,0,0.1);">
        <h2 style="color:#0a66c2;">💬 Add a Comment</h2>
        <textarea name="comment" rows="5" cols="50" placeholder="Write your comment..." required
                  style="border-radius:10px; border:1px solid #ccc; padding:10px; font-size:15px; width:100%; resize:none;"></textarea><br>
        <button type="submit" 
                style="background:#0a66c2; color:white; border:none; 
                       padding:10px 25px; border-radius:25px; font-weight:bold; cursor:pointer;">
            Post Comment
        </button>
    </form>
</body>
</html>
