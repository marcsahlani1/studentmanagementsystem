<?php 
require_once 'config/db.php';
$error='';
$success='';
$username = "";
$email = "";
$password = "";
$role = 'user';
$fname = "";
$lname = "";
$dob = "";
$address = "";
$phone = "";
$gender = "";
$desc = "";
$pfp = "";
if($_SERVER['REQUEST_METHOD']==='POST'){
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['pass'] ?? '');
    $role = 'user';
    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $address = trim($_POST['user_address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $desc = trim($_POST['desc'] ?? '');
    $pfp = trim($_POST['pfp'] ?? '');
    //DEBUGGING !!!!
     

    if(empty($username)||empty($email)||empty($password)||empty($fname)||empty($lname)||empty($dob)||
        empty($address)||empty($phone)||empty($gender)){
        $error="All fields are required.";
    }else{ 
        //TO DO:  check image size and validate inputs with JS
         //check if username is unique
        $test = $pdo->prepare("SELECT COUNT(*) FROM USERS WHERE username = ?");
        $test->execute([$username]);
        $count = $test->fetchColumn();
        if($count > 0){$error="Username already in use.";}
        // check if email is unique
        $test = $pdo->prepare("SELECT email FROM USERS WHERE email = ?");
        $test->execute([$email]);
        $count = $test->fetchColumn();
        if($count > 0){$error="Email already in use.";} 
        //VALIDATE DATE FORMAT
        $d = DateTime::createFromFormat('Y-m-d',$dob);//Y represents 4 digits year, y(lower) represents 2 digits year
        if(!$d || $d->format('Y-m-d') !== $dob){
            $error = "Invalid date format or date.";
        }
        //VALIDATE PROFILE PICTURE
        if(isset($_FILES['pfp']) && $_FILES['pfp']['error'] !== UPLOAD_ERR_NO_FILE){
            if($_FILES['pfp']['error'] === UPLOAD_ERR_OK){
                $file_tmp_path = $_FILES['pfp']['tmp_name'];
                $file_name = $_FILES['pfp']['name'];
                $file_size = $_FILES['pfp']['size'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg','png','gif'];
                $max_size = 2 * 1024 * 1024; //2MB
                if(!in_array($file_ext,$allowed_extensions)){
                    $error = "Invalid file format. Only JPG, JPEG, PNG and GIF.";
                }
                else if($file_size > $max_size){
                    $error = "Profile picture size should not exceed 2MB.";
                }
                else{
                    $profile_pic_name = uniqid('profile_',true) . '.' . $file_ext;
                    $upload_file_dir = 'uploads/';

                    if(!is_dir($upload_file_dir)){
                        mkdir($upload_file_dir, 0755 , true);
                    }
                    $dest_path = $upload_file_dir . $profile_pic_name;

                    if(!move_uploaded_file($file_tmp_path, $dest_path)){
                     $error = "There was an error moving the profile picture.";
                     $dest_path = "images/default.jpeg";
                    }
                }
            }else{
                  $error = "Error uploading the profile picture.";
                  $profile_pic_name = "images/default.jpeg";
            }
        }else{ 
            $profile_pic_name= "images/default.jpeg";
            }
        if($error ==""){
         $hashed_password = password_hash($password, PASSWORD_DEFAULT);
         $stmt = $pdo->prepare("INSERT INTO USERS
           (username,email,pass,user_role,fname,lname,dob,user_address,user_desc,pfp,gender,phone)
           VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
         try{
            $stmt->execute([$username,$email,$hashed_password,
              $role,$fname,$lname,$dob,$address,$desc,$profile_pic_name,$gender,$phone]);
            header("Location: login.php");
            exit;
         }catch(PDOException $e){
            $error = "Registration failed.";
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
    <title>Student Management System</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
     <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
</head>
<body>
<div class="register-container">
    
    <h2>Register New Account</h2>
    <?php if(!empty($error)): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
     <?php endif; ?> 
    <form action="register.php" method="POST" id="form" enctype="multipart/form-data">
        <div class="form-grp">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo $username; ?>" required>
        </div>
        <div class="form-grp">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo $email;?>" required>
        </div>
        <div class="form-grp">
            <label for="pass">Password:</label>
            <input type="password" id="pass" name="pass" value="<?php echo $password;?>" required>
        </div>
        <div class="form-grp">
            <label for="fname">First Name:</label>
            <input type="text" id="fname" name="fname" value="<?php echo $fname;?>" required>
        </div>
        <div class="form-grp">
            <label for="lname">Last Name:</label>
            <input type="text" id="lname" name="lname" value="<?php echo $lname;?>" required>
        </div>
        <div class="form-grp">
            <label for="phone">Phone Number:</label>
            <input type="text" id="phone" name="phone" value="<?php echo $phone;?>" required>
        </div>
        <div class="form-grp">
            <label for="gender">Gender:</label>
            <select id="gender" name="gender" required value="<?php echo $gender;?>">
                <option value="" disabled selected>Choose</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
        </div>
        <div class="form-grp">
            <label for="user_address">Address:</label>
            <input type="text" id="user_address" name="user_address" value="<?php echo $address;?>" required>
        </div>
        <div class="form-grp">
            <label for="pfp">Profile Picture:</label>
            <input type="file" id="pfp" name="pfp" accept=".jpg, .jpeg, .png, .gif" value="<?php echo $pfp;?>">
        </div>
        <div class="form-grp">
            <label for="desc">Description:</label>
            <textarea id="desc" name="desc" value="<?php echo $desc;?>"></textarea>
        </div> 
        <div class="form-grp">
            <label for="dob">Date of Birth</label>
            <input type="text" id="dob" name="dob" placeholder="YYYY-MM-DD" value="<?php echo $dob;?>" required>
        </div> 
        <button type="submit" class="btn">Register</button>
    </form>
</div>
<script src="js/validation.js"></script>
</body>
</html>
