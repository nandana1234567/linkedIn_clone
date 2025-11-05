<?php
session_start();
include 'config.php';

// 🕒 Friendly "time ago" function
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;

    if ($difference < 60) return "Just now";
    elseif ($difference < 3600) return floor($difference / 60) . " minute(s) ago";
    elseif ($difference < 86400) return floor($difference / 3600) . " hour(s) ago";
    elseif ($difference < 604800) return floor($difference / 86400) . " day(s) ago";
    elseif ($difference < 2592000) return floor($difference / 604800) . " week(s) ago";
    elseif ($difference < 31536000) return floor($difference / 2592000) . " month(s) ago";
    else return floor($difference / 31536000) . " year(s) ago";
}

// 🔒 Redirect if not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// ✅ Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

if (!$userData) {
    die("❌ User not found.");
}

// ✅ Fetch user’s posts
$postQuery = $conn->prepare("
    SELECT posts.*, users.name, users.profile_image 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    WHERE posts.user_id = ? 
    ORDER BY posts.created_at DESC
");
$postQuery->bind_param("i", $user_id);
$postQuery->execute();
$posts = $postQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($user_name); ?> | Profile</title>
<link rel="stylesheet" href="styles.css">
<script>
function scrollToComments(postId) {
    const box = document.getElementById('comment-box-' + postId);
    if (box) {
        box.scrollIntoView({ behavior: 'smooth', block: 'start' });
        box.querySelector('input[name="comment_content"]').focus();
    }
}
</script>
</head>
<body style="font-family:'Segoe UI',sans-serif;background:#f3f2ef;margin:0;">

<!-- 🌟 Navbar -->
<div style="background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.1);display:flex;justify-content:space-between;align-items:center;padding:12px 50px;">
  <div style="font-weight:bold;font-size:22px;color:#0a66c2;">LinkedIn Clone</div>
  <div>
    <a href="home.php" style="margin-right:15px;text-decoration:none;color:#444;">Home</a>
    <a href="profile.php" style="margin-right:15px;text-decoration:none;color:#0a66c2;font-weight:600;">Profile</a>
    <a href="logout.php" style="text-decoration:none;color:#444;">Logout</a>
  </div>
</div>

<!-- 🧍 Profile Section -->
<div style="width:700px;max-width:95%;margin:30px auto;">
  <div style="background:linear-gradient(to right,#0073b1,#004182);color:#fff;padding:25px;border-radius:15px;text-align:center;">
    <img src="profile_pic/<?php echo htmlspecialchars($userData['profile_image'] ?? 'default.png'); ?>" 
         alt="Profile Picture" style="width:130px;height:130px;border-radius:50%;border:4px solid #fff;object-fit:cover;">
    <h2 style="margin:10px 0;"><?php echo htmlspecialchars($userData['name']); ?></h2>
    <p style="font-size:15px;"><?php echo htmlspecialchars($userData['bio'] ?? 'No bio added yet.'); ?></p>

    <a href="edit_profile.php" 
       style="display:inline-block;margin-top:10px;background:#fff;color:#0a66c2;
              padding:8px 20px;border-radius:25px;text-decoration:none;font-weight:bold;">
       Edit Profile
    </a>
  </div>

  <h3 style="color:#0a66c2;margin-top:25px;">Your Posts</h3>

  <?php if ($posts->num_rows > 0): ?>
      <?php while ($row = $posts->fetch_assoc()): ?>
          <?php
          // ✅ Like check
          $likeCheck = $conn->prepare("SELECT 1 FROM likes WHERE user_id = ? AND post_id = ?");
          $likeCheck->bind_param("ii", $user_id, $row['id']);
          $likeCheck->execute();
          $isLiked = $likeCheck->get_result()->num_rows > 0;

          // ✅ Comments fetch
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

          <div style="background:#fff;border-radius:12px;padding:20px;margin-top:15px;
                      box-shadow:0 3px 10px rgba(0,0,0,0.1);">

              <!-- 🧾 Post Header -->
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                  <img src="profile_pic/<?php echo htmlspecialchars($row['profile_image'] ?? 'default.png'); ?>" 
                       alt="User" style="width:45px;height:45px;border-radius:50%;object-fit:cover;border:2px solid #eee;">
                  <div>
                      <strong style="color:#0a66c2;"><?php echo htmlspecialchars($row['name']); ?></strong><br>
                      <span style="font-size:12px;color:#777;"><?php echo timeAgo($row['created_at']); ?></span>
                  </div>
              </div>

              <!-- 📝 Post Content -->
              <p style="font-size:15px;color:#333;"><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>

              <?php if (!empty($row['image'])): ?>
                  <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" 
                       style="width:100%;border-radius:10px;margin-top:10px;" alt="Post Image">
              <?php endif; ?>

              <!-- ❤️ Actions -->
              <div style="margin-top:10px;display:flex;gap:15px;">
                  <a href="posts/like.php?id=<?php echo $row['id']; ?>" 
                     style="text-decoration:none;
                            color:<?php echo $isLiked ? '#0a66c2' : '#444'; ?>;
                            font-weight:<?php echo $isLiked ? 'bold' : '500'; ?>;">
                      <?php echo $isLiked ? '👍 Liked' : '👍 Like'; ?>
                  </a>

                  <a href="javascript:void(0);" onclick="scrollToComments(<?php echo $row['id']; ?>)"
                     style="text-decoration:none;color:#0a66c2;font-weight:500;">💬 Comment</a>

                  <a href="posts/edit.php?id=<?php echo $row['id']; ?>" 
                     style="text-decoration:none;color:#0a66c2;font-weight:500;">✏️ Edit</a>
                  
                  <a href="posts/delete.php?id=<?php echo $row['id']; ?>" 
                     onclick="return confirm('Delete this post?');"
                     style="text-decoration:none;color:red;font-weight:500;">🗑️ Delete</a>
              </div>

              <!-- 💭 Comment Section -->
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
                                  <span style="font-size:12px;color:#777;"><?php echo timeAgo($comment['created_at']); ?></span>
                              </div>
                          </div>
                      <?php endwhile; ?>
                  <?php else: ?>
                      <p style="color:#999;font-size:14px;">No comments yet.</p>
                  <?php endif; ?>

                  <!-- ✍️ Add Comment -->
                  <form method="POST" action="home.php" style="margin-top:10px;display:flex;gap:8px;">
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
      <p style="text-align:center;color:#777;">No posts yet.</p>
  <?php endif; ?>
</div>

</body>
</html>
