<?php 
session_start();
require_once 'config/db.php';
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';
$stmt = $pdo->prepare("SELECT  * FROM USERS WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$new_picture_name = '';
$new_filename = '';
if($_SERVER["REQUEST_METHOD"] === "POST"){
    $fname = trim($_POST["fname"]);
    $lname = trim($_POST["lname"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["user_address"]);
    $desc = trim($_POST["desc"]);
    
    //Handle Profile Picture
    $new_picture_name = $user['pfp'];
    if(isset($_FILES['pfp']) && $_FILES['pfp']['error'] == UPLOAD_ERR_OK){
        $file_tmp_path = $_FILES['pfp']['tmp_name'];
        $file_name = $_FILES['pfp']['name'];
        $file_size = $_FILES['pfp']['size'];

        $allowed_ext= ['jpeg' , 'jpg' , 'png', 'gif'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $max_size = 2 * 1024 * 1024;

        if(!in_array($file_ext, $allowed_ext)){
            $error = "Invalid picture type. Only allowed JPEG, JPG, PNG and GIF.";
        }elseif($file_size > $max_size){
            $error = "Maximum file size is 2MB.";
        }else{
            $upload_dir = 'uploads/';
            $new_filename = uniqid('profile_',true) . '.' . $file_ext;
            $dest_path = $upload_dir . $new_filename;
            
            if(move_uploaded_file($file_tmp_path,$dest_path)){
                //delete old pfp
                if(!empty($user['pfp']) && file_exists($upload_dir . $user['pfp'])){
                    /*unlink($upload_dir . $user['pfp']);*/
                    @unlink($upload_dir . $user['pfp']);

                }
                $new_picture_name = $new_filename;
            }else{$error = "Error moving the file!";}
        }
    }
     if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "Enter a valid email!";
     }else{
        // CHECK EMAIL UNIQUENESS
        if($email !== $user['email']){
          $stmt_check = $pdo->prepare("SELECT user_id FROM USERS WHERE email = ? AND user_id = ?");
          $stmt_check->execute([$email,$user_id]);
          if($stmt_check->rowCount() > 0){
             $error = "This email is already in use";
          }
        }
     }
     
     
     //UPDATE THE DATABASE
     if(empty($error)){
        $update_stmt = $pdo->prepare("UPDATE USERS SET fname = ?, lname = ?, email = ?, phone = ?,
                     user_address = ?, user_desc = ?, pfp = ? WHERE user_id = ?");
        if($update_stmt->execute([$fname,$lname,$email,$phone,$address,$desc,$new_picture_name,$user_id])){
            $message = "Profile updated successfully! Refresh to see changes.";
        }else{$error = "Failed to update profile!";}
     }
     header("Location: profile.php");
     exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="css/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="container">
        <h2>My Profile</h2>
        <?php if(!empty($message)): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
         <?php if(!empty($error)): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <div> 
            <?php 
            if(file_exists('uploads/' . $new_picture_name)): ?>
                <img src="uploads/<?php echo htmlspecialchars($user['pfp']);?>?v=<?php echo time();?>" alt="Profile picture" width="300" height="300" onerror="this.onerror=null; this.src='images/default.jpeg';">
            <?php else: ?>
                <img src="images/default.jpeg" alt="Default Profile Picture">
            <?php endif; ?>
        </div>
        <form action="profile.php" method="POST" enctype="multipart/form-data">
            <div class="form-grp">
                <label for="fname">First Name:</label>
                <input type="text" id="fname" name="fname" value="<?php echo htmlspecialchars($user['fname']); ?>">
            </div>
            <div class="form-grp">
                <label for="lname">Last Name:</label>
                <input type="text" id="lname" name="lname" value="<?php echo htmlspecialchars($user['lname']); ?>">
            </div>
            <div class="form-grp">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
            </div>
            <div class="form-grp">
                <label for="phone">Phone Number:</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
            </div>
            <div class="form-grp">
                <label for="user_address">Address:</label>
                <input type="text" id="user_address" name="user_address" value="<?php echo htmlspecialchars($user['user_address']); ?>">
            </div>
            <div class="form-grp">
                <label for="desc">Description:</label>
                <textarea id="desc" name="desc" rows="4"> <?php echo htmlspecialchars($user['user_desc']); ?></textarea>
            </div>
            <div class="form-grp">
                <label for="fname">Profile Picture:</label>
                <input type="file" id="pfp" name="pfp" accept="image/png, image/jpg, image/jpeg, image/gif">
            </div>
            <button type="submit" class="btn">Update Profile</button>
        </form>
        <p><a href="dashboard.php">Back to Dashboard:</a></p>
    </div>
</body>
</html>