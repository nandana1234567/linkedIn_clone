<?php
include '../config.php';
session_start();

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = $_POST['content'];
    $update = $conn->prepare("UPDATE posts SET content = ? WHERE id = ?");
    $update->bind_param("si", $content, $id);
    $update->execute();
    header("Location: ../home.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Edit Post</title></head>
<body style="font-family:Segoe UI; background:#f3f2ef; text-align:center; padding:40px;">
    <form method="POST" style="background:#fff; padding:25px; border-radius:10px; display:inline-block;">
        <h2>✏️ Edit Post</h2>
        <textarea name="content" rows="5" cols="50" required
                  style="border-radius:10px; border:1px solid #ccc; padding:10px;"><?php echo htmlspecialchars($post['content']); ?></textarea><br>
        <button type="submit" style="background:#0a66c2; color:white; border:none; 
                                     padding:10px 20px; border-radius:25px; font-weight:bold; cursor:pointer;">
            Save Changes
        </button>
    </form>
</body>
</html>
