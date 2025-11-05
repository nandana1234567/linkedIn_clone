<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

// Update profile
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $bio = trim($_POST['bio']);
    $email = trim($_POST['email']);
    $profile_image = $userData['profile_image'];

    // Handle image upload
    if (!empty($_FILES['profile_image']['name'])) {
        $imageName = time() . '_' . basename($_FILES['profile_image']['name']);
        $targetDir = "profile_pic/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $targetPath = $targetDir . $imageName;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
            $profile_image = $imageName;
            $_SESSION['profile_image'] = $imageName;
        }
    }

    $update = $conn->prepare("UPDATE users SET name=?, email=?, bio=?, profile_image=? WHERE id=?");
    $update->bind_param("ssssi", $name, $email, $bio, $profile_image, $user_id);
    $update->execute();

    $_SESSION['user_name'] = $name;
    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Profile</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body style="font-family:'Segoe UI',sans-serif;background:#f3f2ef;margin:0;">

<div style="background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.1);display:flex;justify-content:space-between;align-items:center;padding:12px 50px;">
  <div style="font-weight:bold;font-size:22px;color:#0a66c2;">LinkedIn Clone</div>
  <a href="profile.php" style="text-decoration:none;color:#444;">← Back to Profile</a>
</div>

<div style="width:600px;max-width:95%;margin:40px auto;background:#fff;border-radius:15px;box-shadow:0 3px 12px rgba(0,0,0,0.1);padding:25px;">
  <h2 style="color:#0a66c2;text-align:center;">Edit Your Profile</h2>
  <form method="POST" enctype="multipart/form-data" style="margin-top:20px;">
    <label>Name:</label><br>
    <input type="text" name="name" value="<?php echo htmlspecialchars($userData['name']); ?>" style="width:100%;padding:10px;border-radius:8px;border:1px solid #ccc;margin-bottom:10px;"><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" style="width:100%;padding:10px;border-radius:8px;border:1px solid #ccc;margin-bottom:10px;"><br>

    <label>Bio:</label><br>
    <textarea name="bio" rows="3" style="width:100%;padding:10px;border-radius:8px;border:1px solid #ccc;"><?php echo htmlspecialchars($userData['bio']); ?></textarea><br>

    <label>Profile Picture:</label><br>
    <input type="file" name="profile_image" accept="image/*" style="margin-top:5px;"><br>
    <img src="profile_pic/<?php echo htmlspecialchars($userData['profile_image'] ?? 'default.png'); ?>" 
         style="width:100px;height:100px;border-radius:50%;margin-top:10px;object-fit:cover;">

    <div style="text-align:center;margin-top:20px;">
      <button type="submit" style="background:#0a66c2;color:#fff;padding:10px 25px;border:none;border-radius:25px;font-weight:bold;cursor:pointer;">Save Changes</button>
    </div>
  </form>
</div>

</body>
</html>
