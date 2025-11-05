<?php
session_start();
include 'config.php';

// 🕒 Friendly time ago function
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;

    if ($difference < 60) {
        return "Just now";
    } elseif ($difference < 3600) {
        $minutes = floor($difference / 60);
        return "$minutes minute" . ($minutes > 1 ? "s" : "") . " ago";
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return "$hours hour" . ($hours > 1 ? "s" : "") . " ago";
    } elseif ($difference < 604800) {
        $days = floor($difference / 86400);
        return "$days day" . ($days > 1 ? "s" : "") . " ago";
    } elseif ($difference < 2592000) {
        $weeks = floor($difference / 604800);
        return "$weeks week" . ($weeks > 1 ? "s" : "") . " ago";
    } elseif ($difference < 31536000) {
        $months = floor($difference / 2592000);
        return "$months month" . ($months > 1 ? "s" : "") . " ago";
    } else {
        $years = floor($difference / 31536000);
        return "$years year" . ($years > 1 ? "s" : "") . " ago";
    }
}

// ✅ Ensure user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$profile_image = $_SESSION['profile_image'] ?? 'default.png';

// ✅ Handle new post submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['content'])) {
    $content = trim($_POST['content']);
    $image = "";

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
    header("Location: home.php");
    exit();
}

// ✅ Handle new comment submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['comment_content'])) {
    $comment = trim($_POST['comment_content']);
    $post_id = (int)$_POST['post_id'];
    if (!empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $post_id, $user_id, $comment);
        $stmt->execute();
    }
    header("Location: home.php#comment-box-$post_id");
    exit();
}

// ✅ Fetch all posts with user info
$query = "
    SELECT posts.*, users.name, users.profile_image 
    FROM posts
    JOIN users ON posts.user_id = users.id
    ORDER BY posts.created_at DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LinkedIn Clone | Home</title>
<link rel="stylesheet" href="styles.css">
<style>
.time-ago {
  color: #888;
  font-style: italic;
  font-size: 12px;
  margin-left: 4px;
}
</style>
<script>
// Smooth scroll to comment box
function scrollToComments(postId) {
    const box = document.getElementById('comment-box-' + postId);
    if (box) {
        box.scrollIntoView({ behavior: 'smooth', block: 'start' });
        box.querySelector('input[name="comment_content"]').focus();
    }
}
</script>
</head>
<body style="font-family:'Segoe UI',sans-serif;background:#f3f2ef;margin:0;padding:0;">

<!-- 🌟 NAVBAR -->
<div style="background:#fff;display:flex;justify-content:space-between;align-items:center;
            padding:12px 50px;box-shadow:0 2px 8px rgba(0,0,0,0.1);position:sticky;top:0;z-index:100;">
  <div style="font-size:22px;font-weight:bold;color:#0a66c2;">LinkedIn Clone</div>
  <div>
    <a href="home.php" style="color:#0a66c2;text-decoration:none;margin:0 15px;font-weight:600;">Home</a>
    <a href="profile.php" style="color:#444;text-decoration:none;margin:0 15px;">Profile</a>
    <a href="logout.php" style="color:#444;text-decoration:none;margin:0 15px;">Logout</a>
  </div>
  <div style="display:flex;align-items:center;gap:10px;">
    <img src="profile_pic/<?php echo htmlspecialchars($profile_image); ?>" 
         style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #0a66c2;">
    <span style="font-weight:500;"><?php echo htmlspecialchars($user_name); ?></span>
  </div>
</div>

