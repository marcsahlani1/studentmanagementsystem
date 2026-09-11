<?php 
session_start();
require_once 'config/db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin'){
    header("Location: login.php");
    exit();
}
$error = '';
$success_msg = '';
//GET ID FROM URL (WE USE ?ID=... IN USERS.PHP ON THE EDIT BUTTON)
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: users.php");
    exit();
}
$user_id = $_GET['id'];
//FETCH USER DATA FROM DATABASE
$stmt = $pdo->prepare("SELECT * FROM USERS WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if(!$user){
    header("Location: users.php");
    exit();
}
//HANDLE FORM SUBMISSION FOR UPDATING
if($_SERVER['REQUEST_METHOD'] === "POST"){
    $username = trim($_POST['username']);
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $gender = trim($_POST['gender']);
    $phone = trim($_POST['phone']);
    $desc = trim($_POST['desc']);
    $address = trim($_POST['address']);
    $pass = trim($_POST['pass']);
    $dob = trim($_POST['dob']);
    if(empty($username) || empty($fname) || empty($lname) || empty($email) || empty($role) ||
       empty($gender) || empty($dob) || empty($phone) || empty($address)){
        $error = "Please fill all required fields!";
    }else{
        //CHECK USERNAME UNIQUENESS(EXCLUDE CURRENT USER WITH HIS ID)
        $stmt = $pdo->prepare("SELECT user_id FROM USERS WHERE username = ? AND user_id != ?");
        $stmt->execute([$username,$user_id]);
        if($stmt->rowCount() > 0){
            $error = "Username already taken!";
        }else{
            //CHECK EMAIL UNIQUENESS
            $stmt = $pdo->prepare("SELECT user_id FROM USERS WHERE email = ? AND user_id != ?");
            $stmt->execute([$email,$user_id]);
            if($stmt->rowCount() > 0){
                $error = "Email already taken!";
            }
        }
    }
    //PROFILE PICTURE VALIDATION
    $pfp = $user['pfp'];
    if(empty($error)  && isset($_FILES['pfp']) && $_FILES['pfp']['error'] === UPLOAD_ERR_OK){
        $filetmppath = $_FILES['pfp']['tmp_name'];
        $filename = $_FILES['pfp']['name'];
        $filesize = $_FILES['pfp']['size'];
        $fileext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowedext = ['jpg','jpeg','png','gif'];
        $maxsize = 2 * 1024 * 1024;
        if(!in_array($fileext,$allowedext)){
            $error = "Invalid file format! Only JPG,JPEG,PNG and GIF.";
        }elseif($filesize > $maxsize){
            $error = "File size too big! Maximum is 2MB";
        }else{
            //generate unique filename to avoid conflicts
            $newfilename = uniqid('profile_' , true) . '.' . $fileext;
            $uploadfiledir = 'uploads/';
            $destpath = $uploadfiledir . $newfilename;
            if(move_uploaded_file($filetmppath,$destpath)){
                //DELETE OLD PFP AS LONG AS ITS NOT THE DEFAULT
                if(!empty($user['pfp']) && file_exists($user['pfp'])){
                    @unlink($user['pfp']);
                }
                $pfp = $newfilename;
            }else{
                $error = "Error moving the uploaded file.";
            }
        }
    }
    //IF NO ERRORS, UPDATE DATABASE
    if(empty($error)){
        if(!empty($pass)){
            //UPDATE WITH NEW PASSWORD HASH
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
            $updatestmt = $pdo->prepare("UPDATE USERS SET username = ?, pass = ?,
                                        fname = ?, lname = ?, email = ?, user_role = ?, gender = ?, phone = ?, 
                                        user_desc = ?, user_address = ?, pfp = ? WHERE user_id = ?");
            $success = $updatestmt->execute([$username,$hashed_pass,$fname,$lname,$email,$role,$gender,$phone,
                                            $desc,$address,$pfp,$user_id]);
        }else{
            //UPDATE WITHOUT CHANGING PASS
            $updatestmt = $pdo->prepare("UPDATE USERS SET username = ?,
                                        fname = ?, lname = ?, email = ?, user_role = ?, gender = ?, phone = ?, 
                                        user_desc = ?, user_address = ?, pfp = ? WHERE user_id = ?");
            $success = $updatestmt->execute([$username,$fname,$lname,$email,$role,$gender,$phone,
                                            $desc,$address,$pfp,$user_id]);            
        }
        if($success){
            $success_msg = "User updated successfully!";
            //REFRESH USER DETAILS
            $stmt = $pdo->prepare("SELECT * FROM USERS WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        }else{
            $error = "Database update failed.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div>
        <h2>Edit User</h2>
        <p><a href="users.php">Back to Users</a></p>
        <?php if(!empty($error)): ?>
            <div><?php echo htmlspecialchars($error);?></div>
        <?php endif;?>
        <?php if(!empty($success_msg)): ?>
            <div><?php echo htmlspecialchars($success_msg);?></div>
        <?php endif;?>

        <form action="edit_user.php?id=<?php echo $user_id;?>" method="POST" enctype="multipart/form-data">
            <div>
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']);?>" required>
            </div>
            <div>
                <label>Password(leave blank to keep current password)</label>
                <input type="password" name="pass">
            </div>
            <div>
                <label>First Name</label>
                <input type="text" name="fname" required value="<?php echo htmlspecialchars($user['fname']);?>">
            </div>
            <div>
                <label>Last Name</label>
                <input type="text" name="lname" required value="<?php echo htmlspecialchars($user['lname']);?>">
            </div>
            <div>
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']);?>" required>
            </div>
            <div>
                <label>Role</label>
                <select name="role">
                    <option value="user" <?php if($user['user_role'] === 'user') echo "selected";?>>User</option>
                    <option value="admin" <?php if($user['user_role'] === 'admin') echo "selected";?>>Admin</option>
                </select>
            </div>
            <div>
                <label>Gender</label>
                <select name="gender">
                    <option value="male" <?php if($user['gender'] === 'male') echo "selected";?>>Male</option>
                    <option value="female" <?php if($user['gender'] === 'female') echo "selected";?>>Female</option>
                </select>
            </div>
            <div>
                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>
            <div>
                <label>Date Of Birth</label>
                <input type="date" name="dob" value="<?php echo htmlspecialchars($user['dob']);?>" required>
            </div>
            <div>
                <label>Description</label>
                <textarea name="desc"><?php echo htmlspecialchars($user['user_desc'] ?? '');?></textarea>
            </div>
            <div>
                <label>Address</label>
                <textarea name="address"><?php echo htmlspecialchars($user['user_address'] ?? '');?></textarea>
            </div>
            <div>
                <label>Profile Picture</label>
                <?php if(!empty($user['pfp'])): ?>
                    <br><img src="<?php echo htmlspecialchars('uploads/' . $user['pfp']);?>" alt="Profile Picture" width="80"><br>
                <?php endif;?>
                <input type="file" name="pfp">
            </div>
            <div>
                <button type="submit">Update User</button>
            </div>
        </form>
    </div>
</body>
</html>

