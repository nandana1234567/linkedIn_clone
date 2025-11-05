<?php
include 'config.php';

$successMsg = $errorMsg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $bio = trim($_POST['bio']);
    $profile_image = 'default.png';

    // 📸 Handle profile picture upload
    if (!empty($_FILES['profile_pic']['name'])) {
        if (!is_dir("profile_pic")) mkdir("profile_pic", 0777, true);
        $imageName = time() . "_" . basename($_FILES['profile_pic']['name']);
        $targetPath = "profile_pic/" . $imageName;
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetPath)) {
            $profile_image = $imageName;
        }
    }

    // 🧾 Insert user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, bio, profile_image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $password, $bio, $profile_image);

    if ($stmt->execute()) {
        $successMsg = "✅ Registration successful! <a href='index.php' style='color:#fff;text-decoration:underline;'>Login now</a>";
    } else {
        $errorMsg = "❌ Error: " . htmlspecialchars($stmt->error);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign Up - LinkedIn Clone</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body style="font-family:'Segoe UI',sans-serif;background:#f3f2ef;">

<div class="form-container" style="
    background:#fff;
    width:400px;
    margin:80px auto;
    padding:30px;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
    text-align:center;">
    
    <h2 style="color:#0a66c2;margin-bottom:20px;">Create Account</h2>
    
    <?php if ($successMsg): ?>
        <div style="
            background:#0a66c2;
            color:#fff;
            padding:15px;
            border-radius:8px;
            font-weight:600;
            margin-bottom:20px;">
            <?php echo $successMsg; ?>
        </div>
    <?php elseif ($errorMsg): ?>
        <div style="
            background:#f8d7da;
            color:#842029;
            padding:15px;
            border-radius:8px;
            font-weight:600;
            margin-bottom:20px;">
            <?php echo $errorMsg; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" style="text-align:left;">
        <input type="text" name="name" placeholder="Full Name" required
               style="width:100%;padding:10px;margin-bottom:10px;border:1px solid #ccc;border-radius:6px;">
        <input type="email" name="email" placeholder="Email" required
               style="width:100%;padding:10px;margin-bottom:10px;border:1px solid #ccc;border-radius:6px;">
        <input type="password" name="password" placeholder="Password" required
               style="width:100%;padding:10px;margin-bottom:10px;border:1px solid #ccc;border-radius:6px;">
        <textarea name="bio" placeholder="Write something about you..."
                  style="width:100%;padding:10px;height:70px;margin-bottom:10px;border:1px solid #ccc;border-radius:6px;"></textarea>
        <input type="file" name="profile_pic" accept="image/*" style="margin-bottom:15px;">

        <button type="submit" style="
            width:100%;
            background:#0a66c2;
            color:#fff;
            border:none;
            padding:12px;
            border-radius:25px;
            font-weight:bold;
            cursor:pointer;">
            Register
        </button>
    </form>

    <p style="margin-top:15px;">Already have an account? 
        <a href="index.php" style="color:#0a66c2;font-weight:bold;">Login here</a>
    </p>
</div>

</body>
</html>
