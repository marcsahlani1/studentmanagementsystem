<?php 
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}
require_once 'config/db.php';
$error = '';
$message = '';
$user_id = $_SESSION['user_id'];
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $current_pass = $_POST['current_pass'] ?? '';
    $new_pass = $_POST['new_pass'] ?? '';
    $confirm_pass = $_POST['confirm_pass'] ?? '';
    
    if(empty($current_pass) || empty($new_pass) ||empty($confirm_pass)){
        $error = "Please fill all fields!";
    }elseif($new_pass !== $confirm_pass){
        $error = "Confirmation doesn't match!";
    }elseif(strlen($new_pass < 6)){
        $error = "Password length should be at least 6 characters!";
    }else{
        $stmt = $pdo->prepare("SELECT pass FROM USERS WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        if($user && password_verify($current_pass, $user['pass'])){
            $new_hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare("UPDATE USERS SET pass = ? WHERE user_id = ?");
            if($update_stmt->execute([$new_hashed_pass,$user_id])){
                $message = "Password changed successfully!";
            }else{
                $error = "An error occurred while updating the password, try again.";
            }
        }else{
            $error = "Incorrect current password!";
        }
    }
}
?>
<!DOCTYPE hmtl>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="css/styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="container">
        <h2>Change Password</h2>
        <?php if(!empty($error)): ?>
            <div><?php echo htmlspecialchars($error);?></div>
        <?php endif;?>
        <?php if(!empty($message)): ?>
            <div><?php echo htmlspecialchars($message);?></div>
        <?php endif;?>
        <form action="change_password.php" method="POST">
            <div class="form-grp">
                <label for="current_pass">Current password:</label>
                <input type="password" id="current_pass" name="current_pass" required>
            </div>
            <div class="form-grp">
                <label for="new_pass">New password:</label>
                <input type="password" id="new_pass" name="new_pass" required>
            </div>
            <div class="form-grp">
                <label for="confirm_pass">Current password:</label>
                <input type="password" id="confirm_pass" name="confirm_pass" required>
            </div>
            <button type="submit" class="btn">Change Password</button>
        </form>
    </div>
    <p><a href="dashboard.php">Back to dashboard</a></p>
</body>
</html>

