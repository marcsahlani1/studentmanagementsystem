<?php
session_start();
require_once 'config/db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin'){
    header("Location: login.php");
    exit();
}
$error = "";
$success_msg = "";


//ADD USERS
if($_SERVER["REQUEST_METHOD"] === "POST"){
    $username = trim($_POST['username'] ?? '');
    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname']  ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = trim($_POST['pass'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $desc = trim($_POST['desc'] ?? '');

    if(empty($username) || empty($fname) || empty($lname) || empty($email) || empty($pass) || empty($role) ||
       empty($gender) || empty($dob) || empty($phone) || empty($address)){
        $error = "Please fill all required fields!";
    }elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){
        $error = "Invalid email!";
    }else{
        //check if username or email already exists
        $checkusernamestmt = $pdo->prepare("SELECT user_id FROM USERS WHERE username = ?");
        $checkusernamestmt->execute([$username]);
        $checkemailstmt = $pdo->prepare("SELECT user_id FROM USERS WHERE email = ?");
        $checkemailstmt->execute([$email]);
        if($checkusernamestmt->rowCount() > 0){
            $error = "Username already taken!";
        }elseif($checkemailstmt->rowCount() > 0){
            $error = "Email already taken!";
        }else{
            //HASH THE PASSWORD
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

            //HANDLE PROFILE PICTURE
            $pfp = 'images/default.jpeg'; // DEFAULT IMAGE
            if(isset($_FILES['pfp']) && $_FILES['pfp']['error'] === UPLOAD_ERR_OK){
                $filetmppath = $_FILES['pfp']['tmp_name'];
                $filename = $_FILES['pfp']['name'];
                $filesize = $_FILES['pfp']['size'];
                $fileextension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                if(in_array($fileextension,$allowedExtensions)){
                    if($filesize <= 2 * 1024 * 1024){
                        $newfilename = md5(time() . $filename) . '.' . $fileextension;
                        $uploadfiledir = 'uploads/';

                        $dest_path = $uploadfiledir . $newfilename;
                        if(move_uploaded_file($filetmppath,$dest_path)){
                            $pfp = $newfilename;
                        }else{
                            $error = "Error moving the uploaded file!";
                        }
                    }else{
                        $error = "File size should not exceed 2MB !";
                    }
                }else{
                    $error = "Invalid file type. Only JPEG, JPG, PNG and GIF.";
                }
            }
        }//INSERT USING PDO
        if(empty($error)){
            $insertstmt = $pdo->prepare("INSERT INTO USERS 
                                       (username,fname,lname,dob,phone,user_address,user_desc,email,pfp,pass,user_role,gender)
                                         VALUES(?,?,?,?,?,?,?,?,?,?,?,?)");
            $success = $insertstmt->execute([$username,$fname,$lname,$dob,
                                    $phone,$address,$desc,$email,$pfp,$hashed_pass,$role,$gender]);
            if($success){
                $success_msg = "User added successfully!";
            }else{
                $error = "Could not add user!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div>
        <h2>Add New User</h2>
        <a href="users.php">Back to Users List</a>
    </div>
    <?php if(!empty($error)): ?>
        <div><?php echo htmlspecialchars($error);?></div>
    <?php endif;?>
    <?php if(!empty($success_msg)): ?>
        <div><?php echo htmlspecialchars($success_msg);?></div>
    <?php endif;?>
    <form action="add_users.php" method="POST" enctype="multipart/form-data">
        <div>
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div>
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div>
            <label>First Name</label>
            <input type="text" name="fname" required>
        </div>
        <div>
            <label>Last Name</label>
            <input type="text" name="lname" required>
        </div>
        <div>
            <label>Password</label>
            <input type="password" name="pass" required>
        </div>
        <div>
            <label>Role</label>
            <select name="role">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div>
            <label>Gender</label>
            <select name="gender">
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
        </div>
        <div>
            <label>Date Of Birth</label>
            <input type="date" name="dob">
        </div>
        <div>
            <label>Phone</label>
            <input type="text" name="phone">
        </div>
        <div>
            <label>Address</label>
            <textarea name="address" rows="2"></textarea>
        </div>
        <div>
            <label>Description</label>
            <textarea name="desc" rows="2"></textarea>
        </div>
        <div>
            <label>Profile Picture</label>
            <input type="file" name="pfp">
        </div>
        <div>
            <a href="users.php">Cancel</a>
            <button type="submit">Save User</button>
        </div>
    </form>
</body>
</html>