<!-- 🧱 MAIN FEED -->
<div style="width:700px;max-width:95%;margin:30px auto;">

    <!-- ✏️ CREATE POST BOX -->
    <div style="background:#fff;border-radius:12px;padding:20px;
                box-shadow:0 3px 10px rgba(0,0,0,0.1);margin-bottom:25px;">
        <form method="POST" enctype="multipart/form-data">
            <textarea name="content" placeholder="Start a post..." required
                style="width:100%;height:80px;padding:10px;border:1px solid #ccc;
                       border-radius:8px;resize:none;font-size:15px;font-family:inherit;"></textarea>
            <input type="file" name="image" accept="image/*" style="margin-top:10px;">
            <button type="submit"
                style="margin-top:12px;background:#0a66c2;color:#fff;border:none;
                       padding:10px 25px;border-radius:25px;font-weight:bold;cursor:pointer;">
                Post
            </button>
        </form>
    </div>

    <!-- 📢 FEED POSTS -->
    <h3 style="color:#0a66c2;margin-bottom:25px;text-align:center;">Recent Posts</h3>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
            // Check if user liked this post
            $likeCheck = $conn->prepare("SELECT 1 FROM likes WHERE user_id = ? AND post_id = ?");
            $likeCheck->bind_param("ii", $user_id, $row['id']);
            $likeCheck->execute();
            $isLiked = $likeCheck->get_result()->num_rows > 0;

            // Fetch comments
            $commentsQuery = $conn->prepare("
                SELECT comments.comment, comments.created_at, users.name, users.profile_image
                FROM comments
                JOIN users ON comments.user_id = users.id
                WHERE comments.post_id = ?
                ORDER BY comments.created_at ASC
            ");
            $commentsQuery->bind_param("i", $row['id']);
            $commentsQuery->execute();
            $commentsResult = $commentsQuery->get_result();
            ?>

            <div style="background:#fff;border-radius:12px;padding:20px;
                        box-shadow:0 4px 10px rgba(0,0,0,0.08);margin-bottom:25px;">

                <!-- 🧑‍💼 POST HEADER -->
                <div style="display:flex;align-items:center;gap:12px;">
                    <img src="profile_pic/<?php echo htmlspecialchars($row['profile_image'] ?? 'default.png'); ?>" 
                         alt="User" style="width:55px;height:55px;border-radius:50%;object-fit:cover;border:2px solid #e0e0e0;">
                    <div>
                        <strong style="color:#0a66c2;font-size:16px;"><?php echo htmlspecialchars($row['name']); ?></strong><br>
                        <span class="time-ago"><?php echo htmlspecialchars(timeAgo($row['created_at'])); ?></span>
                    </div>
                </div>

                <!-- 💬 POST CONTENT -->
                <p style="margin-top:15px;font-size:15px;color:#333;line-height:1.6;">
                    <?php echo nl2br(htmlspecialchars($row['content'])); ?>
                </p>

                <?php if (!empty($row['image'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" 
                         alt="Post Image" style="width:100%;border-radius:10px;margin-top:10px;">
                <?php endif; ?>

                <!-- ❤️ POST ACTIONS -->
                <div style="margin-top:15px;display:flex;align-items:center;gap:20px;">
                    <a href="posts/like.php?id=<?php echo $row['id']; ?>" 
                       style="text-decoration:none;
                              color:<?php echo $isLiked ? '#0a66c2' : '#444'; ?>;
                              font-weight:<?php echo $isLiked ? 'bold' : '500'; ?>;
                              transition:0.3s;">
                        <?php echo $isLiked ? '👍 Liked' : '👍 Like'; ?>
                    </a>

                    <a href="javascript:void(0);" onclick="scrollToComments(<?php echo $row['id']; ?>)"
                       style="text-decoration:none;color:#0a66c2;font-weight:500;">💬 Comment</a>

                    <?php if ($row['user_id'] == $user_id): ?>
                        <a href="posts/edit.php?id=<?php echo $row['id']; ?>" 
                           style="text-decoration:none;color:#0a66c2;font-weight:500;">✏️ Edit</a>
                        <a href="posts/delete.php?id=<?php echo $row['id']; ?>" 
                           onclick="return confirm('Are you sure you want to delete this post?');"
                           style="text-decoration:none;color:red;font-weight:500;">🗑️ Delete</a>
                    <?php endif; ?>
                </div>

                <!-- 💭 COMMENT SECTION -->
                <div id="comment-box-<?php echo $row['id']; ?>" 
                     style="margin-top:15px;border-top:1px solid #ddd;padding-top:10px;">
                    <?php if ($commentsResult->num_rows > 0): ?>
                        <?php while ($comment = $commentsResult->fetch_assoc()): ?>
                            <div style="display:flex;align-items:flex-start;gap:10px;margin-top:10px;">
                                <img src="profile_pic/<?php echo htmlspecialchars($comment['profile_image'] ?? 'default.png'); ?>" 
                                     alt="User" style="width:35px;height:35px;border-radius:50%;object-fit:cover;">
                                <div>
                                    <strong style="color:#0a66c2;"><?php echo htmlspecialchars($comment['name']); ?></strong>
                                    <p style="margin:4px 0;color:#333;font-size:14px;"><?php echo htmlspecialchars($comment['comment']); ?></p>
                                    <span class="time-ago"><?php echo htmlspecialchars(timeAgo($comment['created_at'])); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color:#999;font-size:14px;">No comments yet.</p>
                    <?php endif; ?>

                    <!-- ✍️ ADD NEW COMMENT -->
                    <form method="POST" style="margin-top:10px;display:flex;gap:8px;">
                        <input type="hidden" name="post_id" value="<?php echo $row['id']; ?>">
                        <input type="text" name="comment_content" placeholder="Write a comment..."
                               style="flex:1;padding:8px 12px;border:1px solid #ccc;border-radius:25px;">
                        <button type="submit" style="background:#0a66c2;color:#fff;border:none;
                                padding:8px 15px;border-radius:25px;cursor:pointer;font-weight:bold;">
                            ➤
                        </button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center;color:#555;">No posts yet. Create one above!</p>
    <?php endif; ?>
</div>

</body>
</html>
